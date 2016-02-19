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

/**
 * add back end modules
 */

$GLOBALS['FM_AUTO_PATH'] = 'system/modules/fmodule/assets/';

if( (version_compare(VERSION, '4.0', '>=') && !$GLOBALS['FM_NO_COMPOSER'] && $GLOBALS['FM_NO_COMPOSER'] != true ) )
{
    $GLOBALS['FM_AUTO_PATH'] = 'bundles/fmodule/';
}

$GLOBALS['BE_MOD']['system']['fmodule'] = array(

    'icon' =>  $GLOBALS['FM_AUTO_PATH'].'icon.png',
    'tables' => array(
        'tl_fmodules',
        'tl_fmodules_filters',
        'tl_fmodules_feed',
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
    $GLOBALS['TL_CSS'][] = $GLOBALS['FM_AUTO_PATH'] . 'stylesheet.css';
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

$GLOBALS['TL_HOOKS']['removeOldFeeds'][] = array('FModule', 'purgeOldFeeds');
$GLOBALS['TL_HOOKS']['generateXmlFiles'][] = array('FModule', 'generateFeeds');

/**
 * Ajax Icon
 */
$GLOBALS['TL_MOOTOOLS'][] =
    "<script>
        if(AjaxRequest)
        {
            AjaxRequest.toggleFMField = function(el)
            {
                el.blur();
                var image = $(el).getFirst('img');
                var href = $(el).get('href');
                var tempSrc = image.get('src');
                var src = image.get('data-src');

                var featured = (image.get('data-state') == 1);

		        if (!featured) {
                    image.src = src;
                    image.set('data-src', tempSrc);
                    image.set('data-state', 1);
                    new Request({'url': href}).get({'rt': Contao.request_token});
                } else {
                    image.src = src;
                    image.set('data-src', tempSrc);
                    image.set('data-state', 0);
                    new Request({'url': href}).get({'rt':Contao.request_token});
                }

                return false;

            }
        }
    </script>";

/**
 * Add permissions
 */
$GLOBALS['TL_PERMISSIONS'][] = 'fmodules';
$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesp';

$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesfeed';
$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesfeedp';

$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesfilters';
$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesfiltersp';

// add to prosearch
$GLOBALS['PS_SEARCHABLE_MODULES']['fmodule'] = array(
    'tables' => array('tl_fmodules', 'tl_fmodules_filters'),
    'searchIn' => array('name','tablename', 'info', 'title', 'type', 'fieldID'),
    'title' => array('name','title'),
    'setCustomIcon' => array(array('ProSearchApi', 'setCustomIcon')),
    'setCustomShortcut' => array(array('ProSearchApi', 'setCustomShortcut'))
);

$GLOBALS['TL_WRAPPERS']['start'][] = 'legend_start';
$GLOBALS['TL_WRAPPERS']['stop'][] = 'legend_end';
