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
            )
        ),
        'operations' => array(

            'editheader' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['editheader'],
                'href' => 'act=edit',
                'icon' => 'header.gif'
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
        '__selector__' => array('protected', 'addMandatoryHandler', 'disableOperationButtons'),
        'default' => '{main_legend},name,info,tablename;{navigation_legend},selectNavigation,selectPosition;{palettes_builder_legend},paletteBuilder;{list_legend},sorting,orderBy;{mandatory_legend},addMandatoryHandler;{permission_legend},disableOperationButtons'
    ),
    'subpalettes' => array(
        'addMandatoryHandler' => 'mandatoryHandler',
        'disableOperationButtons' => 'operationButtons'
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
                array('tl_fmodules', 'parseTableName'),
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
            'exclude' => true,
            'default' => 'title',
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_fmodules'],
            'options_callback' => array('tl_fmodules', 'getSortingOptions'),
            'eval' => array('mandatory' => true, 'tl_class' => 'w50'),
            'save_callback' => array(
                array('tl_fmodules', 'saveSortingType')
            ),
            'sql' => "varchar(64) NOT NULL default 'title'"
        ),
        'orderBy' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['orderBy'],
            'default' => 'asc',
            'exclude' => true,
            'inputType' => 'select',
            'reference' => &$GLOBALS['TL_LANG']['tl_fmodules'],
            'options' => array('asc', 'desc'),
            'eval' => array('mandatory' => true, 'tl_class' => 'w50'),
            'sql' => "varchar(12) NOT NULL default 'asc'"
        ),
        'sortingType' => array(
            'sql' => "varchar(64) NOT NULL default ''"
        ),
        'paletteBuilder' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['paletteBuilder'],
            'exclude' => true,
            'inputType' => 'checkboxWizard',
            'options_callback' => array('tl_fmodules', 'getPalettes'),
            'reference' => &$GLOBALS['TL_LANG']['tl_fmodules'],
            'eval' => array('multiple' => true),
            'sql' => "blob NULL"
        ),
        'selectNavigation' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['selectNavigation'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => array('tl_fmodules', 'getNavigation'),
            'eval' => array('tl_class' => 'w50', 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'selectPosition' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['selectPosition'],
            'exclude' => true,
            'inputType' => 'select',
            'options_callback' => array('tl_fmodules', 'getPosition'),
            'eval' => array('tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'addMandatoryHandler' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['addMandatoryHandler'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => array('submitOnChange' => true),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'mandatoryHandler' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['mandatoryHandler'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'options_callback' => array('tl_fmodules', 'getDataProperties'),
            'eval' => array('multiple' => true),
            'sql' => "blob NULL"
        ),
        'disableOperationButtons' => array (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['disableOperationButtons'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => array('submitOnChange' => true),
            'sql' => "char(1) NOT NULL default ''"
        ),
        'operationButtons' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules']['operationButtons'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'reference' => &$GLOBALS['TL_LANG']['tl_fmodules'],
            'options' => array('list', 'detail'),
            'eval' => array('multiple' => true, 'csv'=>','),
            'sql' => "varchar(512) NOT NULL default ''"
        )
    )
);

/**
 * Class tl_fmodules
 */
