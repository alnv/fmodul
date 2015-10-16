<?php

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

$fmodules = &$GLOBALS['BE_MOD']['fmodules'];

$GLOBALS['TL_DCA']['tl_content']['fields']['fview'] = array(

	'sql' => "varchar(50) NOT NULL default ''"

);

$view = \Input::get('view');

if(!$fmodules)
{
	return;
}

foreach($fmodules as $name => $module){

    if (\Input::get('do') == $name)
    {
        $GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'fm_'.$name.'_data';
		$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['filter'][] = array('fview = ?', $view);
    }
}

$GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'][] = array('tl_content_extend', 'addView');
$GLOBALS['TL_DCA']['tl_content']['config']['oncopy_callback'][] = array('tl_content_extend', 'onCopyAddfView');
$GLOBALS['TL_DCA']['tl_content']['config']['oncut_callback'][] = array('tl_content_extend', 'onCutAddfView');

/**
 * Class tl_content_extend
 */
class tl_content_extend extends \Contao\Backend
{

	/**
	 * @param DataContainer $dc
	 * @return bool
	 */
	public function addView(DataContainer $dc)
	{
		$view = \Input::get('view');
					
		if($view)
		{
			$id = $dc->activeRecord->id;			
			$this->Database->prepare("UPDATE tl_content SET fview = ? WHERE id = ? LIMIT 1")->execute($view, $id);
		}
				
		return true;
						
	}

	/**
	 * @param DataContainer $dc
	 */
	public function onCutAddfView(DataContainer $dc)
	{
		$view = \Input::get('view');
		$id = \Input::get('id');

		if( $id != '' && $view != '' )
		{
			$this->Database->prepare('UPDATE tl_content SET fview = ? WHERE id = ? LIMIT 1')->execute($view, $id);
		}
	}

	/**
	 * @param $id
	 * @param DataContainer $dc
	 */
	public function onCopyAddfView($id, DataContainer $dc)
	{
		$view = \Input::get('view');

		if( $id != '' && $view != '' )
		{
			$this->Database->prepare('UPDATE tl_content SET fview = ? WHERE id = ? LIMIT 1')->execute($view, $id);
		}
	}

}