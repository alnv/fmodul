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
     * @return array
     */
    public function dcaDataFields()
    {
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
                'eval' => array('maxlength' => 255, 'mandatory' => true, 'tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'teaser'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            'alias' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['alias'],
                'inputType' => 'text',
                'exclude' => true,
                'eval' => array('rgxp' => 'alias', 'maxlength' => 128, 'tl_class' => 'w50', 'doNotCopy' => true, 'fmEditable' => true, 'fmGroup' => 'teaser'),
                'save_callback' => array(array('DCAModuleData', 'generateAlias')),
                'sql' => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
            ),
            'info' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['info'],
                'inputType' => 'text',
                'exclude' => true,
                'search' => true,
                'eval' => array('maxlength' => 255, 'tl_class' => 'clr long', 'fmEditable' => true, 'fmGroup' => 'teaser'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            'description' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['description'],
                'inputType' => 'textarea',
                'exclude' => true,
                'search' => true,
                'eval' => array('tl_class' => 'clr', 'rte' => 'tinyMCE', 'fmEditable' => true, 'fmGroup' => 'teaser'),
                'sql' => "mediumtext NULL"
            ),
            'author' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['author'],
                'default' => $userID,
                'exclude' => true,
                'filter' => true,
                'inputType' => 'select',
                'foreignKey' => 'tl_user.name',
                'eval' => array('doNotCopy' => true, 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'author'),
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
                'eval' => array('rgxp' => 'date', 'doNotCopy' => true, 'datepicker' => true, 'tl_class' => 'w50 wizard', 'fmEditable' => true, 'fmGroup' => 'date'),
                'sql' => "int(10) unsigned NULL"
            ),
            'time' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['time'],
                'default' => time(),
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('rgxp' => 'time', 'doNotCopy' => true, 'tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'date'),
                'sql' => "int(10) unsigned NULL"
            ),
            'source' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['source'],
                'default' => 'default',
                'exclude' => true,
                'inputType' => 'radio',
                'options' => array('default', 'internal', 'external'),
                'reference' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack'],
                'eval' => array('submitOnChange' => true, 'helpwizard' => true),
                'sql' => "varchar(32) NOT NULL default ''"
            ),
            'url' => array(
                'label' => &$GLOBALS['TL_LANG']['MSC']['url'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            'jumpTo' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['jumpTo'],
                'exclude' => true,
                'inputType' => 'pageTree',
                'foreignKey' => 'tl_page.title',
                'eval' => array('mandatory' => true, 'fieldType' => 'radio'),
                'sql' => "int(10) unsigned NOT NULL default '0'",
                'relation' => array('type' => 'belongsTo', 'load' => 'lazy')
            ),
            'target' => array(
                'label' => &$GLOBALS['TL_LANG']['MSC']['target'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('tl_class' => 'w50 m12'),
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
                'eval' => array('multiple' => true, 'fieldType' => 'checkbox', 'filesOnly' => true, 'isDownloads' => true, 'mandatory' => true, 'extensions' => \Config::get('allowedDownload'), 'fmEditable' => true, 'fmGroup' => 'enclosure'),
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
                'eval' => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'extensions' => \Config::get('validImageTypes'), 'fmEditable' => true, 'fmGroup' => 'image'),
                'sql' => "binary(16) NULL"
            ),
            'alt' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['alt'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'tl_class' => 'long', 'fmEditable' => true, 'fmGroup' => 'image'),
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
                'eval' => array('tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'image'),
                'sql' => "char(1) NOT NULL default ''"
            ),
            'caption' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['caption'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'allowHtml' => true, 'tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'image'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            // geo
            'geo_latitude' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['geo_latitude'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 128, 'tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'map'),
                'sql' => "varchar(128) NOT NULL default ''"
            ),
            'geo_longitude' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['geo_longitude'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 128, 'tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'map'),
                'sql' => "varchar(128) NOT NULL default ''"
            ),
            // only address field
            'geo_address' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['geo_address'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'tl_class' => 'long', 'fmEditable' => true, 'fmGroup' => 'map'),
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
                'eval' => array('filesOnly' => true, 'fieldType' => 'radio', 'extensions' => \Config::get('validImageTypes'), 'fmEditable' => true, 'fmGroup' => 'map'),
                'sql' => "binary(16) NULL"
            ),
            'markerAlt' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['markerAlt'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'map'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            'markerCaption' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['markerCaption'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'allowHtml' => true, 'tl_class' => 'w50', 'fmEditable' => true, 'fmGroup' => 'map'),
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
                'eval' => array('multiple' => true, 'size' => 2, 'tl_class' => 'w50 clr'),
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
                'eval' => array('rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard', 'fmEditable' => true, 'fmGroup' => 'expert'),
                'sql' => "varchar(10) NOT NULL default ''"
            ),
            'stop' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['stop'],
                'inputType' => 'text',
                'exclude' => true,
                'eval' => array('rgxp' => 'datim', 'datepicker' => true, 'tl_class' => 'w50 wizard', 'fmEditable' => true, 'fmGroup' => 'expert'),
                'sql' => "varchar(10) NOT NULL default ''"
            )
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
    public function imagePalette($fields = array())
    {
        $palette = array(
            'fields' => array('addImage', 'singleSRC', 'alt', 'size', 'fullsize', 'caption'),
            'palette' => '{image_legend},addImage;',
            '__selector__' => 'addImage',
            'subPalettes' => 'singleSRC,alt,size,caption,fullsize',
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
            'fmGroup' => 'other'
        );

        // set regular expression
        $rgxp = $this->setRgxp($fieldData);
        if($rgxp)
        {
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
        }

        // key - value list
        if ($widgetType[0] == 'list' && $widgetType[1] == 'keyValue') {
            $field['inputType'] = 'keyValueWizard';
        }

        //table
        if ($widgetType[0] == 'table' && $widgetType[1] == 'blank') {
            $field['inputType'] = 'tableWizard';
            $field['eval']['allowHtml'] = true;
            $field['eval']['doNotSaveEmpty'] = true;
            $field['eval']['style'] = 'width:142px;height:66px';
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
            'fmGroup' => 'other'
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
            'fmGroup' => 'other'
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
            'fmGroup' => 'other'
        );

        // set regular expression
        $rgxp = $this->setRgxp($fieldData);
        if($rgxp)
        {
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
            'chosen' => true,
            'blankOptionLabel' => '-',
            'fmEditable' => true,
            'fmGroup' => 'other'
        );

        // set regular expression
        $rgxp = $this->setRgxp($fieldData);
        if($rgxp)
        {
            $field['eval']['rgxp'] = $rgxp;
        }

        $field['sql'] = 'text NULL';

        // radio
        if ($fieldData['fieldAppearance'] == 'radio') {
            $field['inputType'] = 'radio';
        }
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
            'fmGroup' => 'other'
        );
        $field['sql'] = 'text NULL';

        // set regular expression
        $rgxp = $this->setRgxp($fieldData);
        if($rgxp)
        {
            $field['eval']['rgxp'] = $rgxp;

        }

        // set tags
        if ($fieldData['fieldAppearance'] == 'tags') {
            $field['inputType'] = 'select';
            $field['eval']['chosen'] = true;
        }

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
        $field['eval'] = array('tl_class' => 'clr', 'submitOnChange' => true, 'chosen' => true,);
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
        if(!$title) $title = 'no-title-set';
        if(!$description) $description = '';
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
        if($fieldData['rgxp'])
        {
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