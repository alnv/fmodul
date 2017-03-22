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

use Contao\Widget;

/**
 * Class ModeSettings
 * @package FModule
 */
class ModeSettings extends Widget
{

    /**
     * @var bool
     */
    protected $blnSubmitInput = true;

    /**
     * @var string
     */
    protected $strTemplate = 'be_widget';

    /**
     * @var array
     */
    private $modeViewObject = array();

    /**
     * @var string
     */
    private $arrWrapper = null;

    /**
     *
     */
    public function validate()
    {
        $this->varValue = serialize($this->getPost($this->strName));
    }

    /**
     * @return string
     */
    public function generate()
    {

        $allowedDCA = array('tl_module', 'tl_page');
        $doNotSetByType = array('wrapper_field', 'legend_start', 'legend_end', 'widget', 'fulltext_search', 'map_field', 'geo_locator');
        $doNotSetByID = array('orderBy', 'sorting_fields', 'pagination', 'auto_item', 'auto_page');

        if (!in_array($this->strTable, $allowedDCA)) {
            return 'Taxonomy field is not allowed to used in ' . $this->strTable;
        }

        // contao
        if (!is_array($this->varValue)) {
            $this->varValue = array(array(''));
        }

        $this->import('Database');

        $moduleDB = null;
        if ($this->strTable == 'tl_module') {
            $moduleDB = $this->Database->prepare("SELECT f_select_module, f_select_wrapper FROM tl_module WHERE id = ?")->execute($this->currentRecord)->row();
        }

        $modulename = $moduleDB ? $moduleDB['f_select_module'] : '';
        $wrapperID = $moduleDB ? $moduleDB['f_select_wrapper'] : '';

        if ($this->strTable == 'tl_module' && (!$modulename || !$wrapperID)) {
            return '<p>Please select Backend Modul</p>';
        }

        if ($this->strTable == 'tl_module' && !$this->Database->tableExists($modulename)) {
            return '<p>' . $modulename . ' do not exist! </p>';
        }

        $modeSettingsDB = null;

        if ($this->strTable == 'tl_module') {
            $modeSettingsDB = $this->Database->prepare('SELECT tl_fmodules.tablename, tl_fmodules.id AS fmoduleID, tl_fmodules_filters.* FROM tl_fmodules JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ? ORDER BY sorting')->execute($modulename);
            $this->arrWrapper = $this->Database->prepare('SELECT * FROM ' . $modulename . ' WHERE id = ?')->execute($wrapperID)->row();
        }

        if ($this->strTable == 'tl_page') {
            $modeSettingsDB = $this->Database->prepare('SELECT tl_fmodules.tablename, tl_fmodules.id AS fmoduleID, tl_fmodules_filters.* FROM tl_fmodules JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid ORDER BY sorting')->execute();
        }

        if ($this->arrWrapper == null && $this->strTable == 'tl_page') {

            $options = array();

            while ($modeSettingsDB->next()) {

                if (in_array($modeSettingsDB->fieldID, $doNotSetByID)) {
                    continue;
                }

                if (in_array($modeSettingsDB->type, $doNotSetByType)) {
                    continue;
                }

                if ( !$this->Database->tableExists( $modeSettingsDB->tablename ) ) {
                    continue;
                }

                if ( !$this->Database->fieldExists( $modeSettingsDB->fieldID, $modeSettingsDB->tablename ) ) {
                    continue;
                }

                $objOptions = $this->Database->prepare('SELECT ' . $modeSettingsDB->fieldID . ' FROM ' . $modeSettingsDB->tablename . '')->execute();

                if ( $objOptions->numRows ) {

                    $arrRow = $objOptions->row();

                    if ( !empty( $arrRow ) && is_array( $arrRow ) ) {

                        $options[ $modeSettingsDB->fieldID ] = $arrRow[ $modeSettingsDB->fieldID ] ? $arrRow[ $modeSettingsDB->fieldID ] : [];
                    }
                }
            }

            $this->arrWrapper = $options;
            $modeSettingsDB->reset();
        }


        $savedValues = $this->varValue;

        $defaultSet = array(
            'filterValue' => '',
            'overwrite' => '0'
        );

        if ($modeSettingsDB->count() < 1) {
            return 'no fields found';
        }

        $input = array();

        foreach ($savedValues as $fid => $savedValue) {
            $input[$fid] = $savedValue;
        }

        while ($modeSettingsDB->next()) {

            if($modeSettingsDB->dataFromTaxonomy == '1' && $this->strTable == 'tl_page')
            {
                continue;
            }

            if($modeSettingsDB->reactToTaxonomy == '1' && $this->strTable == 'tl_page')
            {
                continue;
            }

            if (in_array($modeSettingsDB->fieldID, $doNotSetByID)) {
                continue;
            }

            if (in_array($modeSettingsDB->type, $doNotSetByType)) {
                continue;
            }

            $options = $this->arrWrapper[$modeSettingsDB->fieldID];

            $viewObject = array(
                "active" => ($input[$modeSettingsDB->fieldID]['active'] ? '1' : '0'),
                "fieldID" => $modeSettingsDB->fieldID,
                "type" => $modeSettingsDB->type,
                "title" => $modeSettingsDB->title,
                "negate" => $modeSettingsDB->negate,
                "description" => $modeSettingsDB->description,
                'dataFromTable' => $modeSettingsDB->dataFromTable,
                "fieldAppearance" => $modeSettingsDB->fieldAppearance,
                "isInteger" => $modeSettingsDB->isInteger,
                "addTime" => $modeSettingsDB->addTime,
                "dataFromTaxonomy" => $modeSettingsDB->dataFromTaxonomy,
                "reactToTaxonomy" => $modeSettingsDB->reactToTaxonomy,
                "reactToField" => $modeSettingsDB->reactToField,
                "options" => (!deserialize($options) ? array() : deserialize($options)),
                "set" => ($input[$modeSettingsDB->fieldID]['set'] ? $input[$modeSettingsDB->fieldID]['set'] : $defaultSet)
            );

            if ($viewObject['fieldID'] == 'address_country') {
                $countries = $this->getCountries();
                $viewObject['options'] = DiverseFunction::conformOptionsArray($countries);
            }

            $this->modeViewObject[] = $viewObject;

        }

        $return =
            '<div>
                <div>
                    <div id="ctrl_' . $this->strId . '">
                        ' . $this->setModeBlocks() . '
                    </div>
                </div>
            </div>';

        return $return;

    }

