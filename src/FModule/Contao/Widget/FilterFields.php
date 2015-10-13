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
        if(!$this->filterFields)
        {
            return '<p>'.$GLOBALS['TL_LANG']['MSC']['fm_ff_no_list'].'</p>';
        }

        if( !is_array( $this->varValue ))
        {
            $this->varValue = $this->filterFields;
        }

        if( count($this->varValue) !=  count($this->filterFields) )
        {
            $this->varValue = $this->filterFields;
        }

        $values = $this->varValue;

        if (!\Cache::has('tabindex')) {
            \Cache::set('tabindex', 1);
        }

        $tabindex = \Cache::get('tabindex');
        $tbodyTemplate = '';

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
            $appearance = is_string( $value['appearance'] ) ? deserialize( $value['appearance'] ) : $value['appearance'];

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

            $rowTemplate =
                '<tr>
					<td>
						<h4>' . $value['title'] . ':</h4>
						<input type="hidden" value="' . $value['id'] . '" name="' . $this->strName . '[' . $key . '][objID]">
						<input type="hidden" value="' . $value['title'] . '" name="' . $this->strName . '[' . $key . '][title]">
						<input type="hidden" value="' . $this->currentListID . '" name="' . $this->strName . '[' . $key . '][currentID]">
						<input type="hidden" value="' . $value['fieldID'] . '" name="' . $this->strName . '[' . $key . '][fieldID]">
						<input type="hidden" value="' . $value['type'] . '" name="' . $this->strName . '[' . $key . '][type]">
						<input type="hidden" value="'.htmlspecialchars($strAppearance).'" name="' . $this->strName . '[' . $key . '][appearance]">
						<input type="hidden" value="' . $value['isInteger'] . '" name="' . $this->strName . '[' . $key . '][isInteger]">
					</td>
					<td> <select tabindex="' . $tabindex++ . '" name="' . $this->strName . '[' . $key . '][used_appearance]" id="ctrl_' . $this->strId . '[' . $key . '][appearance]" class="tl_select" style="width: 150px;">' . $selectOptionsTemplate . '</select> </td>
					<td> <select tabindex="' . $tabindex++ . '" name="' . $this->strName . '[' . $key . '][used_templates]" id="ctrl_' . $this->strId . '[' . $key . '][template]" class="tl_select" style="width: 200px;">' . $widgetsOptionsTemplate . '</select> </td>
					<td> <input type="text" tabindex="' . $tabindex++ . '" value="' . $value['cssClass'] . '" name="' . $this->strName . '[' . $key . '][cssClass]" id="ctrl_' . $this->strId . '[' . $key . '][cssClass]" class="tl_text_2"/> </td>
					<td>' . $dragBtnTemplate . '</td>
					<td><input type="checkbox" tabindex="' . $tabindex++ . '" name="' . $this->strName . '[' . $key . '][active]" value="1" id="ctrl_' . $this->strId . '[' . $key . '][active]" class="tl_checkbox" ' . ($value['active'] ? 'checked="checked"' : '') . ' /></td>
				</tr>';

            $tbodyTemplate .= $rowTemplate;

        }

        $widgetTemplate =
            '<table class="tl_optionwizard" id="ctrl_' . $this->strId . '" >
				<thead>
					<tr>
						<th>' . $GLOBALS['TL_LANG']['MSC']['fm_ff_name'] . '</th>
						<th>' . $GLOBALS['TL_LANG']['MSC']['fm_ff_form_type'] . '</th>
						<th>' . $GLOBALS['TL_LANG']['MSC']['fm_ff_template'] . '</th>
						<th>' . $GLOBALS['TL_LANG']['MSC']['fm_ff_class'] . '</th>
						<th></th>
						<th>' . $GLOBALS['TL_LANG']['MSC']['fm_ff_active'] . '</th>
					</tr>
				</thead>
				<tbody class="sortable" data-tabindex="' . $tabindex . '">' . $tbodyTemplate . '</tbody>
			</table>';


        \Cache::set('tabindex', $tabindex);

        return $widgetTemplate;

    }
}