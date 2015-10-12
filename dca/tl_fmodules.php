<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   F Modul
 * @author    Alexander Naumov http://www.alexandernaumov.de
 * @license   GNU GENERAL PUBLIC LICENSE
 * @copyright 2015 Alexander Naumov
 */

/**
 * fmodules DCA
 */
$GLOBALS['TL_DCA']['tl_fmodules'] = array
(
    'config' => array
    (
        'dataContainer' => 'Table',
        'ctable' => array('tl_fmodules_filters'),
        'onload_callback' => array
        (
            array('tl_fmodules', 'checkPermission')
        ),
        'onsubmit_callback' => array(
            array('tl_fmodules', 'createGroupCols')
        ),
        'ondelete_callback' => array
        (
            array('tl_fmodules', 'deleteTable')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary'
            )
        )
    ),

    'list' => array(

        'sorting' => array(

            'mode' => 1,
            'flag' => 1,
            'fields' => array('name'),
            'panelLayout' => 'sort,filter;search,limit'

        ),

        'label' => array(
            'fields' => array('name', 'info'),
            'format' => '%s <span style="color: #c2c2c2;">(%s)</span>'
        ),

        'global_operations' => array(


            'donate' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['editDonate'],
                'button_callback' => array('tl_fmodules', 'setDonateButton'),
                'icon' => 'system/modules/fmodule/assets/donate.png'
            ),


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
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['editheader'],
                'href' => 'act=edit',
                'icon' => 'system/modules/fmodule/assets/settings.png'
            ),

            'editFilters' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['editFilters'],
                'href' => 'table=tl_fmodules_filters',
                'icon' => 'system/modules/fmodule/assets/filter.png'
            ),

            'delete' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),

            'show' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            )
        )

    ),

    'palettes' => array(
        '__selector__' => array('protected'),
        'default' => '{main_legend},name,info,tablename;{list_legend},sorting,orderBy'
    ),

    'fields' => array
    (
        'id' => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),

        'tstamp' => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),

        'tablename' => array(

            'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['tablename'],
            'inputType' => 'text',
            'exclude' => true,
            'eval' => array('mandatory' => true, 'rgxp' => 'extnd', 'unique' => true, 'doNotCopy' => true, 'spaceToUnderscore' => true, 'maxlength' => 64, 'tl_class' => 'w50'),
            'sql' => "varchar(128) COLLATE utf8_bin NOT NULL default ''",
            'save_callback' => array(
                array('tl_fmodules', 'generateTableName'),
                array('tl_fmodules', 'updateTable')
            )

        ),

        'name' => array(

            'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['name'],
            'search' => true,
            'sorting' => true,
            'filter' => true,
            'exclude' => true,
            'inputType' => 'text',
            'eval' => array('mandatory' => true, 'rgxp' => 'extnd', 'tl_class' => 'w50'),
            'sql' => "varchar(128) NOT NULL default ''"

        ),

        'info' => array(

            'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['info'],
            'inputType' => 'text',
            'exclude' => true,
            'eval' => array('tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"

        ),
        'sorting' => array(

            'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['sorting'],
            'default' => 'title',
            'exclude' => true,
            'inputType' => 'radio',
            'reference' => &$GLOBALS['TL_LANG']['tl_fmodules'],
            'options_callback' => array('tl_fmodules', 'getSortingOptions'),
            'save_callback' => array(
                array('tl_fmodules', 'saveSortingType')
            ),
            'sql' => "varchar(64) NOT NULL default 'title'"
        ),

        'sortingType' => array(

            'sql' => "varchar(64) NOT NULL default ''"
        ),

        'orderBy' => array(

            'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['orderBy'],
            'default' => 'asc',
            'exclude' => true,
            'inputType' => 'radio',
            'reference' => &$GLOBALS['TL_LANG']['tl_fmodules'],
            'options' => array('asc', 'desc'),
            'sql' => "varchar(12) NOT NULL default 'asc'"
        )
    )
);

/**
 * Class tl_fmodules
 */
