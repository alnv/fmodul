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
use Contao\Image;
use Contao\StringUtil;

/**
 * Class DCAModule
 * @package FModule
 */
class DCAModuleData extends DCAHelper
{

    /**
     * @var
     */
    protected $name;
    protected $parent;
    protected $id;
    protected $pid;

    private $doNotSetByType = array('wrapper_field', 'legend_start', 'legend_end', 'fulltext_search');
    private $doNotSetByID = array('orderBy', 'sorting_fields', 'pagination');

    /**
     *
     */
    public function init($dcaname, $parentdcaname)
    {
        $this->name = $dcaname;
        $this->parent = $parentdcaname;
        $this->getIDs();
    }

    /**
     * @param $fieldname
     * @return bool
     */
    private function permissionFieldExist($fieldname)
    {
        if (!$this->Database->fieldExists($fieldname, 'tl_user') || !$this->Database->fieldExists($fieldname . 'p', 'tl_user')) {
            return false;
        }

        if (!$this->Database->fieldExists($fieldname, 'tl_user_group') || !$this->Database->fieldExists($fieldname . 'p', 'tl_user_group')) {
            return false;
        }

        return true;
    }

    /**
     * @param $dc
     */
    public function checkPermission($dc)
    {

        $modname = substr($dc->table, 3, strlen($dc->table));
        $modname = str_replace('_data', '', $modname);

        $allowedFields = $modname;

        if (!$this->permissionFieldExist($modname)) {
            return;
        }

        if ($this->User->isAdmin) {
            return;
        }

        if (!is_array($this->User->$allowedFields) || empty($this->User->$allowedFields)) {
            $root = array(0);
        } else {
            $root = $this->User->$allowedFields;
        }

        $id = strlen(Input::get('id')) ? Input::get('id') : CURRENT_ID;

        switch (Input::get('act')) {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!strlen(Input::get('pid')) || !in_array(Input::get('pid'), $root)) {
                    $this->log('Not enough permissions to create F Module items in ' . $modname . ' Wrapper ID "' . Input::get('pid') . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'cut':
            case 'copy':
                if (!in_array(Input::get('pid'), $root)) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' F Module item ID "' . $id . '" to ' . $modname . ' Wrapper ID "' . Input::get('pid') . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $this->Database->prepare("SELECT pid FROM " . $dc->table . " WHERE id=?")
                    ->limit(1)
                    ->execute($id);

                if ($objArchive->numRows < 1) {
                    $this->log('Invalid F Module item ID "' . $id . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }

                if (!in_array($objArchive->pid, $root)) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' F Module item ID "' . $id . '" of ' . $modname . ' Wrapper ID "' . $objArchive->pid . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root)) {
                    $this->log('Not enough permissions to access ' . $modname . ' Wrapper ID "' . $id . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }

                $objArchive = $this->Database->prepare("SELECT id FROM " . $dc->table . " WHERE pid=?")
                    ->execute($id);

                if ($objArchive->numRows < 1) {
                    $this->log('Invalid F Module ID "' . $id . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }

                $session = $this->Session->getData();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $this->Session->setData($session);
                break;

            default:
                if (strlen(Input::get('act'))) {
                    $this->log('Invalid command "' . Input::get('act') . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                } elseif (!in_array($id, $root)) {
                    $this->log('Not enough permissions to access ' . $modname . ' Wrapper ID ' . $id, __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }

    }

    /**
     *
     */
    public function getIDs()
    {
        if (Input::get('act') == 'edit' && Input::get('table') == $this->name) {
            $id = Input::get('id');
            $idsDB = $this->Database->prepare("SELECT id, pid FROM " . $this->name . " WHERE id = ?")->execute($id)->row();
            $this->id = $idsDB['id'];
            $this->pid = $idsDB['pid'];
        }

    }

    /**
     *
     */
    public function setConfig($detailPage)
    {

        $parent = $this->parent;

        $config = array(

            'dataContainer' => 'Table',
            'ptable' => $parent,
            'ctable' => array('tl_content'),
            'enableVersioning' => true,

            'onload_callback' => array
            (
                array('DCAModuleData', 'checkPermission'),
                array('DCAModuleData', 'generateFeed')
            ),
            'onsubmit_callback' => array
            (
                array('DCAModuleData', 'scheduleUpdate')
            ),

            'sql' => array(

                'keys' => array
                (
                    'id' => 'primary',
                    'pid' => 'index'
                )

            )

        );

        if ($detailPage) {
            $config['ctable'] = array('tl_content');
        }

        return $config;
    }


    /**
     *
     */
    public function setList($moduleObj)
    {


        $sortingField = $moduleObj['sorting'];
        $sortingType = $moduleObj['sortingType'];
        $orderBy = $moduleObj['orderBy'];
        $fields = $moduleObj['fields'];

        $flag = 1;
        $mode = 0;
        $arrFlag = explode('.', $sortingType);
        $arrField = explode('.', $sortingField);

        if ($arrField[0] && $arrField[0] != 'id') {
            $mode = 1;
        }

        if ($arrFlag[1]) {
            $flag = (int)$arrFlag[1];
        }

        if ($orderBy == 'desc') {
            $flag = $flag + 1;
        }

        $list = array(

            'sorting' => array(

                'mode' => $mode,
                'flag' => $flag,
                'fields' => array($arrField[0]),
                'panelLayout' => 'sort;search,limit,filter'

            ),

            'label' => array(

                'fields' => array('title', 'info'),
                'format' => '%s <span style="color: #c2c2c2">(%s)</span>'

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
                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['itemheader'],
                    'href' => 'act=edit',
                    'icon' => 'header.gif'//$GLOBALS['FM_AUTO_PATH'] . 'fields.png'
                ),

                'editList' => array
                (
                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['editList'],
                    'href' => 'table=tl_content&view=list',
                    'icon' => $GLOBALS['FM_AUTO_PATH'] . 'page.png'
                ),

                'editDetail' => array(

                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['editDetail'],
                    'href' => 'table=tl_content&view=detail',
                    'icon' => $GLOBALS['FM_AUTO_PATH'] . 'detail.png'

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

                'toggle' => array
                (
                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['toggle'],
                    'icon' => 'visible.gif',
                    'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                    'button_callback' => array('DCAModuleData', 'toggleIcon')
                )
            )
        );

        foreach ($fields as $field) {

            if ($field['fieldID'] && $field['type'] == 'toggle_field') {

                $list['operations'][$field['fieldID']] = array(

                    'label' => array($field['title'], $field['description']),
                    'icon' => $this->getToggleIcon('1', $field['description'], $field['fieldID'], true),
                    'href' => $field['fieldID'],
                    'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleFMField(this)"',
                    'button_callback' => array('DCAModuleData', 'iconFeatured')

                );

            }

        }

        $list['operations']['show'] = array(

            'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['show'],
            'href' => 'act=show',
            'icon' => 'show.gif'

        );

        return $list;
    }

    /**
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @param $attributes
     * @return string
     */
    public function iconFeatured($row, $href, $label, $title, $icon, $attributes)
    {

        $field = $href;

        if (strlen(Input::get('fid'))) {

            $id = Input::get('fid');
            $state = Input::get('state');
            $col = Input::get('col');

            $this->toggleFMField($id, ($state == 1), $col);
            $this->redirect($this->getReferer());

        }

        $href = '&amp;fid=' . $row['id'] . '&amp;col=' . $field . '&amp;state=' . ($row[$field] ? '' : 1);

        $imageHTML = $this->getToggleIcon($row[$field], $label, $field, false);

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $imageHTML . '</a> ';

    }

    /**
     * Feature/unfeature a news item
     *
     * @param integer $intId
     * @param boolean $blnVisible
     *
     * @return string
     */
    public function toggleFMField($intId, $blnVisible, $field)
    {

        $table = Input::get('table');
        $field = $field ? $field : Input::post('col');

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA'][$table]['fields'][$field]['save_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$table]['fields'][$field]['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $this);
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE " . $table . " SET tstamp=" . time() . ", " . $field . "='" . ($blnVisible ? '1' : '') . "' WHERE id=?")->execute($intId);

    }

    /**
     *
     */
    public function setPalettes($fields = array())
    {

        $isLegend = DCAHelper::isLegend($fields);
        $palette = '';

        if ($isLegend) {
            foreach ($fields as $field) {

                $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['fm_legend'][$field['fieldID']] = $field['title'];

                if ($field['type'] == 'legend_start') {
                    $palette .= '{' . $field['fieldID'] . '}';
                }

                if ($field['fieldID'] && !in_array($field['fieldID'], $this->doNotSetByID) && !in_array($field['type'], $this->doNotSetByType)) {
                    $palette .= ',' . $field['fieldID'];
                }

                if ($field['type'] == 'legend_end') {
                    $palette .= ';';
                }
            }
        }


        if (!$isLegend) {
            $palette = '{meta_legend},';
            $arr = array();

            foreach ($fields as $field) {

                if ($field['fieldID'] && !in_array($field['fieldID'], $this->doNotSetByID) && !in_array($field['type'], $this->doNotSetByType)) {

                    $arr[] = $field['fieldID'];

                }

            }

            $palette .= implode(',', $arr) . ';';
        }

        return array(
            '__selector__' => array('source', 'addImage', 'protected', 'addEnclosure', 'published'),
            'default' => '{general_legend},title,alias,author,info,description;{date_legend},date,time;{image_legend},addImage;{enclosure_legend:hide},addEnclosure;{source_legend:hide},source;' . $palette . '{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{publish_legend},published;'
        );
    }

    /**
     *
     */
    public function subPalettes()
    {
        return array(
            'source_internal' => 'jumpTo',
            'source_external' => 'url,target',
            'addImage' => 'singleSRC,alt,size,caption',
            'addEnclosure' => 'enclosure',
            'protected' => 'groups',
            'published' => 'start,stop'
        );
    }


    public function getUserID()
    {
        $hash = Input::cookie('BE_USER_AUTH');
        $id = '0';

        if (isset($hash) && $hash != '') {
            $sessionDB = $this->Database->prepare('SELECT * FROM tl_session WHERE hash = ?')->execute($hash);
            if ($sessionDB->count() > 0) {
                $id = $sessionDB->row()['pid'];
            }
        }
        return $id;
    }

    /**
     *
     */
    public function setFields($moduleObj = array())
    {

        $fields = $moduleObj['fields'];
        $userID = $this->getUserID();

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
                'search' => true,
                'eval' => array('maxlength' => 255, 'mandatory' => true, 'tl_class' => 'w50'),
                'sql' => "varchar(255) NOT NULL default ''"

            ),

            'info' => array(

                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['info'],
                'inputType' => 'text',
                'exclude' => true,
                'search' => true,
                'eval' => array('maxlength' => 255, 'tl_class' => 'long clr'),
                'sql' => "varchar(255) NOT NULL default ''"

            ),

            'author' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['author'],
                'default' => $userID,
                'exclude' => true,
                'filter' => true,
                'inputType' => 'select',
                'foreignKey' => 'tl_user.name',
                'eval' => array('doNotCopy' => true, 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'),
                'relation' => array('type' => 'hasOne', 'load' => 'eager'),
                'sql' => "int(10) unsigned NOT NULL default '0'",
            ),

            'date' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['date'],
                'default' => time(),
                'exclude' => true,
                'filter' => true,
                'sorting' => true,
                'flag' => 8,
                'inputType' => 'text',
                'eval' => array('rgxp' => 'date', 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard'),
                'sql' => "int(10) unsigned NULL"
            ),

            'time' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['time'],
                'default' => time(),
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('rgxp' => 'time', 'doNotCopy' => true, 'tl_class' => 'w50'),
                'sql' => "int(10) unsigned NULL"
            ),

            'description' => array(

                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['description'],
                'inputType' => 'textarea',
                'exclude' => true,
                'search' => true,
                'eval' => array('tl_class' => 'clr', 'rte' => 'tinyMCE'),
                'sql' => "mediumtext NULL"

            ),

            'alias' => array(

                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['alias'],
                'inputType' => 'text',
                'exclude' => true,
                'eval' => array('rgxp' => 'alias', 'maxlength' => 128, 'tl_class' => 'w50', 'doNotCopy' => true),
                'save_callback' => array(array('DCAModuleData', 'generateAlias')),
                'sql' => "varchar(128) COLLATE utf8_bin NOT NULL default ''"

            ),

            'url' => array
            (
                'label' => &$GLOBALS['TL_LANG']['MSC']['url'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),

            'target' => array
            (
                'label' => &$GLOBALS['TL_LANG']['MSC']['target'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('tl_class' => 'w50 m12'),
                'sql' => "char(1) NOT NULL default ''"
            ),

            'source' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['source'],
                'default' => 'default',
                'exclude' => true,
                'inputType' => 'radio',
                'options' => array('default', 'internal', 'external'),
                'reference' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack'],
                'eval' => array('submitOnChange' => true, 'helpwizard' => true),
                'sql' => "varchar(32) NOT NULL default ''"
            ),

            'jumpTo' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['jumpTo'],
                'exclude' => true,
                'inputType' => 'pageTree',
                'foreignKey' => 'tl_page.title',
                'eval' => array('mandatory' => true, 'fieldType' => 'radio'),
                'sql' => "int(10) unsigned NOT NULL default '0'",
                'relation' => array('type' => 'belongsTo', 'load' => 'lazy')
            ),

            'protected' => array(

                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['protected'],
                'inputType' => 'checkbox',
                'exclude' => true,
                'eval' => array('submitOnChange' => true),
                'sql' => "char(1) NOT NULL default ''"

            ),

            'groups' => array(

                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['groups'],
                'inputType' => 'checkbox',
                'exclude' => true,
                'foreignKey' => 'tl_member_group.name',
                'eval' => array('mandatory' => true, 'multiple' => true),
                'sql' => "blob NULL",
                'relation' => array('type' => 'hasMany', 'load' => 'lazy')

            ),

            'guests' => array(

                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['guests'],
                'filter' => true,
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('tl_class' => 'w50'),
                'sql' => "char(1) NOT NULL default ''"

            ),

            'cssID' => array(

                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['cssID'],
                'inputType' => 'text',
                'exclude' => true,
                'eval' => array('multiple' => true, 'size' => 2, 'tl_class' => 'w50 clr'),
                'sql' => "varchar(255) NOT NULL default ''"

            ),

            'published' => array(

                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['published'],
                'inputType' => 'checkbox',
                'filter' => true,
                'exclude' => true,
                'eval' => array('submitOnChange' => true, 'doNotCopy' => true),
                'sql' => "char(1) NOT NULL default ''"

            ),

            'start' => array(

                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['start'],
                'inputType' => 'text',
                'exclude' => true,
                'eval' => array('rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'),
                'sql' => "varchar(10) NOT NULL default ''"

            ),

            'stop' => array(

                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['stop'],
                'inputType' => 'text',
                'exclude' => true,
                'eval' => array('rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard'),
                'sql' => "varchar(10) NOT NULL default ''"

            ),

            //enclosure
            'addEnclosure' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['addEnclosure'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('submitOnChange' => true),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'enclosure' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['enclosure'],
                'exclude' => true,
                'inputType' => 'fileTree',
                'eval' => array('multiple' => true, 'fieldType' => 'checkbox', 'filesOnly' => true, 'isDownloads' => true, 'mandatory' => true, 'extensions' => \Config::get('allowedDownload')),
                'sql' => "blob NULL"
            ),

            //image
            'addImage' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['addImage'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('submitOnChange' => true),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'singleSRC' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['singleSRC'],
                'exclude' => true,
                'inputType' => 'fileTree',
                'eval' => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'extensions' => \Config::get('validImageTypes')),
                'sql' => "binary(16) NULL"
            ),
            'alt' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['alt'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'tl_class' => 'long'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),

            'size' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['size'],
                'exclude' => true,
                'inputType' => 'imageSize',
                'options' => \System::getImageSizes(),
                'reference' => &$GLOBALS['TL_LANG']['MSC'],
                'eval' => array('rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'),
                'sql' => "varchar(64) NOT NULL default ''"
            ),

            'caption' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['caption'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'allowHtml' => true, 'tl_class' => 'w50'),
                'sql' => "varchar(255) NOT NULL default ''"
            )


        );

