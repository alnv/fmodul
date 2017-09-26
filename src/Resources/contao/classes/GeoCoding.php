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

/**
 * Class GeoCoding
 * @package FModule
 */
class GeoCoding extends \Backend
{
    /**
     * @var array
     */
    protected $geoCordsCache = [];

    /**
     * @var null
     */
    static private $instance = null;

    /**
     * @return DCACreator|null
     */
    static public function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param string $address
     * @param $lang
     * @param bool $blnServer
     * @return array|mixed
     */
    public function getGeoCords( $address = '', $lang, $blnServer = false )
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

        if( !is_null( $cacheReturn ) && is_array( $cacheReturn ) ) {

            return $cacheReturn;
        }

        // check if api key exist
        $apiKey = '';
        $strServerID = \Config::get('googleServerKey') ? \Config::get('googleServerKey') : '';
        $strBrowserID = \Config::get('googleApiKey') ? \Config::get('googleApiKey') : '';
        $strGoogleID = !$blnServer ? $strBrowserID : $strServerID;

        if ( $strGoogleID ) {

            $apiKey = '&key='. $strGoogleID . '';
        }

        // create google map api
        $api = 'https://maps.googleapis.com/maps/api/geocode/json?address=%s%s&language=%s&region=%s';
        $strURL = sprintf($api, urlencode($address), $apiKey, urlencode($lang), strlen($lang));

        // send request to google maps api
        $request = new \Request();
        $request->send($strURL);

        // check if request is valid
        if ( $request->hasError() ) {

            return $return;
        }

        $response = $request->response ? json_decode($request->response, true) : array();

        if ( !is_array( $response ) || empty( $response ) ) {

            return $return;
        }

        if ( isset( $response['error_message'] ) && $response['error_message'] ) {

            \System::log( $response['error_message'], '\Fmodule\GeoCoding\getGeoCords', 'Google Maps' );
            return $return;
        }

        // set lng and lat
        if ( $response['results'][0]['geometry'] ) {

            $geometry = $response['results'][0]['geometry'];

            $return['lat'] = $geometry['location'] ? $geometry['location']['lat'] : '';
            $return['lng'] = $geometry['location'] ? $geometry['location']['lng'] : '';
        }

        // save cache
        $this->geoCordsCache[$keyID] = $return;

        // return  geoCoding
        return $return;
    }
}