class tl_fmodules extends \Contao\Backend
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
            $GLOBALS['TL_DCA']['tl_fmodules']['config']['closed'] = true;
        }

        $act = \Input::get('act');

        if (($act == 'delete' || $act == 'deleteAll') && (!$this->user->isAdmin || !$this->User->hasAccess('delete', 'fmodulesp'))) {
            $this->redirect('contao/main.php?act=error');
        }

    }

    /**
     *
     */
    public function setDonateButton($row, $href, $label, $title, $icon, $attributes)
    {
        return '<a href="http://fmodule.alexandernaumov.de/spenden.html" target="_blank" '.$attributes.' tite="'.$title.'" class="header_icon" '.$icon.'>'.$label.'</a>';
    }

    /**
     * @param $varValue
     * @param DataContainer $dc
     * @return string
     */
    public function generateTableName($varValue)
    {

        if ( substr($varValue, 0, 3) == 'fm_') {

            return $varValue;

        }

        throw new \Exception($GLOBALS['TL_LANG']['tl_fmodules']['invalidTableName']);

    }

    /**
     * @param $varValue
     * @param DataContainer $dc
     * @return mixed
     */
    public function createGroupCols(DataContainer $dc)
    {

        $tablename = $dc->activeRecord->tablename;
        $name = substr($tablename, 3, strlen($tablename));

        if( ( !$this->Database->tableExists($name) ) && ( !$this->Database->fieldExists($name, 'tl_user') && !$this->Database->fieldExists($name, 'tl_user_group') ) )
        {
            $this->Database->prepare('ALTER TABLE tl_user ADD ' . $name . ' blob NULL;')->execute();
            $this->Database->prepare('ALTER TABLE tl_user ADD ' . $name . 'p blob NULL;')->execute();

            $this->Database->prepare('ALTER TABLE tl_user_group ADD ' . $name . ' blob NULL;')->execute();
            $this->Database->prepare('ALTER TABLE tl_user_group ADD ' . $name . 'p blob NULL;')->execute();

        }
    }

    /**
     * @param $varValue
     * @param DataContainer $dc
     * @return mixed
     */
    public function saveSortingType($varValue, DataContainer $dc)
    {
        $id = $dc->activeRecord->id;
        $typeArr = explode('.', $varValue);
        $type = $this->Database->prepare('SELECT type FROM tl_fmodules_filters WHERE pid = ? AND fieldID = ?')->execute($id, $typeArr[0])->row()['type'];

        if ($typeArr[1]) {
            $type = $type . '.' . $typeArr[1];
        }

        $this->Database->prepare('UPDATE tl_fmodules SET sortingType = ? WHERE id = ?')->execute($type, $id);

        return $varValue;
    }

    /**
     * @param DataContainer $dc
     * @return array
     */
    public function getSortingOptions(DataContainer $dc)
    {
        $id = $dc->activeRecord->id;
        $db = $this->Database->prepare('SELECT fieldID, title, type FROM tl_fmodules_filters WHERE pid = ?')->execute($id);
        $return = array('id' => 'id', 'title' => 'Titel');
        while ($db->next()) {

            if ($db->type == 'date_field') {
                $return[$db->fieldID . '.5'] = $db->title . ' (d)';
                $return[$db->fieldID . '.7'] = $db->title . ' (m)';
                $return[$db->fieldID . '.9'] = $db->title . ' (y)';

            }

            if ($db->type == 'simple_choice') {
                $return[$db->fieldID] = $db->title;
            }
        }

        return $return;
    }

    /**
     * @param DataContainer $dc
     */
    public function deleteTable(DataContainer $dc)
    {

        $tablename = $dc->activeRecord->tablename;

        if (!$tablename) {
            return;
        }

        $tablename_child = $tablename . '_data';

        $this->Database->prepare("DROP TABLE " . $tablename)->execute();
        $this->Database->prepare("DROP TABLE " . $tablename_child)->execute();
        $this->Database->prepare("DELETE FROM tl_content WHERE ptable = ?")->execute($tablename_child);

        $modname = substr($tablename, 3, strlen($tablename));

        $this->Database->prepare('ALTER TABLE tl_user DROP COLUMN ' . $modname . '')->execute();
        $this->Database->prepare('ALTER TABLE tl_user DROP COLUMN ' . $modname . 'p ')->execute();

        $this->Database->prepare('ALTER TABLE tl_user_group DROP COLUMN ' . $modname . '')->execute();
        $this->Database->prepare('ALTER TABLE tl_user_group DROP COLUMN ' . $modname . 'p ')->execute();


    }

    /**
     * @param $varValue
     * @param DataContainer $dc
     * @return mixed
     */
    public function updateTable($varValue, DataContainer $dc)
    {

        if(!$dc->activeRecord->tablename)
        {
           return $varValue;
        }

        $oldTableName = $dc->activeRecord->tablename;
        $newTableName = $varValue;

        $oldChildTableName = $oldTableName . '_data';
        $newChildTableName = $newTableName . '_data';


        if ( !$this->Database->tableExists($varValue) && $oldTableName != $newTableName ) {

            $this->Database->prepare("RENAME TABLE " . $oldTableName . " TO " . $newTableName . "")->execute();
            $this->Database->prepare("RENAME TABLE " . $oldChildTableName . " TO " . $newChildTableName . "")->execute();
            $this->Database->prepare("UPDATE tl_content SET ptable = ? WHERE ptable = ?")->execute($newChildTableName, $oldChildTableName);

        }

        return $varValue;
    }

}