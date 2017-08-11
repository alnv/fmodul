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

$GLOBALS['BE_MOD']['system']['fmodule'] = [

    'icon' => 'system/modules/fmodule/assets/icon.png',
    'name' => 'F Module',

    'tables' => [

        'tl_fmodules',
        'tl_fmodules_filters',
        'tl_fmodules_feed',
        'tl_fmodules_license'
    ]
];

$GLOBALS['BE_MOD']['system']['taxonomy'] = [

    'icon' => 'system/modules/fmodule/assets/tag.png',
    'name' => 'Taxonomy',
    'tables' => [ 'tl_taxonomies' ]
];

array_insert($GLOBALS['FE_MOD'], 5, [

    'fmodule' => [

        'fmodule_fe_list' => 'ModuleListView',
        'fmodule_fe_detail' => 'ModuleDetailView',
        'fmodule_fe_formfilter' => 'ModuleFormFilter',
        'fmodule_fe_taxonomy' => 'ModuleFModuleTaxonomy',
        'fmodule_fe_registration' => 'ModuleFModuleRegistration'
    ]
]);

$GLOBALS['BE_FFL']['modeSettings'] = 'ModeSettings';
$GLOBALS['BE_FFL']['filterFields'] = 'FilterFields';
$GLOBALS['BE_FFL']['fmOrderByWizard'] = 'FModuleOrderByWizard';
$GLOBALS['BE_FFL']['optionWizardExtended'] = 'OptionWizardExtended';
$GLOBALS['BE_FFL']['keyValueWizardCustom'] = 'KeyValueWizardCustom';

if (TL_MODE == 'BE') {

    $GLOBALS['TL_CSS'][] = 'system/modules/fmodule/assets/stylesheet.css';
}

$GLOBALS['loadGoogleMapLibraries'] = false;

$GLOBALS['TL_HOOKS']['postLogin'][] = [ 'FModule', 'setLanguage' ];
$GLOBALS['TL_HOOKS']['removeOldFeeds'][] = [ 'FModule', 'purgeOldFeeds' ];
$GLOBALS['TL_HOOKS']['generateXmlFiles'][] = [ 'FModule', 'generateFeeds' ];
$GLOBALS['TL_HOOKS']['initializeSystem'][] = [ 'Initialize', 'getClasses' ];
$GLOBALS['TL_HOOKS']['autoComplete'][] = [ 'FModule', 'getAutoCompleteAjax' ];
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['FModule', 'createUserGroupDCA' ];
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = [ 'FModule', 'getSearchablePages' ];
$GLOBALS['TL_HOOKS']['getPageIdFromUrl'][] = [ 'CleanUrls', 'getPageIdFromUrlStr' ];

$GLOBALS['TL_HOOKS']['changelanguageNavigation'][] = [ 'FModuleTranslation', 'translateUrlParameters' ];
$GLOBALS['TL_HOOKS']['translateUrlParameters'][] = [ 'FModuleTranslation', 'translateUrlParametersBackwardsCompatible' ];

$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = [ 'FModuleInsertTags', 'setHooks' ];

if ( TL_MODE == 'BE' ) {

    $GLOBALS['TL_JAVASCRIPT']['FModuleJS'] = $GLOBALS['TL_CONFIG']['debugMode']
        ? 'system/modules/fmodule/assets/FModule.js'
        : 'system/modules/fmodule/assets/FModule.js';
}

$GLOBALS['TL_PERMISSIONS'][] = 'fmodules';
$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesp';

$GLOBALS['TL_PERMISSIONS'][] = 'taxonomies';
$GLOBALS['TL_PERMISSIONS'][] = 'taxonomiesp';

$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesfeed';
$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesfeedp';

$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesfilters';
$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesfiltersp';

$GLOBALS['PS_SEARCHABLE_MODULES']['fmodule'] = [

    'title' => [ 'name', 'title' ],
    'tables' => [ 'tl_fmodules', 'tl_fmodules_filters' ],
    'setCustomIcon' => [ [ 'ProSearchApi', 'setCustomIcon' ] ],
    'setCustomShortcut' => [ [ 'ProSearchApi', 'setCustomShortcut' ] ],
    'searchIn' => [ 'name', 'tablename', 'info', 'title', 'type', 'fieldID' ]
];

$GLOBALS['TL_WRAPPERS']['start'][] = 'legend_start';
$GLOBALS['TL_WRAPPERS']['stop'][] = 'legend_end';