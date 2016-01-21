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

use Contao\Backend;
use Contao\Config;
use Contao\Environment;
use Contao\Files;
use Contao\Image;
use Contao\Input;
use Contao\Database;
use Contao\BackendUser;


/**
 * Class DCACreator
 * @package FModule
 */
class DCACreator
{


    public $modules = array();

    /**
     *
     */
    public function index()
    {


        if (TL_MODE == 'BE') {
            Config::getInstance();
            Environment::getInstance();
            Input::getInstance();
            BackendUser::getInstance();
            Database::getInstance();

            /**
             * Boot BE Modules
             */
            if (Database::getInstance()->tableExists('tl_fmodules')) {
                $logLanguage = $_SESSION['fm_language'] ? $_SESSION['fm_language'] : 'de';
                Backend::loadLanguageFile('tl_fmodules_language_pack', $logLanguage);
                $this->loadDynDCA();
                $this->setDynLanguagePack();
            }


        }

    }


    public function setDynLanguagePack()
    {

        if (!Input::get('do') && !in_array(Input::get('do'), $this->modules)) {
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
    private function getModulesObj()
    {
        $db = Database::getInstance();
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
            $id = $modulesDB->row()['id'];

            $fieldsDB = $db->prepare("SELECT * FROM tl_fmodules_filters WHERE pid = ?")->execute($id);
            $fields = [];

            while ($fieldsDB->next()) {
                $field = [];
                $field['type'] = $fieldsDB->row()['type'];
                $field['fieldID'] = $fieldsDB->row()['fieldID'];
                $field['title'] = $fieldsDB->row()['title'];
                $field['description'] = $fieldsDB->row()['description'];
                $field['dataFromTable'] = $fieldsDB->row()['dataFromTable'];
                $field['isInteger'] = $fieldsDB->row()['isInteger'];
                $field['addTime'] = $fieldsDB->row()['addTime'];
                $field['fieldAppearance'] = $fieldsDB->row()['fieldAppearance'];
                $fields[] = $field;
            }

            $module['fields'] = $fields;
            $modules[] = $module;

        }

        return $modules;
    }

    /**
     * @param $moduleObj
     */
    private function initDCA($moduleObj)
    {
        /**
         * tablename
         */
        $tablename = $moduleObj['tablename'];

        if ($tablename == '') {
            return;
        }

        $this->modules[$tablename] = $moduleObj['name'];

        /**
         * parent
         */
        $dca_settings = new DCAModuleSettings();
        $dca_settings->init($tablename);
        $childname = $dca_settings->getChildName();
        $modulename = substr($tablename, 3, strlen($tablename));

        $GLOBALS['BE_MOD']['fmodules'][$modulename] = $this->getBEMod($tablename, $childname);

        $GLOBALS['TL_DCA'][$tablename] = array(

            'config' => $dca_settings->setConfig(),
            'list' => $dca_settings->setList(),
            'palettes' => $dca_settings->setPalettes($moduleObj['fields']),
            'subpalettes' => $dca_settings->setSubPalettes(),
            'fields' => $dca_settings->setFields($moduleObj['fields'])

        );

        $GLOBALS['TL_LANG']['MOD'][$modulename] = array($moduleObj['name'], $moduleObj['info']);

        $dca_settings->createTable();


        /**
         * child
         */
        $dca_data = new DCAModuleData();
        $dca_data->init($childname, $tablename);

        $GLOBALS['TL_DCA'][$childname] = array(

            'config' => $dca_data->setConfig($moduleObj['detailPage']),
            'list' => $dca_data->setList($moduleObj['sorting'], $moduleObj['sortingType'], $moduleObj['orderBy']),
            'palettes' => $dca_data->setPalettes($moduleObj['fields']),
            'subpalettes' => $dca_data->subPalettes(),
            'fields' => $dca_data->setFields($moduleObj['fields'])

        );

        $modname = substr($tablename, 3, strlen($tablename));

        $GLOBALS['TL_PERMISSIONS'][] = $modname;
        $GLOBALS['TL_PERMISSIONS'][] = $modname . 'p';
        $dca_data->createTable();


    }


    /**
     *
     */
    private function getBEMod($tablename, $childname)
    {
        $icon = $GLOBALS['FM_AUTO_PATH'] . 'fmodule.png';
        $path = $this->getModuleIcon($tablename);

        if (is_string($path)) {
            $icon = $path;
        }

        return [
            'icon' => $icon,
            'tables' => array($tablename, $childname, 'tl_content')
        ];
    }


    public function getModuleIcon($tablename)
    {

        $path = TL_ROOT . '/' . 'files/fmodule/assets/' . $tablename . '_icon.png';
        $file = Files::getInstance();

        if (!file_exists(TL_ROOT . '/' . 'files/fmodule')) {
            $file->mkdir('files/fmodule');
            $file->mkdir('files/fmodule/assets');
        }

        if (file_exists($path)) {
            return (version_compare(VERSION, '4.0', '>=') ? '../files/fmodule/assets/' : 'files/fmodule/assets/') . $tablename . '_icon.png';
        }

        return false;

    }

    /**
     *
     */
    private function loadDynDCA()
    {
        foreach ($this->getModulesObj() as $moduleObj) {
            $this->initDCA($moduleObj);
        }
    }

}