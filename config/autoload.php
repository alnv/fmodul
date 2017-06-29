<?php

ClassLoader::addNamespace('FModule');

ClassLoader::addClasses([

    'FModule\Initialize' => 'system/modules/fmodule/src/Resources/contao/classes/Initialize.php',
    'FModule\DCACreator' => 'system/modules/fmodule/src/Resources/contao/classes/DCACreator.php',
    'FModule\DCAModuleSettings' => 'system/modules/fmodule/src/Resources/contao/classes/DCAModuleSettings.php',
    'FModule\DCAModuleData' => 'system/modules/fmodule/src/Resources/contao/classes/DCAModuleData.php',
    'FModule\SqlData' => 'system/modules/fmodule/src/Resources/contao/classes/SqlData.php',
    'FModule\DCAHelper' => 'system/modules/fmodule/src/Resources/contao/classes/DCAHelper.php',
    'FModule\ViewContainer' => 'system/modules/fmodule/src/Resources/contao/classes/ViewContainer.php',
    'FModule\GeoCoding' => 'system/modules/fmodule/src/Resources/contao/classes/GeoCoding.php',
    'FModule\AutoCompletion' => 'system/modules/fmodule/src/Resources/contao/classes/AutoCompletion.php',
    'FModule\DiverseFunction' => 'system/modules/fmodule/src/Resources/contao/classes/DiverseFunction.php',
    'FModule\FModuleInsertTags' => 'system/modules/fmodule/src/Resources/contao/classes/FModuleInsertTags.php',
    'FModule\FieldAppearance' => 'system/modules/fmodule/src/Resources/contao/classes/FieldAppearance.php',
    'FModule\FModule' => 'system/modules/fmodule/src/Resources/contao/classes/FModule.php',
    'FModule\FModuleAjaxApi' => 'system/modules/fmodule/src/Resources/contao/classes/FModuleAjaxApi.php',
    'FModule\FModuleTranslation' => 'system/modules/fmodule/src/Resources/contao/classes/FModuleTranslation.php',
    'FModule\CleanUrls' => 'system/modules/fmodule/src/Resources/contao/classes/CleanUrls.php',
    'FModule\GalleryGenerator' => 'system/modules/fmodule/src/Resources/contao/classes/GalleryGenerator.php',
    'FModule\FModuleVerification' => 'system/modules/fmodule/src/Resources/contao/classes/FModuleVerification.php',
    'FModule\FModuleLabel' => 'system/modules/fmodule/src/Resources/contao/classes/FModuleLabel.php',

    'FModule\ModeSettings' => 'system/modules/fmodule/src/Resources/contao/widgets/ModeSettings.php',
    'FModule\FilterFields' => 'system/modules/fmodule/src/Resources/contao/widgets/FilterFields.php',
    'FModule\OptionWizardExtended' => 'system/modules/fmodule/src/Resources/contao/widgets/OptionWizardExtended.php',
    'FModule\KeyValueWizardCustom' => 'system/modules/fmodule/src/Resources/contao/widgets/KeyValueWizardCustom.php',
    'FModule\FModuleOrderByWizard' => 'system/modules/fmodule/src/Resources/contao/widgets/FModuleOrderByWizard.php',

    'FModule\FormTextFieldCustom' => 'system/modules/fmodule/src/Resources/contao/forms/FormTextFieldCustom.php',

    'FModule\ModuleDetailView' => 'system/modules/fmodule/src/Resources/contao/module/ModuleDetailView.php',
    'FModule\ModuleListView' => 'system/modules/fmodule/src/Resources/contao/module/ModuleListView.php',
    'FModule\ModuleFModuleRegistration' => 'system/modules/fmodule/src/Resources/contao/module/ModuleFModuleRegistration.php',
    'FModule\ModuleFormFilter' => 'system/modules/fmodule/src/Resources/contao/module/ModuleFormFilter.php',
    'FModule\ModuleFModuleTaxonomy' => 'system/modules/fmodule/src/Resources/contao/module/ModuleFModuleTaxonomy.php',

    'FModule\ContentModelExtend' => 'system/modules/fmodule/src/Resources/contao/model/ContentModelExtend.php',
    'FModule\QueryModel' => 'system/modules/fmodule/src/Resources/contao/models/QueryModel.php',
    'FModule\HelperModel' => 'system/modules/fmodule/src/Resources/contao/models/HelperModel.php',
    'FModule\FModuleModel' => 'system/modules/fmodule/src/Resources/contao/models/FModuleModel.php',
    'FModule\DataModel' => 'system/modules/fmodule/src/Resources/contao/models/DataModel.php',

    'FModule\AjaxApi' => 'system/modules/fmodule/src/Resources/contao/api/AjaxApi.php',

    'FModule\ProSearchApi' => 'system/modules/fmodule/src/Resources/contao/classes/ProSearchApi.php'
]);

TemplateLoader::addFiles([

    'mod_fmodule_detail' => 'system/modules/fmodule/templates',
    'mod_fmodule_list' => 'system/modules/fmodule/templates',
    'mod_form_filter' => 'system/modules/fmodule/templates',
    'mod_fmodule_map' => 'system/modules/fmodule/templates/maps',

    'fm_form_filter' => 'system/modules/fmodule/templates/form',
    'fmodule_full' => 'system/modules/fmodule/templates/list',
    'fmodule_teaser' => 'system/modules/fmodule/templates/list',

    'fm_widget_date_field' => 'system/modules/fmodule/templates/widgets',
    'fm_widget_multi_choice' => 'system/modules/fmodule/templates/widgets',
    'fm_widget_simple_choice' => 'system/modules/fmodule/templates/widgets',
    'fm_widget_search_field' => 'system/modules/fmodule/templates/widgets',
    'fm_widget_fulltext_search' => 'system/modules/fmodule/templates/widgets',
    'fm_widget_wrapper_field' => 'system/modules/fmodule/templates/widgets',
    'fm_widget_toggle_field' => 'system/modules/fmodule/templates/widgets',

    'fm_widget_geo_locator' => 'system/modules/fmodule/templates/widgets',

    'fm_map_field' => 'system/modules/fmodule/templates/maps',
    'fm_map_location' => 'system/modules/fmodule/templates/maps',

    'sign_default' => 'system/modules/fmodule//registration',
    'sign_grouped' => 'system/modules/fmodule//registration',

    'fm_field_textarea' => 'system/modules/fmodule/templates/fields',
    'fm_field_text' => 'system/modules/fmodule/templates/fields',
    'fm_field_table' => 'system/modules/fmodule/templates/fields',
    'fm_field_list' => 'system/modules/fmodule/templates/fields',

    'fm_view' => 'system/modules/fmodule/templates/inserttags',

    'mod_taxonomies' => 'system/modules/fmodule/templates/taxonomies'
]);