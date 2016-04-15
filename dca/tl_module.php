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

// config
$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = array('tl_module_fmodule', 'setFEModule');
$GLOBALS['TL_DCA']['tl_module']['config']['onsubmit_callback'][] = array('tl_module_fmodule', 'saveGeoCoding');

// module palette
$GLOBALS['TL_DCA']['tl_module']['palettes']['fmodule_fe_list'] = '{title_legend},name,headline,type,f_select_module,f_select_wrapper;{fm_mode_legend},f_display_mode;{fm_map_legend},fm_addMap;{fm_sort_legend},f_sorting_fields,f_orderby,f_limit_page,f_perPage;{template_legend},f_list_template,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['fmodule_fe_formfilter'] = '{title_legend},name,headline,type,f_list_field,f_form_fields,f_reset_button,f_active_options;{fm_redirect_legend:hide},fm_redirect_source;{template_legend},f_form_template,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['fmodule_fe_detail'] = '{title_legend},name,headline,type,f_list_field,f_doNotSet_404;{fm_seo_legend},fm_overwrite_seoSettings;{template_legend},f_detail_template,customTpl;{image_legend:hide},imgSize;{comment_legend:hide},com_template;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';
$GLOBALS['TL_DCA']['tl_module']['palettes']['fmodule_fe_registration'] = '{title_legend},name,headline,type,f_select_module,f_select_wrapper;{config_legend},fm_editable_fields,disableCaptcha;{redirect_legend},jumpTo;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';


// selector
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'f_set_filter';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'f_set_sorting';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'fm_addMap';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'fm_overwrite_seoSettings';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'fm_redirect_source';

// sub palettes
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['f_set_filter'] = 'f_filter_fields';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['f_set_sorting'] = 'f_sorting_fields,f_sorting_orderby';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['fm_addMap'] = 'fm_center_address,fm_center_lat,fm_center_lng,fm_map_template,fm_mapZoom,fm_mapType,fm_mapScrollWheel,fm_mapMarker,fm_mapInfoBox,fm_mapStyle';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['fm_overwrite_seoSettings'] = 'fm_seoDescription,fm_seoPageTitle';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['fm_redirect_source_siteID'] = 'fm_redirect_jumpTo';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['fm_redirect_source_siteURL'] = 'fm_redirect_url';

// module fields
$GLOBALS['TL_DCA']['tl_module']['fields']['f_select_module'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_select_module'],
    'default' => '',
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getModules'),
    'eval' => array('tl_class' => 'w50', 'submitOnChange' => true, 'mandatory' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_select_wrapper'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_select_wrapper'],
    'inputType' => 'select',
    'exclude' => true,
    'options' => array(),
    'eval' => array('tl_class' => 'w50', 'submitOnChange' => true, 'mandatory' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
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
    'options' => array('desc', 'asc', 'rand'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_sorting_fields'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_sorting_fields'],
    'inputType' => 'checkboxWizard',
    'exclude' => true,
    'default' => 'id',
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'options' => array('id' => 'ID', 'title' => 'Titel', 'date' => 'Datum'),
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
    'options_callback' => array('tl_module_fmodule', 'getListModules'),
    'eval' => array('tl_class' => 'w50', 'submitOnChange' => true, 'mandatory' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_reset_button'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['f_reset_button'],
    'inputType' => 'checkbox',
    'exclude' => true,
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
// maps
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_addMap'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_addMap'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_center_address'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_center_address'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'long'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_center_lat'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_center_lat'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_center_lng'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_center_lng'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_map_template'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_map_template'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getMapTemplates'),
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_mapZoom'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_mapZoom'],
    'exclude' => true,
    'default' => '6',
    'inputType' => 'select',
    'options' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20),
    'eval' => array('tl_class' => 'w50'),
    'sql' => "int(10) unsigned NOT NULL default '6'"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_mapScrollWheel'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_mapScrollWheel'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr m12'),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_mapMarker'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_mapMarker'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr m12'),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_mapInfoBox'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_mapInfoBox'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr m12'),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_mapType'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_mapType'],
    'exclude' => true,
    'inputType' => 'select',
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'options' => array('ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN'),
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_mapStyle'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_mapStyle'],
    'exclude' => true,
    'inputType' => 'textarea',
    'eval' => array('allowHtml' => true, 'tl_class' => 'clr', 'rte' => 'ace|html'),
    'sql' => "text NULL"
);
// seo settings
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_overwrite_seoSettings'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_overwrite_seoSettings'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_seoDescription'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_seoDescription'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getModuleCols'),
    'eval' => array('tl_class' => 'w50', 'includeBlankOption' => true, 'blankOptionLabel' => '-', 'chosen' => true),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_seoPageTitle'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_seoPageTitle'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getModuleCols'),
    'eval' => array('tl_class' => 'w50', 'includeBlankOption' => true, 'blankOptionLabel' => '-', 'chosen' => true),
    'sql' => "varchar(255) NOT NULL default ''"
);

