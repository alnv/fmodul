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
		$table = $moduleObj['tablename'];

		if(!$field['fieldID'])
		{
			return $options;
		}

		if(!in_array($field['type'], $hasOptions))
		{
			return $options;
		}

		$id = $this->pid ? $this->pid : Input::get('id');

		if( Input::get('act') && Input::get('act') == 'editAll' )
		{
			$id = Input::get('id');
		}

		$optionsDB = $this->Database->prepare('SELECT * FROM '.$table.'')->execute();

		$option = array();

		while($optionsDB->next())
		{
			if(!$id)
			{
				continue;
			}

			if($optionsDB->id != $id)
			{
				continue;
			}

			$option = $optionsDB->row()[$field['fieldID']] ? deserialize($optionsDB->row()[$field['fieldID']]) : array();

		}

		if($field['dataFromTable'] == '1')
		{
			if(!$option['table'])
			{
				return $options;
			}

			if( !$this->Database->tableExists($option['table']) )
			{
				return $options;
			}

			if( !$option['col'] || !$option['title'] )
			{
				return $options;
			}

			$DataFromTableDB = $this->Database->prepare('SELECT '.$option['col'].', '.$option['title'].' FROM '.$option['table'].'')->execute();

			while($DataFromTableDB->next())
			{
				$k = $DataFromTableDB->row()[$option['col']];
				$v = $DataFromTableDB->row()[$option['title']];
				$options[$k] = $v;
			}

			return $options;
		}

		foreach( $option as $value )
		{

			if(!$value['value'])
			{
				continue;
			}

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

}