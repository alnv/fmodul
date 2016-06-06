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

$GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('formp;', 'formp;{fmodules_legend},fmodules,fmodulesp,fmodulesfeed,fmodulesfeedp,fmodulesfilters,fmodulesfiltersp,taxonomies,taxonomiesp;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);

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

$GLOBALS['TL_DCA']['tl_user_group']['fields']['taxonomies'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_user_group']['taxonomies'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_taxonomies.name',
    'options_callback' => array('tl_user_group_fmodule', 'getTaxonomies'),
    'eval' => array('multiple' => true),
    'sql' => "blob NULL"
);

// taxonomies
$GLOBALS['TL_DCA']['tl_user_group']['fields']['taxonomiesp'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_user_group']['taxonomiesp'],
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

$GLOBALS['TL_DCA']['tl_user_group']['fields']['fmodulesfilters'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_user_group']['fmodulesfilters'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'foreignKey' => 'tl_fmodules_filters.title',
    'eval' => array('multiple' => true),
    'sql' => "blob NULL"
);

$GLOBALS['TL_DCA']['tl_user_group']['fields']['fmodulesfiltersp'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_user_group']['fmodulesfiltersp'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => array('create', 'delete'),
    'reference' => &$GLOBALS['TL_LANG']['MSC'],
    'eval' => array('multiple' => true),
    'sql' => "blob NULL"
);

/**
 * Class tl_user_fmodule
 */
class tl_user_group_fmodule extends \Backend{

    /**
     * @return array
     */
    public function getTaxonomies()
    {
        $objTaxonomiesDB= $this->Database->prepare('SELECT id, name FROM tl_taxonomies WHERE pid = ?')->execute('0');
        $arrOptions = array();
        while ($objTaxonomiesDB->next())
        {
            $arrOptions[$objTaxonomiesDB->id] = $objTaxonomiesDB->name;
        }
        return $arrOptions;
    }

}