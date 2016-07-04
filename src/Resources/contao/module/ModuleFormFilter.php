<?php namespace FModule;

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @package   F Modul
 * @author    Alexander Naumov http://www.alexandernaumov.de
 * @license   commercial
 * @copyright 2016 Alexander Naumov
 */

use Contao\Environment;
use Contao\Input;
use Contao\FrontendTemplate;

/**
 * Class ModuleFormFilter
 * @package FModule
 */
class ModuleFormFilter extends \Contao\Module
{

    /**
     * @var string
     */
    protected $strTemplate = 'mod_form_filter';

    /**
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . $this->name . ' ###';
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            return $objTemplate->parse();
        }
        return parent::generate();
    }

    /**
     *
     */
    protected function compile()
    {

        global $objPage;
        $format = $objPage->dateFormat;
        $pageTaxonomy = $objPage->page_taxonomy ? deserialize($objPage->page_taxonomy) : array();
        $fields = deserialize($this->f_form_fields);
        $listID = $this->f_list_field;
        $formTemplate = $this->f_form_template;
        $listModuleDB = $this->Database->prepare('SELECT * FROM tl_module WHERE id = ?')->execute($listID)->row();
        $listModuleTable = $listModuleDB['f_select_module'];
        $listModuleID = $listModuleDB['f_select_wrapper'];
        $modeSettings = deserialize($listModuleDB['f_display_mode']);
        $modeSettings = is_array($modeSettings) ? array_values($modeSettings) : array();
        $activeOption = $this->f_active_options ? deserialize($this->f_active_options) : array();

        if (!is_array($pageTaxonomy)) {
            $pageTaxonomy = array();
        }

        if (!$listModuleTable && !$listModuleID) {
            return;
        }

        $fieldsDB = $this->Database->prepare('SELECT * FROM ' . $listModuleTable . ' WHERE id = ?')->execute($listModuleID)->row();

        if (!is_array($fields)) {
            return;
        }

        $arrWidget = array();
        $autoComplete = new AutoCompletion();

        // generate action
        $formAction = Environment::get('request');

        // override action
        if ($this->fm_redirect_source) {
            $type = $this->fm_redirect_source;

            if ($type == 'siteID') {
                $id = $this->fm_redirect_jumpTo;
                if ($id) {
                    $pageDB = $this->Database->prepare('SELECT * FROM tl_page WHERE id = ?')->execute($id)->row();
                }
                if (!empty($pageDB)) {
                    $formAction = $this->generateFrontendUrl($pageDB);
                }
            }

            if ($type == 'siteURL') {
                $url = $this->fm_redirect_url;
                if ($url) {
                    $formAction = $this->replaceInsertTags($url);
                }
            }
        }

        foreach ($fields as $i => $field) {

            // get field id
            $fieldID = $field['fieldID'];

            // set labels
            $label = $this->setLabels($field);
            $fields[$i]['title'] = $label[0];
            $fields[$i]['description'] = $label[1];

            // get filter value
            $inputValue = Input::get($fieldID) ? Input::get($fieldID) : '';

            // get options from wrapper
            if ($field['type'] == 'multi_choice' || $field['type'] == 'simple_choice') {

                $wrapperOptions = deserialize($fieldsDB[$fieldID]);

                if ($field['dataFromTaxonomy'] == '1') {
                    $wrapperOptions = $this->getDataFromTaxonomy($fieldsDB['select_taxonomy_' . $fieldID]);
                }

                if ($field['reactToTaxonomy'] == '1') {
                    $wrapperOptions = $this->getDataFromTaxonomyTags($field['reactToField'], $fieldsDB);
                }

                if ($wrapperOptions['table'] && !in_array($fieldID, $activeOption)) {
                    $fields[$i]['options'] = $this->getDataFromTable($fieldsDB[$fieldID]);
                }

                if (is_null($wrapperOptions['table']) && !in_array($fieldID, $activeOption)) {
                    $fields[$i]['options'] = $wrapperOptions;
                }
            }

            // set countries
            if ($field['fieldID'] == 'address_country') {
                $countries = $this->getCountries();
                $fields[$i]['options'] = DiverseFunction::conformOptionsArray($countries);
            }

            // get options
            if ($fieldID && in_array($fieldID, $activeOption)) {

                $results = $autoComplete->getAutoCompletion($listModuleTable, $listModuleID, $fieldID, $objPage->dateFormat, $objPage->timeFormat);

                // taxonomy tags
                if ($field['reactToTaxonomy'] == '1') {
                    $tempResults = array();
                    $arrValues = $this->getDataFromTaxonomyTags($field['reactToField'], $fieldsDB, true);
                    foreach($results as $result)
                    {
                        if(!in_array($result['value'], $arrValues))
                        {
                            continue;
                        }
                        $tempResults[] = $result;
                    }
                    $results = $tempResults;
                    unset($tempResults);
                }

                $fields[$i]['options'] = is_array($results) ? $results : array();
            }

            // set table name
            $fields[$i]['tablename'] = !strpos($listModuleTable, '_data') ? $listModuleTable . '_data' : $listModuleTable;

            // date field
            if ($field['type'] == 'date_field') {
                $format = $field['addTime'] ? $objPage->datimFormat : $format;
                $fields[$i]['format'] = $format;
                $fields[$i]['operator'] = $this->getOperator();
                $fields[$i]['selected_operator'] = Input::get($fieldID . '_int');
            }

            // search field (int)
            if ($field['type'] == 'search_field' && $field['isInteger'] == '1') {
                $fields[$i]['operator'] = $this->getOperator();
                $fields[$i]['selected_operator'] = Input::get($fieldID . '_int');
            }

            // search field
            if ($field['type'] == 'search_field') {
                //backwards compatible
                $fields[$i]['auto_complete'] = $fields[$i]['options'];
            }

            if ($field['type'] == 'toggle_field') {
                $fields[$i]['showLabel'] = $GLOBALS['TL_LANG']['MSC']['fm_highlight_show'];
                $fields[$i]['ignoreLabel'] = $GLOBALS['TL_LANG']['MSC']['fm_highlight_ignore'];
            }

            $fields[$i]['wrapperID'] = $listModuleID;
            $fields[$i]['selected'] = $inputValue;

            //
            if ($field['type'] == 'search_field' && $field['isInteger'] == '1') {
                if (!$fields[$i]['selected'] && !is_null(Input::get($field['fieldID']))) {
                    $fields[$i]['selected'] = '';
                }
            }

            // ex
            if ($field['type'] == 'toggle_field' && !$inputValue) {
                $fields[$i]['selected'] = '';
            }

            $tplName = $this->parseTemplateName($fields[$i]['used_templates']);

            // ready for parsing
            $arrWidget[$fieldID] = array(
                'data' => $fields[$i],
                'tpl' => $tplName
            );
        }

        $strWidget = '';

        // check if tpl is enabled and parse
        foreach ($arrWidget as $fieldID => $widget) {

            //
            if ($widget['data']['type'] == 'wrapper_field' && $widget['data']['from_field'] && $widget['data']['to_field']) {

                if ($widget['data']['from_field'] == $widget['data']['to_field']) {

                    $fromFieldData = $arrWidget[$widget['data']['from_field']]['data'];
                    $widget['data']['title'] = $fromFieldData['title'];
                    $widget['data']['description'] = $fromFieldData['description'];

                    $fromFieldData['operator'] = array('gte' => $GLOBALS['TL_LANG']['MSC']['f_gte']);
                    $fromFieldData['title'] = $GLOBALS['TL_LANG']['MSC']['fm_from_label'];
                    $fromFieldData['description'] = '';
                    $fromTemplateObj = new FrontendTemplate($arrWidget[$widget['data']['from_field']]['tpl']);
                    $fromTemplateObj->setData($fromFieldData);
                    $from_template = $fromTemplateObj->parse();
                    $widget['data']['from_template'] = $from_template;

                    //to
                    $toFieldData = $arrWidget[$widget['data']['to_field']]['data'];
                    $toFieldData['fieldID'] = $toFieldData['fieldID'] . '_btw';
                    $toFieldData['title'] = $GLOBALS['TL_LANG']['MSC']['fm_to_label'];
                    $toFieldData['description'] = '';
                    $selectValue = Input::get($toFieldData['fieldID']);
                    if (!is_null($selectValue) && !$selectValue && $toFieldData['type'] != 'date_field') {
                        $selectValue = '';
                    }
                    $toFieldData['selected'] = $selectValue;
                    $toFieldData['operator'] = array('lte' => $GLOBALS['TL_LANG']['MSC']['f_lte']);
                    $toTemplateObj = new FrontendTemplate($arrWidget[$widget['data']['to_field']]['tpl']);
                    $toTemplateObj->setData($toFieldData);
                    $to_template = $toTemplateObj->parse();
                    $widget['data']['to_template'] = $to_template;

                } else {

                    // generate from field tpl
                    $fromFieldData = $arrWidget[$widget['data']['from_field']]['data'];
                    $fromFieldData['operator'] = array('gte' => $GLOBALS['TL_LANG']['MSC']['f_gte']);
                    $fromTemplateObj = new FrontendTemplate($arrWidget[$widget['data']['from_field']]['tpl']);
                    $fromTemplateObj->setData($fromFieldData);
                    $from_template = $fromTemplateObj->parse();
                    $widget['data']['from_template'] = $from_template;

                    // generate to field tpl
                    $toFieldData = $arrWidget[$widget['data']['to_field']]['data'];
                    $toFieldData['operator'] = array('lte' => $GLOBALS['TL_LANG']['MSC']['f_lte']);
                    $toTemplateObj = new FrontendTemplate($arrWidget[$widget['data']['to_field']]['tpl']);
                    $toTemplateObj->setData($toFieldData);
                    $to_template = $toTemplateObj->parse();
                    $widget['data']['to_template'] = $to_template;

                }
            }

            // sort out fixed field
            if ($this->sortOutFixedField($fieldID, $modeSettings)) {
                continue;
            }

            //if not active
            if (!$widget['data']['active']) {
                continue;
            }

            //
            if ($widget['data']['overwrite'] == '1') {
                continue;
            }

            //
            if ($pageTaxonomy[$fieldID] && $pageTaxonomy[$fieldID]['set']['overwrite'] == '1') {
                continue;
            }

            $widgetTemplate = new FrontendTemplate($widget['tpl']);
            $widgetTemplate->setData($widget['data']);
            $strWidget .= $widgetTemplate->parse();
        }

        $strResult = '';
        $objTemplate = new FrontendTemplate($formTemplate);
        $objTemplate->setData(array('widgets' => $strWidget, 'filter' => $GLOBALS['TL_LANG']['MSC']['widget_submit']));
        $strResult .= $objTemplate->parse();
        $this->Template->action = $formAction;
        $this->Template->reset = $GLOBALS['TL_LANG']['MSC']['fm_ff_reset'];
        $this->Template->cssID = $this->cssID;
        $this->Template->fields = $strResult;

    }

