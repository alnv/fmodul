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

use FModule\ViewContainer;
use FModule\FieldAppearance;
use FModule\SqlData;


/**
 * tl_fmodules_filters
 */
$GLOBALS['TL_DCA']['tl_fmodules_filters'] = array
(
    'config' => array
    (
        'dataContainer' => 'Table',
        'ptable' => 'tl_fmodules',
        'onload_callback' => array
        (
            array('tl_fmodules_filters', 'checkPermission'),
        ),
        'ondelete_callback' => array(
            array('tl_fmodules_filters', 'delete_cols')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid' => 'index'

            )
        )
    ),
    'list' => array(
        'sorting' => array(
            'mode' => 4,
            'fields' => array('sorting'),
            'headerFields' => array('name', 'info', 'tablename'),
            'panelLayout' => 'filter,search,limit',
            'child_record_callback' => array('tl_fmodules_filters', 'listFilters')
        ),
        'global_operations' => array(

            'all' => array
            (
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )

        ),
        'operations' => array(
            'editheader' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.gif'
            ),
            'copy' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['copy'],
                'href' => 'act=paste&amp;mode=copy',
                'icon' => 'copy.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ),
            'cut' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"'
            ),
            'delete' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            )
        )
    ),

    'palettes' => array(
        '__selector__' => array('type', 'reactToTaxonomy'),
        'default' => '{type_legend},type;',
        'simple_choice' => '{type_legend},type;{setting_legend},fieldID,title,description,fieldAppearance,dataFromTable,negate,autoPage;{taxonomy_legend},dataFromTaxonomy;{expert_legend},fmGroup,rgxp,evalCss,isMandatory;',
        'multi_choice' => '{type_legend},type;{setting_legend},fieldID,title,description,fieldAppearance,dataFromTable,negate,autoPage;{taxonomy_legend},reactToTaxonomy;{expert_legend},fmGroup,rgxp,evalCss,isMandatory;',
        'search_field' => '{type_legend},type;{setting_legend},fieldID,title,description,isInteger;{expert_legend},fmGroup,rgxp,evalCss,isMandatory;',
        'date_field' => '{type_legend},type;{setting_legend},fieldID,title,description,addTime;{expert_legend:hide},fmGroup,evalCss,isMandatory;',
        'fulltext_search' => '{type_legend},type;{setting_legend},fieldID,title,description;{fulltext_search_settings},fullTextSearchOrderBy,fullTextSearchFields;',
        'toggle_field' => '{type_legend},type;{setting_legend},fieldID,title,description;{expert_legend:hide},fmGroup,evalCss;',
        'wrapper_field' => '{type_legend},type;{setting_legend},fieldID,title,description,from_field,to_field;',
        'geo_locator' => '{type_legend},type;{setting_legend},fieldID,title,description;{locator_legend},locatorType;',
        'legend_start' => '{type_legend},type;{setting_legend},fieldID,title;',
        'legend_end' => '{type_legend},type;{setting_legend},fieldID,title;',
        'widget' => '{type_legend},type;{setting_legend},widget_type,widgetTemplate,fieldID,title,description;{expert_legend},fmGroup,rgxp,evalCss,isMandatory;',
        'map_field' => '{type_legend},type;{setting_legend},fieldID,title,description;{map_settings_legend},mapTemplate,mapZoom,mapType,mapScrollWheel,mapMarker,mapInfoBox,mapStyle;',
    ),

    'subpalettes' => array(
        'reactToTaxonomy' => 'reactToField'
    ),

    'fields' => array
    (
        'id' => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid' => array
        (
            'foreignKey' => 'tl_fmodules.id',
            'sql' => "int(10) unsigned NOT NULL default '0'",
            'relation' => array('type' => 'belongsTo', 'load' => 'eager')
        ),
        'tstamp' => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting' => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'type' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['type'],
            'default' => 'simple_choice',
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_fmodules_filters'],
            'options' => array('simple_choice', 'multi_choice', 'search_field', 'date_field', 'fulltext_search', 'widget', 'toggle_field', 'geo_locator', 'map_field', 'wrapper_field', 'legend_start', 'legend_end'),
            'eval' => array('submitOnChange' => true, 'mandatory' => true, 'chosen' => true),
            'sql' => "varchar(32) NOT NULL default ''"
        ),
        'locatorType' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['locatorType'],
            'exclude' => true,
            'default' => 'geo_zip',
            'inputType' => 'radio',
            'options' => array('geo_street', 'geo_zip', 'geo_city', 'geo_state', 'geo_country'),
            'reference' => &$GLOBALS['TL_LANG']['tl_fmodules_filters'],
            'sql' => "varchar(32) NOT NULL default ''"
        ),
        'fieldID' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['fieldID'],
            'inputType' => 'text',
            'exclude' => true,
            'filter' => true,
            'eval' => array('mandatory' => true, 'rgxp' => 'extnd', 'spaceToUnderscore' => true, 'doNotCopy' => true, 'maxlength' => 64, 'tl_class' => 'w50'),
            'save_callback' => array(array('tl_fmodules_filters', 'create_cols')),
            'sql' => "varchar(64) NOT NULL default ''"
        ),
        'title' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['title'],
            'inputType' => 'text',
            'exclude' => true,
            'search' => true,
            'eval' => array('maxlength' => 255, 'mandatory' => true, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'from_field' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['from_field'],
            'inputType' => 'select',
            'exclude' => true,
            'options_callback' => array('tl_fmodules_filters', 'getFromFields'),
            'eval' => array('maxlength' => 255, 'mandatory' => true, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),

        'to_field' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['to_field'],
            'inputType' => 'select',
            'exclude' => true,
            'options_callback' => array('tl_fmodules_filters', 'getToFields'),
            'eval' => array('maxlength' => 255, 'mandatory' => true, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'description' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['description'],
            'inputType' => 'textarea',
            'exclude' => true,
            'search' => true,
            'eval' => array('tl_class' => 'clr'),
            'sql' => "blob NULL"
        ),
        'fieldAppearance' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['fieldAppearance'],
            'inputType' => 'select',
            'exclude' => true,
            'options_callback' => array('tl_fmodules_filters', 'getAppearance'),
            'eval' => array('tl_class' => 'w50', 'chosen' => true),
            'sql' => "varchar(64) NOT NULL default ''"
        ),
        'widget_type' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['widget_type'],
            'reference' => &$GLOBALS['TL_LANG']['tl_fmodules_filters'],
            'inputType' => 'select',
            'exclude' => true,
            'options' => array('text.blank', 'textarea.blank', 'textarea.tinyMCE', 'list.blank', 'list.keyValue', 'table.blank'),
            'eval' => array('mandatory' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-', 'tl_class' => 'w50'),
            'load_callback' => array(array('tl_fmodules_filters', 'look_widget')),
            'sql' => "varchar(64) NOT NULL default ''"
        ),
        'dataFromTable' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['dataFromTable'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('tl_class' => 'clr'),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'evalCss' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['evalCss'],
            'inputType' => 'text',
            'exclude' => true,
            'eval' => array('tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'rgxp' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['rgxp'],
            'inputType' => 'select',
            'exclude' => true,
            'options' => array('alias', 'alnum', 'alpha', 'date', 'datim', 'digit', 'email', 'emails', 'extnd', 'folderalias', 'friendly', 'language', 'locale', 'natural', 'phone', 'prcnt', 'url', 'time'),
            'eval' => array('includeBlankOption' => true, 'blankOptionLabel' => '-', 'chosen' => true, 'tl_class' => 'w50'),
            'sql' => "varchar(32) NOT NULL default ''"
        ),
        'fmGroup' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['fmGroup'],
            'inputType' => 'text',
            'exclude' => true,
            'eval' => array('maxlength' => 64, 'tl_class' => 'w50'),
            'sql' => "varchar(64) NOT NULL default ''"
        ),
        'widgetTemplate' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['widgetTemplate'],
            'inputType' => 'select',
            'exclude' => true,
            'options_callback' => array('tl_fmodules_filters', 'getWidgetTemplates'),
            'eval' => array('tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'isInteger' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['isInteger'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('tl_class' => 'clr'),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'addTime' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['addTime'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('tl_class' => 'clr'),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'negate' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['negate'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('tl_class' => 'clr'),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'autoPage' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['autoPage'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('tl_class' => 'clr'),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'isMandatory' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['isMandatory'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('tl_class' => 'w50 m12'),
            'sql' => "char(1) NOT NULL default ''"
        ),
        // taxonomy
        'dataFromTaxonomy' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['dataFromTaxonomy'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('tl_class' => 'clr'),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'reactToTaxonomy' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['reactToTaxonomy'],
            'inputType' => 'checkbox',
            'exclude' => true,
            'eval' => array('submitOnChange' => true, 'tl_class' => 'clr'),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'reactToField' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['reactToField'],
            'inputType' => 'select',
            'exclude' => true,
            'options_callback' => array('tl_fmodules_filters', 'getTaxonomyFields'),
            'eval' => array('chosen' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-', 'mandatory' => true, 'tl_class' => 'clr'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        // map
        'mapTemplate' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['mapTemplate'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => array('tl_fmodules_filters', 'getMapFieldTemplates'),
            'eval' => array('maxlength' => 255, 'mandatory' => true, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'mapZoom' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['mapZoom'],
            'exclude' => true,
            'default' => '10',
            'inputType' => 'select',
            'options' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20),
            'eval' => array('tl_class' => 'w50'),
            'sql' => "int(10) unsigned NOT NULL default '10'"
        ),
        'mapScrollWheel' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['mapScrollWheel'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => array('tl_class' => 'clr'),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'mapMarker' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['mapMarker'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => array('tl_class' => 'w50 m12'),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'mapInfoBox' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['mapInfoBox'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => array('tl_class' => 'w50 m12'),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'mapType' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['mapType'],
            'exclude' => true,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_fmodules_filters'],
            'options' => array('ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN'),
            'eval' => array('tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'mapStyle' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['mapStyle'],
            'exclude' => true,
            'inputType' => 'textarea',
            'eval' => array('allowHtml' => true, 'tl_class' => 'clr', 'rte' => 'ace|html'),
            'sql' => "text NULL"
        ),
        // fullTextSearch Settings
        'fullTextSearchFields' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['fullTextSearchFields'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => array('tl_fmodules_filters', 'getDataCols'),
            'eval' => array('multiple' => true, 'csv' => ',', 'chosen' => true, 'tl_class' => 'clr'),
            'sql' => "text NULL"
        ),
        'fullTextSearchOrderBy' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['fullTextSearchOrderBy'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => array('tl_fmodules_filters', 'getDataCols'),
            'eval' => array('chosen' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-', 'tl_class' => 'clr'),
            'sql' => "varchar(255) NOT NULL default ''"
        )
    )
);


/**
 * Class tl_fmodules_filters
 */
class tl_fmodules_filters extends \Backend
{

    /**
     * construct
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * @param \DataContainer $dc
     * @return array
     */
    public function getTaxonomyFields(\DataContainer $dc)
    {
        $pid = $dc->activeRecord->pid;
        $id = $dc->id;
        $options = array();

        if (!$pid) return $options;

        $fieldsDB = $this->Database->prepare('SELECT * FROM tl_fmodules_filters WHERE pid = ? AND dataFromTaxonomy = "1" AND id != ?')->execute($pid, $id);

        if(!$fieldsDB->count())
        {
           return $options;
        }

        while($fieldsDB->next())
        {
            if(!$fieldsDB->fieldID) continue;
            $options[$fieldsDB->fieldID] = $fieldsDB->title;
        }

        return $options;
    }

    /**
     * @param $dc
     * @return array
     */
    public function getDataCols(\DataContainer $dc)
    {
        $pid = $dc->activeRecord->pid;
        $options = array();

        if (!$pid) return $options;

        $moduleDB = $this->Database->prepare('SELECT * FROM tl_fmodules WHERE id = ?')->execute($pid);
        $tablename = '';

        while ($moduleDB->next()) {
            if ($moduleDB->tablename) {
                $tablename = $moduleDB->tablename;
            }
        }

        if (!$tablename) {
            return $options;
        }

        $tableData = $tablename . '_data';
        $doNotSetByName = array('pid', 'id', 'tstamp');

        // get editable fields
        System::loadLanguageFile($tableData);
        $this->loadDataContainer($tableData);
        $fields = $GLOBALS['TL_DCA'][$tableData]['fields'] ? $GLOBALS['TL_DCA'][$tableData]['fields'] : array();
        foreach ($fields as $name => $field) {

            if(in_array($name, $doNotSetByName))
            {
                continue;
            }

            $options[$name] = $field['label'][0] ? $field['label'][0] . ' (' . $name . ')' : $name;
        }

        return $options;
    }

    /**
     * @param $dc
     * @return array
     */
    public function getWidgetTemplates(\DataContainer $dc)
    {
        $type = $dc->activeRecord->widget_type;

        if ($type) {
            $tplName = explode('.', $type)[0];
            return $this->getTemplateGroup('fm_field_' . $tplName);
        }

        return array();
    }

    /**
     * @return array
     */
    public function getMapFieldTemplates()
    {
        return $this->getTemplateGroup('fm_map_field');
    }

    /**
     * @param $value
     * @return mixed
     */
    public function look_widget($value)
    {
        if ($value) {
            $GLOBALS['TL_DCA']['tl_fmodules_filters']['fields']['widget_type']['inputType'] = 'text';
            $GLOBALS['TL_DCA']['tl_fmodules_filters']['fields']['widget_type']['eval']['readonly'] = true;
        }

        return $value;
    }

    /**
     * @param $arrRow
     * @return string
     */
    public function listFilters($arrRow)
    {

        $mandatoryTpl = '';

        if ($arrRow['type'] == 'legend_start') {
            return '<span style="color: #77ac45;">' . htmlentities('<') . '' . $arrRow['title'] . '' . htmlentities('>') . '</span>';
        }

        if ($arrRow['type'] == 'legend_end') {
            return '<span style="color: #77ac45;">' . htmlentities('</') . '' . $arrRow['title'] . '' . htmlentities('>') . '</span>';
        }

        if ($arrRow['isMandatory']) {
            $mandatoryTpl = '<span style="color: tomato;">*</span>';
        }

        return '<span>' . $arrRow['title'] . ' <span style="color:#cdcdcd;">[' . $arrRow['type'] . ': ' . $arrRow['fieldID'] . ']</span>' . $mandatoryTpl . '</span>';
    }

    /**
     * @throws Exception
     */
    public function checkPermission()
    {

        if ($this->User->isAdmin) {
            return;
        }

        if (!is_array($this->User->fmodulesfilters) || empty($this->User->fmodulesfilters)) {
            $root = array(0);
        } else {
            $root = $this->User->fmodulesfilters;
        }

        $GLOBALS['TL_DCA']['tl_fmodules_filters']['list']['sorting']['root'] = $root;

        if (!$this->User->hasAccess('create', 'fmodulesfiltersp')) {
            $GLOBALS['TL_DCA']['tl_fmodules_filters']['config']['closed'] = true;
        }

        switch (Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;
            case 'edit':
                if (!in_array(Input::get('id'), $root)) {

                    $arrNew = $this->Session->get('new_records');

                    if (is_array($arrNew['tl_fmodules_filters']) && in_array(Input::get('id'), $arrNew['tl_fmodules_filters'])) {
                        // Add permissions on user level
                        if ($this->User->inherit == 'custom' || !$this->User->groups[0]) {
                            $objUser = $this->Database->prepare("SELECT fmodulesfilters, fmodulesfiltersp FROM tl_user WHERE id=?")
                                ->limit(1)
                                ->execute($this->User->id);

                            $arrFModulep = deserialize($objUser->fmodulesfiltersp);

                            if (is_array($arrFModulep) && in_array('create', $arrFModulep)) {
                                $arrFModules = deserialize($objUser->fmodulesfilters);
                                $arrFModules[] = Input::get('id');

                                $this->Database->prepare("UPDATE tl_user SET fmodulesfilters=? WHERE id=?")
                                    ->execute(serialize($arrFModules), $this->User->id);
                            }
                        } // Add permissions on group level
                        elseif ($this->User->groups[0] > 0) {
                            $objGroup = $this->Database->prepare("SELECT fmodulesfilters, fmodulesfiltersp FROM tl_user_group WHERE id=?")
                                ->limit(1)
                                ->execute($this->User->groups[0]);

                            $arrFModulep = deserialize($objGroup->fmodulesfiltersp);

                            if (is_array($arrFModulep) && in_array('create', $arrFModulep)) {
                                $arrFModules = deserialize($objGroup->fmodulesfilters);
                                $arrFModules[] = Input::get('id');

                                $this->Database->prepare("UPDATE tl_user_group SET fmodulesfilters=? WHERE id=?")
                                    ->execute(serialize($arrFModules), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = Input::get('id');
                        $this->User->fmodulesfilters = $root;
                    }
                }
            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(Input::get('id'), $root) || (Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'fmodulesfiltersp'))) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' F Module filter ID "' . Input::get('id') . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'fmodulesfiltersp')) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (strlen(Input::get('act'))) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' F Module filter ', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

        }

    }

    /**
     * @param \DataContainer $dc
     * @return array
     */
    public function getAppearance(\DataContainer $dc)
    {

        $type = $dc->activeRecord->type;
        $style = FieldAppearance::getAppearance();
        $options = array();

        if ($type == 'simple_choice') {
            $options = $style['simple_choice'];
        }

        if ($type == 'multi_choice') {
            $options = $style['multi_choice'];
        }

        return $options;
    }


    /**
     * @param $values
     * @param \DataContainer $dc
     * @return mixed
     * @throws Exception
     */
    public function create_cols($values, \DataContainer $dc)
    {

        $pid = $dc->activeRecord->pid;
        $tempVal = $dc->activeRecord->fieldID;
        $type = $dc->activeRecord->type;

        if (!$values) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['tl_fmodules_filters']['fieldIDEmpty'], $values));
        }

        $tablename = $this->Database->prepare("SELECT tablename FROM tl_fmodules WHERE id = ?")->execute($pid)->row()['tablename'];
        $dataTable = $tablename . '_data';

        // blocked colNames
        $notAllowedCols = array(
            'alter',
            'key',
            'type',
            'date',
            'primary',
            'auto_increment',
            'data',
            'insert',
            'delete',
            'update',
            'options',
            'max',
            'min',
            'drop',
            'date',
            'time',
            'fmodule',
            'fmodules',
            'fmodulesfilters',
            'fmodulesfeed',
            'sourcePalette',
            'protectedPalette',
            'expertPalette',
            'publishPalette',
            'generalPalette',
            'geoPalette',
            'geoAddressPalette',
            'markerPalette'
        );

        if (in_array(mb_strtolower($values), $notAllowedCols)) {

            throw new \Exception(sprintf($GLOBALS['TL_LANG']['tl_fmodules_filters']['notAllowed'], $values));
        }

        $viewContainer = new ViewContainer();
        $dcaDataArr = $viewContainer->dcaDataFields();
        $dcaSettingsArr = $viewContainer->dcaSettingField();
        $dcaData = array_keys($dcaDataArr);
        $dcaSettings = array_keys($dcaSettingsArr);

        if (in_array($values, $dcaData) || in_array($values, $dcaSettings)) {

            throw new \Exception(sprintf($GLOBALS['TL_LANG']['tl_fmodules_filters']['notAllowed'], $values));
        }

        if ($values == $tempVal) {

            return $tempVal;
        }

        $filtersDB = $this->Database->prepare('SELECT fieldID FROM tl_fmodules_filters WHERE pid = ? AND fieldID = ?')->execute($pid, $values);

        if ($filtersDB->numRows >= 1) {

            if ($values == 'auto_item' || $values == 'auto_page') {

                throw new \Exception(sprintf($GLOBALS['TL_LANG']['tl_fmodules_filters']['autoAttributeExist'], $values));
            }

            throw new \Exception(sprintf($GLOBALS['TL_LANG']['tl_fmodules_filters']['fieldIDExist'], $values));

        }

        if (!$this->Database->fieldExists($values, $tablename)) {

            // create
            if (!$tempVal || $values == $tempVal) {

                //parent
                SqlData::insertColFilterInput($tablename, $values);

                //child
                if ($type == 'search_field' || $type == 'widget') {
                    SqlData::insertColSearchField($dataTable, $values);
                }

                if ($type == 'date_field') {
                    SqlData::insertColDateField($dataTable, $values);
                }

                if ($type == 'simple_choice' || $type == 'multi_choice') {
                    SqlData::insertColSelectOptions($dataTable, $values);
                }

                if ($type == 'toggle_field') {
                    SqlData::insertColTogglefield($dataTable, $values);
                }

            } else {

                // rename
                if ($this->Database->fieldExists($tempVal, $tablename)) {

                    //parent
                    SqlData::renameColFilterInput($tablename, $tempVal, $values);

                    //child
                    if ($type == 'search_field' || $type == 'widget') {
                        SqlData::renameColSearchField($dataTable, $tempVal, $values);
                    }

                    if ($type == 'date_field') {
                        SqlData::renameColDateField($dataTable, $tempVal, $values);
                    }

                    if ($type == 'simple_choice' || $type == 'multi_choice') {
                        SqlData::renameColSelectOptions($dataTable, $tempVal, $values);
                    }

                    if ($type == 'toggle_field') {
                        SqlData::renameColTogglefield($dataTable, $tempVal, $values);
                    }

                }

            }
        }
        return $values;
    }

    /**
     * @return array
     */
    public function getFromFields()
    {

        return $this->getWrapperFields();
    }

    /**
     * @return array
     */
    public function getToFields()
    {
        return $this->getWrapperFields();
    }

    /**
     * @return array
     */
    public function getWrapperFields()
    {
        $id = Input::get('id');
        $pid = null;

        if ($id) {
            $currentItemDB = $this->Database->prepare('SELECT * FROM tl_fmodules_filters WHERE id = ?')->execute($id);

            if ($currentItemDB->count()) {
                $pid = $currentItemDB->row()['pid'];
            }
        }

        $filterDB = $this->Database->prepare('SELECT * FROM tl_fmodules_filters WHERE pid = ?')->execute($pid);
        $return = array();

        while ($filterDB->next()) {

            if (!in_array($filterDB->type, array('date_field', 'search_field'))) {
                continue;
            }

            if ($filterDB->type == 'search_field' && !$filterDB->isInteger) {
                continue;
            }

            $return[$filterDB->fieldID] = $filterDB->title;
        }

        return $return;
    }

    /**
     * @param \DataContainer $dc
     * @return null
     */
    public function delete_cols(\DataContainer $dc)
    {

        $doNotDeleteByType = array('fulltext_search', 'wrapper_field', 'legend_start', 'legend_end', 'map_field');

        if (!$dc->activeRecord->fieldID) {
            return null;
        }

        if (in_array($dc->activeRecord->type, $doNotDeleteByType)) {
            return null;
        }

        $pid = $dc->activeRecord->pid;
        $col = $dc->activeRecord->fieldID;
        $tablename = $this->Database->prepare("SELECT tablename FROM tl_fmodules WHERE id = ?")->execute($pid)->row()['tablename'];
        $dataTable = $tablename . '_data';
        if ($this->Database->fieldExists($col, $dataTable)) {
            SqlData::deleteCol($dataTable, $col);
        }
        if ($this->Database->fieldExists($col, $tablename)) {
            SqlData::deleteCol($tablename, $col);
        }
    }

}