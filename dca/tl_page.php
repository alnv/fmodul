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

$GLOBALS['TL_DCA']['tl_page']['palettes']['regular'] = str_replace('includeLayout;', 'includeLayout;{fmodule_taxonomy_legend:hide},page_taxonomy;', $GLOBALS['TL_DCA']['tl_page']['palettes']['regular']);

$GLOBALS['TL_DCA']['tl_page']['fields']['page_taxonomy'] = array(

    'label' => &$GLOBALS['TL_LANG']['tl_page']['page_taxonomy'],
    'inputType' => 'modeSettings',
    'eval' => array('submitOnChange' => true),
    'sql' => "blob NULL"

);