    /**
     * @return string
     */
    private function setModeBlocks()
    {
        $html = '<div class="fmode_settings">';

        $methods = array(
            'simple_choice' => 'setSimpleChoiceSettings',
            'multi_choice' => 'setMultiChoiceSettings',
            'search_field' => 'setSearchFieldSettings',
            'date_field' => 'setDateFieldSettings',
            'toggle_field' => 'setToggleFieldSettings'
        );

        foreach ($this->modeViewObject as $viewObject) {

            $str = '<div class="f_checkbox">
                       <h4><input type="checkbox" value="1" name="%s" id="%s" %s %s> <label for="%s">%s</label></h4>
                       <p class="tl_help tl_tip">' . sprintf( $GLOBALS['TL_LANG']['MSC']['fm_activate_filter'], $viewObject['title'], $viewObject['fieldID'] ) . '</p>
                    </div>';

            $name = $this->strName . '[' . $viewObject['fieldID'] . '][active]';
            $id = "ctrl_" . $viewObject['fieldID'];
            $checked = ($viewObject['active'] == '1' ? 'checked="checked"' : '');
            $attributes = $this->getAttributes();
            $for = "ctrl_" . $viewObject['fieldID'];
            $label = $viewObject['title'];
            $checkbox = sprintf($str, $name, $id, $checked, $attributes, $for, $label);

            $html = $html . $checkbox;

            if ($viewObject['active'] == '1') {

                $func = $methods[$viewObject['type']];
                $temp = call_user_func(array($this, $func), $viewObject['fieldID'], $viewObject);
                $box = '<div class="f_settings">' . $temp . '</div>';;
                $html = $html . $box;
            }
        }

        return $html . '</div>';
    }