    /**
     * @param $field
     * @return array
     */
    private function setLabels($field)
    {
        $globLabel = $GLOBALS['TL_LANG']['tl_fmodules_language_pack'][$field['fieldID']];
        $title = $globLabel[0] ? $globLabel[0] : $field['title'];
        $description = $globLabel[1] ? $globLabel[1] : $field['description'];
        if (!$title) $title = 'no-title-set';
        if (!$description) $description = '';
        return array($title, $description);
    }

    /**
     * @param $fieldID
     * @param $modeSettings
     * @return bool
     */
    public function sortOutFixedField($fieldID, $modeSettings)
    {
        $skip = false;
        for ($j = 0; $j < count($modeSettings); $j++) {
            if ($modeSettings[$j]['fieldID'] == $fieldID) {
                if (isset($modeSettings[$j]['set']['overwrite']) && $modeSettings[$j]['set']['overwrite'] == '1') {
                    $skip = true;
                }
            }
        }
        return $skip;
    }

    /**
     * @param $usesTemplates
     * @return mixed
     */
    public function parseTemplateName($usesTemplates)
    {
        $arrReplace = array('#', '<', '>', '(', ')', '\\', '=');
        $arrSearch = array('&#35;', '&#60;', '&#62;', '&#40;', '&#41;', '&#92;', '&#61;');
        $strVal = str_replace($arrSearch, $arrReplace, $usesTemplates);
        $strVal = str_replace(' ', '', $strVal);
        return preg_replace('/[\[{\(].*[\]}\)]/U', '', $strVal);
    }

