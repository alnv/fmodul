<?php

ClassLoader::addNamespace('FModule');

$pathToFiles = 'system/modules/fmodule/';

if ( ( version_compare( VERSION, '4.0', '>=' ) && !$GLOBALS['FM_NO_COMPOSER'] && $GLOBALS['FM_NO_COMPOSER'] != true ) ) {
    
    $pathToFiles = 'vendor/fmodule/fmodule/';
}

ClassLoader::addClasses([

    'FModule\Initialize' => $pathToFiles . 'src/Resources/contao/classes/Initialize.php',
    'FModule\DCACreator' => $pathToFiles . 'src/Resources/contao/classes/DCACreator.php',
    'FModule\DCAModuleSettings' => $pathToFiles . 'src/Resources/contao/classes/DCAModuleSettings.php',
    'FModule\DCAModuleData' => $pathToFiles . 'src/Resources/contao/classes/DCAModuleData.php',
    'FModule\SqlData' => $pathToFiles . 'src/Resources/contao/classes/SqlData.php',
    'FModule\DCAHelper' => $pathToFiles . 'src/Resources/contao/classes/DCAHelper.php',
    'FModule\ViewContainer' => $pathToFiles . 'src/Resources/contao/classes/ViewContainer.php',
    'FModule\GeoCoding' => $pathToFiles . 'src/Resources/contao/classes/GeoCoding.php',
    'FModule\AutoCompletion' => $pathToFiles . 'src/Resources/contao/classes/AutoCompletion.php',
    'FModule\DiverseFunction' => $pathToFiles . 'src/Resources/contao/classes/DiverseFunction.php',
    'FModule\FModuleInsertTags' => $pathToFiles . 'src/Resources/contao/classes/FModuleInsertTags.php',
    'FModule\FieldAppearance' => $pathToFiles . 'src/Resources/contao/classes/FieldAppearance.php',
    'FModule\FModule' => $pathToFiles . 'src/Resources/contao/classes/FModule.php',
    'FModule\FModuleAjaxApi' => $pathToFiles . 'src/Resources/contao/classes/FModuleAjaxApi.php',
    'FModule\FModuleTranslation' => $pathToFiles . 'src/Resources/contao/classes/FModuleTranslation.php',
    'FModule\CleanUrls' => $pathToFiles . 'src/Resources/contao/classes/CleanUrls.php',
    'FModule\GalleryGenerator' => $pathToFiles . 'src/Resources/contao/classes/GalleryGenerator.php',
    'FModule\FModuleVerification' => $pathToFiles . 'src/Resources/contao/classes/FModuleVerification.php',

    'FModule\ModeSettings' => $pathToFiles . 'src/Resources/contao/widgets/ModeSettings.php',
    'FModule\FilterFields' => $pathToFiles . 'src/Resources/contao/widgets/FilterFields.php',
    'FModule\OptionWizardExtended' => $pathToFiles . 'src/Resources/contao/widgets/OptionWizardExtended.php',
    'FModule\KeyValueWizardCustom' => $pathToFiles . 'src/Resources/contao/widgets/KeyValueWizardCustom.php',
    'FModule\FModuleOrderByWizard' => $pathToFiles . 'src/Resources/contao/widgets/FModuleOrderByWizard.php',

    'FModule\FormTextFieldCustom' => $pathToFiles . 'src/Resources/contao/forms/FormTextFieldCustom.php',

    'FModule\ModuleDetailView' => $pathToFiles . 'src/Resources/contao/module/ModuleDetailView.php',
    'FModule\ModuleListView' => $pathToFiles . 'src/Resources/contao/module/ModuleListView.php',
    'FModule\ModuleFModuleRegistration' => $pathToFiles . 'src/Resources/contao/module/ModuleFModuleRegistration.php',
    'FModule\ModuleFormFilter' => $pathToFiles . 'src/Resources/contao/module/ModuleFormFilter.php',
    'FModule\ModuleFModuleTaxonomy' => $pathToFiles . 'src/Resources/contao/module/ModuleFModuleTaxonomy.php',

    'FModule\ContentModelExtend' => $pathToFiles . 'src/Resources/contao/model/ContentModelExtend.php',
    'FModule\QueryModel' => $pathToFiles . 'src/Resources/contao/models/QueryModel.php',
    'FModule\HelperModel' => $pathToFiles . 'src/Resources/contao/models/HelperModel.php',
    'FModule\FModuleModel' => $pathToFiles . 'src/Resources/contao/models/FModuleModel.php',
    'FModule\DataModel' => $pathToFiles . 'src/Resources/contao/models/DataModel.php',

    'FModule\AjaxApi' => $pathToFiles . 'src/Resources/contao/api/AjaxApi.php',

    'FModule\ProSearchApi' => $pathToFiles . 'src/Resources/contao/classes/ProSearchApi.php'
]);

$pathToTemplates = $pathToFiles . 'templates';

if ( ( version_compare( VERSION, '4.0', '>=' ) && !$GLOBALS['FM_NO_COMPOSER'] && $GLOBALS['FM_NO_COMPOSER'] != true ) ) {

    $pathToTemplates = $pathToFiles . 'src/Resources/contao/templates';
}

TemplateLoader::addFiles([

    'mod_fmodule_detail' => $pathToTemplates,
    'mod_fmodule_list' => $pathToTemplates,
    'mod_form_filter' => $pathToTemplates,
    'mod_fmodule_map' => $pathToTemplates . '/maps',

    'fm_form_filter' => $pathToTemplates . '/form',
    'fmodule_full' => $pathToTemplates . '/list',
    'fmodule_teaser' => $pathToTemplates . '/list',

    'fm_widget_date_field' => $pathToTemplates . '/widgets',
    'fm_widget_multi_choice' => $pathToTemplates . '/widgets',
    'fm_widget_simple_choice' => $pathToTemplates . '/widgets',
    'fm_widget_search_field' => $pathToTemplates . '/widgets',
    'fm_widget_fulltext_search' => $pathToTemplates . '/widgets',
    'fm_widget_wrapper_field' => $pathToTemplates . '/widgets',
    'fm_widget_toggle_field' => $pathToTemplates . '/widgets',

    'fm_widget_geo_locator' => $pathToTemplates . '/widgets',

    'fm_map_field' => $pathToTemplates . '/maps',
    'fm_map_location' => $pathToTemplates . '/maps',

    'sign_default' => $pathToTemplates . '/registration',
    'sign_grouped' => $pathToTemplates . '/registration',

    'fm_field_textarea' => $pathToTemplates . '/fields',
    'fm_field_text' => $pathToTemplates . '/fields',
    'fm_field_table' => $pathToTemplates . '/fields',
    'fm_field_list' => $pathToTemplates . '/fields',

    'fm_view' => $pathToTemplates . '/inserttags',

    'mod_taxonomies' => $pathToTemplates . '/taxonomies'
]);