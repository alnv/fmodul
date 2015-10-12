<?php

\Contao\ClassLoader::addNamespace('FModule');

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    'FModule\DCACreator' => 'system/modules/fmodule/classes/DCACreator.php',
    'FModule\DCAModuleSettings' => 'system/modules/fmodule/classes/DCAModuleSettings.php',
    'FModule\DCAModuleData' => 'system/modules/fmodule/classes/DCAModuleData.php',
    'FModule\SqlData' => 'system/modules/fmodule/classes/SqlData.php',
    'FModule\OptionWizardExtended' => 'system/modules/fmodule/widgets/OptionWizardExtended.php',
    'FModule\DCAHelper' => 'system/modules/fmodule/classes/DCAHelper.php',
    'FModule\ModuleDetailView' => 'system/modules/fmodule/modules/ModuleDetailView.php',
    'FModule\ModuleListView' => 'system/modules/fmodule/modules/ModuleListView.php',
    'FModule\ModeSettings' => 'system/modules/fmodule/widgets/ModeSettings.php',
    'FModule\FilterFields' => 'system/modules/fmodule/widgets/FilterFields.php',
    'FModule\ModuleFormFilter' => 'system/modules/fmodule/modules/ModuleFormFilter.php',
    'FModule\FieldAppearance' => 'system/modules/fmodule/classes/FieldAppearance.php',
    'FModule\ContentModelExtend' => 'system/modules/fmodule/models/ContentModelExtend.php',
    'FModule\FModule' => 'system/modules/fmodule/classes/FModule.php',

));

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_fmodule_detail' => 'system/modules/fmodule/templates',
	'mod_fmodule_list' => 'system/modules/fmodule/templates',
    'fm_form_filter' => 'system/modules/fmodule/templates/form',
    'fmodule_full' => 'system/modules/fmodule/templates/list',
    'fmodule_teaser' => 'system/modules/fmodule/templates/list',
    'mod_form_filter' => 'system/modules/fmodule/templates',

    'fm_widget_date_field' => 'system/modules/fmodule/templates/widgets',
    'fm_widget_multi_choice' => 'system/modules/fmodule/templates/widgets',
    'fm_widget_simple_choice' => 'system/modules/fmodule/templates/widgets',
    'fm_widget_search_field' => 'system/modules/fmodule/templates/widgets'

));