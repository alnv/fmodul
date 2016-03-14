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
}