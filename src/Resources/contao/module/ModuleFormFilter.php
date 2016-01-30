<?php namespace FModule;

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   F Modul
 * @author    Alexander Naumov http://www.alexandernaumov.de
 * @license   commercial
 * @copyright 2015 Alexander Naumov
 */

use Contao\Input;
use Contao\FrontendTemplate;

class ModuleFormFilter extends \Contao\Module
{

    protected $strTemplate = 'mod_form_filter';

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

    protected function compile()
    {

        global $objPage;

        $format = $objPage->dateFormat;
        $fields = deserialize($this->f_form_fields);
        $listID = $this->f_list_field;
        $formTemplate = $this->f_form_template;
        $listModuleDB = $this->Database->prepare('SELECT * FROM tl_module WHERE id = ?')->execute($listID)->row();
        $listModuleTable = $listModuleDB['f_select_module'];
        $listModuleID = $listModuleDB['f_select_wrapper'];
        $modeSettings = deserialize($listModuleDB['f_display_mode']);
        $modeSettings = is_array($modeSettings) ? array_values($modeSettings) : array();

        if (!$listModuleTable && !$listModuleID) {
            return;
        }

        $fieldsDB = $this->Database->prepare('SELECT * FROM ' . $listModuleTable . ' WHERE id = ?')->execute($listModuleID)->row();

        if (!is_array($fields)) {

            return;

        }

        $arrWidget = array();

        foreach ($fields as $i => $field) {

            // get field id
            $fieldID = $field['fieldID'];

            // get filter value
            $inputValue = Input::get($fieldID) ? Input::get($fieldID) : '';

            // get options from wrapper
            if ($fieldsDB[$fieldID] && !isset(deserialize($fieldsDB[$fieldID])['table'])) {
                $fields[$i]['options'] = deserialize($fieldsDB[$fieldID]);
            }

            // get options from tables
            if ($fieldsDB[$fieldID] && isset(deserialize($fieldsDB[$fieldID])['table'])) {
                $fields[$i]['options'] = $this->getDataFromTable($fieldsDB[$fieldID]);
            }

            // set tablename
            $fields[$i]['tablename'] = !strpos($listModuleTable, '_data') ? $listModuleTable . '_data' : $listModuleTable;


            // set filter types

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
                $autoComplete = new FModule();
                $arr = $autoComplete->getAutoCompleteFromSearchField($listModuleTable, $fieldID, $listModuleID, $inputValue);
                $fields[$i]['auto_complete'] = $arr;

            }
			
			if($field['type'] == 'toggle_field')
			{
				$fields[$i]['showLabel'] = $field['negate'] ? $GLOBALS['TL_LANG']['MSC']['fm_highlight_ignore'] : $GLOBALS['TL_LANG']['MSC']['fm_highlight_show'];
				$fields[$i]['ignoreLabel'] = $field['negate'] ? $GLOBALS['TL_LANG']['MSC']['fm_highlight_show'] : $GLOBALS['TL_LANG']['MSC']['fm_highlight_ignore'];
			}
			
            $fields[$i]['wrapperID'] = $listModuleID;
            $fields[$i]['selected'] = $inputValue;

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

            $widgetTemplate = new FrontendTemplate($widget['tpl']);
            $widgetTemplate->setData($widget['data']);
            $strWidget .= $widgetTemplate->parse();

        }

        $strResult = '';
        $objTemplate = new FrontendTemplate($formTemplate);
        $objTemplate->setData(array('widgets' => $strWidget, 'filter' => $GLOBALS['TL_LANG']['MSC']['widget_submit']));
        $strResult .= $objTemplate->parse();
        $this->Template->reset = $GLOBALS['TL_LANG']['MSC']['fm_ff_reset'];
        $this->Template->cssID = $this->cssID;
        $this->Template->fields = $strResult;

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
     * @param $opt
     * @return array
     */
    private function getDataFromTable($opt)
    {
        $o = array();
        $opt = deserialize($opt);

        if (!$this->Database->tableExists($opt['table'])) {
            return $o;
        }

        if ($opt['col'] == '' || $opt['title'] == '') {
            $o[] = array(
                'label' => '-',
                'value' => '',
            );
            return $o;
        }

        $dataFromTableDB = $this->Database->prepare('SELECT ' . $opt['col'] . ', ' . $opt['title'] . ' FROM ' . $opt['table'] . '')->execute();

        while ($dataFromTableDB->next()) {

            $o[] = array(

                'label' => $dataFromTableDB->$opt['title'],
                'value' => $dataFromTableDB->$opt['col'],

            );

        }

        return $o;
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