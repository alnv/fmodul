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

/**
 * Class DCACreator
 * @package FModule
 */
class DCACreator
{

    /**
     * @var array
     */
    public $modules = array();

    /**
     * @var null
     */
    static private $instance = null;

    /**
     * @return DCACreator|null
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     *
     */
    public function createLabels()
    {
        if (!\Input::get('do') && !in_array(\Input::get('do'), $this->modules)) {

            return;
        }

        $languages = &$GLOBALS['TL_LANG']['tl_fmodules_language_pack'];

        foreach ($languages as $key => $value) {

            foreach ($this->modules as $module => $name) {

                if ($key == 'new') {

                    $GLOBALS['TL_LANG'][$module]['new'] = $value[0];
                    $GLOBALS['TL_LANG'][$module . '_data']['new'] = array(sprintf($value[1][0], $name), $value[1][1]);
                    continue;
                }

                if ($key == 'fm_legend') {

                    $GLOBALS['TL_LANG'][$module] = $value;
                    $GLOBALS['TL_LANG'][$module . '_data'] = $value;
                    continue;
                }

                $GLOBALS['TL_LANG'][$module][$key] = $value;
                $GLOBALS['TL_LANG'][$module . '_data'][$key] = $value;
            }
        }
    }

    /**
     * @return array
     */
    private function createModules()
    {
        $db = \Database::getInstance();
        $modulesDB = $db->prepare("SELECT * FROM tl_fmodules")->execute();
        $modules = [];

        while ($modulesDB->next()) {

            $module = [];
            $module['name'] = $modulesDB->row()['name'];
            $module['tablename'] = $modulesDB->row()['tablename'];
            $module['info'] = $modulesDB->row()['info'];
            $module['sorting'] = $modulesDB->row()['sorting'];
            $module['sortingType'] = $modulesDB->row()['sortingType'];
            $module['orderBy'] = $modulesDB->row()['orderBy'];
            $module['paletteBuilder'] = $modulesDB->row()['paletteBuilder'];
            $module['selectNavigation'] = $modulesDB->row()['selectNavigation'];
            $module['selectPosition'] = $modulesDB->row()['selectPosition'];
            $module['addMandatoryHandler'] = $modulesDB->row()['addMandatoryHandler'];
            $module['mandatoryHandler'] = $modulesDB->row()['mandatoryHandler'];
            $module['disableOperationButtons'] = $modulesDB->row()['disableOperationButtons'];
            $module['operationButtons'] = $modulesDB->row()['operationButtons'];
            $id = $modulesDB->row()['id'];

            // backwards compatible
            $orderBy = 'sorting';

            if (!$db->fieldExists('sorting', 'tl_fmodules_filters')) {

                $orderBy = 'id';
            }

            $fieldsDB = $db->prepare("SELECT * FROM tl_fmodules_filters WHERE pid = ? ORDER BY " . $orderBy . "")->execute($id);
            $fields = [];

            while ($fieldsDB->next()) {
                $field = [];
                $field['pid'] = $fieldsDB->row()['pid'];
                $field['type'] = $fieldsDB->row()['type'];
                $field['fieldID'] = $fieldsDB->row()['fieldID'];
                $field['title'] = $fieldsDB->row()['title'];
                $field['description'] = $fieldsDB->row()['description'];
                $field['dataFromTable'] = $fieldsDB->row()['dataFromTable'];
                $field['widgetTemplate'] = $fieldsDB->row()['widgetTemplate'];
                $field['isInteger'] = $fieldsDB->row()['isInteger'];
                $field['autoPage'] = $fieldsDB->row()['autoPage'];
                $field['addTime'] = $fieldsDB->row()['addTime'];
                $field['from_field'] = $fieldsDB->row()['from_field'];
                $field['to_field'] = $fieldsDB->row()['to_field'];
                $field['widget_type'] = $fieldsDB->row()['widget_type'];
                $field['evalCss'] = $fieldsDB->row()['evalCss'];
                $field['isMandatory'] = $fieldsDB->row()['isMandatory'];
                $field['fieldAppearance'] = $fieldsDB->row()['fieldAppearance'];
                $field['mapTemplate'] = $fieldsDB->row()['mapTemplate'];
                $field['mapZoom'] = $fieldsDB->row()['mapZoom'];
                $field['mapScrollWheel'] = $fieldsDB->row()['mapScrollWheel'];
                $field['mapType'] = $fieldsDB->row()['mapType'];
                $field['mapStyle'] = $fieldsDB->row()['mapStyle'];
                $field['mapMarker'] = $fieldsDB->row()['mapMarker'];
                $field['rgxp'] = $fieldsDB->row()['rgxp'];
                $field['fmGroup'] = $fieldsDB->row()['fmGroup'];
                $field['dataFromTaxonomy'] = $fieldsDB->row()['dataFromTaxonomy'];
                $field['reactToTaxonomy'] = $fieldsDB->row()['reactToTaxonomy'];
                $field['reactToField'] = $fieldsDB->row()['reactToField'];
                $field['preventToggleIcon'] = $fieldsDB->row()['preventToggleIcon'];
                $fields[] = $field;
            }

            $module['fields'] = $fields;
            $modules[] = $module;
        }
        return $modules;
    }

    /**
     * @param $modulename
     * @return null
     */
    public function getModuleByTableName($modulename)
    {
        $modules = $this->createModules();

        foreach ($modules as $module) {

            if ($modulename == $module['tablename']) {

                return $module;
            }
        }

        return null;
    }

