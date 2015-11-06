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
 
$pathToTemplates =  version_compare(VERSION, '4.0', '>=') ? $pathToFiles.'src/Resources/contao/templates' : $pathToFiles.'templates';
 
TemplateLoader::addFiles(array
(
	'mod_fmodule_detail' => $pathToTemplates,
	'mod_fmodule_list' => $pathToTemplates,
    'fm_form_filter' => $pathToTemplates.'/form',
    'fmodule_full' => $pathToTemplates.'/list',
    'fmodule_teaser' => $pathToTemplates.'/list',
    'mod_form_filter' => $pathToTemplates,

    'fm_widget_date_field' => $pathToTemplates.'/widgets',
    'fm_widget_multi_choice' => $pathToTemplates.'/widgets',
    'fm_widget_simple_choice' => $pathToTemplates.'/widgets',
    'fm_widget_search_field' => $pathToTemplates.'/widgets',
    'fm_widget_fulltext_search' => $pathToTemplates.'/widgets',
));