<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   F Modul
 * @author    Alexander Naumov http://www.alexandernaumov.de
 * @license   commercial
 * @copyright 2015 Alexander Naumov
 */

/**
 *
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
            'mode' => 0
        ),

        'label' => array(
            'fields' => array('title'),
            'format' => '%s'
        ),

        'global_operations' => array(

            'all' => array
            (
                'label'               => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'                => 'act=select',
                'class'               => 'header_edit_all',
                'attributes'          => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )

        ),

        'operations' => array(

            'editheader' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['editheader'],
                'href' => 'act=edit',
                'icon' => 'edit.gif'
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
        '__selector__' => array('type'),
        'default' => '{type_legend},type;',
        'simple_choice' => '{type_legend},type;{setting_legend},fieldID,title,dataFromTable,negate,fieldAppearance;',
        'multi_choice' => '{type_legend},type;{setting_legend},fieldID,title,dataFromTable,negate,fieldAppearance;',
        'search_field' => '{type_legend},type;{setting_legend},fieldID,title,isInteger;',
        'date_field' => '{type_legend},type;{setting_legend},fieldID,title;',
        'fulltext_search' => '{type_legend},type;{setting_legend},fieldID,title, isFuzzy;',

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

        'type' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['type'],
            'default' => 'simple_choice',
            'exclude' => true,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_fmodules_filters'],
            'options' => array('simple_choice', 'multi_choice', 'search_field', 'date_field', 'fulltext_search'),
            'eval' => array('submitOnChange' => true),
            'sql' => "varchar(32) NOT NULL default ''"
        ),

        'fieldID' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['fieldID'],
            'inputType' => 'text',
            'exclude' => true,
            'eval' => array('mandatory' => true, 'rgxp' => 'extnd', 'spaceToUnderscore' => true, 'maxlength' => 64, 'tl_class' => 'w50'),
            'save_callback' => array( array('tl_fmodules_filters', 'create_cols') ),
            'sql' => "varchar(64) NOT NULL default ''"
        ),

        'title' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['title'],
            'inputType' => 'text',
            'exclude' => true,
            'eval' => array('maxlength' => 255, 'mandatory' => true, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),

        'fieldAppearance' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['fieldAppearance'],
            'inputType' => 'radio',
            'exclude' => true,
            'options_callback' => array('tl_fmodules_filters', 'getAppearance'),
            'eval' => array('mandatory' => true, 'tl_class' => 'w50'),
            'sql' => "varchar(64) NOT NULL default ''"
        ),

        'dataFromTable' => array(

            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['dataFromTable'],
            'inputType' => 'checkbox',
            'exclude'=> true,
            'eval' => array('tl_class' => 'clr m12'),
            'sql' => "char(1) NOT NULL default ''"

        ),

        'isInteger' => array(

            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['isInteger'],
            'inputType' => 'checkbox',
            'exclude'=> true,
            'eval' => array('tl_class' => 'clr m12'),
            'sql' => "char(1) NOT NULL default ''"
        ),

        'isFuzzy' => array(

            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['isFuzzy'],
            'inputType' => 'checkbox',
            'exclude'=> true,
            'eval' => array('tl_class' => 'clr m12'),
            'sql' => "char(1) NOT NULL default ''"
        ),

        'negate' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['negate'],
            'inputType' => 'checkbox',
            'exclude'=> true,
            'eval' => array('tl_class' => 'clr m12'),
            'sql' => "char(1) NOT NULL default ''"
        )
    )
);

/**
 * Class tl_fmodules_filters
 */
class tl_fmodules_filters extends \Contao\Backend
{

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');

    }

    /**
     *
     */
    public function checkPermission()
    {

        if ($this->User->isAdmin) {
            return;
        }

        if (!$this->User->hasAccess('create', 'fmodulesp')) {
            $GLOBALS['TL_DCA']['tl_fmodules_filters']['config']['closed'] = true;
        }

        $act = \Input::get('act');

        if (($act == 'delete' || $act == 'deleteAll') && (!$this->user->isAdmin || !$this->User->hasAccess('delete', 'fmodulesp'))) {
            $this->redirect('contao/main.php?act=error');
        }

    }

    public function getAppearance(DataContainer $dc)
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
     * create new col
     */
    public function create_cols($values, DataContainer $dc)
    {
        if($values == '')
        {
            return $values;
        }

        $pid = $dc->activeRecord->pid;
        $tempVal = $dc->activeRecord->fieldID;
        $type = $dc->activeRecord->type;
        $tname = $this->Database->prepare("SELECT tablename FROM tl_fmodules WHERE id = ?")->execute($pid)->row()['tablename'];
        $childTable = $tname . '_data';
        $exist = $this->Database->fieldExists($values, $tname);

        if (!$exist) {
            if ($tempVal == '' || $values == $tempVal) {
                //create

                //parent
                \FModule\SqlData::insertColFilterInput($tname, $values);

                //child
                if ($type == 'search_field') {
                    \FModule\SqlData::insertColSearchField($childTable, $values);
                }

                if ($type == 'date_field') {
                    \FModule\SqlData::insertColDateField($childTable, $values);
                }

                if ($type == 'simple_choice' || $type == 'multi_choice') {
                    \FModule\SqlData::insertColSelectOptions($childTable, $values);
                }

            } else {

                if ($this->Database->fieldExists($tempVal, $tname)) {

                    //rename

                    //parent
                    \FModule\SqlData::renameColFilterInput($tname, $tempVal, $values);

                    //child
                    if ($type == 'search_field') {
                        \FModule\SqlData::renameColSearchField($childTable, $tempVal, $values);
                    }

                    if ($type == 'date_field') {
                        \FModule\SqlData::renameColDateField($childTable, $tempVal, $values);
                    }

                    if ($type == 'simple_choice' || $type == 'multi_choice') {
                        \FModule\SqlData::renameColSelectOptions($childTable, $tempVal, $values);
                    }

                }

            }

        }

        return $values;

    }

    public function delete_cols(DataContainer $dc)
    {
        if($dc->activeRecord->fieldID == '')
        {
            return;
        }

        $pid = $dc->activeRecord->pid;
        $col = $dc->activeRecord->fieldID;
        $tname = $this->Database->prepare("SELECT tablename FROM tl_fmodules WHERE id = ?")->execute($pid)->row()['tablename'];
        $childTable = $tname . '_data';

        \FModule\SqlData::deleteCol($tname, $col);
        \FModule\SqlData::deleteCol($childTable, $col);

    }

}