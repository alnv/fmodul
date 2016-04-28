<?php

ClassLoader::addNamespace('FModule');

$pathToFiles = 'system/modules/fmodule/';
if ((version_compare(VERSION, '4.0', '>=') && !$GLOBALS['FM_NO_COMPOSER'] && $GLOBALS['FM_NO_COMPOSER'] != true)) {
    $pathToFiles = 'vendor/fmodule/fmodule/';
}

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    // classes
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

    // widgets
    'FModule\OptionWizardExtended' => $pathToFiles . 'src/Resources/contao/widgets/OptionWizardExtended.php',
    'FModule\ModeSettings' => $pathToFiles . 'src/Resources/contao/widgets/ModeSettings.php',
    'FModule\FilterFields' => $pathToFiles . 'src/Resources/contao/widgets/FilterFields.php',
    'FModule\KeyValueWizardCustom' => $pathToFiles . 'src/Resources/contao/widgets/KeyValueWizardCustom.php',

    // forms
    'FModule\FormTextFieldCustom' => $pathToFiles . 'src/Resources/contao/forms/FormTextFieldCustom.php',

    // modules
    'FModule\ModuleDetailView' => $pathToFiles . 'src/Resources/contao/module/ModuleDetailView.php',
    'FModule\ModuleListView' => $pathToFiles . 'src/Resources/contao/module/ModuleListView.php',
    'FModule\ModuleFModuleRegistration' => $pathToFiles . 'src/Resources/contao/module/ModuleFModuleRegistration.php',
    'FModule\ModuleFormFilter' => $pathToFiles . 'src/Resources/contao/module/ModuleFormFilter.php',

    // models
    'FModule\ContentModelExtend' => $pathToFiles . 'src/Resources/contao/model/ContentModelExtend.php',
    'FModule\QueryModel' => $pathToFiles . 'src/Resources/contao/models/QueryModel.php',
    'FModule\HelperModel' => $pathToFiles . 'src/Resources/contao/models/HelperModel.php',
    'FModule\FModuleModel' => $pathToFiles . 'src/Resources/contao/models/FModuleModel.php',
    'FModule\DataModel' => $pathToFiles . 'src/Resources/contao/models/DataModel.php',

    // api
    'FModule\AjaxApi' => $pathToFiles . 'src/Resources/contao/api/AjaxApi.php',

    // proSearch
    'FModule\ProSearchApi' => $pathToFiles . 'src/Resources/contao/classes/ProSearchApi.php'
));

$pathToTemplates = $pathToFiles . 'templates';
if ((version_compare(VERSION, '4.0', '>=') && !$GLOBALS['FM_NO_COMPOSER'] && $GLOBALS['FM_NO_COMPOSER'] != true)) {
    $pathToTemplates = $pathToFiles . 'src/Resources/contao/templates';
}

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
    // modules
    'mod_fmodule_detail' => $pathToTemplates,
    'mod_fmodule_list' => $pathToTemplates,
    'mod_form_filter' => $pathToTemplates,
    'mod_fmodule_map' => $pathToTemplates . '/maps',

    // 2 modules
    'fm_form_filter' => $pathToTemplates . '/form',
    'fmodule_full' => $pathToTemplates . '/list',
    'fmodule_teaser' => $pathToTemplates . '/list',

    // widgets
    'fm_widget_date_field' => $pathToTemplates . '/widgets',
    'fm_widget_multi_choice' => $pathToTemplates . '/widgets',
    'fm_widget_simple_choice' => $pathToTemplates . '/widgets',
    'fm_widget_search_field' => $pathToTemplates . '/widgets',
    'fm_widget_fulltext_search' => $pathToTemplates . '/widgets',
    'fm_widget_wrapper_field' => $pathToTemplates . '/widgets',
    'fm_widget_toggle_field' => $pathToTemplates . '/widgets',

    // maps
    'fm_map_field' => $pathToTemplates . '/maps',
    'fm_map_location' => $pathToTemplates . '/maps',

    // registration
    'sign_default' => $pathToTemplates . '/registration',
    'sign_grouped' => $pathToTemplates . '/registration',

    // fields
    'fm_field_textarea' => $pathToTemplates . '/fields',
    'fm_field_text' => $pathToTemplates . '/fields',
    'fm_field_table' => $pathToTemplates . '/fields',
    'fm_field_list' => $pathToTemplates . '/fields',

    // insertTags
    'fm_view' => $pathToTemplates . '/inserttags',
));