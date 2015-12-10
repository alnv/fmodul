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

}