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

use Contao\Frontend;

/**
 * Class FModule
 * @package FModule
 */
class FModuleLanguage extends Frontend
{

    /**
     * @var string
     */
    protected $strTable = '';

    /**
     * @param $arrGet
     * @param $strLanguage
     * @param $arrRootPage
     * @param $addToNavigation
     * @return mixed
     */
    public function translateUrlParameters($arrGet, $strLanguage, $arrRootPage, $addToNavigation)
    {

        global $objPage;

        if (!\Config::get('useAutoItem')) {
            return $arrGet;
        }

        // set alias
        $alias = \Input::get('auto_item');

        if (!$alias) {
            return $arrGet;
        }

        if (isset($objPage->addTranslateUrl) && $objPage->addTranslateUrl == '1') {
            $this->strTable = $objPage->translateUrl;
        }

        if (!$this->strTable) {
            return $arrGet;
        }

        $table = $this->strTable;
        $tableData = $this->strTable . '_data';

        $objData = $this->Database->prepare('SELECT ' . $tableData . '.*, ' . $table . '.fallback FROM ' . $tableData . ' LEFT OUTER JOIN ' . $table . ' ON ' . $tableData . '.pid=' . $table . '.id WHERE ' . $tableData . '.id=? OR ' . $tableData . '.alias=?')
            ->limit(1)
            ->execute((int)$alias, $alias);

        if ($objData->numRows) {
            $alias = $objData->fallback ? $objData->languageMain : $objData->alias;
            $objItem = $this->Database->prepare('SELECT ' . $tableData . '.alias FROM ' . $tableData . ' LEFT OUTER JOIN ' . $table . ' ON ' . $tableData . '.pid=' . $table . '.id WHERE ' . $table . '.language=? AND (' . $tableData . '.alias=? OR mainLanguage=?)')->execute($strLanguage, $alias, $alias);

            if ($objItem->numRows) {
                $alias = $objItem->alias ? $objItem->alias : $objItem->id;
            }
        }

        // fr -> alias: reference-alpha-11, detail: Référence
        // en -> alias: reference-alpha, detail: Reference
        // de -> alias: referenz-alpha detail: Referenz
        /*
        if ($strLanguage === 'fr') {
            $arrGet['url']['items'] = 'reference-alpha-11';
        }
        if ($strLanguage === 'de') {
            $arrGet['url']['items'] = 'referenz-alpha';
        }
        if ($strLanguage === 'en') {
            $arrGet['url']['items'] = 'reference-alpha';
        }
        */
        return $arrGet;
    }

}