    /**
     * @param $module
     * @return null
     */
    private function createDCA($module)
    {
        // init tablename
        $tablename = $module['tablename'];

        if (!$tablename) return null;

        $this->modules[$tablename] = $module['name'];

        // parent
        $dcaSettings = new DCAModuleSettings();
        $dcaSettings->init($tablename);
        $childname = $dcaSettings->getChildName();
        $modulename = substr($tablename, 3, strlen($tablename));
        $navigation = $module['selectNavigation'] ? $module['selectNavigation'] : 'fmodules';
        $position = $module['selectPosition'] ? $module['selectPosition'] : 0;

        // create be module
        $backendModule = array();
        $backendModule[$modulename] = $this->createBackendModule($tablename, $childname);
        array_insert($GLOBALS['BE_MOD'][$navigation], $position, $backendModule);

        // parent
        $GLOBALS['TL_DCA'][$tablename] = array(
            'config' => $dcaSettings->setConfig(),
            'list' => $dcaSettings->setList(),
            'palettes' => $dcaSettings->setPalettes($module),
            'subpalettes' => $dcaSettings->setSubPalettes(),
            'fields' => $dcaSettings->setFields($module['fields'])
        );
        $GLOBALS['TL_LANG']['MOD'][$modulename] = array($module['name'], $module['info']);
        $dcaSettings->createTable();
        
        // child
        $dcaData = new DCAModuleData();
        $dcaData->init($childname, $tablename);
        $palette = $dcaData->setPalettes($module);
        $GLOBALS['TL_DCA'][$childname] = array(
            'config' => $dcaData->setConfig($module['detailPage']),
            'list' => $dcaData->setList($module),
            'select' => array('buttons_callback' => array(array('DCACreator', 'addAliasButton'))), // generate alias
            'palettes' => array('__selector__' => $palette['__selector__'], 'default' => $palette['default']),
            'subpalettes' => $palette['subPalettes'],
            'fields' => $dcaData->setFields($module)
        );

        // set permissions
        $modname = substr($tablename, 3, strlen($tablename));
        $GLOBALS['TL_PERMISSIONS'][] = $modname;
        $GLOBALS['TL_PERMISSIONS'][] = $modname . 'p';
        $dcaData->createTable();
    }

    /**
     * @param $arrButtons
     * @return string
     */
    public function addAliasButton($arrButtons)
    {

        if (\Input::post('FORM_SUBMIT') == 'tl_select' && isset($_POST['alias'])) {

            // init objects
            $objSession = \Session::getInstance();
            $objDataBase = \Database::getInstance();

            $session = $objSession->getData();
            $ids = $session['CURRENT']['IDS'];
            $strTable = \Input::get('table');

            if (!$strTable || !$objDataBase->tableExists($strTable)) {
                return $arrButtons;
            }

            foreach ($ids as $id) {

                $objData = $objDataBase->prepare('SELECT id, alias, title FROM ' . $strTable . ' WHERE id=?')->limit(1)->execute($id);

                if (!$objData->count()) {
                    continue;
                }

                $arrData = $objData->row();
                $strAlias = \StringUtil::generateAlias($arrData['title']);

                if ($strAlias == $arrData['alias']) {
                    continue;
                }

                $objAlias = $objDataBase->prepare('SELECT id FROM ' . $strTable . ' WHERE alias = ?')->execute($strAlias);
                if ($objAlias->numRows > 1) {
                    $strAlias .= '-' . $arrData['id'];
                }

                // Initialize the version manager
                $objVersions = new \Versions($strTable, $id);
                $objVersions->initialize();

                $objDataBase->prepare('UPDATE '.$strTable.' SET alias=? WHERE id=?')->execute($strAlias, $id);

                $objVersions->create();

            }

            \Controller::redirect(\Controller::getReferer());

        }

        $arrButtons['alias'] = '<input type="submit" name="alias" id="alias" class="tl_submit" accesskey="a" value="' . specialchars($GLOBALS['TL_LANG']['MSC']['aliasSelected']) . '"> ';
        return $arrButtons;
    }

    /**
     * @param $tablename
     * @param $childname
     * @return array
     */
    private function createBackendModule($tablename, $childname)
    {
        $icon = 'system/modules/fmodule/assets/fmodule.png';
        $path = $this->getModuleIcon($tablename);

        if (is_string($path)) {
            $icon = $path;
        }

        return [
            'icon' => $icon,
            'tables' => array($tablename, $childname, 'tl_content')
        ];
    }

    /**
     * @param $tablename
     * @return bool|string
     */
    public function getModuleIcon($tablename)
    {
        $path = TL_ROOT . '/' . 'files/fmodule/assets/' . $tablename . '_icon';
        $file = \Files::getInstance();
        $allowedFormat = array('gif', 'png', 'svg');

        if (!file_exists(TL_ROOT . '/' . 'files/fmodule')) {
            $file->mkdir('files/fmodule');
            $file->mkdir('files/fmodule/assets');
        }

        foreach ($allowedFormat as $format) {
            if (file_exists($path . '.' . $format)) {
                return (version_compare(VERSION, '4.0', '>=') ? '../files/fmodule/assets/' : 'files/fmodule/assets/') . $tablename . '_icon' . '.' . $format;
            }
        }

        return false;
    }

    /**
     *
     */
    public function loadModules()
    {
        foreach ($this->createModules() as $module) {
            
            $this->createDCA($module);
        }
    }

}