    /**
     * @param $index
     * @param $viewObject
     * @return string
     */
    private function setToggleFieldSettings($index, $viewObject)
    {

        $desc = $viewObject['description'] ? $viewObject['description'] : $GLOBALS['TL_LANG']['MSC']['fm_criterion'];
        $selected = $viewObject['set']['filterValue'];

        $label1 = $GLOBALS['TL_LANG']['MSC']['fm_highlight_show'];
        $label2 = $GLOBALS['TL_LANG']['MSC']['fm_highlight_ignore'];
        $template =
            '<div>
				<div>
					<input type="hidden" value="' . $viewObject['fieldID'] . '" name="' . $this->strName . '[' . $index . '][fieldID]">
					<h4><label>' . $GLOBALS['TL_LANG']['MSC']['fm_highlight'] . '</label></h4>
					<select class="tl_select" value="' . $viewObject['set']['filterValue'] . '" name="' . $this->strName . '[' . $index . '][set][filterValue]">
					   <option value="1" ' . ($selected ? 'selected' : '') . '>' . $label1 . '</option>
                        <option value="" ' . (!$selected ? 'selected' : '') . '>' . $label2 . '</option>
                    </select>
                    <p class="tl_help tl_tip" title="">' . $desc . '</p>
				</div>
				<div>
				    <div class="mode_checkbox">
                        <h4><input type="checkbox" id="ctrl_' . $index . '[' . $viewObject['fieldID'] . '][ignore]" value="1" name="' . $this->strName . '[' . $index . '][set][ignore]" ' . ($viewObject['set']['ignore'] == '1' ? 'checked="checked"' : '') . '><label for="ctrl_' . $index . '[' . $viewObject['fieldID'] . '][ignore]">' . $GLOBALS['TL_LANG']['MSC']['fm_field_ignore'] . '</label></h4>
                        <p class="tl_help tl_tip" title="">' . $GLOBALS['TL_LANG']['MSC']['fm_field_ignore_desc'] . '</p>
                    </div>
                    <div class="mode_checkbox">
                        <h4><input type="checkbox" id="ctrl_' . $index . '[' . $viewObject['fieldID'] . '][overwrite]" value="1" name="' . $this->strName . '[' . $index . '][set][overwrite]" ' . ($viewObject['set']['overwrite'] == '1' ? 'checked="checked"' : '') . '><label for="ctrl_' . $index . '[' . $viewObject['fieldID'] . '][overwrite]">' . $GLOBALS['TL_LANG']['MSC']['fm_overwrite'] . '</label></h4>
                        <p class="tl_help tl_tip" title="">' . $GLOBALS['TL_LANG']['MSC']['fm_ignore'] . '</p>
                    </div>
                </div>
			</div>';

        return $template;
    }

