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
use Contao\Image;
use Contao\StringUtil;
use Contao\BackendUser;
use Symfony\Component\Intl\Util\Version;


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
     *
     */
    public function checkPermission($dc)
    {

        $modname = substr($dc->table, 3, strlen($dc->table));
        $modname = str_replace('_data', '', $modname);

        if ($this->User->isAdmin) {
            return;
        }

        if (!$this->User->hasAccess('create', $modname . 'p')) {
            $GLOBALS['TL_DCA'][$dc->table]['config']['closed'] = true;
        }

        $act = \Input::get('act');

        if (($act == 'delete' || $act == 'deleteAll') && (!$this->user->isAdmin || !$this->User->hasAccess('delete', $modname . 'p'))) {
            $this->redirect('contao/main.php?act=error');
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
    public function setList($sortingField, $sortingType, $orderBy)
    {

        $flag = 1;

        $arrFlag = explode('.', $sortingType);
        $arrField = explode('.', $sortingField);

        if ($arrFlag[1]) {
            $flag = (int)$arrFlag[1];
        }

        if ($orderBy == 'desc') {
            $flag = $flag + 1;
        }

        $list = array(

            'sorting' => array(

                'mode' => 0,
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
                    'icon' => (version_compare(VERSION, '4.0', '>=') ? 'bundles/fmodule/' : 'system/modules/fmodule/assets/') . 'fields.png'
                ),

                'editList' => array
                (
                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['editList'],
                    'href' => 'table=tl_content&view=list',
                    'icon' => (version_compare(VERSION, '4.0', '>=') ? 'bundles/fmodule/' : 'system/modules/fmodule/assets/') . 'page.png'
                ),

                'editDetail' => array(

                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['editDetail'],
                    'href' => 'table=tl_content&view=detail',
                    'icon' => (version_compare(VERSION, '4.0', '>=') ? 'bundles/fmodule/' : 'system/modules/fmodule/assets/') . 'detail.png'

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

        $fieldStr = '{meta_legend},';
        $arr = array();

        foreach ($fields as $field) {

            if ($field['fieldID'] !== '') {

                $arr[] = $field['fieldID'];

            }

        }

        $fieldStr = $fieldStr . implode(',', $arr) . ';';

        return array(
            '__selector__' => array('source', 'addImage', 'protected', 'addEnclosure', 'published'),
            'default' => '{general_legend},title,alias,author,info,description;{image_legend},addImage;{enclosure_legend:hide},addEnclosure;{source_legend:hide},source;' . $fieldStr . '{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{publish_legend},published'

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
    public function setFields($fields = array())
    {

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
                'eval' => array('rgxp' => 'alias', 'maxlength' => 128, 'tl_class' => 'w50'),
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

            $options = $this->getOptions($field);

            if ($field['fieldID'] !== '' && $field['type'] == 'simple_choice') {

                $arr[$field['fieldID']] = array(

                    'label' => array($field['title'], ''),
                    'filter' => true,
                    'exclude' => true,
                    'inputType' => 'select',
                    'options' => $options,
                    'eval' => array('tl_class' => 'clr'),
                    'sql' => "blob NULL"

                );

                if( $field['fieldID'] == 'auto_page' || $field['fieldID'] == 'auto_itemm' )
                {
                    $arr[$field['fieldID']]['filter'] = false;
                }

                if ($field['fieldAppearance'] == 'radio') {
                    $arr[$field['fieldID']]['inputType'] = 'radio';
                }

                if ($field['fieldAppearance'] == 'select') {
                    $arr[$field['fieldID']]['inputType'] = 'select';
                }

            }

            if ($field['fieldID'] !== '' && $field['type'] == 'multi_choice') {
                $arr[$field['fieldID']] = array(
                    'label' => array($field['title'], ''),
                    'filter' => true,
                    'exclude' => true,
                    'inputType' => 'checkbox',
                    'options' => $options,
                    'eval' => array('multiple' => true, 'tl_class' => 'clr', 'csv' => ','),
                    'sql' => "blob NULL"
                );

                if( $field['fieldID'] == 'auto_page' || $field['fieldID'] == 'auto_item' )
                {
                    $arr[$field['fieldID']]['filter'] = false;
                }

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

                    'label' => array($field['title'], ''),
                    'search' => true,
                    'exclude' => true,
                    'inputType' => 'text',
                    'eval' => array('tl_class' => 'long'),
                    'sql' => "mediumtext NOT NULL default ''"
                );
            }

            if ($field['fieldID'] !== '' && $field['type'] == 'date_field') {
                $arr[$field['fieldID']] = array(
                    'label' => array($field['title'], ''),
                    'default' => time(),
                    'exclude' => true,
                    'sorting' => true,
                    'inputType' => 'text',
                    'eval' => array('rgxp' => 'date', 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'wizard'),
                    'sql' => "int(10) unsigned NULL"
                );
            }

        }
        return $arr;
    }


    /**
     * @param $varValue
     * @param DataContainer $dc
     * @return string
     * @throws Exception
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

        return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"').'</a> ';

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
}