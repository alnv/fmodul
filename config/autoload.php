<?php

\Contao\ClassLoader::addNamespace('FModule');

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    'FModule\DCACreator' => 'system/modules/fmodule/src/Resources/contao/classes/DCACreator.php',
    'FModule\DCAModuleSettings' => 'system/modules/fmodule/src/Resources/contao/classes/DCAModuleSettings.php',
    'FModule\DCAModuleData' => 'system/modules/fmodule/src/Resources/contao/classes/DCAModuleData.php',
    'FModule\SqlData' => 'system/modules/fmodule/src/Resources/contao/classes/SqlData.php',
    'FModule\OptionWizardExtended' => 'system/modules/fmodule/src/Resources/contao/widget/OptionWizardExtended.php',
    'FModule\DCAHelper' => 'system/modules/fmodule/src/Resources/contao/classes/DCAHelper.php',
    'FModule\ModuleDetailView' => 'system/modules/fmodule/srcResources/contao/module/ModuleDetailView.php',
    'FModule\ModuleListView' => 'system/modules/fmodule/src/Resources/contao/module/ModuleListView.php',
    'FModule\ModeSettings' => 'system/modules/fmodule/src/Resources/contao/widget/ModeSettings.php',
    'FModule\FilterFields' => 'system/modules/fmodule/src/Resources/contao/widget/FilterFields.php',
    'FModule\ModuleFormFilter' => 'system/modules/fmodule/src/Resources/contao/module/ModuleFormFilter.php',
    'FModule\FieldAppearance' => 'system/modules/fmodule/src/Resources/contao/classes/FieldAppearance.php',
    'FModule\ContentModelExtend' => 'system/modules/fmodule/src/Resources/contao/model/ContentModelExtend.php',
    'FModule\FModule' => 'system/modules/fmodule/src/Resources/contao/classes/FModule.php',

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