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

use Contao\Widget;

class FilterFields extends Widget
{

    protected $blnSubmitInput = true;

    protected $strTemplate = 'be_widget';

    public function validate()
    {
        $this->varValue = serialize($this->getPost($this->strName));
    }

    public function generate()
    {

        $arrButtons = array('drag', 'up', 'down');
        $strCommand = 'cmd_' . $this->strField;


        if (\Input::get($strCommand) && is_numeric(\Input::get('cid')) && \Input::get('id') == $this->currentRecord) {

            $this->import('Database');

            switch (\Input::get($strCommand)) {
                case 'up':
                    $this->varValue = array_move_up($this->varValue, \Input::get('cid'));
                    break;

                case 'down':
                    $this->varValue = array_move_down($this->varValue, \Input::get('cid'));
                    break;

            }

            $this->Database->prepare("UPDATE " . $this->strTable . " SET " . $this->strField . "=? WHERE id=?")->execute(serialize($this->varValue), $this->currentRecord);
            $this->redirect(preg_replace('/&(amp;)?cid=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote($strCommand, '/') . '=[^&]*/i', '', \Environment::get('request'))));

        }

        //no selected list
        if (!$this->filterFields) {
            return '<p>' . $GLOBALS['TL_LANG']['MSC']['fm_ff_no_list'] . '</p>';
        }

        if (!is_array($this->varValue)) {
            $this->varValue = $this->filterFields;
        }

        if (count($this->varValue) != count($this->filterFields)) {
            $this->varValue = $this->filterFields;
        }

        $values = $this->varValue;

        if (!\Cache::has('tabindex')) {
            \Cache::set('tabindex', 1);
        }

        $tabindex = \Cache::get('tabindex');
        $strBodyTemplate = '';

        foreach ($values as $key => $value) {

            $widgetArr = $this->getTemplateGroup('fm_widget_' . $value['type']);
            $widgetsOptionsTemplate = '';

            foreach ($widgetArr as $widget) {

                $arrReplace = array('#', '<', '>', '(', ')', '\\', '=');
                $arrSearch = array('&#35;', '&#60;', '&#62;', '&#40;', '&#41;', '&#92;', '&#61;');

                $strVal = str_replace($arrSearch, $arrReplace, $value['used_templates']);
                $widgetsOptionsTemplate .= '<option value="' . $widget . '" ' . ($strVal == $widget ? 'selected' : '') . '>' . $widget . '</option>';
            }

            $selectOptionsTemplate = '<option value="default" selected >Standard</option>';
            $appearance = is_string($value['appearance']) ? deserialize($value['appearance']) : $value['appearance'];

            if ($appearance) {

                $selectOptionsTemplate = '';

                foreach ($appearance as $v => $label) {
                    $selectOptionsTemplate .= '<option value="' . $v . '" ' . ($value['used_appearance'] == $v ? 'selected' : '') . '>' . $label . '</option>';
                }

                if ($value['type'] == 'multi_choice') {
                    $selectOptionsTemplate .= '<option value="select" ' . ($value['used_appearance'] == 'select' ? 'selected' : '') . '>Select</option>';
                }

            }

            $strAppearance = $appearance != '' ? serialize($appearance) : '';
            $dragBtnTemplate = '';

            foreach ($arrButtons as $button) {
                $class = ($button == 'up' || $button == 'down') ? ' class="button-move"' : '';
                if ($button == 'drag') {
                    $dragBtnTemplate .= \Image::getHtml('drag.gif', '', 'class="drag-handle" title="' . sprintf($GLOBALS['TL_LANG']['MSC']['move']) . '"');
                } else {
                    $dragBtnTemplate .= '<a href="' . $this->addToUrl('&amp;' . $strCommand . '=' . $button . '&amp;cid=' . $key . '&amp;id=' . $this->currentRecord) . '"' . $class . ' title="' . specialchars($GLOBALS['TL_LANG']['MSC']['ow_' . $button]) . '" onclick="Backend.optionsWizard(this,\'' . $button . '\',\'ctrl_' . $this->strId . '\');return false">' . \Image::getHtml($button . '.gif', $GLOBALS['TL_LANG']['MSC']['ow_' . $button]) . '</a> ';

                }
            }

            $strFieldTemplate =
                '<div class="fm_field_block">' .
                    '<input type="hidden" value="' . $value['id'] . '" name="' . $this->strName . '[' . $key . '][objID]">' .
                    '<input type="hidden" value="' . $value['title'] . '" name="' . $this->strName . '[' . $key . '][title]">' .
                    '<input type="hidden" value="' . $this->currentListID . '" name="' . $this->strName . '[' . $key . '][currentID]">' .
                    '<input type="hidden" value="' . $value['fieldID'] . '" name="' . $this->strName . '[' . $key . '][fieldID]">' .
                    '<input type="hidden" value="' . $value['type'] . '" name="' . $this->strName . '[' . $key . '][type]">' .
                    '<input type="hidden" value="'.htmlspecialchars($strAppearance).'" name="' . $this->strName . '[' . $key . '][appearance]">' .
                    '<input type="hidden" value="' . $value['isInteger'] . '" name="' . $this->strName . '[' . $key . '][isInteger]">' .
                    '<input type="hidden" value="' . $value['addTime'] . '" name="' . $this->strName . '[' . $key . '][addTime]">' .
                    '<input type="hidden" value="' . $value['from_field'] . '" name="' . $this->strName . '[' . $key . '][from_field]">' .
                    '<input type="hidden" value="' . $value['to_field'] . '" name="' . $this->strName . '[' . $key . '][to_field]">' .
                    '<input type="hidden" value="' . $value['description'] . '" name="' . $this->strName . '[' . $key . '][description]">' .
                    '<input type="hidden" value="' . $value['dataFromTaxonomy'] . '" name="' . $this->strName . '[' . $key . '][dataFromTaxonomy]">' .
                    '<input type="hidden" value="' . $value['reactToTaxonomy'] . '" name="' . $this->strName . '[' . $key . '][reactToTaxonomy]">' .
                    '<input type="hidden" value="' . $value['reactToField'] . '" name="' . $this->strName . '[' . $key . '][reactToField]">' .
                    '<h3 class="fm_field_block_headline" onclick="fmToggleFieldBlock(this)">' . $value['title'] . '<span class="fm_field_block_drag">' . $dragBtnTemplate . '</span></h3>' .
                    '<div class="fm_field_block_item">' .
                        '<div class="w50">' .
                            '<h3><label>' . $GLOBALS['TL_LANG']['MSC']['fm_ff_form_type'][0] . '</label></h3>' .
                            '<select tabindex="' . $tabindex++ . '" name="' . $this->strName . '[' . $key . '][used_appearance]" id="ctrl_' . $this->strId . '[' . $key . '][appearance]" class="tl_select" >' . $selectOptionsTemplate . '</select>' .
                            '<p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['MSC']['fm_ff_form_type'][1] . '</p>' .
                        '</div>' .
                        '<div  class="w50">' .
                            '<h3><label>' . $GLOBALS['TL_LANG']['MSC']['fm_ff_template'][0] . '</label></h3>' .
                            '<select tabindex="' . $tabindex++ . '" name="' . $this->strName . '[' . $key . '][used_templates]" id="ctrl_' . $this->strId . '[' . $key . '][template]" class="tl_select" >' . $widgetsOptionsTemplate . '</select>' .
                            '<p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['MSC']['fm_ff_template'][1] . '</p>' .
                        '</div>' .
                        '<div  class="w50">' .
                            '<h3><label for="ctrl_' . $this->strId . '[' . $key . '][cssClass]">' . $GLOBALS['TL_LANG']['MSC']['fm_ff_class'][0] . '</label></h3>' .
                            '<input type="text" tabindex="' . $tabindex++ . '" value="' . $value['cssClass'] . '" name="' . $this->strName . '[' . $key . '][cssClass]" id="ctrl_' . $this->strId . '[' . $key . '][cssClass]" class="tl_text"/>' .
                            '<p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['MSC']['fm_ff_class'][1] . '</p>' .
                        '</div>' .
                        '<div class="clr">' .
                            '<div class="tl_checkbox_single_container">' .
                            '<input type="checkbox" tabindex="' . $tabindex++ . '" name="' . $this->strName . '[' . $key . '][active]" value="1" id="ctrl_' . $this->strId . '[' . $key . '][active]" class="tl_checkbox" ' . ($value['active'] ? 'checked="checked"' : '') . ' />' .
                            '<label for="ctrl_' . $this->strId . '[' . $key . '][active]">' . $GLOBALS['TL_LANG']['MSC']['fm_ff_active'][0] . '</label>' .
                            '<p class="tl_help tl_tip">' . $GLOBALS['TL_LANG']['MSC']['fm_ff_active'][1] . '</p>' .
                            '</div>' .
                        '</div>' .
                    '</div>' .
                '</div>';


            $strBodyTemplate .= $strFieldTemplate;

        }

        $strWidget = '<div class="tl_optionwizard tl_filter_fields" id="ctrl_' . $this->strId . '"><div class="sortable" data-tabindex="' . $tabindex . '">' . $strBodyTemplate . '</div></div>';

        $strJS =
            '<script>
               function fmToggleFieldBlock(e) {   
                  if(typeof $ != "undefined") {
                     var tab = $(e);
                     var block = tab.getNext();
                     var isCollapsed = typeof tab.hasClass("collapsed") != "boolean" ? tab.hasClass("collapsed")[0] : tab.hasClass("collapsed");                   
                     if(isCollapsed) {
                        tab.removeClass("collapsed");
                        block.removeClass("collapsed");
                     }else{
                        tab.addClass("collapsed");
                        block.addClass("collapsed");
                     }                 
                  }                   
               } 
            </script>';

        \Cache::set('tabindex', $tabindex);

        return $strWidget.$strJS;

    }
}