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

$arrModules = [];
$objDatabase = \Database::getInstance();

if ( $objDatabase->tableExists('tl_fmodules') && empty( $arrModules ) )  {

	$objModule = $objDatabase->prepare('SELECT * FROM tl_fmodules')->execute();

	if ( $objModule->count() ) {

		while( $objModule->next() ) {

			if ( $objModule->tablename ) {

				$arrModules[] = substr( $objModule->tablename, 3, strlen( $objModule->tablename ) );
			}
		}
	}
}

$GLOBALS['TL_DCA']['tl_content']['fields']['fview'] = [

	'label' => [ "View Mode", "F Module intern view mode." ],
	'sql' => "varchar(50) NOT NULL default ''"
];

$strViewMode = \Input::get('view');

foreach( $arrModules as $strTablename ){

	if ( \Input::get('do') == $strTablename ) {

		$GLOBALS['TL_DCA']['tl_content']['config']['ptable'] = 'fm_' . $strTablename . '_data';
		$GLOBALS['TL_DCA']['tl_content']['list']['sorting']['filter'][] = [ 'fview = ?', $strViewMode ];
	}
}

$GLOBALS['TL_DCA']['tl_content']['config']['onsubmit_callback'][] = [ 'tl_content_fmodule', 'addView' ];
$GLOBALS['TL_DCA']['tl_content']['config']['oncut_callback'][] = [ 'tl_content_fmodule', 'onCutAddFView' ];
$GLOBALS['TL_DCA']['tl_content']['config']['oncopy_callback'][] = [ 'tl_content_fmodule', 'onCopyAddFView' ];
$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = [ 'tl_content_fmodule', 'redirectToView' ];


class tl_content_fmodule extends \Contao\Backend
{


	public function redirectToView( \DataContainer $dc ) {

		$strPID = \Input::get('pid');
		$strViewMode = \Input::get('view');

		if ( !$strViewMode && $strPID ) {

			$objContent = $this->Database->prepare( 'SELECT * FROM tl_content WHERE id = ?' )->limit(1)->execute( $strPID );

			if ( $objContent->numRows && $objContent->fview ) {

				$strViewMode = $objContent->fview;
				$strUri = \Environment::get('requestUri');

				\Controller::redirect( $strUri . '&view='. $strViewMode );
			}
		}
	}


	public function addView( \DataContainer $dca ) {

		$strViewMode = \Contao\Input::get('view');
		$strID = $dca->activeRecord->id;

		if ( $strViewMode ) {

			$this->Database->prepare("UPDATE tl_content SET fview = ? WHERE id = ?")->execute( $strViewMode, $strID );
		}

		return true;
	}


	public function onCutAddFView( \DataContainer $dca ) {

		$strViewMode = \Contao\Input::get('view');
		$strID = \Contao\Input::get('id');

		if ( $strID && $strViewMode ) {

			$this->Database->prepare('UPDATE tl_content SET fview = ? WHERE id = ?')->execute( $strViewMode, $strID );
		}
	}


	public function onCopyAddFView( $strID, \DataContainer $dca ) {

		$strViewMode = \Contao\Input::get('view');

		if ( $strID && $strViewMode ) {

			$this->Database->prepare('UPDATE tl_content SET fview = ? WHERE id = ?')->execute( $strViewMode, $strID );
		}
	}
}