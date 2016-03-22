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

namespace FModule;

use Contao\Environment;
use Contao\Config;

/**
 * Class DiverseFunction
 */
class DiverseFunction
{
    /**
     * @param $options
     * @return array
     */
    public static function conformOptionsArray($options)
    {
        $optionValueLabel = [];
        if (is_array($options)) {
            foreach ($options as $iso => $name) {
                $optionValueLabel[] = array(
                    'value' => $iso,
                    'label' => $name
                );
            }
        }
        return $optionValueLabel;
    }

    /**
     * @param $templateName
     * @return mixed
     */
    public static function parseTemplateName($templateName)
    {
        $arrReplace = array('#', '<', '>', '(', ')', '\\', '=');
        $arrSearch = array('&#35;', '&#60;', '&#62;', '&#40;', '&#41;', '&#92;', '&#61;');
        $strVal = str_replace($arrSearch, $arrReplace, $templateName);
        $strVal = str_replace(' ', '', $strVal);
        return preg_replace('/[\[{\(].*[\]}\)]/U', '', $strVal);
    }

    /**
     * @param bool $hasLibraries
     * @param string $language
     * @param string $GlobalMapID
     * @return string
     */
    public static function setMapJs($hasLibraries = true, $language = 'en', $GlobalMapID = '')
    {
        $startPoint = $hasLibraries ? 'FModuleLoadLibraries' : 'FModuleLoadMaps';
        $apiKey = '';
        if (Config::get('googleApiKey')) {
            $apiKey = '&amp;key=' . Config::get('googleApiKey') . '';
        }
        $mapJSLoadTemplate =
            '<script async defer>
                (function(){
                    var FModuleGoogleApiLoader = function(){
                        var mapApiScript = document.createElement("script");
                        mapApiScript.src = "http' . (Environment::get('ssl') ? 's' : '') . '://maps.google.com/maps/api/js?language=' . $language . $apiKey . '";
                        mapApiScript.onload = ' . $startPoint . ';
                        document.body.appendChild(mapApiScript);
                    };
                    var FModuleLoadLibraries = function()
                    {
                        var mapInfoBox = document.createElement("script");
                        mapInfoBox.src = "http' . (Environment::get('ssl') ? 's' : '') . '://google-maps-utility-library-v3.googlecode.com/svn/tags/infobox/1.1.9/src/infobox_packed.js";
                        mapInfoBox.onload = FModuleLoadMaps;
                        document.body.appendChild(mapInfoBox);
                    };
                    var FModuleLoadMaps = function()
                    {
                        try{
                            if(undefined != FModuleGoogleMap){for(var i = 0; i < FModuleGoogleMap.length; i++){FModuleGoogleMap[i]();}}
                        } catch(err)
                        {
                            console.warn("No Google Map found!");
                        }
                    };
                    if (document.addEventListener){document.addEventListener("DOMContentLoaded", FModuleGoogleApiLoader, false);} else if (document.attachEvent){document.attachEvent("onload", FModuleGoogleApiLoader);}
                })();
            </script>';
        return $mapJSLoadTemplate;
    }

}