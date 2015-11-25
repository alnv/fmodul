<?php

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

$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('formp;', 'formp;{fmodules_legend},fmodules,fmodulesp,fmodulesfeed,fmodulesfeedp;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['fmodules'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_user_group']['fmodules'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_fmodules.name',
    'eval' => array('multiple' => true),
    'sql' => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['fmodulesp'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_user_group']['fmodulesp'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => array('create', 'delete'),
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => array('multiple' => true),
    'sql' => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['fmodulesfeed'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_user_group']['fmodulesfeed'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_fmodules_feed.title',
    'eval' => array('multiple' => true),
    'sql' => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['fmodulesfeedp'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_user_group']['fmodulesfeedp'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => array('create', 'delete'),
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => array('multiple' => true),
    'sql' => "blob NULL"
);