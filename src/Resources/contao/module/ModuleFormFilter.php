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

class ModuleFormFilter extends \Contao\Module
{

    protected $strTemplate = 'mod_form_filter';

    public function generate()
    {
        if (TL_MODE == 'BE') {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '. $this->name .' ###';
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

        $strWidget = '';

        foreach ($fields as $i => $field) {

            $fieldname = $field['fieldID'];
            $input = Input::get($fieldname);
            $selected = $input ? $input : '';

            $skip = false;

            for ($j = 0; $j < count($modeSettings); $j++) {
                if ($modeSettings[$j]['fieldID'] == $fieldname) {
                    if (isset($modeSettings[$j]['set']['overwrite']) && $modeSettings[$j]['set']['overwrite'] == '1') {
                        $skip = true;
                    }
                }
            }

            if ($skip) {
                continue;
            }

            if (!$field['active']) {
                continue;
            }

            if ($field['overwrite'] == '1') {
                continue;
            }

            if ($fieldsDB[$fieldname] && !isset(deserialize($fieldsDB[$fieldname])['table'])) {
                $fields[$i]['options'] = deserialize($fieldsDB[$fieldname]);
            }

            if ($fieldsDB[$fieldname] && isset(deserialize($fieldsDB[$fieldname])['table'])) {
                $fields[$i]['options'] = $this->getDataFromTable($fieldsDB[$fieldname]);
            }

            $fields[$i]['tablename'] = !strpos($listModuleTable,'_data') ? $listModuleTable.'_data' : $listModuleTable;

            //set date format
            if($field['type'] == 'date_field')
            {
                $format =  $field['addTime'] ? $objPage->datimFormat : $format;
                $fields[$i]['format'] = $format;
            }

            //set size
            if($field['type'] == 'search_field' || $field['type'] == 'date_field')
            {
                $fields[$i]['operator'] = $this->getOperator();
            }

            if( $field['isInteger'] == '1' || $field['type'] == 'date_field')
            {
                $fields[$i]['selected_operator'] = Input::get($fieldname.'_int');
            }

            // get auto comletion
            if( $field['type'] == 'search_field')
            {
                $autoComplete = new FModule();
                $arr = $autoComplete->getAutoCompleteFromSearchField($listModuleTable, $fieldname, $listModuleID, $input);
                $fields[$i]['auto_complete'] = $arr;

            }

            $fields[$i]['wrapperID'] = $listModuleID;
            $fields[$i]['selected'] = $selected;
			
            $arrReplace = array('#', '<', '>', '(', ')', '\\', '=');
            $arrSearch = array('&#35;', '&#60;', '&#62;', '&#40;', '&#41;', '&#92;', '&#61;');
            $strVal = str_replace($arrSearch, $arrReplace, $fields[$i]['used_templates']);
            $strVal = str_replace(' ', '', $strVal);
            $tpl = preg_replace('/[\[{\(].*[\]}\)]/U', '', $strVal);

            $widgetTemplate = new \FrontendTemplate($tpl);
            $widgetTemplate->setData($fields[$i]);
            $strWidget .= $widgetTemplate->parse();

        }

        $strResult = '';
        $objTemplate = new \FrontendTemplate($formTemplate);
        
        $objTemplate->setData( array('widgets' => $strWidget, 'filter' => $GLOBALS['TL_LANG']['MSC']['widget_submit'] ) );
        $strResult .= $objTemplate->parse();
        
        $this->Template->cssID = $this->cssID;
        $this->Template->fields = $strResult;

    }

    private function getDataFromTable($opt)
    {
        $o = array();
        $opt = deserialize($opt);

        if (!$this->Database->tableExists($opt['table'])) {
            return $o;
        }
		
		if( $opt['col'] == '' || $opt['title'] == '' )
		{
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