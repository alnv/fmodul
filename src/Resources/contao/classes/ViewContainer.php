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
    public function dcaFields()
    {

        $userID = $this->getUserID();

        return array(
            'id' => array('sql' => 'int(10) unsigned NOT NULL auto_increment'),
            'tstamp' => array('sql' => "int(10) unsigned NOT NULL default '0'"),
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
            'author' => array(
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
            'date' => array(
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
            'time' => array(
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
            'url' => array(
                'label' => &$GLOBALS['TL_LANG']['MSC']['url'],
                'exclude' => true,
                'inputType' => 'text',
                'eval' => array('mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            'target' => array(
                'label' => &$GLOBALS['TL_LANG']['MSC']['target'],
                'exclude' => true,
                'inputType' => 'checkbox',
                'eval' => array('tl_class' => 'w50 m12'),
                'sql' => "char(1) NOT NULL default ''"
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
            'jumpTo' => array(
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
                'eval' => array('multiple' => true, 'fieldType' => 'checkbox', 'filesOnly' => true, 'isDownloads' => true, 'mandatory' => true, 'extensions' => \Config::get('allowedDownload')),
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
                'eval' => array('filesOnly' => true, 'fieldType' => 'radio', 'mandatory' => true, 'extensions' => \Config::get('validImageTypes')),
                'sql' => "binary(16) NULL"
            ),
            'alt' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['alt'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'tl_class' => 'long'),
                'sql' => "varchar(255) NOT NULL default ''"
            ),
            'size' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['size'],
                'exclude' => true,
                'inputType' => 'imageSize',
                'options' => System::getImageSizes(),
                'reference' => &$GLOBALS['TL_LANG']['MSC'],
                'eval' => array('rgxp' => 'natural', 'includeBlankOption' => true, 'nospace' => true, 'helpwizard' => true, 'tl_class' => 'w50'),
                'sql' => "varchar(64) NOT NULL default ''"
            ),
            'caption' => array(
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['caption'],
                'exclude' => true,
                'search' => true,
                'inputType' => 'text',
                'eval' => array('maxlength' => 255, 'allowHtml' => true, 'tl_class' => 'w50'),
                'sql' => "varchar(255) NOT NULL default ''"
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
    public function imagePalette($fields = array())
    {
        $palette = array(
            'fields' => array('addImage', 'singleSRC', 'alt', 'size', 'caption'),
            'palette' => '{image_legend},addImage;',
            '__selector__' => 'addImage',
            'subPalettes' => 'singleSRC,alt,size,caption',
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

        foreach($fields as $field)
        {
            $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['fm_legend'][$field['fieldID']] = $field['title'];
            $palette['fields'][] = $field['fieldID'];
            $palette['palette'] .= ','.$field['fieldID'].'';
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

        $field['label'] = $this->setLabel($fieldData['title'], $fieldData['description']);
        $field['exclude'] = true;

        $field['eval'] = array(
            'tl_class' => $this->setTLClass($fieldData),
            'mandatory' => $this->setMandatory($fieldData['isMandatory']),
        );

        $field['inputType'] = 'text';

        // textarea blank
        if($widgetType[0] == 'textarea' && $widgetType[1] == 'blank')
        {
            $field['inputType'] = 'textarea';
        }

        // textarea tinymce
        if($widgetType[0] == 'textarea' && $widgetType[1] == 'tinyMCE')
        {
            $field['inputType'] = 'textarea';
            $field['eval']['rte'] = 'tinyMCE';
        }

        // list
        if($widgetType[0] == 'list' && $widgetType[1] == 'blank')
        {
            $field['inputType'] = 'listWizard';
        }

        // key - value list
        if($widgetType[0] == 'list' && $widgetType[1] == 'keyValue')
        {
            $field['inputType'] = 'keyValueWizard';
        }

        //table
        if($widgetType[0] == 'table' && $widgetType[1] == 'blank')
        {
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
        $field['label'] = $this->setLabel($fieldData['title'], $fieldData['description']);
        $field['exclude'] = true;
        $field['filter'] = true;

        $field['inputType'] = 'checkbox';

        $field['eval'] = array(
            'tl_class' => $this->setTLClass($fieldData),
            'doNotCopy' => true
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
        $field['label'] = $this->setLabel($fieldData['title'], $fieldData['description']);

        $field['search'] = true;
        $field['exclude'] = true;
        $field['filter'] = true;
        $field['sorting'] = true;

        $field['default'] = time();

        $field['inputType'] = 'text';

        $field['eval'] = array(
            'mandatory' => $this->setMandatory($fieldData['isMandatory']),
            'tl_class' => $this->setTLClass($fieldData),
            'rgxp' => 'date',
            'doNotCopy' => true,
            'datepicker' => true,
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
        $field['label'] = $this->setLabel($fieldData['title'], $fieldData['description']);
        $field['search'] = true;
        $field['exclude'] = true;

        $field['inputType'] = 'text';

        $field['eval'] = array(
            'mandatory' => $this->setMandatory($fieldData['isMandatory']),
            'tl_class' => $this->setTLClass($fieldData)
        );

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
        $field['label'] = $this->setLabel($fieldData['title'], $fieldData['description']);

        // filter
        $field['filter'] = true;
        $field['search'] = true;
        $field['exclude'] = true;

        $field['inputType'] = 'select';
        $field['options'] = $options;

        $field['eval'] = array(
            'mandatory' => $this->setMandatory($fieldData['isMandatory']),
            'tl_class' => $this->setTLClass($fieldData),
            'includeBlankOption' => true,
            'blankOptionLabel' => '-'
        );

        $field['sql'] = 'text NULL';

        // radio
        if ($fieldData['fieldAppearance'] == 'radio') {
            $field['inputType'] = 'radio';
        }

        return $field;
    }

    /**
     * @param $field
     * @return array
     */
    public function getMultiChoiceField($fieldData, $options)
    {

        $field = array();
        $field['label'] = $this->setLabel($fieldData['title'], $fieldData['description']);

        // filter
        $field['filter'] = true;
        $field['search'] = true;
        $field['exclude'] = true;

        $field['inputType'] = 'checkbox';
        $field['options'] = $options;

        $field['eval'] = array(
            'multiple' => true,
            'mandatory' => $this->setMandatory($fieldData['isMandatory']),
            'tl_class' => $this->setTLClass($fieldData),
            'csv' => ','
        );

        $field['sql'] = 'text NULL';

        // tags
        if ($fieldData['fieldAppearance'] == 'tags') {
            $field['inputType'] = 'select';
            $field['eval']['chosen'] = true;
        }

        return $field;
    }

    /**
     * @param $title
     * @param $description
     * @return array
     */
    protected function setLabel($title, $description)
    {
        $description = $description ? $description : 'No description found';
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
        if($fieldData['evalCss'])
        {
            $cssStr .=  $fieldData['evalCss'];
        }

        if($fieldData['type'] == 'date_field')
        {
            $cssStr .=  ' wizard';
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
     * @param $name
     * @param $arguments
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

        foreach($fields as $field)
        {
            if($field['fieldID'] == $name)
            {
                $pointer = true;
                $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['fm_legend'][$field['fieldID']] = $field['title'];
                $palette['palette'] .= '{'.$name.'}';
            }

            if($pointer)
            {
                $palette['fields'][] = $field['fieldID'];
                $palette['palette'] .= ','.$field['fieldID'].'';
            }

            if($pointer && $field['type'] == 'legend_end')
            {
                $pointer = false;
                $palette['palette'] .= ';';
            }

        }

        return $palette;

    }

}