        foreach ($fields as $field) {

            $options = $this->getOptions($field, $moduleObj);

            if (in_array($field['fieldID'], $this->doNotSetByID)) {
                continue;
            }

            $mandatory = $field['isMandatory'] ? true : false;
            $evalCss = $field['evalCss'] ? $field['evalCss'] : 'clr';

            if ($field['fieldID'] !== '' && $field['type'] == 'widget') {
                $arr[$field['fieldID']] = DCAHelper::getFieldFromWidget($field);
            }

            if ($field['fieldID'] !== '' && $field['type'] == 'simple_choice') {
                $arr[$field['fieldID']] = array(
                    'label' => array($field['title'], $field['description']),
                    'filter' => true,
                    'search' => true,
                    'exclude' => true,
                    'inputType' => 'select',
                    'options' => $options,
                    'eval' => array('tl_class' => $evalCss, 'mandatory' => $mandatory, 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
                    'sql' => "text NULL"
                );
                if ($field['fieldAppearance'] == 'radio') {
                    $arr[$field['fieldID']]['inputType'] = 'radio';
                }
                if ($field['fieldAppearance'] == 'select') {
                    $arr[$field['fieldID']]['inputType'] = 'select';
                }
            }

            if ($field['fieldID'] !== '' && $field['type'] == 'multi_choice') {
                $arr[$field['fieldID']] = array(
                    'label' => array($field['title'], $field['description']),
                    'filter' => true,
                    'search' => true,
                    'exclude' => true,
                    'inputType' => 'checkbox',
                    'options' => $options,
                    'eval' => array('multiple' => true, 'mandatory' => $mandatory, 'tl_class' => $evalCss, 'csv' => ','),
                    'sql' => "text NULL"
                );
                if ($field['fieldAppearance'] == 'checkbox') {
                    $arr[$field['fieldID']]['inputType'] = 'checkbox';
                }
                if ($field['fieldAppearance'] == 'tags') {
                    $arr[$field['fieldID']]['inputType'] = 'select';
                    $arr[$field['fieldID']]['eval']['chosen'] = true;
                }
            }

            if ($field['fieldID'] !== '' && $field['type'] == 'search_field') {
                $arr[$field['fieldID']] = array(
                    'label' => array($field['title'], $field['description']),
                    'search' => true,
                    'exclude' => true,
                    'inputType' => 'text',
                    'eval' => array('tl_class' => $evalCss, 'mandatory' => $mandatory),
                    'sql' => "text NULL"
                );
            }

            if ($field['fieldID'] !== '' && $field['type'] == 'date_field') {
                $arr[$field['fieldID']] = array(
                    'label' => array($field['title'], $field['description']),
                    'default' => time(),
                    'exclude' => true,
                    'sorting' => true,
                    'search' => true,
                    'inputType' => 'text',
                    'eval' => array('rgxp' => 'date', 'doNotCopy' => true, 'mandatory' => $mandatory, 'datepicker' => true, 'tl_class' => 'wizard ' . $evalCss . ''),
                    'sql' => "int(10) unsigned NULL"
                );
                if ($field['addTime']) {
                    $arr[$field['fieldID']]['eval']['rgxp'] = 'datim';
                }
            }

            if ($field['fieldID'] !== '' && $field['type'] == 'toggle_field') {
                $arr[$field['fieldID']] = array(
                    'label' => array($field['title'], $field['description']),
                    'inputType' => 'checkbox',
                    'exclude' => true,
                    'filter' => true,
                    'eval' => array('tl_class' => $evalCss, 'doNotCopy' => true),
                    'sql' => "char(1) NOT NULL default ''"
                );
            }
        }

        return $arr;
    }

