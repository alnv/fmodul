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
        'date_field' => '{type_legend},type;{setting_legend},fieldID,title,addTime;',
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
        'addTime' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_filters']['addTime'],
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

        if($this->User->isAdmin)
        {
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

        switch (Input::get('act'))
        {
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
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['tl_fmodules_filters']['fieldIDEmpty'], $values));
        }

        $pid = $dc->activeRecord->pid;
        $tempVal = $dc->activeRecord->fieldID;
        $type = $dc->activeRecord->type;

        $notAllowedCols = array('id','tstamp','title','info','adddetailpage','rootpage','source','allowcomments','notify','sortorder','perpage','moderate','bbcode','requirelogin','disablecaptcha', 'protected','groups','guests','cssID','published','start','stop','addenclosure','enclosure','addimage','singlesrc','alt','size','caption', 'alter', 'key', 'type', 'date','primary', 'auto_increment', 'data', 'insert', 'delete', 'update', 'options','max','min', 'drop','date','time');

        if(in_array(mb_strtolower($values), $notAllowedCols))
        {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['tl_fmodules_filters']['notAllowed'], $values));
        }

        if($values == $tempVal)
        {
          return $tempVal;
        }

        $filtersDB = $this->Database->prepare('SELECT fieldID FROM tl_fmodules_filters WHERE pid = ? AND fieldID = ?')->execute($pid, $values);

        if( $filtersDB->numRows >= 1 )
        {
            if( $values == 'auto_item' || $values == 'auto_page')
            {
                throw new \Exception(sprintf($GLOBALS['TL_LANG']['tl_fmodules_filters']['autoAttributeExist'], $values));
            }

            throw new \Exception(sprintf($GLOBALS['TL_LANG']['tl_fmodules_filters']['fieldIDExist'], $values));

        }

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

    /**
     * @param DataContainer $dc
     */
    public function delete_cols(DataContainer $dc)
    {

        //
        if($dc->activeRecord->fieldID == '')
        {
            return;
        }

        //
        if($dc->activeRecord->type == 'fulltext_search')
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