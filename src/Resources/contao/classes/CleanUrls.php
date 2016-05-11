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
 * Class CleanUrls
 * @package FModule
 */
class CleanUrls extends \Frontend
{
    /**
     * @param $arrFragments
     * @return array
     */
    public function getPageIdFromUrlStr($arrFragments)
    {
        /*
        if(count($arrFragments) > 0)
        {
            $incFragments = count($arrFragments) - 1;
            $setAutoItems = array('auto_taxonomy', 'auto_tag'); // allowed auto_items
            $incAutoItems = 0;

            while($incFragments > -1 && $incAutoItems < count($setAutoItems))
            {
                \Input::setGet($setAutoItems[$incAutoItems], $arrFragments[$incFragments]);
                $incFragments -= 1;
                $incAutoItems += 1;
            }

        }
        */
        return array_unique($arrFragments);
    }
}