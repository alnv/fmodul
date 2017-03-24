<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @package   F Modul
 * @author    Alexander Naumov
 * @license   commercial
 * @copyright Alexander Naumov 2016
 */

$GLOBALS['TL_DCA']['tl_fmodules_license'] = array(

    'config' => array(

        'dataContainer' => 'File',
        'closed' => true,
    ),

    'palettes' => array(
        'default' => '{license_legend},fmodule_license',
    ),

    'fields' => array(

        'fmodule_license' => array(

            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_license']['fmodule_license'],
            'inputType' => 'text',
            'eval' => array('tl_class' => 'w50'),
            'save_callback' => array( array('tl_fmodules_license', 'verifyLicence') )
        )
    )

);

class tl_fmodules_license extends \Backend {


    public function verifyLicence( $varValue ) {

        $objCatalogManagerVerification = new \FModule\FModuleVerification();
        $blnValidLicence = $objCatalogManagerVerification->verify( $varValue );

        if ( !$varValue ) return '';

        if ( !$blnValidLicence ) {

            throw new \Exception( $GLOBALS['TL_LANG']['tl_fmodules_license']['invalidKey'] );
        }

        return $varValue;
    }
}