    /**
     * @param $index
     * @param $viewObject
     * @return string
     */
    private function setSimpleChoiceSettings($index, $viewObject)
    {

        $optionsTpl = '';
        $strSubmitAttr = '';
        if ($viewObject['dataFromTaxonomy'] == '1' && $this->arrWrapper) {
            $taxonomyID = $this->arrWrapper['select_taxonomy_' . $viewObject['fieldID']];
            $viewObject['options'] = $this->getDataFromTaxonomy($taxonomyID);
            $strSubmitAttr = 'onchange="Backend.autoSubmit(' . $this->strTable . ')"';
        }

        if ($viewObject['dataFromTable'] == '1') {
            $viewObject['options'] = $this->getDataFromTable($viewObject);
        }

        $selected = $viewObject['set']['filterValue'];
        foreach ($viewObject['options'] as $option) {
            $optionsTpl = $optionsTpl . '<option value="' . $option['value'] . '" ' . ($selected == $option['value'] ? 'selected' : '') . ' >' . $option['label'] . '</option>';
        }

        $desc = $viewObject['description'] ? $viewObject['description'] : $GLOBALS['TL_LANG']['MSC']['fm_criterion'];

        $template =
            '<div>
                <div>
                    <input name="' . $this->strName . '[' . $index . '][fieldID]" value="' . $viewObject['fieldID'] . '" type="hidden">
                    <h4><label>' . $GLOBALS['TL_LANG']['MSC']['fm_select'] . '</label></h4>
                    <select class="tl_select tl_chosen" value="' . $viewObject['set']['filterValue'] . '" name="' . $this->strName . '[' . $index . '][set][filterValue]" ' . $strSubmitAttr . ' >
                        ' . $optionsTpl . '
                    </select>
                    <p class="tl_help tl_tip" title="">' . $desc . '</p>
                </div>
                <div>
                    <div class="mode_checkbox">
                        <h4><input type="checkbox" id="ctrl_' . $index . '[' . $viewObject['fieldID'] . ']" value="1" name="' . $this->strName . '[' . $index . '][set][overwrite]" ' . ($viewObject['set']['overwrite'] == '1' ? 'checked="checked"' : '') . '><label for="ctrl_' . $index . '[' . $viewObject['fieldID'] . ']">' . $GLOBALS['TL_LANG']['MSC']['fm_overwrite'] . '</label></h4>
                        <p class="tl_help tl_tip" title="">' . $GLOBALS['TL_LANG']['MSC']['fm_ignore'] . '</p>
                    </div>
                </div>
            </div>';
        return $template;
    }

    /**
     * @param $index
     * @param $viewObject
     * @return string
     */
    private function setMultiChoiceSettings($index, $viewObject)
    {

        $optionsTpl = '';

        if ($viewObject['reactToTaxonomy'] == '1' && $this->arrWrapper) {
            $viewObject['options'] = $this->getDataFromTaxonomyTags($viewObject['reactToField'], $this->arrWrapper);
        }

        if ($viewObject['dataFromTable'] == '1') {
            $viewObject['options'] = $this->getDataFromTable($viewObject);
        }

        if ($viewObject['set']['filterValue'] == '' || is_string($viewObject['set']['filterValue'])) {
            $viewObject['set']['filterValue'] = array();
        }

        foreach ($viewObject['options'] as $option) {
            $optionsTpl = $optionsTpl . '<option value="' . $option['value'] . '" ' . (in_array($option['value'], $viewObject['set']['filterValue']) ? 'selected' : '') . ' >' . $option['label'] . '</option>';
        }

        $desc = $viewObject['description'] ? $viewObject['description'] : $GLOBALS['TL_LANG']['MSC']['fm_criterion'];

        $template =
            '<div>
                <div>
                    <input name="' . $this->strName . '[' . $index . '][fieldID]" value="' . $viewObject['fieldID'] . '"type="hidden">
                    <h4><label>' . $GLOBALS['TL_LANG']['MSC']['fm_select'] . '</label></h4>
                    <select class="tl_mselect tl_chosen" multiple name="' . $this->strName . '[' . $index . '][set][filterValue][]">
                        ' . $optionsTpl . '
                    </select>
                    <p class="tl_help tl_tip" title="">' . $desc . '</p>
                </div>
                <div>
                    <div class="mode_checkbox">
                        <h4><input type="checkbox"  id="ctrl_' . $index . '[' . $viewObject['fieldID'] . ']" value="1" name="' . $this->strName . '[' . $index . '][set][overwrite]" ' . ($viewObject['set']['overwrite'] == '1' ? 'checked="checked"' : '') . '><label for="ctrl_' . $index . '[' . $viewObject['fieldID'] . ']">' . $GLOBALS['TL_LANG']['MSC']['fm_overwrite'] . '</label></h4>
                        <p class="tl_help tl_tip" title="">' . $GLOBALS['TL_LANG']['MSC']['fm_ignore'] . '</p>
                    </div>
                </div>
            </div>';
        return $template;
    }

