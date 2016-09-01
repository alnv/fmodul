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

        $strFormTemplate = $this->f_form_template;
        $strDateFormat = $objPage->dateFormat;
        $arrPageTaxonomy = $this->getPageTaxonomy($objPage->page_taxonomy);
        $arrFEFields = $this->f_form_fields ? deserialize($this->f_form_fields) : array();
        $arrFields = array();
        $arrWidgets = array();
        $strWidget = '';
        $arrActiveOptions = $this->f_active_options ? deserialize($this->f_active_options) : array();
        $objAutoComplete = new AutoCompletion();


        // model information
        $strListViewID = $this->f_list_field;
        $objModule = $this->Database->prepare('SELECT * FROM tl_module WHERE id = ?')->execute($strListViewID)->row();
        $strModuleTableName = $objModule['f_select_module'];
        $strWrapperID = $objModule['f_select_wrapper'];

        $arrModeSettings = deserialize($objModule['f_display_mode']);
        $arrModeSettings = is_array($arrModeSettings) ? array_values($arrModeSettings) : array();

        if (!$strModuleTableName || !$strWrapperID) {
            return;
        }

        // set fields from db
        if (is_array($arrFEFields)) {

            $arrIDs = array();

            foreach ($arrFEFields as $strID => $arrFEField) {
                $arrIDs[] = $strID;
            }

            $strPlaceholder = implode(',', array_fill(0, count($arrIDs), '?'));
            $objFields = $this->Database->prepare('SELECT * FROM tl_fmodules_filters WHERE id IN (' . $strPlaceholder . ')')->execute($arrIDs);
            if ($objFields->count()) {
                while ($objFields->next()) {
                    $arrField = $objFields->row();
                    $arrFields[$arrField['id']] = $arrField;
                }
            }

            // merge field from fe and fields from db
            $_arrFields = array();
            foreach ($arrFEFields as $strID => $arrFEField) {

                // do not display if field has dependency on other field
                if($arrFEField['dependsOn']) {
                    if(!\Input::get($arrFEField['dependsOn'])) {
                        continue;
                    }
                }

                $_arrFields[$arrFEField['fieldID']] = $arrFEField;
                foreach ($arrFields[$strID] as $strKey => $strValue) {
                    $_arrFields[$arrFEField['fieldID']][$strKey] = $strValue;
                }
            }

            // replace fields array
            $arrFields = $_arrFields;
        }

        // generate action attribute
        $strAction = \Environment::get('request');
        if ($this->fm_redirect_source) {
            $type = $this->fm_redirect_source;
            if ($type == 'siteID') {
                $id = $this->fm_redirect_jumpTo;
                if ($id) {
                    $pageDB = $this->Database->prepare('SELECT * FROM tl_page WHERE id = ?')->execute($id)->row();
                }
                if (!empty($pageDB)) {
                    $strAction = $this->generateFrontendUrl($pageDB);
                }
            }

            if ($type == 'siteURL') {
                $url = $this->fm_redirect_url;
                if ($url) {
                    $strAction = $this->replaceInsertTags($url);
                }
            }
        }

        // get field values
        $arrActiveFields = array();
        $blnStartPoint = true;
        $arrNotRelateAbleFields = array('orderBy', 'sorting_fields', 'pagination');
        foreach ($arrFields as $strFieldID => $arrField) {

            $strValue = \Input::get($strFieldID) ? \Input::get($strFieldID) : '';
            $arrFields[$strFieldID]['value'] = $strValue;
            $arrFields[$strFieldID]['enable'] = false;
            $blnIsValue = QueryModel::isValue($strValue);

            // set labels
            $arrLabel = $this->setLabels($arrField);
            $arrFields[$strFieldID]['title'] = $arrLabel[0];
            $arrFields[$strFieldID]['description'] = $arrLabel[1];

            // set enable
            if ($blnIsValue) {
                $arrFields[$strFieldID]['enable'] = true;
            }

            // do not set start point
            if($this->fm_related_start_point && $blnStartPoint) {
                $blnStartPoint = false;
                continue;
            }

            // do not set
            if(in_array($arrField['fieldID'], $arrNotRelateAbleFields)) {
                continue;
            }

            $arrActiveFields[] = $strFieldID;
        }

        unset($blnStartPoint);
        $arrFilteredOptions = array();
        if ($this->fm_related_options) {

            // get only active options
            $arrQueryData = HelperModel::generateSQLQueryFromFilterArray($arrFields);
            $strQuery = $arrQueryData['qStr'];
            $qTextSearch = $arrQueryData['isFulltextSearch'] ? $arrQueryData['$qTextSearch'] : '';

            //get text search results
            $textSearchResults = array();
            if ($qTextSearch) {
                $textSearchResults = QueryModel::getTextSearchResult($qTextSearch, $strModuleTableName, $strWrapperID, $arrQueryData['searchSettings']);
            }

            // get only published items
            $qProtectedStr = ' AND published = "1"';

            // get all items
            $objList = $this->Database->prepare('SELECT * FROM ' . $strModuleTableName . '_data WHERE pid = ' . $strWrapperID . $qProtectedStr . $strQuery)->query();

            // filtered options
            $_arrFilteredOptions = array();

            while ($objList->next()) {

                $arrListItem = $objList->row();

                if ($qTextSearch) {
                    if (!$textSearchResults[$arrListItem['id']]) {
                        continue;
                    }
                }

                foreach ($arrActiveFields as $strActiveField) {
                    $arrFilteredOptions[$strActiveField] = array();
                    $arrValues = explode(',', $arrListItem[$strActiveField]);
                    $_arrFilteredOptions[$strActiveField][] = array_values($arrValues);
                }

            }

            // pluck values
            foreach ($_arrFilteredOptions as $strFieldID => $arrFilteredOption) {
                $arrFilteredOption = call_user_func_array('array_merge', $arrFilteredOption);
                $arrFilteredOption = array_unique($arrFilteredOption);
                $arrFilteredOptions[$strFieldID] = $arrFilteredOption;
            }
        }

        // set options
        $objWrapper = $this->Database->prepare('SELECT * FROM ' . $strModuleTableName . ' WHERE id = ?')->execute($strWrapperID)->row();

        foreach ($arrFields as $strFieldID => $arrField) {

            if ($arrField['type'] == 'multi_choice' || $arrField['type'] == 'simple_choice') {

                $arrWrapperOptions = $objWrapper[$strFieldID] ? deserialize($objWrapper[$strFieldID]) : array();

                if ($arrField['dataFromTaxonomy'] == '1') {
                    $arrWrapperOptions = $this->getDataFromTaxonomy($objWrapper['select_taxonomy_' . $strFieldID]);
                }

                if ($arrField['reactToTaxonomy'] == '1') {
                    $arrWrapperOptions = $this->getDataFromTaxonomyTags($arrField['reactToField'], $objWrapper);
                }

                if ($arrWrapperOptions['table'] && !in_array($strFieldID, $arrActiveOptions)) {
                    $arrFields[$strFieldID]['options'] = $this->getDataFromTable($objWrapper[$strFieldID]);
                }

                if (is_null($arrWrapperOptions['table']) && !in_array($strFieldID, $arrActiveOptions)) {
                    $arrFields[$strFieldID]['options'] = $arrWrapperOptions;
                }
            }

            // set countries
            if ($arrField['fieldID'] == 'address_country') {
                $arrCountries = $this->getCountries();
                $arrFields[$strFieldID]['options'] = DiverseFunction::conformOptionsArray($arrCountries);
            }

            // get options
            if ($strFieldID && in_array($strFieldID, $arrActiveOptions)) {

                $results = $objAutoComplete->getAutoCompletion($strModuleTableName, $strWrapperID, $strFieldID, $objPage->dateFormat, $objPage->timeFormat);

                // taxonomy tags
                if ($arrField['reactToTaxonomy'] == '1') {
                    $tempResults = array();
                    $arrValues = $this->getDataFromTaxonomyTags($arrField['reactToField'], $arrField, true);
                    foreach ($results as $result) {
                        if (!in_array($result['value'], $arrValues)) {
                            continue;
                        }
                        $tempResults[] = $result;
                    }
                    $results = $tempResults;
                    unset($tempResults);
                }

                $arrFields[$strFieldID]['options'] = is_array($results) ? $results : array();
            }
            
            if ($this->fm_related_options && is_array($arrFields[$strFieldID]['options'])) {

                $arrNewOptions = array();

                foreach ($arrFields[$strFieldID]['options'] as $intIndex => $arrKeyValue) {

                    if (is_array($arrFilteredOptions[$strFieldID]) && !in_array($arrKeyValue['value'], $arrFilteredOptions[$strFieldID])) {
                        continue;
                    }

                    $arrNewOptions[] = $arrKeyValue;
                }

                $arrFields[$strFieldID]['options'] = $arrNewOptions;
            }

            // set table name
            $arrFields[$strFieldID]['tablename'] = !strpos($strModuleTableName, '_data') ? $strModuleTableName . '_data' : $strModuleTableName;

            // date field
            if ($arrField['type'] == 'date_field') {
                $format = $arrField['addTime'] ? $objPage->datimFormat : $strDateFormat;
                $arrFields[$strFieldID]['format'] = $format;
                $arrFields[$strFieldID]['operator'] = $this->getOperator();
                $arrFields[$strFieldID]['selected_operator'] = \Input::get($strFieldID . '_int');
            }

            // search field (int)
            if ($arrField['type'] == 'search_field' && $arrField['isInteger'] == '1') {
                $arrFields[$strFieldID]['operator'] = $this->getOperator();
                $arrFields[$strFieldID]['selected_operator'] = \Input::get($strFieldID . '_int');
            }

            // search field
            if ($arrField['type'] == 'search_field') {
                //backwards compatible
                $arrFields[$strFieldID]['auto_complete'] = $arrFields[$strFieldID]['options'];
            }

            if ($arrField['type'] == 'toggle_field') {
                $arrFields[$strFieldID]['showLabel'] = $GLOBALS['TL_LANG']['MSC']['fm_highlight_show'];
                $arrFields[$strFieldID]['ignoreLabel'] = $GLOBALS['TL_LANG']['MSC']['fm_highlight_ignore'];
            }

            $arrFields[$strFieldID]['wrapperID'] = $strWrapperID;
            $arrFields[$strFieldID]['selected'] = $arrFields[$strFieldID]['value'];

            //
            if ($arrField['type'] == 'search_field' && $arrField['isInteger'] == '1') {
                if (!$arrFields[$strFieldID]['selected'] && !is_null(\Input::get($arrField['fieldID']))) {
                    $arrFields[$strFieldID]['selected'] = '';
                }
            }

            //
            if ($arrField['type'] == 'toggle_field' && !$arrFields[$strFieldID]['value']) {
                $arrFields[$strFieldID]['selected'] = '';
            }

            // set templates
            $strTemplateName = $this->parseTemplateName($arrField['used_templates']);
            $arrWidgets[$strFieldID] = array(
                'data' => $arrFields[$strFieldID],
                'tpl' => $strTemplateName
            );

        }

        foreach ($arrWidgets as $strFieldID => $arrWidget) {

            if ($arrWidget['data']['type'] == 'wrapper_field' && $arrWidget['data']['from_field'] && $arrWidget['data']['to_field']) {

                if ($arrWidget['data']['from_field'] == $arrWidget['data']['to_field']) {

                    $fromFieldData = $arrWidgets[$arrWidget['data']['from_field']]['data'];
                    $arrWidget['data']['title'] = $fromFieldData['title'];
                    $arrWidget['data']['description'] = $fromFieldData['description'];

                    $fromFieldData['operator'] = array('gte' => $GLOBALS['TL_LANG']['MSC']['f_gte']);
                    $fromFieldData['title'] = $GLOBALS['TL_LANG']['MSC']['fm_from_label'];
                    $fromFieldData['description'] = '';
                    $fromTemplateObj = new \FrontendTemplate($arrWidgets[$arrWidget['data']['from_field']]['tpl']);
                    $fromTemplateObj->setData($fromFieldData);
                    $from_template = $fromTemplateObj->parse();
                    $arrWidget['data']['from_template'] = $from_template;

                    //to
                    $toFieldData = $arrWidgets[$arrWidget['data']['to_field']]['data'];
                    $toFieldData['fieldID'] = $toFieldData['fieldID'] . '_btw';
                    $toFieldData['title'] = $GLOBALS['TL_LANG']['MSC']['fm_to_label'];
                    $toFieldData['description'] = '';
                    $selectValue = \Input::get($toFieldData['fieldID']);
                    if (!is_null($selectValue) && !$selectValue && $toFieldData['type'] != 'date_field') {
                        $selectValue = '';
                    }
                    $toFieldData['selected'] = $selectValue;
                    $toFieldData['operator'] = array('lte' => $GLOBALS['TL_LANG']['MSC']['f_lte']);
                    $toTemplateObj = new \FrontendTemplate($arrWidgets[$arrWidget['data']['to_field']]['tpl']);
                    $toTemplateObj->setData($toFieldData);
                    $to_template = $toTemplateObj->parse();
                    $arrWidget['data']['to_template'] = $to_template;

                } else {

                    // generate from field tpl
                    $fromFieldData = $arrWidgets[$arrWidget['data']['from_field']]['data'];
                    $fromFieldData['operator'] = array('gte' => $GLOBALS['TL_LANG']['MSC']['f_gte']);
                    $fromTemplateObj = new \FrontendTemplate($arrWidgets[$arrWidget['data']['from_field']]['tpl']);
                    $fromTemplateObj->setData($fromFieldData);
                    $from_template = $fromTemplateObj->parse();
                    $arrWidget['data']['from_template'] = $from_template;

                    // generate to field tpl
                    $toFieldData = $arrWidgets[$arrWidget['data']['to_field']]['data'];
                    $toFieldData['operator'] = array('lte' => $GLOBALS['TL_LANG']['MSC']['f_lte']);
                    $toTemplateObj = new \FrontendTemplate($arrWidgets[$arrWidget['data']['to_field']]['tpl']);
                    $toTemplateObj->setData($toFieldData);
                    $to_template = $toTemplateObj->parse();
                    $arrWidget['data']['to_template'] = $to_template;

                }
            }

            // sort out fixed field
            if ($this->sortOutFixedField($strFieldID, $arrModeSettings)) {
                continue;
            }

            // disable inactive fields
            if (!$arrWidget['data']['active']) {
                continue;
            }

            //
            if ($arrWidget['data']['overwrite'] == '1') {
                continue;
            }

            //
            if ($arrPageTaxonomy[$strFieldID] && $arrPageTaxonomy[$strFieldID]['set']['overwrite'] == '1') {
                continue;
            }

            $strWidgetTemplate = new \FrontendTemplate($arrWidget['tpl']);
            $strWidgetTemplate->setData($arrWidget['data']);
            $strWidget .= $strWidgetTemplate->parse();

        }


        $strResult = '';

        $objTemplate = new \FrontendTemplate($strFormTemplate);
        $objTemplate->setData(array('widgets' => $strWidget, 'filter' => $GLOBALS['TL_LANG']['MSC']['widget_submit'], 'enableSubmit' => $this->fm_disable_submit));

        $strResult .= $objTemplate->parse();

        $this->Template->action = $strAction;
        $this->Template->reset = $GLOBALS['TL_LANG']['MSC']['fm_ff_reset'];
        $this->Template->cssID = $this->cssID;
        $this->Template->fields = $strResult;

    }

    /**
     * @param $strPageTaxonomy
     * @return array|mixed|string
     */
    protected function getPageTaxonomy($strPageTaxonomy)
    {
        $arrPageTaxonomy = $strPageTaxonomy ? deserialize($strPageTaxonomy) : array();
        if (is_string($arrPageTaxonomy)) {
            $arrPageTaxonomy = array();
        }
        return $arrPageTaxonomy;
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
        if (!$taxonomyID) {
            return $arrOptions;
        }
        $taxonomiesDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE pid = ? AND published = "1"')->execute($taxonomyID);
        while ($taxonomiesDB->next()) {
            if (!$taxonomiesDB->alias) {
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

        if (!$field || !$specieAlias) {
            return $arrOptions;
        }

        $specieID = isset($fieldsDB['select_taxonomy_' . $field]) ? $fieldsDB['select_taxonomy_' . $field] : '';
        if (!$specieID) {
            return $arrOptions;
        }

        $tagsDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE pid = (SELECT id FROM tl_taxonomies WHERE alias = ? AND pid = ?)')->execute($specieAlias, $specieID);
        while ($tagsDB->next()) {
            if (!$tagsDB->alias) {
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