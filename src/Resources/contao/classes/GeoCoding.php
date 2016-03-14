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
use Contao\Config;
use Contao\Request;

/**
 * Class GeoCoding
 * @package FModule
 */
class GeoCoding extends Backend
{
    /**
     * @var array
     */
    protected $geoCordsCache = [];

    /**
     * @param string $address
     * @param $lang
     * @return array
     */
    public function getGeoCords($address = '', $lang)
    {
        // default return value
        $return = array('lat' => '0', 'lng' => '0');

        // check if parameters are set
        if (!$lang) $lang = 'de';
        if (!$address) return $return;

        // set id
        $keyID = md5(urlencode($address));

        // get lat and lng from cache
        $cacheReturn = $this->geoCordsCache[$keyID];
        if(!is_null($cacheReturn) && is_array($cacheReturn)) return $cacheReturn;

        // check if api key exist
        $apiKey = '';
        if(Config::get('googleApiKey'))
        {
            $apiKey = '&key='.Config::get('googleApiKey').'';
        }

        // create google map api
        $api = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s%s&sensor=false&language=%s&region=%s';
        $strURL = sprintf($api, urlencode($address), $apiKey, urlencode($lang), strlen($lang));

        // send request to google maps api
        $request = new Request();
        $request->send($strURL);

        // check if request is valid
        if ($request->hasError()) return $return;
        $response = $request->response ? json_decode($request->response, true) : array();
        if(!is_array($response)) return $return;
        if (empty($response)) return $return;

        // set lng and lat
        if ($response['results'][0]['geometry'])
        {
            $geometry = $response['results'][0]['geometry'];
            $return['lat'] = $geometry['location'] ? $geometry['location']['lat'] : '';
            $return['lng'] = $geometry['location'] ? $geometry['location']['lng'] : '';
        }

        // save cache
        $this->geoCordsCache[$keyID] = $return;

        // return  geocoding
        return $return;
    }
}