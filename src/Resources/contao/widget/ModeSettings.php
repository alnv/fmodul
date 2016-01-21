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
use Contao\Widget;

class ModeSettings extends Widget
{

    protected $blnSubmitInput = true;

    protected $strTemplate = 'be_widget';

    private $modeViewObject = array();

    public function validate()
    {
        $this->varValue = serialize($this->getPost($this->strName));
    }

    public function generate()
    {

        if ($this->strTable != 'tl_module') {
            return '<p>ModeSetting is not enable</p>';
        }

        if (!is_array($this->varValue)) {
            $this->varValue = array(array(''));
        }

        $this->import('Database');

        $fmoduleDB = $this->Database->prepare("SELECT f_select_module, f_select_wrapper FROM " . $this->strTable . " WHERE id = ?")->execute($this->currentRecord)->row();

        $modulename = $fmoduleDB['f_select_module'];
        $wrapperid = $fmoduleDB['f_select_wrapper'];

        if ($modulename == '' || $wrapperid == '') {
            return '<p>No Module selected</p>';
        }

        if (!$this->Database->tableExists($modulename)) {
            return '<p>' . $modulename . ' dont exist! </p>';
        }

        $modeSettingsDB = $this->Database->prepare(
            'SELECT tl_fmodules.id AS fmoduleID, tl_fmodules_filters.*
            FROM tl_fmodules
            JOIN tl_fmodules_filters
            ON tl_fmodules.id = tl_fmodules_filters.pid
            WHERE tablename = ?'
        )->execute($modulename);

        $optionsDB = $this->Database->prepare('SELECT * FROM ' . $modulename . ' WHERE id = ?')->execute($wrapperid)->row();

        $index = 0;
        $input = $this->varValue;

        $defaultSet = array(
            'filterValue' => '',
            'overwrite' => '0'
        );

        while ($modeSettingsDB->next()) {

            if ($modeSettingsDB->fieldID == 'orderBy' || $modeSettingsDB->fieldID == 'sorting_fields' || $modeSettingsDB->fieldID == 'pagination') {
                continue;
            }

            $options = $optionsDB[$modeSettingsDB->fieldID];

            $viewObject = array(
                "active" => ($input[$index]['active'] ? '1' : '0'),
                "fieldID" => $modeSettingsDB->fieldID,
                "type" => $modeSettingsDB->type,
                "title" => $modeSettingsDB->title,
                "description" => $modeSettingsDB->description,
                'dataFromTable' => $modeSettingsDB->dataFromTable,
                "fieldAppearance" => $modeSettingsDB->fieldAppearance,
                "isInteger" => $modeSettingsDB->isInteger,
                "addTime" => $modeSettingsDB->addTime,
                "options" => (!deserialize($options) ? array() : deserialize($options)),
                "set" => ($input[$index]['set'] ? $input[$index]['set'] : $defaultSet)
            );

            $this->modeViewObject[] = $viewObject;
            $index++;
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


    private function setModeBlocks()
    {
        $html = '<div class="fmode_settings">';

        $methods = array(
            'simple_choice' => 'setSimpleChoiceSettings',
            'multi_choice' => 'setMultiChoiceSettings',
            'search_field' => 'setSearchFieldSettings',
            'date_field' => 'setDateFieldSettings'
        );

        foreach ($this->modeViewObject as $index => $viewObject) {

            if ($viewObject['type'] == 'fulltext_search') {
                continue;
            }

            $str = '<div class="f_checkbox">
                       <h4><input type="checkbox" value="1" name="%s" id="%s" %s %s> <label for="%s">%s</label></h4>
                       <p class="tl_help tl_tip" title="">' . $GLOBALS['TL_LANG']['MSC']['fm_activate_filter'] . '</p>
                    </div>';

            $name = $this->strName . '[' . $index . '][active]';
            $id = "ctrl_" . $viewObject['fieldID'];
            $checked = ($viewObject['active'] == '1' ? 'checked="checked"' : '');
            $attributes = $this->getAttributes();
            $for = "ctrl_" . $viewObject['fieldID'];
            $label = $viewObject['title'];

            $checkbox = sprintf($str, $name, $id, $checked, $attributes, $for, $label, $span);

            $html = $html . $checkbox;

            if ($viewObject['active'] == '1') {

                $func = $methods[$viewObject['type']];
                $temp = call_user_func(array($this, $func), $index, $viewObject);
                $box = '<div class="f_settings">' . $temp . '</div>';;
                $html = $html . $box;
            }

        }

        return $html . '</div>';
    }


    private function setSimpleChoiceSettings($index, $viewObject)
    {

        $optionsTpl = '';

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
                    <input name="' . $this->strName . '[' . $index . '][fieldID]" value="' . $viewObject['fieldID'] . '"type="hidden">
                    <h4><label>' . $GLOBALS['TL_LANG']['MSC']['fm_select'] . '</label></h4>
                    <select class="tl_select" value="' . $viewObject['set']['filterValue'] . '" name="' . $this->strName . '[' . $index . '][set][filterValue]">
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

    private function setMultiChoiceSettings($index, $viewObject)
    {

        $optionsTpl = '';

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

    private function getDataFromTable($viewObject)
    {
        $o = array();

        if (!isset($viewObject['options']['table']) || !$this->Database->tableExists($viewObject['options']['table'])) {
            return $o;
        }

        $dataFromTableDB = $this->Database->prepare('SELECT ' . $viewObject['options']['col'] . ', ' . $viewObject['options']['title'] . ' FROM ' . $viewObject['options']['table'] . '')->execute();

        while ($dataFromTableDB->next()) {

            $v = $dataFromTableDB->row()[$viewObject['options']['title']];
            $k = $dataFromTableDB->row()[$viewObject['options']['col']];

            $o[] = array(
                'label' => $v,
                'value' => $k,
            );
        }

        return $o;
    }


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