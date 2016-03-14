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

use Contao\Module;
use Contao\BackendTemplate;


/**
 * Class ModuleDetailView
 * @package FModule
 */
class ModuleGoogleMapsView extends Module
{
    /**
     * @var string
     */
    protected $strTemplate = 'mod_fmodule_google_maps';

    /**
     *
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {

            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '. $this->name .' ###';
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            return $objTemplate->parse();

        }
        return parent::generate();
    }

    /**
     *
     */
    protected function compile()
    {
        //
    }
}