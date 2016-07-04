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

//
$GLOBALS['TL_DCA']['tl_settings']['palettes']['default'] = str_replace('disableAlias;', 'disableAlias;{fmodule_legend:hide},googleApiKey,taxonomyDisable;', $GLOBALS['TL_DCA']['tl_settings']['palettes']['default']);

// googleApiKey
$GLOBALS['TL_DCA']['tl_settings']['fields']['googleApiKey'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['googleApiKey'],
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50')
);

// taxonomyDisable
$GLOBALS['TL_DCA']['tl_settings']['fields']['taxonomyDisable'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_settings']['taxonomyDisable'],
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'w50 m12')
);