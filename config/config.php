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

/**
 * add back end modules
 */
$GLOBALS['BE_MOD']['system']['fmodule'] = array(

    'icon' =>  (version_compare(VERSION, '4.0', '>=') ? 'bundles/fmodule/' : 'system/modules/fmodule/assets/').'icon.png',
    'tables' => array(

        'tl_fmodules',
        'tl_fmodules_filters',
        'tl_fmodules_license'
    )
);

/**
 * add front end modules
 */
array_insert($GLOBALS['FE_MOD'],5, array(
	
	'fmodule' => array(
		
		'fmodule_fe_list' => 'ModuleListView',
		'fmodule_fe_detail' => 'ModuleDetailView',
        'fmodule_fe_formfilter' => 'ModuleFormFilter'
		
	)
	
));

/**
 * widgets
 */
$GLOBALS['BE_FFL']['optionWizardExtended'] = 'OptionWizardExtended';
$GLOBALS['BE_FFL']['modeSettings'] = 'ModeSettings';
$GLOBALS['BE_FFL']['filterFields'] = 'FilterFields';


/**
 * files
 */
if (TL_MODE == 'BE') {
    $GLOBALS['TL_CSS'][] = (version_compare(VERSION, '4.0', '>=') ? 'bundles/fmodule/' : 'system/modules/fmodule/assets/') . 'stylesheet.css';
}

/**
 * add hocks
 */
$GLOBALS['TL_HOOKS']['initializeSystem'][] = array('DCACreator', 'index');
$GLOBALS['TL_HOOKS']['postLogin'][] = array('FModule', 'setLanguage');
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('FModule', 'getSearchablePages');
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('FModule', 'fm_hooks');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('FModule', 'createUserGroupDCA');
$GLOBALS['TL_HOOKS']['autoComplete'][] = array('FModule', 'getAutoCompleteAjax');

/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesp';

/**
 * check for license
 */
$validSums = new \FModule\FModule();
$license = \Contao\Config::get('fmodule_license');
if( !isset( $license ) || !in_array(md5($license), $validSums->validSums, true) )
{
    $GLOBALS['TL_HEAD'][] = '<div style="position:fixed; bottom: 0; left: 0; width: 100%; background: tomato; padding-top: 10px; padding-bottom: 10px; font-size: 14px; color: #fff; z-index: 9999; text-align: center;">F Modul Lizenz <a href="http://fmodul.alexandernaumov.de/kaufen.html" target="_blank" style="color: #fff; text-decoration: underline; font-weight: 600;">kaufen</a></div>';
}