// redirect
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_redirect_source'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_redirect_source'],
    'default' => '',
    'exclude' => true,
    'inputType' => 'select',
    'options' => array('siteID', 'siteURL'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval' => array('submitOnChange' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
    'sql' => "varchar(64) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_redirect'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_form_redirect'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_redirect_jumpTo'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_redirect_jumpTo'],
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => array('mandatory' => true, 'fieldType' => 'radio'),
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => array('type' => 'belongsTo', 'load' => 'lazy')
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_redirect_url'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_redirect_url'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => array('mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
    'sql' => "varchar(255) NOT NULL default ''"
);

// registration
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_editable_fields'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fm_editable_fields'],
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'options_callback' => array('tl_module_fmodule', 'getEditableFModuleProperties'),
    'eval' => array('multiple' => true),
    'sql' => "blob NULL"
);

use FModule\FieldAppearance;
use FModule\GeoCoding;

/**
 * Class tl_module_fmodule
 */
class tl_module_fmodule extends tl_module
{
    /**
     * @var array
     */
    protected $moduleColsCache = array();

    /**
     * @param \Contao\DataContainer $dca
     * @return array
     */
    public function getEditableFModuleProperties(\Contao\DataContainer $dca)
    {
        // set variables here
        $return = array();
        $modulename = $dca->activeRecord->f_select_module;
        $tableData = $modulename . '_data';
        $doNotSetByName = array('tstamp', 'pid', 'id');

        // return empty array
        if (!$modulename) return $return;

        // get editable fields
        System::loadLanguageFile('tl_fmodules_language_pack');
        $this->loadDataContainer($tableData);

        foreach ($GLOBALS['TL_DCA'][$tableData]['fields'] as $name => $field) {
            if(in_array($name, $doNotSetByName))
            {
                continue;
            }
            $return[$name] = $field['label'][0] ? $field['label'][0] . ' (' . $name . ')' : $name;
        }

        return $return;
    }

    /**
     * @param \Contao\DataContainer $dca
     * @return array
     */
    public function getModuleCols(\Contao\DataContainer $dca)
    {
        if (!empty($this->moduleColsCache)) {
            return $this->moduleColsCache;
        }

        $doNotSet = array('id', 'pid', 'tstamp', 'PRIMARY');
        $cols = array();

        if ($dca->activeRecord->f_list_field) {

            $feID = $dca->activeRecord->f_list_field;
            $listFeModuleDB = $this->Database->prepare('SELECT f_select_module FROM tl_module WHERE id = ?')->execute($feID);

            if (!$listFeModuleDB->count()) {
                return $cols;
            }

            $table = null;

            while ($listFeModuleDB->next()) {
                $table = $listFeModuleDB->f_select_module;
            }

            if (!$table) {
                return $cols;
            }

            $dataTable = $table . '_data';
            $colsDB = $this->Database->listFields($dataTable);

            foreach ($colsDB as $col) {
                if (in_array($col['name'], $doNotSet)) {
                    continue;
                }
                $cols[$col['name']] = $col['name'];
            }
        }
        $this->moduleColsCache = $cols;
        return $cols;
    }

    /**
     * @param \Contao\DataContainer $dca
     * @return null
     */
    public function saveGeoCoding(\Contao\DataContainer $dca)
    {
        if (!$dca->activeRecord) {
            return null;
        }

        $geo_address = $dca->activeRecord->fm_center_address ? $dca->activeRecord->fm_center_address : '';
        $address_country = 'en'; // not needed here

        //
        $cords = array();

        //
        if ($geo_address) {
            $geoCoding = new GeoCoding();
            $cords = $geoCoding->getGeoCords($geo_address, $address_country);
        }

        if (!empty($cords)) {
            $tableName = $dca->table ? $dca->table : Input::get('table');
            $id = $dca->id ? $dca->id : Input::get('id');
            $lat = $cords['lat'] ? $cords['lat'] : '';
            $lng = $cords['lng'] ? $cords['lng'] : '';
            if (!$tableName || !$id) {
                return null;
            }
            $this->Database->prepare('UPDATE ' . $tableName . ' SET fm_center_lat=?,fm_center_lng=? WHERE id = ?')->execute($lat, $lng, $id);
        }
    }

