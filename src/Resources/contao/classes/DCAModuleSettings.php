<?php namespace FModule;

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

use Contao\Database;
use Contao\Input;

/**
 * Class DCAModuleSettings
 */
class DCAModuleSettings extends ViewContainer
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
     * @return array
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
     * @return array
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
                    'icon' => 'edit.gif'
                ),

                'editheader' => array
                (
                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['editheader'],
                    'href' => 'act=edit',
                    'icon' => 'header.gif'
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
     * @param $moduleDB
     * @return array
     */
    public function setPalettes($moduleDB)
    {

        $fields = $moduleDB['fields'];

        $fieldStr = '{data_legend},';

        $arr = array();

        foreach ($fields as $field) {

            if(!$field['fieldID'])
            {
                continue;
            }

            if ($field['type'] !== 'simple_choice' || $field['type'] !== 'multi_choice') {
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
     * @return array
     */
    public function setSubPalettes()
    {
        return array(

            'addDetailPage' => 'rootPage',
            'allowComments' => 'notify,sortOrder,perPage,moderate,bbcode,requireLogin,disableCaptcha'

        );
    }

    /**
     * @param array $fields
     * @return array|mixed
     */
    public function setFields($fields = array())
    {

        $arr = $this->dcaSettingField();

        if(empty($fields))
        {
            return $arr;
        }

        foreach ($fields as $field) {

            //
            if(!$field['fieldID'])
            {
                continue;
            }

            //
            if($field['type'] == 'simple_choice' || $field['type'] == 'multi_choice')
            {
                $arr = $this->setOptionsFields($field, $arr);
            }

        }

        return $arr;

    }

    /**
     * @param $field
     * @param $arr
     * @return mixed
     */
    private function setOptionsFields($field, $arr)
    {

        if($field['dataFromTable'] == '1')
        {

            $fieldPrefixes = array('select_table_', 'select_col_', 'select_title_');

            for($i=0; $i < count($fieldPrefixes); $i++)
            {
                if($fieldPrefixes[$i])
                {
                    $arr[$fieldPrefixes[$i].$field['fieldID']] = $this->getOptionFromTableField($fieldPrefixes[$i], $field);
                }
            }

        }else{

            $arr[$field['fieldID']] = $this->getOptionField($field);

        }

        return $arr;
    }

    /**
     * @param $value
     * @param $dc
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

    /**
     * @param $value
     * @param $dc
     */
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

    /**
     * @param $value
     * @param $dc
     */
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

    /**
     * @return array
     */
    public function getTables()
    {
        return $this->Database->listTables();
    }

    /**
     * @param $dc
     * @return array
     */
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

    /**
     * @param $dc
     * @return array
     */
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

    /**
     * @param $value
     * @param $dc
     */
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

    /**
     * @param $value
     * @param $dc
     */
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

    /**
     * @param $value
     * @param $dc
     */
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
     * @return string
     */
    public function getChildName()
    {
        return $this->child = $this->name . '_data';
    }


    /**
     * @return void
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
     * @return void
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