    /**
     * @param $index
     * @param $viewObject
     * @return string
     */
    private function setDateFieldSettings($index, $viewObject)
    {

        $optionsTpl = '';

        $selected = $viewObject['set']['selected_operator'];

        foreach ($this->getOperator() as $value => $label) {
            $optionsTpl = $optionsTpl . '<option value="' . $value . '" ' . ($selected == $value ? 'selected' : '') . ' >' . $label . '</option>';
        }

        $selectOperationTpl = '<select class="tl_select" name="' . $this->strName . '[' . $index . '][set][selected_operator]">' . $optionsTpl . '</select>';
        $desc = $viewObject['description'] ? $viewObject['description'] . ' (' . $GLOBALS['TL_LANG']['MSC']['fm_date_description'] . ')' : $GLOBALS['TL_LANG']['MSC']['fm_date_description'];

        $template =
            '<div>
                <div>
                     <input name="' . $this->strName . '[' . $index . '][fieldID]" value="' . $viewObject['fieldID'] . '"type="hidden">
                     <div style="margin-bottom: 10px;">
                         <h4><label>' . $GLOBALS['TL_LANG']['MSC']['fm_operator_label'] . '</label></h4>
                        ' . $selectOperationTpl . '
                        <p class="tl_help tl_tip" title="">' . $GLOBALS['TL_LANG']['MSC']['fm_operator_description'] . '</p>
                    </div>
                    <div class="wizard">
                        <h4><label>' . $GLOBALS['TL_LANG']['MSC']['fm_date_label'] . '</label></h4>
                        <input id="ctrl_' . $viewObject['fieldID'] . '_' . $index . '" class="tl_text" name="' . $this->strName . '[' . $index . '][set][filterValue]" value="' . $viewObject['set']['filterValue'] . '" onfocus="Backend.getScrollOffset()">
                        <img src="' . (version_compare(VERSION, '4.0', '>=') ? 'assets/datepicker/images/icon.gif' : 'assets/mootools/datepicker/2.2.0/icon.gif') . '" width="20" height="20" id="toggle_' . $viewObject['fieldID'] . '" style="vertical-align:-6px;cursor:pointer">
                    </div>
                     <script>

                        window.addEvent("domready", function(){
                            new Picker.Date( $("ctrl_' . $viewObject['fieldID'] . '_' . $index . '") ,{

                                draggable: false,
                                toggle: $("toggle_' . $viewObject['fieldID'] . '"),
                                format: "%d.%m.%Y ' . ($viewObject['addTime'] ? '%H:%M' : '') . '",
                                positionOffset: {x:-211,y:-111},
                                pickerClass: "datepicker_bootstrap",
                                useFadeInOut: !Browser.ie,
                                titleFormat: "%d. %B %Y ' . ($viewObject['addTime'] ? '%H:%M' : '') . '",
                                ' . ($viewObject['addTime'] ? 'timePicker: true,' : '') . '
                            });
                        });

                     </script>
                    <p class="tl_help tl_tip">' . $desc . '</p>
                </div>
                <div>
                    <div class="mode_checkbox">
                        <h4><input type="checkbox" value="1" id="ctrl_' . $index . '[' . $viewObject['fieldID'] . ']" name="' . $this->strName . '[' . $index . '][set][overwrite]" ' . ($viewObject['set']['overwrite'] == '1' ? 'checked="checked"' : '') . '><label for="ctrl_' . $index . '[' . $viewObject['fieldID'] . ']">' . $GLOBALS['TL_LANG']['MSC']['fm_overwrite'] . '</label></h4>
                        <p class="tl_help tl_tip" title="">' . $GLOBALS['TL_LANG']['MSC']['fm_ignore'] . '</p>
                    </div>
                </div>
            </div>';

        return $template;
    }

