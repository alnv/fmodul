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


class FModuleLabel {

    
    public static function translate( $strValue, $strLabel ) {

        if ( $strValue ) {

            $strTitle = $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['options_label'][ $strValue ];
            $strLabel = $strTitle ? $strTitle : $strLabel;
        }

        return $strLabel;
    }
}