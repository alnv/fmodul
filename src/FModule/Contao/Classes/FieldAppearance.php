<?php namespace FModule;

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   F Modul
 * @author    Alexander Naumov http://www.alexandernaumov.de
 * @license   commercial
 * @copyright 2015 Alexander Naumov
 */

use Contao\Widget;

class FieldAppearance{

    public static function getAppearance()
    {
       $fields = array(
            'simple_choice' => array(
                'radio' => 'Radio',
                'select' => 'Select'
            ),
            'multi_choice' => array(
                'checkbox' => 'Checkboxen',
				'tags' => 'Tags',
            ),
        );

       return $fields;
    }

}