    /**
     * @param $arrTableData
     * @return array
     */
    private function getDataFromTable($arrTableData)
    {
        $arrOptions = array();
        $arrTableData = deserialize($arrTableData);

        if (!$this->Database->tableExists($arrTableData['table'])) {
            return $arrOptions;
        }

        if (!$arrTableData['col'] || !$arrTableData['title']) {
            $arrOptions[] = array('label' => '-', 'value' => '');
            return $arrOptions;
        }

        $dataFromTableDB = $this->Database->prepare('SELECT ' . $arrTableData['col'] . ', ' . $arrTableData['title'] . ' FROM ' . $arrTableData['table'] . '')->execute();

        while ($dataFromTableDB->next()) {

            $arrOptions[] = array(
                'label' => $dataFromTableDB->{$arrTableData['title']},
                'value' => $dataFromTableDB->{$arrTableData['col']},
            );

        }

        return $arrOptions;
    }

    /**
     * @param $taxonomyID
     * @return array
     */
    private function getDataFromTaxonomy($taxonomyID)
    {
        $arrOptions = array();
        if(!$taxonomyID)
        {
            return $arrOptions;
        }
        $taxonomiesDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE pid = ? AND published = "1"')->execute($taxonomyID);
        while($taxonomiesDB->next()) {
            if(!$taxonomiesDB->alias)
            {
                continue;
            }
            $arrOptions[] = array(
                'label' => $taxonomiesDB->name ? $taxonomiesDB->name : $taxonomiesDB->alias,
                'value' => $taxonomiesDB->alias,
            );
        }
        return $arrOptions;
    }

