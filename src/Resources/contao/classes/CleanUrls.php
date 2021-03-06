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
        if(count($arrFragments) > 0 && !\Config::get('taxonomyDisable'))
        {
            $setTaxonomy = false;
            $arrFolderUrlFragments = array();
            $arrTempFragments = $arrFragments;

            if(\Config::get('folderUrl') && isset($arrFragments[0]) && !in_array('auto_item', $arrFragments)) {
                $arrFolderUrlFragments = explode('/', $arrFragments[0]);
            }

            $arrTempFragments = count($arrFolderUrlFragments) > 1 ? $arrFolderUrlFragments : $arrTempFragments;
            $setAutoItems = $this->setParameter($arrTempFragments);
            $arrCustomizedFragments = array();
            if($setAutoItems['specie'] || $setAutoItems['auto_item'])
            {
                $rootTaxonomy = $this->Database->prepare('SELECT id FROM tl_taxonomies WHERE (alias = ? OR alias = ?) AND published = "1"')->limit(1)->execute($setAutoItems['specie'],$setAutoItems['auto_item']);
                if($rootTaxonomy->numRows)
                {
                    $setTaxonomy = true;
                }
            }

            if($setTaxonomy)
            {
                $arrCustomizedFragments[] = $arrFragments[0];
                foreach($setAutoItems as $param => $value)
                {
                    if($param === 'auto_item')
                    {
                        $arrCustomizedFragments[] = $param;
                        $arrCustomizedFragments[] = $value;
                        continue;
                    }
                    \Input::setGet($param, $value);
                }

                // overwrite fragments
                $arrFragments = $arrCustomizedFragments;
            }
        }

        return array_unique($arrFragments);
    }

    /**
     * @param $arrFragments
     * @return array
     */
    private function setParameter($arrFragments)
    {
        $setAutoItems = array('auto_item'=> '', 'specie' => '', 'tags' => array());
        $intUrlPart = 1;

        foreach($setAutoItems as $param => $value)
        {
            if(isset($arrFragments[$intUrlPart]) && $arrFragments[$intUrlPart] && $this->isAutoItem($arrFragments[$intUrlPart]))
            {
                $intUrlPart++;
            }

            if(isset($arrFragments[$intUrlPart]) && $arrFragments[$intUrlPart])
            {
                $setAutoItems[$param] = $arrFragments[$intUrlPart];
            }
            $intUrlPart++;
        }

        return $setAutoItems;
    }

    /**
     * @param $strFragment
     * @return bool
     */
    private function isAutoItem($strFragment)
    {
        $return = false;
        if($strFragment === 'auto_item') $return = true;
        return $return;
    }
}