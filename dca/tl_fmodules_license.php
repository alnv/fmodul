<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   F Modul
 * @author    Alexander Naumov
 * @license   commercial
 * @copyright Alexander Naumov 2015
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
            'save_callback' => array(array('tl_fmodules_license', 'saveKey'))
        )
    )

);

/**
 * Class tl_fmodules_license
 */
class tl_fmodules_license extends \Contao\Backend
{

    /**
     * @var array
     */
   private $validSums = array('d9df09a01bfe794ceaa08ca586feefed', '678c93b7bbfd067379fbb1337f0270cc', '45e45b2f019cfe2b1809e23fe4d26963');

    /**
     * @param $value
     * @param $dc
     * @return mixed
     */
    public function saveKey($varValue, $dc)
    {
        if ( $varValue != '' && !$this->checkKey($varValue) ) {

            throw new \Exception($GLOBALS['TL_LANG']['tl_fmodules_license']['invalidKey']);

        }

        return $varValue;
    }

    /**
     * @param $key
     * @return bool
     */
    public function checkKey($key)
    {

        if(!$key)
        {
            return false;
        }


        if ( in_array(md5($key), $this->validSums, true) ) {

            return true;

        }

        return false;

    }

}