class tl_fmodules extends \Backend
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
     * @return array
     */
    public function getDataProperties()
    {
        // set variables here
        $return = array();

        $dataContainer = \FModule\ViewContainer::getInstance();
        $arrFields = $dataContainer->dcaDataFields();
        $noNotSet = array('id', 'pid', 'tstamp', 'alias', 'author', 'source', 'url', 'jumpTo', 'target', 'protected', 'groups', 'guests', 'cssID', 'published');

        if(is_array($arrFields))
        {
            foreach($arrFields as $name => $field)
            {
                if(in_array($name, $noNotSet))
                {
                    continue;
                }
                $return[$name] = $field['label'] ? $field['label'][0] : $name;
            }
        }

        return $return;
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

        switch (Input::get('act')) {
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
     * @return array
     */
    public function getNavigation()
    {
        $arrModules = $GLOBALS['BE_MOD'] ? $GLOBALS['BE_MOD'] : array();
        $modules = array();
        if (is_array($arrModules)) {
            foreach ($arrModules as $name => $module) {
                $label = '';

                if ($GLOBALS['TL_LANG']['MOD'][$name] && is_array($GLOBALS['TL_LANG']['MOD'][$name])) {
                    $label = $GLOBALS['TL_LANG']['MOD'][$name][0];
                }

                if ($GLOBALS['TL_LANG']['MOD'][$name] && is_string($GLOBALS['TL_LANG']['MOD'][$name])) {
                    $label = $GLOBALS['TL_LANG']['MOD'][$name];
                }

                $modules[$name] = $label ? $label : $name;
            }
        }
        return $modules;
    }

    /**
     * @return array
     */
    public function getPosition()
    {
        return array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30);
    }

    /**
     * @return array
     */
    public function getPalettes()
    {
        return array(
            'datePalette' => 'datePalette',
            'imagePalette' => 'imagePalette',
            'imageSettingsPalette' => 'imageSettingsPalette',
            'galleryPalette' => 'galleryPalette',
            'enclosurePalette' => 'enclosurePalette',
            'geoPalette' => 'geoPalette',
            'geoAddressPalette' => 'geoAddressPalette',
            'markerPalette' => 'markerPalette'
        );
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
        return ($this->User->isAdmin || !empty($this->User->fmodulesfeed) || $this->User->hasAccess('create', 'fmodulesfeedp')) ? '<a href="' . $this->addToUrl($href) . '" class="' . $class . '" title="' . specialchars($title) . '"' . $attributes . '>' . $label . '</a> ' : '';
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
     * @return mixed
     */
    public function parseTableName($varValue) {
        return str_replace('-', '_', $varValue);
    }

    /**
     * @param $varValue
     * @return mixed
     * @throws Exception
     */
    public function generateTableName($varValue)
    {
        if (substr($varValue, 0, 3) == 'fm_' && substr($varValue, 3)) {
            return $varValue;
        }
        throw new \Exception($GLOBALS['TL_LANG']['tl_fmodules']['invalidTableName']);
    }

    /**
     * @param DataContainer $dc
     */
    public function createGroupCols(\DataContainer $dc)
    {
        $tableName = $dc->activeRecord->tablename;
        $moduleName = substr($tableName, 3, strlen($tableName));

        if ((!$this->Database->tableExists($moduleName)) && (!$this->Database->fieldExists($moduleName, 'tl_user') && !$this->Database->fieldExists($moduleName, 'tl_user_group'))) {
            $this->Database->prepare('ALTER TABLE tl_user ADD ' . $moduleName . ' blob NULL;')->execute();
            $this->Database->prepare('ALTER TABLE tl_user ADD ' . $moduleName . 'p blob NULL;')->execute();
            $this->Database->prepare('ALTER TABLE tl_user_group ADD ' . $moduleName . ' blob NULL;')->execute();
            $this->Database->prepare('ALTER TABLE tl_user_group ADD ' . $moduleName . 'p blob NULL;')->execute();
        }

        if (!\Config::get('bypassCache')) {
            $a = new \Automator();
            $a->purgeInternalCache();
        }
    }

    /**
     * @param $varValue
     * @param DataContainer $dc
     * @return string
     */
    public function saveSortingType($varValue, \DataContainer $dc)
    {
        $id = $dc->activeRecord->id;

        if (!$varValue) {
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
    public function getSortingOptions(\DataContainer $dc)
    {
        $id = $dc->activeRecord->id;
        $db = $this->Database->prepare('SELECT fieldID, title, type FROM tl_fmodules_filters WHERE pid = ?')->execute($id);
        $return = array('title' => 'title', 'id' => 'id', 'date.5' => 'date.5', 'date.7' => 'date.7', 'date.9' => 'date.9');
        while ($db->next()) {

            if ($db->fieldID == 'orderBy' || $db->fieldID == 'sorting_fields' || $db->fieldID == 'pagination') {
                continue;
            }

            if ($db->type == 'date_field') {
                $return[$db->fieldID . '.5'] = $db->title . ' (d)';
                $return[$db->fieldID . '.7'] = $db->title . ' (m)';
                $return[$db->fieldID . '.9'] = $db->title . ' (Y)';
            }

            if ($db->type == 'simple_choice') {
                $return[$db->fieldID] = $db->title;
            }
        }
        return $return;
    }

    /**
     * @param DataContainer $dc
     * @return null
     */
    public function deleteTable(\DataContainer $dc)
    {
        $tName = $dc->activeRecord->tablename;

        if (!$tName) return null;
        $dataTable = $tName . '_data';
        $this->Database->prepare("DROP TABLE " . $tName)->execute();
        $this->Database->prepare("DROP TABLE " . $dataTable)->execute();
        $this->Database->prepare("DELETE FROM tl_content WHERE ptable = ?")->execute($dataTable);

        $moduleName = substr($tName, 3, strlen($tName));

        if ($this->Database->fieldExists($moduleName, 'tl_user')) {
            $this->Database->prepare('ALTER TABLE tl_user DROP COLUMN ' . $moduleName . '')->execute();
            $this->Database->prepare('ALTER TABLE tl_user DROP COLUMN ' . $moduleName . 'p ')->execute();
        }
        if ($this->Database->fieldExists($moduleName, 'tl_user_group')) {
            $this->Database->prepare('ALTER TABLE tl_user_group DROP COLUMN ' . $moduleName . '')->execute();
            $this->Database->prepare('ALTER TABLE tl_user_group DROP COLUMN ' . $moduleName . 'p ')->execute();
        }

        if (!\Config::get('bypassCache')) {
            $a = new \Automator();
            $a->purgeInternalCache();
        }
    }

    /**
     * @param $varValue
     * @param DataContainer $dc
     * @return mixed
     */
    public function updateTable($varValue, \DataContainer $dc)
    {
        if (!$dc->activeRecord->tablename) {
            return $varValue;
        }

        $preTableName = $dc->activeRecord->tablename;
        $strTableName = $varValue;
        $preDataTableName = $preTableName . '_data';
        $strDataTableName = $strTableName . '_data';

        if (!$this->Database->tableExists($varValue) && $preTableName != $strTableName) {
            $this->Database->prepare("RENAME TABLE " . $preTableName . " TO " . $strTableName . "")->execute();
            $this->Database->prepare("RENAME TABLE " . $preDataTableName . " TO " . $strDataTableName . "")->execute();
            $this->Database->prepare("UPDATE tl_content SET ptable = ? WHERE ptable = ?")->execute($strDataTableName, $preDataTableName);
        }

        if (!\Config::get('bypassCache')) {
            $a = new \Automator();
            $a->purgeInternalCache();
        }
        return $varValue;
    }
}