<?php

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

use Contao\Input;
use Contao\Backend;
use Contao\Database;

//
$modules = array();
$database = Database::getInstance();
if($database->tableExists('tl_fmodules') && empty($modules))
{
	$moduleDB = $database->prepare('SELECT * FROM tl_fmodules')->execute();
	if($moduleDB->count())
	{
		while($moduleDB->next())
		{
			if($moduleDB->tablename)
			{
				$modules[] = substr($moduleDB->tablename, 3, strlen($moduleDB->tablename));
			}
		}
	}
}


//
$GLOBALS['TL_DCA']['tl_content']['fields']['fview'] = array(
	'label' => array("View", ""),
	'sql' => "varchar(50) NOT NULL default ''"
);

//
$view = Input::get('view');

//
foreach($modules as $tablename){

	if (Input::get('do') == $tablename)
	{
		$GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'fm_'.$tablename.'_data';
		$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['filter'][] = array('fview = ?', $view);
	}
}

//
$GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'][] = array('tl_content_fmodule', 'addView');
$GLOBALS['TL_DCA']['tl_content']['config']['oncopy_callback'][] = array('tl_content_fmodule', 'onCopyAddFView');
$GLOBALS['TL_DCA']['tl_content']['config']['oncut_callback'][] = array('tl_content_fmodule', 'onCutAddFView');

/**
 * Class tl_content_extend
 */
class tl_content_fmodule extends Backend
{

	/**
	 * @param DataContainer $dca
	 * @return bool
	 */
	public function addView(DataContainer $dca)
	{
		$view = Input::get('view');
		$id = $dca->activeRecord->id;

		if($view)
		{
			$this->Database->prepare("UPDATE tl_content SET fview = ? WHERE id = ? LIMIT 1")->execute($view, $id);
		}
		return true;
	}

	/**
	 * @param DataContainer $dca
	 */
	public function onCutAddFView(DataContainer $dca)
	{
		$view = Input::get('view');
		$id = Input::get('id');

		if($id && $view)
		{
			$this->Database->prepare('UPDATE tl_content SET fview = ? WHERE id = ? LIMIT 1')->execute($view, $id);
		}
	}

	/**
	 * @param $id
	 * @param DataContainer $dca
	 */
	public function onCopyAddFView($id, DataContainer $dca)
	{
		$view = Input::get('view');

		if($id && $view)
		{
			$this->Database->prepare('UPDATE tl_content SET fview = ? WHERE id = ? LIMIT 1')->execute($view, $id);
		}
	}
}