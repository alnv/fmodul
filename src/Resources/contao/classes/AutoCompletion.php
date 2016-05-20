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
 
use Contao\Frontend;
use Contao\Input;

/**
 * Class AutoCompletion
 * @package FModule
 */
class AutoCompletion extends Frontend 
{

    /**
     * @param string $tablename
     * @param string $wrapperID
     * @param string $fieldID
     * @param string $dateFormat
     * @param string $timeFormat
     * @return array|string
     */
	public function getAutoCompletion($tablename = '', $wrapperID = '', $fieldID = '', $dateFormat = '', $timeFormat = '')
	{
		//
		$allowedTypes = array('search_field', 'multi_choice', 'simple_choice', 'fulltext_search', 'date_field');
		
		//
		if ((!$tablename || !$wrapperID || !$fieldID)) {
            return "No back end module found";
        }

        //
        if (!$this->Database->tableExists($tablename)) {
            return  $tablename . " do not exist";
        }
		
		//
		$dataTable = $tablename . '_data';
		
		//get items
		$resultsDB = $this->Database->prepare('SELECT * FROM ' . $dataTable . ' WHERE published = "1" AND pid = ?')->execute($wrapperID);
        
        //get field
        $fieldDB = $this->Database->prepare('SELECT * FROM tl_fmodules_filters WHERE fieldID = ?')->execute($fieldID);
        $field = $fieldDB->row();
                		
		// check if field is not empty
		if (!$fieldDB->count()) {
            return 'Field ' . $fieldID . ' do not exist';
        }
		
		//
        if (!in_array($field['type'], $allowedTypes)) {
            return 'This field type is not supported';
        }
        
        //
        $wrapperOptionsDB = null;
        
        //
        $options = array();
		
		if ($field['type'] == 'multi_choice' || $field['type'] == 'simple_choice') {
			
			//
            $wrapperOptionsDB = $this->Database->prepare('SELECT ' . $fieldID . ' FROM ' . $tablename . ' WHERE id = ?')->execute($wrapperID)->row();

			//
            if ($wrapperOptionsDB[$fieldID] && is_string($wrapperOptionsDB[$fieldID])) {
                $wrapperOptionsDB = deserialize($wrapperOptionsDB[$fieldID]);
            }
			
            //
            if (is_array($wrapperOptionsDB) && !empty($wrapperOptionsDB) && $field['dataFromTable'] != '1') {
            
                foreach ($wrapperOptionsDB as $option) {
                    $options[$option['value']] = $option['label'];
                }
            }
			
			//
            if (is_array($wrapperOptionsDB) && !empty($wrapperOptionsDB) && $field['dataFromTable'] == '1') {
            
                if ($wrapperOptionsDB['table'] && $wrapperOptionsDB['col'] && $wrapperOptionsDB['title']) {
            
                    $dataFromTableDB = $this->Database->prepare('SELECT * FROM ' . $wrapperOptionsDB['table'] . ' LIMIT 1000')->execute();
                    $optionsFromTableDB = array();

                    while ($dataFromTableDB->next()) {
                        $keyValue = $dataFromTableDB->row();
                        $optionsFromTableDB[] = array('value' => $keyValue[$wrapperOptionsDB['col']], 'label' => $keyValue[$wrapperOptionsDB['title']]);
                    }

                    if (!empty($optionsFromTableDB)) {
                        foreach ($optionsFromTableDB as $option) {
                            $options[$option['value']] = $option['label'];
                        }
                    }
                }
            }
        }

        if($field['fieldID'] == 'address_country')
        {
            $options = $this->getCountries();
        }

		//
		$autoCompletionArr = array();
		
		//
        while ($resultsDB->next()) {

            $result = $resultsDB->row();
            $items = $result[$fieldID];

            if ($field['type'] == 'multi_choice') {
	            
                $splitResults = explode(',', $items);

                foreach ($splitResults as $splitResult) {
                    $autoCompletionArr[$splitResult] = $options[$splitResult] ? $options[$splitResult] : $splitResult;
                }
            
            }

            if ($field['type'] == 'simple_choice') {
                $autoCompletionArr[$items] = $options[$items] ? $options[$items] : $items;
            }

            if ($field['type'] == 'date_field') {

                $format = $dateFormat;

                if ($field['addTime']) {
                    $format .= ' ' . $timeFormat;
                }

                $autoCompletionArr[$items] = date($format, $items);
            }

            if ($field['type'] == 'fulltext_search') {
            
                $autoCompletionArr[] = $result['title'];
            
            }

            if ($field['type'] == 'search_field' && $field['isInteger']) {
            
                $autoCompletionArr[$items] = $items;
            
            }

            if ($field['type'] == 'search_field' && !$field['isInteger']) {
            
                $itemStr = preg_replace('/[^a-z_\-0-9]/i', ' ', $items);
                $itemStr = trim($itemStr);
                $splitResults = explode(' ', $itemStr);

                foreach ($splitResults as $splitResult) {
                    $autoCompletionArr[] = $splitResult;
                }

            }

        }

        $autoCompletionArr = array_unique($autoCompletionArr);
        $autoCompletionArr = array_filter($autoCompletionArr);
        $autoCompletionArr = Input::decodeEntities($autoCompletionArr);
        
        // convert
        $returnActiveOptions = $this->dataFormatter($field['type'], $autoCompletionArr);
        
        //
        return $returnActiveOptions;
	}

    /**
     * @param $type
     * @param $autoCompletionArr
     * @return array
     */
	protected function dataFormatter($type, $autoCompletionArr)
	{
		
		//
		$returnActiveOptions = array();
		
		//
		if($type == 'fulltext_search' || $type == 'search_field')
		{
			foreach ($autoCompletionArr as $value => $label) {

            	$returnActiveOptions[] = $label;

        	}
			
			return $returnActiveOptions;
			
		}
		
		//
		foreach ($autoCompletionArr as $value => $label) {

            $returnActiveOptions[] = array(
                'label' => $label,
                'value' => $value
            );

        }
		
		//
		return $returnActiveOptions;
		
	}	
}