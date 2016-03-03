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
	 * @param $field
	 * @param $moduleObj
	 * @return array
	 */
	public function getOptions($field, $moduleObj)
	{

		$options = array();
		$hasOptions = array('multi_choice', 'simple_choice');

		if(!in_array($field['type'], $hasOptions))
		{
			return $options;
		}

		if(!$field['fieldID'])
		{
			return $options;
		}

		$id = $this->pid ? $this->pid : Input::get('id');

		if( Input::get('act') && Input::get('act') == 'editAll' )
		{
			$id = Input::get('id');
		}

		$table = $moduleObj['tablename'];

		if(!$id)
		{
			return $options;
		}

		$optionsDB = $this->Database->prepare('SELECT * FROM '.$table.' WHERE id = ?')->execute($id);

		if(!$optionsDB->count())
		{
			return $options;
		}

		$optionsArr = deserialize($optionsDB->row()[$field['fieldID']]);

		if( $field['dataFromTable'] == '1' && isset($optionsArr['table']) )
		{

			if( !$this->Database->tableExists($optionsArr['table']) )
			{
				return $options;
			}

			if( !$optionsArr['col'] || !$optionsArr['title'] )
			{
				return $options;
			}

			$DataFromTableDB = $this->Database->prepare('SELECT '.$optionsArr['col'].', '.$optionsArr['title'].' FROM '.$optionsArr['table'].'')->execute();

			while($DataFromTableDB->next())
			{
				$k = $DataFromTableDB->row()[$optionsArr['col']];
				$v = $DataFromTableDB->row()[$optionsArr['title']];
				$options[$k] = $v;
			}

			return $options;
		}

		foreach( $optionsArr as $value )
		{
			$options[$value['value']] = $value['label'];
		}

		return $options;
        
	}

	/**
	 * @param $state
	 * @return string
	 */
	public function getToggleIcon($state, $label, $fieldID, $noHTML = false)
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

	/**
	 * @param $fields
	 * @return bool
	 */
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

	/**
	 * @param $field
	 * @return array
	 */
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