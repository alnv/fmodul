<?php

\Contao\ClassLoader::addNamespace('FModule');


$pathToFiles = version_compare(VERSION, '4.0', '>=') ? 'vendor/fmodule/fmodule/' : 'system/modules/fmodule/';


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
    'FModule\DCACreator' => $pathToFiles.'src/Resources/contao/classes/DCACreator.php',
    'FModule\DCAModuleSettings' => $pathToFiles.'src/Resources/contao/classes/DCAModuleSettings.php',
    'FModule\DCAModuleData' => $pathToFiles.'src/Resources/contao/classes/DCAModuleData.php',
    'FModule\SqlData' => $pathToFiles.'src/Resources/contao/classes/SqlData.php',
    'FModule\OptionWizardExtended' => $pathToFiles.'src/Resources/contao/widget/OptionWizardExtended.php',
    'FModule\DCAHelper' => $pathToFiles.'src/Resources/contao/classes/DCAHelper.php',
    'FModule\ModuleDetailView' => $pathToFiles.'src/Resources/contao/module/ModuleDetailView.php',
    'FModule\ModuleListView' => $pathToFiles.'src/Resources/contao/module/ModuleListView.php',
    'FModule\ModeSettings' => $pathToFiles.'src/Resources/contao/widget/ModeSettings.php',
    'FModule\FilterFields' => $pathToFiles.'src/Resources/contao/widget/FilterFields.php',
    'FModule\ModuleFormFilter' => $pathToFiles.'src/Resources/contao/module/ModuleFormFilter.php',
    'FModule\FieldAppearance' => $pathToFiles.'src/Resources/contao/classes/FieldAppearance.php',
    'FModule\ContentModelExtend' => $pathToFiles.'src/Resources/contao/model/ContentModelExtend.php',
    'FModule\FModule' => $pathToFiles.'src/Resources/contao/classes/FModule.php',

));

/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'mod_fmodule_detail' => $pathToFiles.'src/Resources/contao/templates',
	'mod_fmodule_list' => $pathToFiles.'src/Resources/contao/templates',
    'fm_form_filter' => $pathToFiles.'src/Resources/contao/templates/form',
    'fmodule_full' => $pathToFiles.'src/Resources/contao/templates/list',
    'fmodule_teaser' => $pathToFiles.'src/Resources/contao/templates/list',
    'mod_form_filter' => $pathToFiles.'src/Resources/contao/templates',

    'fm_widget_date_field' => $pathToFiles.'src/Resources/contao/templates/widgets',
    'fm_widget_multi_choice' => $pathToFiles.'src/Resources/contao/templates/widgets',
    'fm_widget_simple_choice' => $pathToFiles.'src/Resources/contao/templates/widgets',
    'fm_widget_search_field' => $pathToFiles.'src/Resources/contao/templates/widgets',
    'fm_widget_fulltext_search' => $pathToFiles.'src/Resources/contao/templates/widgets'

));