    /**
     * @param $field
     * @param $fieldsDB
     * @param bool $blnValuesOnly
     * @return array
     */
    private function getDataFromTaxonomyTags($field, $fieldsDB, $blnValuesOnly = false)
    {
        $arrOptions = array();
        $arrValues = array();
        $specieAlias = \Input::get($field);

        if(!$field || !$specieAlias)
        {
            return $arrOptions;
        }

        $specieID = isset($fieldsDB['select_taxonomy_' . $field]) ? $fieldsDB['select_taxonomy_' . $field] : '';
        if(!$specieID)
        {
            return $arrOptions;
        }

        $tagsDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE pid = (SELECT id FROM tl_taxonomies WHERE alias = ? AND pid = ?)')->execute($specieAlias, $specieID);
        while($tagsDB->next()) {
            if(!$tagsDB->alias)
            {
                continue;
            }
            $arrValues[] = $tagsDB->alias;
            $arrOptions[] = array(
                'label' => $tagsDB->name ? $tagsDB->name : $tagsDB->alias,
                'value' => $tagsDB->alias,
            );
        }
        return $blnValuesOnly ? $arrValues : $arrOptions;
    }

    /**
     * @return array
     */
    protected function getOperator()
    {
        return array(
            'eq' => $GLOBALS['TL_LANG']['MSC']['f_eq'],
            'lt' => $GLOBALS['TL_LANG']['MSC']['f_lt'],
            'lte' => $GLOBALS['TL_LANG']['MSC']['f_lte'],
            'gt' => $GLOBALS['TL_LANG']['MSC']['f_gt'],
            'gte' => $GLOBALS['TL_LANG']['MSC']['f_gte']
        );
    }
}