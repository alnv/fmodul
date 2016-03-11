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

use Contao\Backend;

/**
 * Class GeoCoding
 * @package FModule
 */
class GeoCoding extends Backend
{
    /**
     * @param string $address
     * @param $country
     * @return array
     */
    public function getGeoCords($address = "", $country)
    {
        if(!$country) $country = "de";
        return array("lat" => "0", "lng" => "0");
    }
}