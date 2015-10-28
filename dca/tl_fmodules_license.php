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
     * @param $value
     * @param $dc
     * @return mixed
     */
    public function saveKey($varValue, $dc)
    {
        if ($varValue != '' && !$this->checkKey($varValue)) {

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

        if (!$key) {
            return false;
        }

        $validSums = new \FModule\FModule();

        if (in_array(md5($key), $validSums->validSums, true)) {

            return true;

        }

        return false;

    }

}