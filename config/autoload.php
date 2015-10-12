<?php

\Contao\ClassLoader::addNamespace('FModule');

/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    'FModule\DCACreator' => 'system/modules/fmodule/src/FModule/Contao/Classes/DCACreator.php',
    'FModule\DCAModuleSettings' => 'system/modules/fmodule/src/FModule/Contao/Classes/DCAModuleSettings.php',
    'FModule\DCAModuleData' => 'system/modules/fmodule/src/FModule/Contao/Classes/DCAModuleData.php',
    'FModule\SqlData' => 'system/modules/fmodule/src/FModule/Contao/Classes/SqlData.php',
    'FModule\OptionWizardExtended' => 'system/modules/fmodule/src/FModule/Contao/Widget/OptionWizardExtended.php',
    'FModule\DCAHelper' => 'system/modules/fmodule/src/FModule/Contao/Classes/DCAHelper.php',
    'FModule\ModuleDetailView' => 'system/modules/fmodule/src/FModule/Contao/Module/ModuleDetailView.php',
    'FModule\ModuleListView' => 'system/modules/fmodule/src/FModule/Contao/Module/ModuleListView.php',
    'FModule\ModeSettings' => 'system/modules/fmodule/src/FModule/Contao/Widget/ModeSettings.php',
    'FModule\FilterFields' => 'system/modules/fmodule/src/FModule/Contao/Widget/FilterFields.php',
    'FModule\ModuleFormFilter' => 'system/modules/fmodule/src/FModule/Contao/Module/ModuleFormFilter.php',
    'FModule\FieldAppearance' => 'system/modules/fmodule/src/FModule/Contao/Classes/FieldAppearance.php',
    'FModule\ContentModelExtend' => 'system/modules/fmodule/src/FModule/Contao/Model/ContentModelExtend.php',
    'FModule\FModule' => 'system/modules/fmodule/src/FModule/Contao/Classes/FModule.php',

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