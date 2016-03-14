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

//config
$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = array('tl_module_fmodule', 'setFEModule');

//module palette
$GLOBALS['TL_DCA']['tl_module']['palettes']['fmodule_fe_list'] = '{title_legend},name,headline,type,f_select_module,f_select_wrapper;{mode_legend},f_display_mode;{sort_legend},f_sorting_fields,f_orderby,f_limit_page,f_perPage;{template_legend},f_list_template,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['fmodule_fe_formfilter'] = '{title_legend},name,headline,type,f_list_field,f_form_fields,f_reset_button,f_active_options;{template_legend},f_form_template,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['fmodule_fe_detail'] = '{title_legend},name,headline,type,f_list_field,f_doNotSet_404;{template_legend},f_detail_template,customTpl;{image_legend:hide},imgSize;{comment_legend:hide},com_template;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['fmodule_fe_googlemaps'] = '{title_legend},name,headline,type;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

//sub
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'f_set_filter';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'f_set_sorting';

//subpalettes
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['f_set_filter'] = 'f_filter_fields';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['f_set_sorting'] = 'f_sorting_fields,f_sorting_orderby';

//fields
$GLOBALS['TL_DCA']['tl_module']['fields']['f_select_module'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_select_module'],
    'default' => '',
    'exclude' => true,
    'inputType' => 'select',
    'includeBlankOption' => true,
    'blankOptionLabel' => '-',
    'options_callback' => array('tl_module_fmodule', 'getModules'),
    'eval' => array('tl_class' => 'w50', 'submitOnChange' => true, 'mandatory' => true),
    'sql' => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['f_select_wrapper'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_select_wrapper'],
    'inputType' => 'select',
    'exclude' => true,
    'includeBlankOption' => true,
    'blankOptionLabel' => '-',
    'options' => array(),
    'eval' => array('tl_class' => 'w50', 'submitOnChange' => true, 'mandatory' => true),
    'sql' => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['f_orderby'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_orderby'],
    'inputType' => 'radio',
    'exclude' => true,
    'default' => 'desc',
    'eval' => array('tl_class' => 'clr m12'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'options' => array('desc','asc','rand'),
    'sql' => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['f_sorting_fields'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_sorting_fields'],
    'inputType' => 'checkboxWizard',
    'exclude' => true,
    'default' => 'id',
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'options' => array('id' => 'ID','title' => 'Titel', 'date' => 'Datum'),
    'eval' => array('multiple' => true),
    'sql' => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['f_perPage'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_perPage'],
    'default' => '0',
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(10) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['f_limit_page'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_limit_page'],
    'default' => '0',
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(10) NOT NULL default ''"
);


$GLOBALS['TL_DCA']['tl_module']['fields']['f_doNotSet_404'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_doNotSet_404'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr m12'),
    'sql' => "char(1) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['f_display_mode'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_display_mode'],
    'exclude' => true,
    'inputType' => 'modeSettings',
    'eval' => array('submitOnChange' => true),
    'sql' => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['f_list_template'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_list_template'],
    'default' => 'fmodule_teaser',
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getListTemplates'),
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['f_detail_template'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_detail_template'],
    'default' => 'fmodule_full',
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getDetailTemplates'),
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(32) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['f_form_template'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_form_template'],
    'default' => 'fm_form_filter',
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getFormTemplates'),
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(32) NOT NULL default ''"
);


$GLOBALS['TL_DCA']['tl_module']['fields']['f_form_fields'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_form_fields'],
    'exclude' => true,
    'inputType' => 'filterFields',
    'eval' => array('tl_class' => 'clr'),
    'sql' => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['f_list_field'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_list_field'],
    'exclude' => true,
    'inputType' => 'select',
    'includeBlankOption' => true,
    'blankOptionLabel' => '-',
    'options_callback' => array('tl_module_fmodule', 'getListModules'),
    'eval' => array('tl_class' => 'w50', 'submitOnChange' => true, 'mandatory' => true),
    'sql' => "varchar(255) NOT NULL default ''"
);

$GLOBALS['TL_DCA']['tl_module']['fields']['f_reset_button'] = array(

    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_reset_button'],
    'inputType' => 'checkbox',
    'exclude'=> true,
    'eval' => array('tl_class' => 'clr m12'),
    'sql' => "char(1) NOT NULL default ''"

);

$GLOBALS['TL_DCA']['tl_module']['fields']['f_active_options'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_active_options'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => array(),
    'eval' => array('multiple' => true),
    'sql' => "blob NULL"
);

/**
 * Class tl_module_fmodule
 */
class tl_module_fmodule extends tl_module
{

    /**
     * @param DataContainer $dc
     * @return array
     */
    public function getListModules(DataContainer $dc)
    {
        $type = 'fmodule_fe_list';
        $listID = $dc->activeRecord->f_list_field;
        $tl_moduleDB = $this->Database->prepare('SELECT name, id, f_select_module FROM tl_module WHERE type = ?')->execute($type);

        $options = array('' => $GLOBALS['TL_LANG']['tl_module']['f_label_select_list']);
        $filters = array();

        while ($tl_moduleDB->next()) {

            $options[$tl_moduleDB->id] = $tl_moduleDB->name;
            $filters[$tl_moduleDB->id] = $tl_moduleDB->f_select_module;

        }

        $activeOptions = array();

        if ($listID != '') {

            $selectedList = $filters[$listID];
                        
            $filterFieldsDB = $this->Database->prepare('SELECT tl_fmodules_filters.* FROM tl_fmodules JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ? ORDER BY tl_fmodules_filters.sorting')->execute($selectedList);
            $filterFields = array();
            $doNotSetByType = array('legend_start', 'legend_end', 'widget', 'map_field');
            $doNotSetByID = array('auto_page', 'auto_item');

            $allowedOptionTypes = array('search_field', 'multi_choice', 'simple_choice', 'fulltext_search', 'date_field');
            $allowedOptionID = array('pagination', 'orderBy', 'sorting_fields');

            while ($filterFieldsDB->next()) {

                if( $filterFieldsDB->fieldID && in_array($filterFieldsDB->type, $allowedOptionTypes) && !in_array($filterFieldsDB->fieldID, $allowedOptionID))
                {
                    $activeOptions[$filterFieldsDB->fieldID] = $filterFieldsDB->title;
                }

                if(in_array($filterFieldsDB->type, $doNotSetByType))
                {
                    continue;
                }

                if(in_array($filterFieldsDB->fieldID, $doNotSetByID))
                {
                    continue;
                }

                //active options
                $filterFields[$filterFieldsDB->id] = array(
                    'id' => $filterFieldsDB->id,
                    'label' => $filterFieldsDB->title,
                    'fieldID' => $filterFieldsDB->fieldID,
                    'title' => $filterFieldsDB->title,
                    'description' => $filterFieldsDB->description,
                    'type' => $filterFieldsDB->type,
                    'isInteger' => $filterFieldsDB->isInteger,
                    'addTime' => $filterFieldsDB->addTime,
                    'from_field' => $filterFieldsDB->from_field,
                    'to_field' => $filterFieldsDB->to_field,
                    'active' => '',
                    'cssClass' => '',
                    'templates' => array(),
                    'appearance' => \FModule\FieldAppearance::getAppearance()[$filterFieldsDB->type],
                    'used_templates' => '',
                    'used_appearance' => ''
                );
            }

            $GLOBALS['TL_DCA']['tl_module']['fields']['f_form_fields']['eval']['filterFields'] = $filterFields;
            $GLOBALS['TL_DCA']['tl_module']['fields']['f_form_fields']['eval']['currentListID'] = $listID;
            $GLOBALS['TL_DCA']['tl_module']['fields']['f_active_options']['options'] = $activeOptions;
        }
        return $options;
    }

    /**
     * @return array
     */
    public function getDetailTemplates()
    {
        return $this->getTemplateGroup('fmodule_');
    }

    /**
     * @return array
     */
    public function getListTemplates()
    {
        return $this->getTemplateGroup('fmodule_');
    }

    /**
     * @return array
     */
    public function getFormTemplates()
    {
        return $this->getTemplateGroup('fm_form_');
    }

    /**
     * @return array
     */
    public function getModules()
    {

        $return = array('' => $GLOBALS['TL_LANG']['tl_module']['f_label_select_list']);
        $fmodulesDB = $this->Database->prepare('SELECT tablename, name FROM tl_fmodules')->execute();

        while ($fmodulesDB->next()) {
            $return[$fmodulesDB->tablename] = $fmodulesDB->name;
        }

        return $return;

    }

    /**
     * @param $dc
     */
    public function setFEModule($dc)
    {

        $id = $dc->id;
        $moduleDB = $this->Database->prepare('SELECT f_select_module FROM tl_module WHERE id = ?')->execute($id);
        $modulename = '';

        $doNotSetByType = array('fulltext_search', 'legend_start', 'legend_end', 'widget', 'wrapper_field','toggle_field');
        $doNotSetByID = array('auto_item', 'auto_page', 'pagination', 'orderBy', 'sorting_fields');

        while ($moduleDB->next()) {
            $modulename = $moduleDB->f_select_module;
        }

        if ($modulename == '' || is_null($modulename)) {
            return;
        }

        if (!$this->Database->tableExists($modulename)) {
            return;
        }

        // get wrapper fields
        $fmoduleDB = $this->Database->prepare('SELECT id, title, info FROM ' . $modulename)->execute();

        $wrapper = array('' => $GLOBALS['TL_LANG']['tl_module']['f_label_select_list']);

        while ($fmoduleDB->next()) {
            $wrapper[$fmoduleDB->id] = $fmoduleDB->title . ' (' . $fmoduleDB->info . ')';
        }

        $GLOBALS['TL_DCA']['tl_module']['fields']['f_select_wrapper']['options'] = $wrapper;

        // get filter fields
        $filterDB = $this->Database->prepare(
            'SELECT * FROM tl_fmodules
			JOIN tl_fmodules_filters 
			ON tl_fmodules.id = tl_fmodules_filters.pid 
			WHERE tablename = ?'
        )->execute($modulename);

        $sorting = array('id' => 'ID','title' => 'Titel', 'date' => 'Datum');

        while ($filterDB->next()) {


            if(in_array($filterDB->fieldID, $doNotSetByID))
            {
                continue;
            }

            if(in_array($filterDB->type, $doNotSetByType))
            {
                continue;
            }

            $sorting[$filterDB->fieldID] = $filterDB->title;

        }

        $GLOBALS['TL_DCA']['tl_module']['fields']['f_sorting_fields']['options'] = $sorting;
    }
}