    /**
     * @param $index
     * @param $viewObject
     * @return string
     */
    private function setSearchFieldSettings($index, $viewObject)
    {

        $optionsTpl = '';
        $selected = $viewObject['set']['selected_operator'];

        foreach ($this->getOperator() as $value => $label) {
            $optionsTpl = $optionsTpl . '<option value="' . $value . '" ' . ($selected == $value ? 'selected' : '') . ' >' . $label . '</option>';
        }

        $selectOperationTpl = '<select class="tl_select" name="' . $this->strName . '[' . $index . '][set][selected_operator]" ' . ($viewObject['isInteger'] == '1' ? '' : 'disabled') . ' >' . $optionsTpl . '</select>';
        $desc = $viewObject['description'] ? $viewObject['description'] : $GLOBALS['TL_LANG']['MSC']['fm_criterion'];

        $template =
            '<div>
                <div>
                     <input name="' . $this->strName . '[' . $index . '][fieldID]" value="' . $viewObject['fieldID'] . '"type="hidden">

                    <div style="margin-bottom: 10px;">
                         <h4><label>' . $GLOBALS['TL_LANG']['MSC']['fm_operator_label'] . '</label></h4>
                        ' . $selectOperationTpl . '
                        <p class="tl_help tl_tip" title="">' . $GLOBALS['TL_LANG']['MSC']['fm_operator_description'] . '</p>
                    </div>

                   <div>
                        <h4><label>' . $GLOBALS['TL_LANG']['MSC']['fm_select'] . '</label></h4>
                        <input class="tl_text" name="' . $this->strName . '[' . $index . '][set][filterValue]" value="' . $viewObject['set']['filterValue'] . '">
                        <p class="tl_help tl_tip" title="">' . $desc . '</p>
                   </div>

                </div>
                <div>
                    <div class="mode_checkbox">
                        <h4><input type="checkbox" value="1" id="ctrl_' . $index . '[' . $viewObject['fieldID'] . ']" name="' . $this->strName . '[' . $index . '][set][overwrite]" ' . ($viewObject['set']['overwrite'] == '1' ? 'checked="checked"' : '') . '><label for="ctrl_' . $index . '[' . $viewObject['fieldID'] . ']">' . $GLOBALS['TL_LANG']['MSC']['fm_overwrite'] . '</label></h4>
                        <p class="tl_help tl_tip" title="">' . $GLOBALS['TL_LANG']['MSC']['fm_ignore'] . '</p>
                    </div>
                </div>
            </div>';

        return $template;
    }

    /**
     * @param $viewObject
     * @return array
     */
    private function getDataFromTable($viewObject)
    {
        $arrOptions = array();

        if (!isset($viewObject['options']['table']) || !$this->Database->tableExists($viewObject['options']['table'])) {
            return $arrOptions;
        }

        $dataFromTableDB = $this->Database->prepare('SELECT ' . $viewObject['options']['col'] . ', ' . $viewObject['options']['title'] . ' FROM ' . $viewObject['options']['table'] . '')->execute();

        while ($dataFromTableDB->next()) {

            $v = $dataFromTableDB->row()[$viewObject['options']['title']];
            $k = $dataFromTableDB->row()[$viewObject['options']['col']];

            $arrOptions[] = array(
                'label' => $v,
                'value' => $k,
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
        $specieAlias = '';

        if (is_array($this->varValue)) {
            $specieAlias = isset($this->varValue[$field]['set']['filterValue']) ? $this->varValue[$field]['set']['filterValue'] : '';
        }

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
    private function getOperator()
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