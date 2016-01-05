<?php namespace FModule;

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

use Contao\Database;
use Contao\Input;

/**
 * Class DCAModuleSettings
 */
class DCAModuleSettings extends DCAHelper
{

    /**
     * @var
     */
    protected $child;
    protected $name;

    /**
     * @param $dcaname
     */
    public function init($dcaname)
    {
        $this->name = $dcaname;

    }

    /**
     * @param $fieldname
     * @return bool
     */
    private function permissionFieldExist($fieldname)
    {
        if(!$this->Database->fieldExists($fieldname, 'tl_user') || !$this->Database->fieldExists($fieldname.'p', 'tl_user'))
        {
            return false;
        }

        if(!$this->Database->fieldExists($fieldname, 'tl_user_group') || !$this->Database->fieldExists($fieldname.'p', 'tl_user_group'))
        {
            return false;
        }

        return true;
    }

    /**
     * @param $dc
     * @throws \Exception
     */
    public function checkPermission($dc)
    {

        $modname = substr($dc->table, 3, strlen($dc->table));

        $allowedFields = $modname;
        $permission = $modname.'p';

        if( !$this->permissionFieldExist($modname) )
        {
            return;
        }

        if($this->User->isAdmin)
        {
            return;
        }

        if (!is_array($this->User->$allowedFields) || empty($this->User->$allowedFields)) {

            $root = array(0);

        } else {

            $root = $this->User->$allowedFields;

        }

        $GLOBALS['TL_DCA'][$dc->table]['list']['sorting']['root'] = $root;

        if (!$this->User->hasAccess('create', $permission)) {

            $GLOBALS['TL_DCA'][$dc->table]['config']['closed'] = true;

        }

        switch (Input::get('act'))
        {
            case 'create':
            case 'select':
                break;
            case 'edit':
                if (!in_array(Input::get('id'), $root)) {

                    $arrNew = $this->Session->get('new_records');

                    if (is_array($arrNew[$dc->table]) && in_array(Input::get('id'), $arrNew[$dc->table])) {
                        // Add permissions on user level
                        if ($this->User->inherit == 'custom' || !$this->User->groups[0]) {
                            $objUser = $this->Database->prepare("SELECT ".$allowedFields.", ".$permission." FROM tl_user WHERE id=?")
                                ->limit(1)
                                ->execute($this->User->id);

                            $arrFModulep = deserialize($objUser->$permission);

                            if (is_array($arrFModulep) && in_array('create', $arrFModulep)) {
                                $arrFModules = deserialize($objUser->$allowedFields);
                                $arrFModules[] = Input::get('id');

                                $this->Database->prepare("UPDATE tl_user SET ".$allowedFields."=? WHERE id=?")
                                    ->execute(serialize($arrFModules), $this->User->id);
                            }
                        } // Add permissions on group level
                        elseif ($this->User->groups[0] > 0) {
                            $objGroup = $this->Database->prepare("SELECT ".$allowedFields.", ".$permission." FROM tl_user_group WHERE id=?")
                                ->limit(1)
                                ->execute($this->User->groups[0]);

                            $arrFModulep = deserialize($objGroup->$permission);

                            if (is_array($arrFModulep) && in_array('create', $arrFModulep)) {
                                $arrFModules = deserialize($objGroup->$allowedFields);
                                $arrFModules[] = Input::get('id');

                                $this->Database->prepare("UPDATE tl_user_group SET ".$allowedFields."=? WHERE id=?")
                                    ->execute(serialize($arrFModules), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = Input::get('id');
                        $this->User->$allowedFields = $root;
                    }
                }
            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(Input::get('id'), $root) || (Input::get('act') == 'delete' && !$this->User->hasAccess('delete', $permission))) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' '.$allowedFields.' ID "' . Input::get('id') . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', $permission)) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (strlen(Input::get('act'))) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' '.$allowedFields.' ', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }

    }

    /**
     *
     */
    public function setConfig()
    {

        $child_table = $this->getChildName();
        $config = array(
            'dataContainer' => 'Table',
            'ctable' => array($child_table),
            'enableVersioning' => true,
            'onload_callback' => array
            (
                array('DCAModuleSettings', 'checkPermission'),
            ),
            'sql' => array(
                'keys' => array
                (
                    'id' => 'primary'
                )
            )
        );

        return $config;

    }


    /**
     *
     */
    public function setList()
    {

        $list = array(

            'sorting' => array(
                'mode' => 0
            ),

            'label' => array(
                'fields' => array('title', 'info'),
                'format' => '%s <span style="color: #c2c2c2;">(%s)</span>'
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

				'edit' => array
                (
                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['edit'],
                    'href' => 'table=' . $this->child,
                    'icon' => 'edit.gif'//$GLOBALS['FM_AUTO_PATH'] . 'list.png'
                ),

                'editheader' => array
                (
                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['editheader'],
                    'href' => 'act=edit',
                    'icon' => 'header.gif'//$GLOBALS['FM_AUTO_PATH'] . 'settings.png'
                ),

                'copy' => array
                (
                    'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['copy'],
                    'href' => 'act=copy',
                    'icon' => 'copy.gif'
                ),

                'delete' => array
                (
                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['delete'],
                    'href' => 'act=delete',
                    'icon' => 'delete.gif',
                    'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['deleteMsg'] . '\'))return false;Backend.getScrollOffset()"'
                ),

                'show' => array
                (
                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['show'],
                    'href' => 'act=show',
                    'icon' => 'show.gif'
                )
            )
        );

        return $list;
    }

    /**
     *
     */
    public function setPalettes($fields = array())
    {

        $fieldStr = '{data_legend},';

        $arr = array();

        foreach ($fields as $field) {

            if ($field['fieldID'] !== '' && ($field['type'] !== 'simple_choice' || $field['type'] !== 'multi_choice')) {
                if ($field['dataFromTable'] == '1') {
                    $arr[] = 'select_table_' . $field['fieldID'];
                    $arr[] = 'select_col_' . $field['fieldID'];
                    $arr[] = 'select_title_' . $field['fieldID'];
                } else {
                    $arr[] = $field['fieldID'];
                }
            }

        }

        $fieldStr = $fieldStr . implode(',', $arr) . ';';

        return array(

            '__selector__' => array('addDetailPage', 'allowComments'),
            'default' => '{general_legend},title,info;{root_legend},addDetailPage;' . $fieldStr . '{comments_legend:hide},allowComments'

        );
    }

    /**
     *
     */
    public function setSubPalettes()
    {
        return array(

            'addDetailPage' => 'rootPage',
            'allowComments' => 'notify,sortOrder,perPage,moderate,bbcode,requireLogin,disableCaptcha'

        );
    }

    /**
     *
     */
    public function setFields($fields = array())
    {

        $arr = array(

            'id' => array(
                'sql' => 'int(10) unsigned NOT NULL auto_increment'
            ),

            'tstamp' => array(
                'sql' => "int(10) unsigned NOT NULL default '0'"
            ),

            'title' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['title'],
                'inputType' => 'text',
                'exclude' => true,
                'eval' => array('maxlength' => 255, 'mandatory' => true, 'tl_class' => 'w50'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),

            'info' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['info'],
                'inputType' => 'text',
                'exclude' => true,
                'eval' => array('maxlength' => 255, 'tl_class' => 'w50'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),

            'addDetailPage' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['addDetailPage'],
                'inputType' => 'checkbox',
                'exclude' => true,
                'eval' => array('tl_class' => 'clr', 'submitOnChange' => true),
                'sql' => "char(1) NOT NULL default ''"
            ),

            'rootPage' => array(

                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['rootPage'],
                'inputType' => 'pageTree',
                'exclude' => true,
                'foreignKey' => 'tl_page.title',
                'eval' => array('fieldType' => 'radio', 'tl_class' => 'clr m12', 'mandatory' => true),
                'relation' => array('type' => 'hasOne', 'load' => 'eager'),
                'sql' => "int(10) unsigned NOT NULL default '0'"

            ),

            //comments
            'allowComments' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['allowComments'],
                'exclude' => true,
                'filter' => true,
                'inputType' => 'checkbox',
                'eval' => array('submitOnChange' => true),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'notify' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['notify'],
                'default' => 'notify_admin',
                'exclude' => true,
                'inputType' => 'select',
                'options' => array('notify_admin', 'notify_author', 'notify_both'),
                'reference' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack'],
                'sql' => "varchar(32) NOT NULL default ''"
            ),
            'sortOrder' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['sortOrder'],
                'default' => 'ascending',
                'exclude' => true,
                'inputType' => 'select',
                'options' => array('ascending', 'descending'),
                'reference' => &$GLOBALS['TL_LANG']['MSC'],
                'eval' => array('tl_class' => 'w50'),
                'sql' => "varchar(32) NOT NULL default ''"
            ),
            'perPage' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['perPage'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('rgxp' => 'natural', 'tl_class' => 'w50'),
                'sql' => "smallint(5) unsigned NOT NULL default '0'"
            ),
            'moderate' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['moderate'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('tl_class' => 'w50'),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'bbcode' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['bbcode'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('tl_class' => 'w50'),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'requireLogin' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['requireLogin'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('tl_class' => 'w50'),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'disableCaptcha' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['disableCaptcha'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('tl_class' => 'w50'),
                'sql' => "char(1) NOT NULL default ''"
            )

        );

        foreach ($fields as $field) {

            if ($field['fieldID'] !== '' && ($field['type'] == 'simple_choice' || $field['type'] == 'multi_choice')) {

                if ($field['dataFromTable'] == '1') {

                    $arr['select_table_' . $field['fieldID']] = array(

                        'label' => array(sprintf($GLOBALS['TL_LANG']['tl_fmodules_language_pack']['select_table'][0], $field['title']), $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['select_table'][1]),
                        'fmodule_filter' => true,
                        'inputType' => 'select',
                        'exclude' => true,
                        'load_callback' => array(array('DCAModuleSettings', 'loadDefaultTable')),
                        'options_callback' => array('DCAModuleSettings', 'getTables'),
                        'save_callback' => array(array('DCAModuleSettings', 'save_select_table')),
                        'eval' => array('submitOnChange' => true, 'tl_class' => 'clr'),
                        'sql' => "text NULL"

                    );

                    $arr['select_col_' . $field['fieldID']] = array(

                        'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['select_col'],
                        'fmodule_filter' => true,
                        'inputType' => 'select',
                        'load_callback' => array(array('DCAModuleSettings', 'loadDefaultCol')),
                        'options_callback' => array('DCAModuleSettings', 'getCols'),
                        'save_callback' => array(array('DCAModuleSettings', 'save_select_col')),
                        'exclude' => true,
                        'eval' => array('tl_class' => 'w50'),
                        'sql' => "text NULL"
                    );

                    $arr['select_title_' . $field['fieldID']] = array(

                        'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['select_title'],
                        'fmodule_filter' => true,
                        'inputType' => 'select',
                        'load_callback' => array(array('DCAModuleSettings', 'loadDefaultTitle')),
                        'options_callback' => array('DCAModuleSettings', 'getTitle'),
                        'save_callback' => array(array('DCAModuleSettings', 'save_select_title')),
                        'exclude' => true,
                        'eval' => array('tl_class' => 'w50'),
                        'sql' => "text NULL"
                    );

                } else {

                    $arr[$field['fieldID']] = array(

                        'label' => array($field['title'], ''),
                        'fmodule_filter' => true,
                        'inputType' => 'optionWizardExtended',
                        'exclude' => true,
                        'eval' => array('tl_class' => 'clr m12'),
                        'sql' => "text NULL"

                    );

                }

            }
        }
        return $arr;

    }

    /**
     *
     */
    public function loadDefaultTitle($value, $dc)
    {

        $field = $dc->field;
        $fieldname = substr($field, strlen('select_title_'), strlen($field));
        $title = deserialize($dc->activeRecord->$fieldname)['title'];
        $options = $this->getTitle($dc);
        if (isset($title) && is_string($title)) {
            foreach ($options as $value) {
                if ($value == $title) {
                    array_unshift($options, $value);
                }
            }
            $GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['options'] = $options;
            unset($GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['options_callback']);
        }

    }

    public function loadDefaultCol($value, $dc)
    {

        $field = $dc->field;
        $fieldname = substr($field, strlen('select_col_'), strlen($field));
        $col = deserialize($dc->activeRecord->$fieldname)['col'];
        $options = $this->getCols($dc);

        if (isset($col) && is_string($col)) {
            foreach ($options as $value) {
                if ($value == $col) {
                    array_unshift($options, $value);
                }
            }
            $GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['options'] = $options;
            unset($GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['options_callback']);
        }

    }

    public function loadDefaultTable($value, $dc)
    {


        $field = $dc->field;
        $fieldname = substr($field, strlen('select_table_'), strlen($field));
        $table = deserialize($dc->activeRecord->$fieldname)['table'];

        $options = $this->getTables();

        if (isset($table) && is_string($table)) {
            foreach ($options as $value) {
                if ($value == $table) {
                    array_unshift($options, $value);
                }
            }
            $GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['options'] = $options;
            unset($GLOBALS['TL_DCA'][$dc->table]['fields'][$field]['options_callback']);
        }

    }

    public function getTables()
    {
        return $this->Database->listTables();
    }

    public function getTitle($dc)
    {
        $field = $dc->field;
        $fieldname = substr($field, strlen('select_title_'), strlen($field));
        $table = deserialize($dc->activeRecord->$fieldname)['table'];
        if (isset($table) && is_string($table) && $this->Database->tableExists($table)) {
            return $this->Database->getFieldNames($table);
        }
        return array();
    }

    public function getCols($dc)
    {
        $field = $dc->field;
        $fieldname = substr($field, strlen('select_col_'), strlen($field));
        $table = deserialize($dc->activeRecord->$fieldname)['table'];
        if (isset($table) && is_string($table) && $this->Database->tableExists($table)) {
            return $this->Database->getFieldNames($table);
        }
        return array();
    }

    public function save_select_table($value, $dc)
    {
        $id = $dc->id;
        $database = array();
        $database['table'] = $value;
        $field = $dc->field;
        $fieldname = substr($field, strlen('select_table_'), strlen($field));
        $dc->activeRecord->$fieldname = serialize($database);
        $this->Database->prepare('UPDATE ' . $dc->table . ' SET ' . $fieldname . '= ? WHERE id = ?')->execute(serialize($database), $id);
    }

    public function save_select_title($value, $dc)
    {
        $id = $dc->id;
        $field = $dc->field;
        $fieldname = substr($field, strlen('select_title_'), strlen($field));
        $database = deserialize($dc->activeRecord->$fieldname);
        $database['title'] = $value;
        $dc->activeRecord->$fieldname = serialize($database);
        $this->Database->prepare('UPDATE ' . $dc->table . ' SET ' . $fieldname . '= ? WHERE id = ?')->execute(serialize($database), $id);
    }

    public function save_select_col($value, $dc)
    {
        $id = $dc->id;
        $field = $dc->field;
        $fieldname = substr($field, strlen('select_col_'), strlen($field));
        $database = deserialize($dc->activeRecord->$fieldname);
        $database['col'] = $value;
        $dc->activeRecord->$fieldname = serialize($database);
        $this->Database->prepare('UPDATE ' . $dc->table . ' SET ' . $fieldname . '= ? WHERE id = ?')->execute(serialize($database), $id);
    }

    /**
     *
     */
    public function getChildName()
    {
        return $this->child = $this->name . '_data';
    }


    /**
     *
     */
    public function createCols()
    {
        $db = Database::getInstance();
        $rows = $GLOBALS['TL_DCA'][$this->name]['fields'];

        foreach ($rows as $name => $row) {

            if ($row['fmodule_filter']) {
                continue;
            }

            if ($name == 'id' || $name == 'tstamp') {
                continue;
            }

            if (!$db->fieldExists($name, $this->name)) {
                $db->prepare('ALTER TABLE ' . $this->name . ' ADD ' . $name . ' ' . $row['sql'])->execute();
            }
        }
    }

    /**
     *
     */
    public function createTable()
    {
        $db = Database::getInstance();
        $defaultCols = "id int(10) unsigned NOT NULL auto_increment, tstamp int(10) unsigned NOT NULL default '0'";

        if (!$db->tableExists($this->name)) {
            Database::getInstance()->prepare("CREATE TABLE IF NOT EXISTS " . $this->name . " (" . $defaultCols . ", PRIMARY KEY (id))")
                ->execute();
        }

        $this->createCols();

    }

}