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

}