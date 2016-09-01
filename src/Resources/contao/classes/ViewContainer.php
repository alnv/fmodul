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

use Contao\System;
use Contao\Input;

/**
 * Class ViewContainer
 * @package FModule
 */
class ViewContainer extends DCAHelper
{

    /**
     * @var null
     */
    static private $instance = null;

    /**
     * @return DCAModuleData|null
     */
    static public function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @var bool
     */
    protected $overwriteMandatory = false;

    /**
     * @param array $arrSettings
     * @return array
     */
    public function dcaDataFields($arrSettings = array())
    {
        $arrMandatory = $arrSettings['arrMandatory'];
        $this->overwriteMandatory = $arrSettings['addMandatory'] ? true : false;
        $userID = $this->getUserID();
        $fields = array(
            'id' => array('sql' => 'int(10) unsigned NOT NULL auto_increment'),
            'tstamp' => array('sql' => "int(10) unsigned NOT NULL default '0'"),
            'title' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['title'],
                'inputType' => 'text',
                'exclude' => true,
                'sorting' => true,
                'search' => true,
                'eval' => array('maxlength' => 255, 'mandatory' => $this->setCustomMandatory($arrMandatory, 'title', '1'), 'tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'teaser'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            'alias' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['alias'],
                'inputType' => 'text',
                'exclude' => true,
                'eval' => array('rgxp' => 'alias', 'maxlength' => 128, 'tl_class' => 'w50', 'unique' => true, 'fmEditable' => true, 'fmGroup' => 'teaser'),
                'save_callback' => array(array('DCAModuleData', 'generateAlias')),
                'sql' => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
            ),
            'info' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['info'],
                'inputType' => 'text',
                'exclude' => true,
                'search' => true,
                'eval' => array('maxlength' => 255, 'tl_class' => 'clr long', 'mandatory' => $this->setCustomMandatory($arrMandatory, 'info'), 'fmEditable' => true, 'fmGroup' => 'teaser'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            'description' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['description'],
                'inputType' => 'textarea',
                'exclude' => true,
                'search' => true,
                'eval' => array('tl_class' => 'clr', 'mandatory' => $this->setCustomMandatory($arrMandatory, 'description'), 'rte' => 'tinyMCE', 'fmEditable' => true, 'fmGroup' => 'teaser'),
                'sql' => "mediumtext NULL"
            ),
            'author' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['author'],
                'default' => $userID,
                'exclude' => true,
                'filter' => true,
                'inputType' => 'select',
                'foreignKey' => 'tl_user.name',
                'eval' => array('doNotCopy' => true, 'chosen' => true, 'includeBlankOption' => true, 'mandatory' => true, 'tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'author'),
                'relation' => array('type' => 'hasOne', 'load' => 'eager'),
                'sql' => "int(10) unsigned NOT NULL default '0'",
            ),
            'date' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['date'],
                'default' => time(),
                'exclude' => true,
                'filter' => true,
                'sorting' => true,
                'flag' => 8,
                'inputType' => 'text',
                'eval' => array('rgxp' => 'date', 'doNotCopy' => true, 'datepicker' => true, 'mandatory' => $this->setCustomMandatory($arrMandatory, 'date'), 'tl_class' => 'w50 wizard', 'fmEditable' => true, 'fmGroup' => 'date'),
                'sql' => "int(10) unsigned NULL"
            ),
            'time' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['time'],
                'default' => time(),
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('rgxp' => 'time', 'doNotCopy' => true, 'tl_class' => 'w50', 'mandatory' => $this->setCustomMandatory($arrMandatory, 'time'), 'fmEditable' => true, 'fmGroup' => 'date'),
                'sql' => "int(10) unsigned NULL"
            ),
            'source' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['source'],
                'default' => 'default',
                'exclude' => true,
                'inputType' => 'select',
                'options' => array('default', 'internal', 'external'),
                'reference' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack'],
                'eval' => array('submitOnChange' => true, 'helpwizard' => true, 'mandatory' => $this->setCustomMandatory($arrMandatory, 'source'), 'fmEditable' => true, 'fmGroup' => 'source'),
                'sql' => "varchar(32) NOT NULL default ''"
            ),
            'url' => array(
                'label' => &$GLOBALS['TL_LANG']['MSC']['url'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => $this->setCustomMandatory($arrMandatory, 'url', '1'), 'fmEditable' => true, 'fmGroup' => 'source'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            'jumpTo' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['jumpTo'],
                'exclude' => true,
                'inputType' => 'pageTree',
                'foreignKey' => 'tl_page.title',
                'eval' => array('fieldType' => 'radio', 'mandatory' => $this->setCustomMandatory($arrMandatory, 'jumpTo', '1'), 'fmEditable' => true, 'fmGroup' => 'source'),
                'sql' => "int(10) unsigned NOT NULL default '0'",
                'relation' => array('type' => 'belongsTo', 'load' => 'lazy')
            ),
            'target' => array(
                'label' => &$GLOBALS['TL_LANG']['MSC']['target'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('tl_class' => 'w50 m12', 'mandatory' => $this->setCustomMandatory($arrMandatory, 'target'), 'fmEditable' => true, 'fmGroup' => 'source'),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'addEnclosure' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['addEnclosure'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('submitOnChange' => true),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'enclosure' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['enclosure'],
                'exclude' => true,
                'inputType' => 'fileTree',
                'eval' => array('multiple' => true, 'fieldType' => 'checkbox', 'filesOnly' => true, 'isDownloads' => true, 'mandatory' => $this->setCustomMandatory($arrMandatory, 'enclosure', '1'), 'extensions' => \Config::get('allowedDownload'), 'fmEditable' => true, 'fmGroup' => 'enclosure'),
                'sql' => "blob NULL"
            ),
            'addImage' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['addImage'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('submitOnChange' => true),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'singleSRC' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['singleSRC'],
                'exclude' => true,
                'inputType' => 'fileTree',
                'eval' => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => $this->setCustomMandatory($arrMandatory, 'singleSRC', '1'), 'extensions' => \Config::get('validImageTypes'), 'fmEditable' => true, 'fmGroup' => 'image'),
                'sql' => "binary(16) NULL"
            ),
            'alt' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['alt'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'mandatory' => $this->setCustomMandatory($arrMandatory, 'alt'), 'tl_class' => 'long', 'fmEditable' => true, 'fmGroup' => 'image'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            'size' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['size'],
                'exclude' => true,
                'inputType' => 'imageSize',
                'options' => System::getImageSizes(),
                'reference' => &$GLOBALS['TL_LANG']['MSC'],
                'eval' => array('rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'image'),
                'sql' => "varchar(64) NOT NULL default ''"
            ),
            'fullsize' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['fullsize'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('tl_class' => 'w50', 'mandatory' => $this->setCustomMandatory($arrMandatory, 'fullsize'), 'fmEditable' => true, 'fmGroup' => 'image'),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'caption' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['caption'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'allowHtml' => true, 'mandatory' => $this->setCustomMandatory($arrMandatory, 'caption'), 'tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'image'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            // geo
            'geo_latitude' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['geo_latitude'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 128, 'tl_class' => 'w50', 'mandatory' => $this->setCustomMandatory($arrMandatory, 'geo_latitude'), 'fmEditable' => true, 'fmGroup' => 'map'),
                'sql' => "varchar(128) NOT NULL default ''"
            ),
            'geo_longitude' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['geo_longitude'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 128, 'tl_class' => 'w50', 'mandatory' => $this->setCustomMandatory($arrMandatory, 'geo_longitude'), 'fmEditable' => true, 'fmGroup' => 'map'),
                'sql' => "varchar(128) NOT NULL default ''"
            ),
            // only address field
            'geo_address' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['geo_address'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'tl_class' => 'long', 'mandatory' => $this->setCustomMandatory($arrMandatory, 'geo_address'), 'fmEditable' => true, 'fmGroup' => 'map'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            // marker
            'addMarker' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['addMarker'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('submitOnChange' => true),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'markerSRC' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['markerSRC'],
                'exclude' => true,
                'inputType' => 'fileTree',
                'eval' => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => $this->setCustomMandatory($arrMandatory, 'markerSRC'), 'extensions' => \Config::get('validImageTypes'), 'fmEditable' => true, 'fmGroup' => 'map'),
                'sql' => "binary(16) NULL"
            ),
            'markerAlt' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['markerAlt'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'tl_class' => 'w50', 'mandatory' => $this->setCustomMandatory($arrMandatory, 'markerAlt'), 'fmEditable' => true, 'fmGroup' => 'map'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            'markerCaption' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['markerCaption'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'allowHtml' => true, 'mandatory' => $this->setCustomMandatory($arrMandatory, 'markerCaption'), 'tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'map'),
                'sql' => "varchar(255) NOT NULL default ''"
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
                'eval' => array('multiple' => true, 'size' => 2, 'tl_class' => 'w50 clr', 'fmEditable' => true, 'fmGroup' => 'expert'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            'published' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['published'],
                'inputType' => 'checkbox',
                'filter' => true,
                'exclude' => true,
                'eval' => array('submitOnChange' => true, 'doNotCopy' => true, 'fmEditable' => true, 'fmGroup' => 'expert'),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'start' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['start'],
                'inputType' => 'text',
                'exclude' => true,
                'eval' => array('rgxp' => 'datim', 'datepicker' => true, 'mandatory' => $this->setCustomMandatory($arrMandatory, 'start'), 'tl_class' => 'w50 wizard', 'fmEditable' => true, 'fmGroup' => 'expert'),
                'sql' => "varchar(10) NOT NULL default ''"
            ),
            'stop' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['stop'],
                'inputType' => 'text',
                'exclude' => true,
                'eval' => array('rgxp' => 'datim', 'datepicker' => true, 'mandatory' => $this->setCustomMandatory($arrMandatory, 'stop'), 'tl_class' => 'w50 wizard', 'fmEditable' => true, 'fmGroup' => 'expert'),
                'sql' => "varchar(10) NOT NULL default ''"
            ),
            // languageMain
            'mainLanguage' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['mainLanguage'],
                'exclude' => false,
                'inputType' => 'select',
                'options_callback' => array('DCAModuleData', 'getFallbackData'),
                'eval' => array('includeBlankOption' => true, 'chosen' => true, 'blankOptionLabel' => '-', 'tl_class' => 'w50'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            // gallery
            'addGallery' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['addGallery'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('submitOnChange' => true),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'orderSRC' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['orderSRC'],
                'sql' => "blob NULL"
            ),
            'multiSRC' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['multiSRC'],
                'exclude' => true,
                'inputType' => 'fileTree',
                'eval' => array('multiple' => true, 'fieldType' => 'checkbox', 'orderField' => 'orderSRC', 'files' => true, 'mandatory' => true),
                'sql' => "blob NULL",
                'load_callback' => array(array('DCAModuleData', 'setMultiSrcFlags'))
            ),
            'sortBy' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['sortBy'],
                'exclude' => true,
                'inputType' => 'select',
                'options' => array('custom', 'name_asc', 'name_desc', 'date_asc', 'date_desc', 'random'),
                'reference' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack'],
                'eval' => array('tl_class' => 'w50'),
                'sql' => "varchar(32) NOT NULL default ''"
            ),
            'metaIgnore' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['metaIgnore'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('tl_class' => 'w50 m12'),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'perRow' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['perRow'],
                'default' => 4,
                'exclude' => true,
                'inputType' => 'select',
                'options' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12),
                'eval' => array('tl_class' => 'w50'),
                'sql' => "smallint(5) unsigned NOT NULL default '0'"
            ),
            'perPageGallery' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['perPageGallery'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('rgxp' => 'natural', 'tl_class' => 'w50'),
                'sql' => "smallint(5) unsigned NOT NULL default '0'"
            ),
            'numberOfItems' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['numberOfItems'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('rgxp' => 'natural', 'tl_class' => 'w50'),
                'sql' => "smallint(5) unsigned NOT NULL default '0'"
            ),
            'galleryTpl' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['galleryTpl'],
                'exclude' => true,
                'inputType' => 'select',
                'options_callback' => array('DCAModuleData', 'getGalleryTemplates'),
                'eval' => array('tl_class' => 'w50'),
                'sql' => "varchar(64) NOT NULL default ''"
            ),
        );

        // add pid
        if ($this->parent) {
            $fields['pid'] = array(
                'foreignKey' => $this->parent . '.id',
                'sql' => "int(10) unsigned NOT NULL default '0'",
                'relation' => array('type' => 'belongsTo', 'load' => 'eager')
            );
        }

        return $fields;
    }

    /**
     * @return array
     */
    public function dcaSettingField()
    {
        return array(
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
            'language' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['language'],
                'exclude' => true,
                'inputType' => 'text',
                'search' => true,
                'eval' => array('mandatory' => true, 'rgxp' => 'language', 'maxlength' => 5, 'nospace' => true, 'doNotCopy' => true, 'tl_class' => 'w50'),
                'sql' => "varchar(5) NOT NULL default ''"
            ),
            'fallback' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['fallback'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('doNotCopy' => true, 'tl_class' => 'w50 m12'),
                'save_callback' => array(array('DCAModuleSettings', 'checkFallback')),
                'sql' => "char(1) NOT NULL default ''"
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
                'eval' => array('fieldType' => 'radio', 'tl_class' => 'clr', 'mandatory' => true),
                'relation' => array('type' => 'hasOne', 'load' => 'eager'),
                'sql' => "int(10) unsigned NOT NULL default '0'"

            ),
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
    }

    /**
     * @param array $fields
     * @return array
     */
    public function generalPalette($fields = array())
    {
        $palette = array(
            'fields' => array('title', 'alias', 'author', 'info', 'description'),
            'palette' => '{general_legend},title,alias,author,info,description;',
            '__selector__' => '',
            'subPalettes' => '',
        );

        return $palette;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function datePalette($fields = array())
    {
        $palette = array(
            'fields' => array('date', 'time'),
            'palette' => '{date_legend},date,time;',
            '__selector__' => '',
            'subPalettes' => '',
        );

        return $palette;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function markerPalette($fields = array())
    {
        $palette = array(
            'fields' => array('addMarker', 'markerSRC', 'markerAlt', 'markerCaption'),
            'palette' => '{marker_legend},addMarker;;',
            '__selector__' => 'addMarker',
            'subPalettes' => 'markerSRC,markerAlt,markerCaption',
        );
        return $palette;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function geoPalette($fields = array())
    {
        $palette = array(
            'fields' => array('geo_latitude', 'geo_longitude'),
            'palette' => '{geo_legend},geo_latitude,geo_longitude;',
            '__selector__' => '',
            'subPalettes' => '',
        );

        return $palette;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function geoAddressPalette($fields = array())
    {
        $palette = array(
            'fields' => array('geo_address'),
            'palette' => '{geo_address_legend},geo_address;',
            '__selector__' => '',
            'subPalettes' => '',
        );

        return $palette;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function imageSettingsPalette($fields = array())
    {
        $palette = array(
            'fields' => array('alt', 'size', 'fullsize', 'caption'),
            'palette' => '{image_settings_legend},alt,size,caption,fullsize;',
            '__selector__' => '',
            'subPalettes' => '',
        );
        return $palette;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function imagePalette($fields = array())
    {
        $palette = array(
            'fields' => array('addImage', 'singleSRC'),
            'palette' => '{image_legend},addImage;',
            '__selector__' => 'addImage',
            'subPalettes' => 'singleSRC',
        );
        return $palette;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function galleryPalette($fields = array())
    {
        $palette = array(
            'fields' => array('addGallery', 'multiSRC', 'sortBy', 'metaIgnore', 'perRow', 'perPageGallery', 'numberOfItems', 'galleryTpl'),
            'palette' => '{gallery_legend},addGallery;',
            '__selector__' => 'addGallery',
            'subPalettes' => 'multiSRC, sortBy, metaIgnore, perRow, perPageGallery, numberOfItems, galleryTpl',
        );
        return $palette;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function enclosurePalette($fields = array())
    {
        $palette = array(
            'fields' => array('addEnclosure', 'enclosure'),
            'palette' => '{enclosure_legend:hide},addEnclosure;',
            '__selector__' => 'addEnclosure',
            'subPalettes' => 'enclosure',
        );

        return $palette;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function sourcePalette($fields = array())
    {
        $palette = array(
            'fields' => array('source', 'jumpTo', 'url', 'target'),
            'palette' => '{source_legend:hide},source;',
            '__selector__' => 'source',
            'subPalettes' => array(
                'source_internal' => 'jumpTo',
                'source_external' => 'url,target'
            ),
        );

        return $palette;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function protectedPalette($fields = array())
    {
        $palette = array(
            'fields' => array('protected', 'groups'),
            'palette' => '{protected_legend:hide},protected;',
            '__selector__' => 'protected',
            'subPalettes' => 'groups',
        );

        return $palette;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function expertPalette($fields = array())
    {
        $palette = array(
            'fields' => array('guests', 'cssID'),
            'palette' => '{expert_legend:hide},guests,cssID;',
            '__selector__' => '',
            'subPalettes' => '',
        );

        return $palette;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function publishPalette($fields = array())
    {
        $palette = array(
            'fields' => array('published', 'start', 'stop'),
            'palette' => '{publish_legend},published;',
            '__selector__' => 'published',
            'subPalettes' => 'start,stop',
        );

        return $palette;
    }

    /**
     * @param array $fields
     * @return array
     */
    public function metaPalette($fields = array())
    {
        $palette = array(
            'fields' => array(''),
            'palette' => '',
            '__selector__' => '',
            'subPalettes' => '',
        );

        $palette['palette'] .= '{meta_legend}';

        foreach ($fields as $field) {
            $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['fm_legend'][$field['fieldID']] = $field['title'];
            $palette['fields'][] = $field['fieldID'];
            $palette['palette'] .= ',' . $field['fieldID'] . '';
        }

        $palette['palette'] .= ';';

        return $palette;
    }

    /**
     * @param $fieldData
     * @return array
     */
    public function getWidgetField($fieldData)
    {
        $field = array();
        $widgetType = explode('.', $fieldData['widget_type']);
        $field['label'] = $this->setLabels($fieldData['title'], $fieldData['description'], $fieldData['fieldID']);
        $field['exclude'] = true;
        $field['eval'] = array(
            'tl_class' => $this->setTLClass($fieldData),
            'mandatory' => $this->setMandatory($fieldData['isMandatory']),
            'fmEditable' => true,
            'fmGroup' => $this->getFMGroup($fieldData)
        );

        // set regular expression
        $rgxp = $this->setRgxp($fieldData);
        if ($rgxp) {
            $field['eval']['rgxp'] = $rgxp;
        }

        $field['inputType'] = 'text';
        $field['sql'] = "text NULL";

        // textarea blank
        if ($widgetType[0] == 'textarea' && $widgetType[1] == 'blank') {
            $field['inputType'] = 'textarea';
        }

        // textarea tinymce
        if ($widgetType[0] == 'textarea' && $widgetType[1] == 'tinyMCE') {
            $field['inputType'] = 'textarea';
            $field['eval']['rte'] = 'tinyMCE';
        }

        // list
        if ($widgetType[0] == 'list' && $widgetType[1] == 'blank') {
            $field['inputType'] = 'listWizard';
            $field['eval']['fmEditable'] = false;
            $field['eval']['fmGroup'] = '';
        }

        // key - value list
        if ($widgetType[0] == 'list' && $widgetType[1] == 'keyValue') {
            $field['inputType'] = 'keyValueWizard';
            $field['eval']['fmEditable'] = false;
            $field['eval']['fmGroup'] = '';
        }

        //table
        if ($widgetType[0] == 'table' && $widgetType[1] == 'blank') {
            $field['inputType'] = 'tableWizard';
            $field['eval']['allowHtml'] = true;
            $field['eval']['doNotSaveEmpty'] = true;
            $field['eval']['style'] = 'width:142px;height:66px';
            $field['eval']['fmEditable'] = false;
            $field['eval']['fmGroup'] = '';
        }

        return $field;
    }

    /**
     * @param $fieldData
     * @return array
     */
    public function getToggleField($fieldData)
    {
        $field = array();
        $field['label'] = $this->setLabels($fieldData['title'], $fieldData['description'], $fieldData['fieldID']);
        $field['exclude'] = true;
        $field['filter'] = true;
        $field['inputType'] = 'checkbox';
        $field['eval'] = array(
            'tl_class' => $this->setTLClass($fieldData),
            'doNotCopy' => true,
            'fmEditable' => true,
            'fmGroup' => $this->getFMGroup($fieldData)
        );
        $field['sql'] = "char(1) NOT NULL default ''";
        return $field;
    }

    /**
     * @param $fieldData
     * @return array
     */
    public function getDateField($fieldData)
    {
        $field = array();
        $field['label'] = $this->setLabels($fieldData['title'], $fieldData['description'], $fieldData['fieldID']);
        $field['search'] = true;
        $field['filter'] = true;
        $field['sorting'] = true;
        $field['exclude'] = true;
        $field['default'] = time();
        $field['flag'] = 8;
        $field['inputType'] = 'text';
        $field['eval'] = array(
            'mandatory' => $this->setMandatory($fieldData['isMandatory']),
            'tl_class' => $this->setTLClass($fieldData),
            'rgxp' => 'date',
            'doNotCopy' => true,
            'datepicker' => true,
            'fmEditable' => true,
            'fmGroup' => $this->getFMGroup($fieldData)
        );
        $field['sql'] = 'int(10) unsigned NULL';

        // if time is enable
        if ($fieldData['addTime']) {
            $field['eval']['rgxp'] = 'datim';
        }
        return $field;
    }

    /**
     * @param $fieldData
     * @return array
     */
    public function getSearchField($fieldData)
    {
        $field = array();
        $field['label'] = $this->setLabels($fieldData['title'], $fieldData['description'], $fieldData['fieldID']);
        $field['search'] = true;
        $field['exclude'] = true;
        $field['inputType'] = 'text';
        $field['eval'] = array(
            'mandatory' => $this->setMandatory($fieldData['isMandatory']),
            'tl_class' => $this->setTLClass($fieldData),
            'fmEditable' => true,
            'fmGroup' => $this->getFMGroup($fieldData)
        );

        // set regular expression
        $rgxp = $this->setRgxp($fieldData);
        if ($rgxp) {
            $field['eval']['rgxp'] = $rgxp;
        }

        $field['sql'] = 'text NULL';
        return $field;
    }

    /**
     * @param $fieldData
     * @param $options
     * @return array
     */
    public function getSimpleChoiceField($fieldData, $options)
    {
        $field = array();
        $field['label'] = $this->setLabels($fieldData['title'], $fieldData['description'], $fieldData['fieldID']);
        $field['filter'] = true;
        $field['sorting'] = true;
        $field['exclude'] = true;
        $field['inputType'] = 'select';
        $field['options'] = $options;
        $field['eval'] = array(
            'mandatory' => $this->setMandatory($fieldData['isMandatory']),
            'tl_class' => $this->setTLClass($fieldData),
            'includeBlankOption' => true,
            'blankOptionLabel' => '-',
            'chosen' => true,
            'fmEditable' => true,
            'fmGroup' => $this->getFMGroup($fieldData)
        );

        // set regular expression
        $rgxp = $this->setRgxp($fieldData);
        if ($rgxp) {
            $field['eval']['rgxp'] = $rgxp;
        }

        // dataFromTaxonomy
        if ($fieldData['dataFromTaxonomy'] == '1') {
            $field['eval']['submitOnChange'] = true;
        }

        // radio
        if ($fieldData['fieldAppearance'] == 'radio') {
            $field['inputType'] = 'radio';
        }

        // sql
        $field['sql'] = 'text NULL';

        return $field;
    }

    /**
     * @param $fieldData
     * @param $options
     * @return array
     */
    public function getMultiChoiceField($fieldData, $options)
    {
        $field = array();
        $field['label'] = $this->setLabels($fieldData['title'], $fieldData['description'], $fieldData['fieldID']);
        $field['filter'] = true;
        $field['exclude'] = true;
        $field['inputType'] = 'checkbox';
        $field['options'] = $options;
        $field['eval'] = array(
            'multiple' => true,
            'mandatory' => $this->setMandatory($fieldData['isMandatory']),
            'tl_class' => $this->setTLClass($fieldData),
            'csv' => ',',
            'fmEditable' => true,
            'fmGroup' => $this->getFMGroup($fieldData)
        );

        // set regular expression
        $rgxp = $this->setRgxp($fieldData);
        if ($rgxp) {
            $field['eval']['rgxp'] = $rgxp;
        }

        // reactToTaxonomy
        if ($fieldData['reactToTaxonomy'] == '1' && $fieldData['reactToField']) {
            unset($field['options']);
            $field['filter'] = false;
            $field['options_callback'] = array('DCAModuleData', 'getTaxonomiesTags');
        }

        // set tags
        if ($fieldData['fieldAppearance'] == 'tags') {
            $field['inputType'] = 'select';
            $field['eval']['chosen'] = true;
        }

        // sql
        $field['sql'] = 'text NULL';

        return $field;
    }

    /**
     * @param $fieldData
     * @return array
     */
    public function getOptionField($fieldData)
    {
        $field = array();
        $field['label'] = $this->setLabels($fieldData['title'], $fieldData['description'], $fieldData['fieldID']);
        $field['exclude'] = true;
        $field['fmodule_filter'] = true;
        $field['eval'] = array('tl_class' => 'clr m12');
        $field['inputType'] = 'optionWizardExtended';
        $field['sql'] = 'text NULL';
        return $field;
    }

    /**
     * @param $fieldData
     * @return array
     */
    public function getTaxonomySelectField($fieldData)
    {
        $field = array();
        $field['label'] = $this->setLabels($fieldData['title'], $fieldData['description'], $fieldData['fieldID']);
        $field['exclude'] = true;
        $field['fmodule_filter'] = true; // @todo rausfinden wieso es da ist :)
        $field['eval'] = array('tl_class' => 'clr', 'chosen' => true);
        $field['inputType'] = 'select';
        $field['options_callback'] = array('DCAModuleSettings', 'getParentTaxonomies');
        $field['sql'] = 'text NULL';
        return $field;
    }

    /**
     * @param $type
     * @param $fieldData
     * @return array
     */
    public function getOptionFromTableField($type, $fieldData)
    {
        $field = array();
        $field['exclude'] = true;
        $field['fmodule_filter'] = true;
        $field['inputType'] = 'select';
        $field['eval'] = array('tl_class' => 'clr', 'submitOnChange' => true, 'chosen' => true);
        $field['sql'] = 'text NULL';

        if ($type == 'select_table_') {
            $field['label'] = array(sprintf($GLOBALS['TL_LANG']['tl_fmodules_language_pack']['select_table'][0], $fieldData['title']), $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['select_table'][1]);
            $field['load_callback'] = array(array('DCAModuleSettings', 'loadDefaultTable'));
            $field['options_callback'] = array('DCAModuleSettings', 'getTables');
            $field['save_callback'] = array(array('DCAModuleSettings', 'save_select_table'));
        }

        if ($type == 'select_col_') {
            $field['label'] = &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['select_col'];
            $field['load_callback'] = array(array('DCAModuleSettings', 'loadDefaultCol'));
            $field['options_callback'] = array('DCAModuleSettings', 'getCols');
            $field['save_callback'] = array(array('DCAModuleSettings', 'save_select_col'));
        }

        if ($type == 'select_title_') {
            $field['label'] = &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['select_title'];
            $field['load_callback'] = array(array('DCAModuleSettings', 'loadDefaultTitle'));
            $field['options_callback'] = array('DCAModuleSettings', 'getTitle');
            $field['save_callback'] = array(array('DCAModuleSettings', 'save_select_title'));
        }

        if ($type != 'select_table_') {
            $field['eval']['tl_class'] = 'w50';
            $field['eval']['submitOnChange'] = false;
        }

        return $field;
    }

    /**
     * @param $title
     * @param $description
     * @param $fieldID
     * @return array
     */
    protected function setLabels($title, $description, $fieldID = '')
    {
        $globLabel = $GLOBALS['TL_LANG']['tl_fmodules_language_pack'][$fieldID];
        $title = $globLabel[0] ? $globLabel[0] : $title;
        $description = $globLabel[1] ? $globLabel[1] : $description;
        if (!$title) $title = 'no-title-set';
        if (!$description) $description = '';
        return array($title, $description);
    }

    /**
     * @param $mandatory
     * @return bool
     */
    protected function setMandatory($mandatory)
    {
        return $mandatory ? true : false;
    }

    /**
     * @param $arrMandatory
     * @param $field
     * @param string $defaultMandatory
     * @return bool
     */
    protected function setCustomMandatory($arrMandatory, $field, $defaultMandatory = '0')
    {
        if (!$this->overwriteMandatory) {
            return $this->setMandatory($defaultMandatory);
        }

        if (isset($arrMandatory[$field]) && $this->overwriteMandatory) {
            return true;
        }

        return false;
    }

    /**
     * @param $fieldData
     * @return string
     */
    protected function setTLClass($fieldData)
    {
        $cssStr = '';

        // check evalCss
        if ($fieldData['evalCss']) {
            $cssStr .= $fieldData['evalCss'];
        }

        if ($fieldData['type'] == 'date_field') {
            $cssStr .= ' wizard';
        }

        return $cssStr;
    }

    /**
     * @param $fieldData
     * @return string
     */
    protected function getFMGroup($fieldData)
    {
        if (isset($fieldData['fmGroup']) && $fieldData['fmGroup']) {
            return $fieldData['fmGroup'];
        }
        return 'other';
    }

    /**
     * @return string
     */
    protected function getUserID()
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
     * @param $fieldData
     * @return null
     */
    protected function setRgxp($fieldData)
    {
        if ($fieldData['rgxp']) {
            return $fieldData['rgxp'];
        }
        return null;
    }

    /**
     * @param $name
     * @param $arguments
     * @return array
     */
    public function __call($name, $arguments)
    {
        $fields = $arguments[0] ? $arguments[0] : array();
        $palette = array(
            'fields' => array(),
            'palette' => '',
            '__selector__' => '',
            'subPalettes' => '',
        );
        $pointer = false;
        foreach ($fields as $field) {
            if ($field['fieldID'] == $name) {
                $pointer = true;
                $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['fm_legend'][$field['fieldID']] = $field['title'];
                $palette['palette'] .= '{' . $name . '}';
            }

            if ($pointer) {
                $palette['fields'][] = $field['fieldID'];
                $palette['palette'] .= ',' . $field['fieldID'] . '';
            }

            if ($pointer && $field['type'] == 'legend_end') {
                $pointer = false;
                $palette['palette'] .= ';';
            }

        }
        return $palette;
    }
}