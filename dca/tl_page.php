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

$GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] = str_replace('includeLayout;', 'includeLayout;{fmodule_taxonomy_legend:hide},page_taxonomy;{fmodule_translateUrl:hide},addTranslateUrl;', $GLOBALS['TL_DCA']['tl_page']['palettes']['regular']);
$GLOBALS['TL_DCA']['tl_page']['palettes']['__selector__'][] = 'addTranslateUrl';
$GLOBALS['TL_DCA']['tl_page']['subpalettes']['addTranslateUrl'] = 'translateUrl';

// page_taxonomy
$GLOBALS['TL_DCA']['tl_page']['fields']['page_taxonomy'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_page']['fields']['page_taxonomy'],
    'inputType' => 'modeSettings',
    'eval' => array('submitOnChange' => true),
    'sql' => "blob NULL"
);

// addTranslateUrl
$GLOBALS['TL_DCA']['tl_page']['fields']['addTranslateUrl'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_page']['fields']['addTranslateUrl'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true),
    'sql' => "char(1) NOT NULL default ''"
);

// translateUrl
$GLOBALS['TL_DCA']['tl_page']['fields']['translateUrl'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_page']['fields']['translateUrl'],
    'inputType' => 'select',
    'options_callback' => array('tl_page_extend', 'getModules'),
    'eval' => array('chosen' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
    'sql' => "varchar(255) NOT NULL default ''"
);

/**
 * Class tl_page_extend
 */
class tl_page_extend extends tl_page
{
    /**
     * @return array
     */
    public function getModules()
    {
        $modulesDB = $this->Database->prepare('SELECT * FROM tl_fmodules')->execute();
        $arrModules = array();
        if($modulesDB->count())
        {
            while($modulesDB->next())
            {
                if($modulesDB->tablename)
                {
                    $arrModules[$modulesDB->tablename] = $modulesDB->name;
                }
            }
        }
        return $arrModules;
    }
}