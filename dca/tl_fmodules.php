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

            'license' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['editLicense'],
                'href' => 'table=tl_fmodules_license',
                'class' => 'header_icon',
                'icon' => 'header.gif'
            ),

            'buy_license' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['buyLicense'],
                'class' => 'header_store',
                'href' => 'key=createBuyLink',
                'button_callback' => array('tl_fmodules', 'createBuyLink')
            ),

            'feeds' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['feeds'],
                'href' => 'table=tl_fmodules_feed',
                'class' => 'header_rss',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
                'button_callback' => array('tl_fmodules', 'manageFeeds')
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
                'icon' => 'header.gif'//$GLOBALS['FM_AUTO_PATH'] . 'settings.png'
            ),

            'editFilters' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['editFilters'],
                'href' => 'table=tl_fmodules_filters',
                'icon' => $GLOBALS['FM_AUTO_PATH'] . 'filter.png'
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

        if (!is_array($this->User->fmodules) || empty($this->User->fmodules)) {
            $root = array(0);
        } else {
            $root = $this->User->fmodules;
        }

        $GLOBALS['TL_DCA']['tl_fmodules']['list']['sorting']['root'] = $root;

        if (!$this->User->hasAccess('create', 'fmodulesp')) {
            $GLOBALS['TL_DCA']['tl_fmodules']['config']['closed'] = true;
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

                    if (is_array($arrNew['tl_fmodules']) && in_array(Input::get('id'), $arrNew['tl_fmodules'])) {
                        // Add permissions on user level
                        if ($this->User->inherit == 'custom' || !$this->User->groups[0]) {
                            $objUser = $this->Database->prepare("SELECT fmodules, fmodulesp FROM tl_user WHERE id=?")
                                ->limit(1)
                                ->execute($this->User->id);

                            $arrFModulep = deserialize($objUser->fmodulesp);

                            if (is_array($arrFModulep) && in_array('create', $arrFModulep)) {
                                $arrFModules = deserialize($objUser->fmodules);
                                $arrFModules[] = Input::get('id');

                                $this->Database->prepare("UPDATE tl_user SET fmodules=? WHERE id=?")
                                    ->execute(serialize($arrFModules), $this->User->id);
                            }
                        } // Add permissions on group level
                        elseif ($this->User->groups[0] > 0) {
                            $objGroup = $this->Database->prepare("SELECT fmodules, fmodulesp FROM tl_user_group WHERE id=?")
                                ->limit(1)
                                ->execute($this->User->groups[0]);

                            $arrFModulep = deserialize($objGroup->fmodulesp);

                            if (is_array($arrFModulep) && in_array('create', $arrFModulep)) {
                                $arrFModules = deserialize($objGroup->fmodules);
                                $arrFModules[] = Input::get('id');

                                $this->Database->prepare("UPDATE tl_user_group SET fmodules=? WHERE id=?")
                                    ->execute(serialize($arrFModules), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = Input::get('id');
                        $this->User->fmodules = $root;
                    }
                }
            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(Input::get('id'), $root) || (Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'fmodulesp'))) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' F Module  ID "' . Input::get('id') . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'fmodulesp')) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (strlen(Input::get('act'))) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' F Module ', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }

    }

    /**
     * @param $href
     * @param $label
     * @param $title
     * @param $class
     * @param $attributes
     * @return string
     */
    public function manageFeeds($href, $label, $title, $class, $attributes)
    {
        return ($this->User->isAdmin || !empty($this->User->fmodulesfeed) || $this->User->hasAccess('create', 'fmodulesfeedp')) ? '<a href="'.$this->addToUrl($href).'" class="'.$class.'" title="'.specialchars($title).'"'.$attributes.'>'.$label.'</a> ' : '';
    }

    /**
     * @return string
     */
    public function createBuyLink()
    {
        return '<a href="http://fmodul.alexandernaumov.de/kaufen.html" target="_blank" title="' . specialchars($GLOBALS['TL_LANG']['tl_fmodules']['buyLicense'][1]) . '" class="header_store">' . $GLOBALS['TL_LANG']['tl_fmodules']['buyLicense'][0] . '</a>';
    }


    /**
     * @param $varValue
     * @param DataContainer $dc
     * @return string
     */
    public function generateTableName($varValue)
    {

        if (substr($varValue, 0, 3) == 'fm_') {

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

        if ((!$this->Database->tableExists($name)) && (!$this->Database->fieldExists($name, 'tl_user') && !$this->Database->fieldExists($name, 'tl_user_group'))) {
            $this->Database->prepare('ALTER TABLE tl_user ADD ' . $name . ' blob NULL;')->execute();
            $this->Database->prepare('ALTER TABLE tl_user ADD ' . $name . 'p blob NULL;')->execute();

            $this->Database->prepare('ALTER TABLE tl_user_group ADD ' . $name . ' blob NULL;')->execute();
            $this->Database->prepare('ALTER TABLE tl_user_group ADD ' . $name . 'p blob NULL;')->execute();

        }

        if (!\Contao\Config::get('bypassCache')) {
            $automator = new \Contao\Automator();
            $automator->purgeInternalCache();
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

        if ($varValue == '') {
            $varValue = 'id';
        }

        $typeArr = explode('.', $varValue);
        $type = $this->Database->prepare('SELECT type FROM tl_fmodules_filters WHERE pid = ? AND fieldID = ?')->execute($id, $typeArr[0])->row()['type'];

        if ($typeArr[1]) {
            $type = $type . '.' . $typeArr[1];
        }

        if ($type == null) {
            $type = $varValue;
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
        $return = array('id' => 'id', 'title' => 'Titel', 'date.7' => 'Datum');
        while ($db->next()) {

            if($db->fieldID == 'orderBy' || $db->fieldID == 'sorting_fields' || $db->fieldID == 'pagination')
            {
                continue;
            }

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

        if (!\Contao\Config::get('bypassCache')) {
            $automator = new \Contao\Automator();
            $automator->purgeInternalCache();
        }

    }

    /**
     * @param $varValue
     * @param DataContainer $dc
     * @return mixed
     */
    public function updateTable($varValue, DataContainer $dc)
    {

        if (!$dc->activeRecord->tablename) {
            return $varValue;
        }

        $oldTableName = $dc->activeRecord->tablename;
        $newTableName = $varValue;

        $oldChildTableName = $oldTableName . '_data';
        $newChildTableName = $newTableName . '_data';


        if (!$this->Database->tableExists($varValue) && $oldTableName != $newTableName) {

            $this->Database->prepare("RENAME TABLE " . $oldTableName . " TO " . $newTableName . "")->execute();
            $this->Database->prepare("RENAME TABLE " . $oldChildTableName . " TO " . $newChildTableName . "")->execute();
            $this->Database->prepare("UPDATE tl_content SET ptable = ? WHERE ptable = ?")->execute($newChildTableName, $oldChildTableName);

        }

        if (!\Contao\Config::get('bypassCache')) {
            $automator = new \Contao\Automator();
            $automator->purgeInternalCache();
        }

        return $varValue;
    }

}