    /**
     * @param \Contao\DataContainer $dca
     * @return array
     */
    public function getListModules(\Contao\DataContainer $dca)
    {
        $type = 'fmodule_fe_list';
        $listID = $dca->activeRecord->f_list_field;
        $tl_moduleDB = $this->Database->prepare('SELECT name, id, f_select_module FROM tl_module WHERE type = ?')->execute($type);
        $options = array();
        $filters = array();

        while ($tl_moduleDB->next()) {
            $options[$tl_moduleDB->id] = $tl_moduleDB->name;
            $filters[$tl_moduleDB->id] = $tl_moduleDB->f_select_module;
        }

        $activeOptions = array();

        if ($listID) {

            $selectedList = $filters[$listID];
            $filterFieldsDB = $this->Database->prepare('SELECT tl_fmodules_filters.* FROM tl_fmodules JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ? ORDER BY tl_fmodules_filters.sorting')->execute($selectedList);
            $filterFields = array();
            $doNotSetByType = array('legend_start', 'legend_end', 'widget', 'map_field');
            $doNotSetByID = array('auto_page', 'auto_item');
            $allowedOptionTypes = array('search_field', 'multi_choice', 'simple_choice', 'fulltext_search', 'date_field');
            $allowedOptionID = array('pagination', 'orderBy', 'sorting_fields');

            while ($filterFieldsDB->next()) {

                if ($filterFieldsDB->fieldID && in_array($filterFieldsDB->type, $allowedOptionTypes) && !in_array($filterFieldsDB->fieldID, $allowedOptionID)) {
                    $activeOptions[$filterFieldsDB->fieldID] = $filterFieldsDB->title;
                }

                if (in_array($filterFieldsDB->type, $doNotSetByType)) {
                    continue;
                }

                if (in_array($filterFieldsDB->fieldID, $doNotSetByID)) {
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
                    'appearance' => FieldAppearance::getAppearance()[$filterFieldsDB->type],
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
     * @param \Contao\DataContainer $dca
     * @return array
     */
    public function getListTemplates(\Contao\DataContainer $dca)
    {
        return $this->getTemplateGroup('fmodule_');
    }

    /**
     * @param \Contao\DataContainer $dca
     * @return array
     */
    public function getMapTemplates(\Contao\DataContainer $dca)
    {
        return $this->getTemplateGroup('fm_map_location');
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
        $return = array();
        $fmodulesDB = $this->Database->prepare('SELECT tablename, name FROM tl_fmodules')->execute();
        while ($fmodulesDB->next()) {
            $return[$fmodulesDB->tablename] = $fmodulesDB->name;
        }
        return $return;
    }

    /**
     * @param \Contao\DataContainer $dca
     * @return null
     */
    public function setFEModule(\Contao\DataContainer $dca)
    {
        $id = $dca->id;
        $moduleDB = $this->Database->prepare('SELECT * FROM tl_module WHERE id = ? LIMIT 1')->execute($id);
        $modulename = '';
        $doNotSetByType = array('fulltext_search', 'legend_start', 'legend_end', 'widget', 'wrapper_field', 'toggle_field', 'map_field');
        $doNotSetByID = array('auto_item', 'auto_page', 'pagination', 'orderBy', 'sorting_fields');
        $type = '';

        while ($moduleDB->next()) {
            $modulename = $moduleDB->f_select_module;
            $type = $moduleDB->type;
        }

        if (!$modulename || is_null($modulename)) return null;
        if (!$this->Database->tableExists($modulename)) return null;

        $modulesDB = $this->Database->prepare('SELECT id, title, info FROM ' . $modulename)->execute();
        $wrapper = array();

        while ($modulesDB->next()) {
            $wrapper[$modulesDB->id] = $modulesDB->title . ' (' . $modulesDB->info . ')';
        }
        $GLOBALS['TL_DCA']['tl_module']['fields']['f_select_wrapper']['options'] = $wrapper;

        // break up
        if ($type != 'fmodule_fe_list') {
            return null;
        }

        // set sorting fields
        $filterDB = $this->Database->prepare('SELECT * FROM tl_fmodules JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ?')->execute($modulename);
        $sorting = array('id' => 'ID', 'title' => 'Titel', 'date' => 'Datum');

        while ($filterDB->next()) {

            if (in_array($filterDB->fieldID, $doNotSetByID)) {
                continue;
            }

            if (in_array($filterDB->type, $doNotSetByType)) {
                continue;
            }
            $sorting[$filterDB->fieldID] = $filterDB->title;

        }

        $GLOBALS['TL_DCA']['tl_module']['fields']['f_sorting_fields']['options'] = $sorting;
    }
}