    /**
     * @param $varValue
     * @param $dc
     * @return string
     * @throws \Exception
     */
    public function generateAlias($varValue, $dc)
    {
        $autoAlias = false;

        // Generate alias if there is none
        if ($varValue == '') {
            $autoAlias = true;
            $varValue = StringUtil::generateAlias($dc->activeRecord->title);
        }

        $table = Input::get('table');
        $pid = $dc->activeRecord->pid;

        $objAlias = $this->Database->prepare("SELECT id FROM " . $table . " WHERE alias=? AND pid = ?")->execute($varValue, $pid);

        // Check whether the alias exists
        if ($objAlias->numRows > 1 && !$autoAlias) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));

        }

        // Add ID to alias
        if ($objAlias->numRows && $autoAlias) {
            $varValue .= '-' . $dc->id;
        }

        return $varValue;
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

            if ($name == 'id' || $name == 'tstamp' || $name == 'pid') {
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
        $defaultCols = "id int(10) unsigned NOT NULL auto_increment, tstamp int(10) unsigned NOT NULL default '0', pid int(10) unsigned NOT NULL default '0'";

        if (!$db->tableExists($this->name)) {
            Database::getInstance()->prepare("CREATE TABLE IF NOT EXISTS " . $this->name . " (" . $defaultCols . ", PRIMARY KEY (id))")
                ->execute();
        }

        $this->createCols();

    }

    /**
     *
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {

        if (strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';

    }

    /**
     *
     */
    public function toggleVisibility($intId, $blnVisible, $dc = null)
    {

        $table = Input::get('table');

        // Check permissions to edit
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA'][$table]['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$table]['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, ($dc ?: $this));
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, ($dc ?: $this));
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE " . $table . " SET tstamp=" . time() . ", published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")->execute($intId);


    }

    /**
     *
     */
    public function generateFeed()
    {

        $session = $this->Session->get('fmodules_feed_updater');

        if (!is_array($session) || empty($session)) {
            return;
        }

        $this->import('FModule');

        foreach ($session as $table) {
            $this->FModule->generateFeedsByArchive($table);
        }

        $this->import('Automator');
        $this->Automator->generateSitemap();

        $this->Session->set('fmodules_feed_updater', null);

    }

    /**
     * @param $dc
     */
    public function scheduleUpdate($dc)
    {
        $table = Input::get('table');

        // Return if there is no ID
        if (!$table) {
            return;
        }

        if (substr($table, -5) != '_data') {
            return;
        }

        $table = substr($table, 0, (strlen($table) - 5));

        // Store the ID in the session
        $session = $this->Session->get('fmodules_feed_updater');
        $session[] = $table;
        $this->Session->set('fmodules_feed_updater', array_unique($session));

    }

}