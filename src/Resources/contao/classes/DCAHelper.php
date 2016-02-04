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

use Contao\Database;
use Contao\Backend;
use Contao\Image;
use Contao\Input;

/**
 * Class DCAHelper
 * @package FModule
 */
class DCAHelper extends Backend
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->import('BackendUser', 'User');
	}
	
	/**
	 *
	 */
	public function getOptions($field)
	{
		
		$options = array();
		$id = $this->pid;

		if( Input::get('act') && Input::get('act') == 'editAll' )
		{
			$id = Input::get('id');
		}

		if($field['fieldID'] == '')
		{
			return $options;
		}

		if($field['type'] == 'fulltext_search')
		{
			return $options;
		}

		$optionsDB = deserialize( Database::getInstance()->prepare("SELECT ".$field['fieldID']." FROM ".$this->parent." WHERE id = ?")->execute($id)->row()[$field['fieldID']] );

		if( count( $optionsDB ) <= 0 )
		{
			return $options;
		}

		if( $field['dataFromTable'] == '1' && isset($optionsDB['table']) )
		{

			if( !Database::getInstance()->tableExists($optionsDB['table']) )
			{
				return $options;
			}
			
			if( $optionsDB['col'] == '' || $optionsDB['title'] == '' )
			{
				return $options;
			}
			
			$DataFromTableDB = Database::getInstance()->prepare('SELECT '.$optionsDB['col'].', '.$optionsDB['title'].' FROM '.$optionsDB['table'].'')->execute();

			while($DataFromTableDB->next())
			{
				$k = $DataFromTableDB->row()[$optionsDB['col']];
				$v = $DataFromTableDB->row()[$optionsDB['title']];
				$options[$k] = $v;
			}

			return $options;
		}

		foreach( $optionsDB as $value )
		{
			$options[$value['value']] = $value['label'];
		}

    	return $options;  
        
	}

	/**
	 * @param $state
	 * @return string
	 */
	public function getToogleIcon($state, $label, $fieldID, $noHTML = false)
	{

		$src = $state ? 'files/fmodule/assets/'.$fieldID.'.' : 'files/fmodule/assets/'.$fieldID.'_.';
		$temp = $state ? 'files/fmodule/assets/'.$fieldID.'_.' : 'files/fmodule/assets/'.$fieldID.'.';

		$allowedFormat = array('gif', 'png', 'svg');

		foreach($allowedFormat as $format)
		{

			if (is_file(TL_ROOT .'/'. $src.$format) && !$noHTML)
			{
				return Image::getHtml($src.$format, $label, 'data-src="'.$temp.$format.'" data-state="' . ($state ? 1 : 0) . '"');
			}

			if (is_file(TL_ROOT .'/'. $src.$format) && $noHTML)
			{
				return $src.$format;
			}

		}

		$icon = $state ? 'featured.gif': 'featured_.gif';
		$nIcon = $state ? 'featured_.gif': 'featured.gif';

		$temp = 'system/themes/' . Backend::getTheme() . '/images/'.$nIcon ;
		$src = 'system/themes/' . Backend::getTheme() . '/images/'.$icon ;

		if($noHTML)
		{
			return $src;
		}

		return Image::getHtml($src, $label, 'data-src="'.$temp.'" data-state="' . ($state ? 1 : 0) . '"');

	}


	public function isLegend($fields)
	{
		$legendsFound = 0;
		foreach($fields as $field)
		{
			if($field['type'] == 'legend_start' || $field['type'] == 'legend_end')
			{
				$legendsFound += 1;
			}
		}

		if($legendsFound > 0 && $legendsFound % 2 == 0)
		{
			return true;
		}

		return false;

	}


	public function getFieldFromWidget($field)
	{

		$widgetArr = explode('.', $field['widget_type']);
		$mandatory = $field['isMandatory'] ? true: false;

		$return = array(

			'label' => array($field['title'], $field['description']),
			'inputType' => 'text',
			'exclude' => true,
			'eval' => array('mandatory' => $mandatory),
			'sql' => "text NULL"

		);

		if($widgetArr[0] == 'textarea' && $widgetArr[1] == 'blank')
		{
			$return['inputType'] = 'textarea';
		}

		if($widgetArr[0] == 'textarea' && $widgetArr[1] == 'tinyMCE')
		{
			$return['inputType'] = 'textarea';
			$return['eval']['rte'] = 'tinyMCE';
		}

		if($widgetArr[0] == 'list' && $widgetArr[1] == 'blank')
		{
			$return['inputType'] = 'listWizard';
		}

		if($widgetArr[0] == 'list' && $widgetArr[1] == 'keyValue')
		{
			$return['inputType'] = 'keyValueWizard';
		}

		if($widgetArr[0] == 'table' && $widgetArr[1] == 'blank')
		{
			$return['inputType'] = 'tableWizard';
			$return['eval']['allowHtml'] = true;
			$return['eval']['doNotSaveEmpty'] = true;
			$return['eval']['style'] = 'width:142px;height:66px';
		}

		return $return;

	}

}