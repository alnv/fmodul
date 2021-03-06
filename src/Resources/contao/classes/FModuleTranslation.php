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
 * Class FModuleTranslation
 * @package FModule
 */
class FModuleTranslation extends \Frontend
{

    /**
     * @var string
     */
    protected $strTable = '';

    /**
     * @param \Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent $event
     * @return null
     */
    public function translateUrlParameters(\Terminal42\ChangeLanguage\Event\ChangelanguageNavigationEvent $event)
    {

        global $objPage;
        $targetRoot = $event->getNavigationItem()->getRootPage();
        $strLanguage = $targetRoot->rootLanguage ? $targetRoot->rootLanguage : $targetRoot->language; // The target language

        // Find your current and new alias from the current URL
        if (!\Config::get('useAutoItem')) return null;

        $varAlias = \Input::get('auto_item');

        if (isset($objPage->addTranslateUrl) && $objPage->addTranslateUrl == '1') {
            $this->strTable = $objPage->translateUrl;
        }

        if (!$this->strTable) return null;

        $table = $this->strTable;
        $tableData = $this->strTable . '_data';

        // get current item
        $objItem = $this->Database->prepare('SELECT ' . $tableData . '.*, ' . $table . '.fallback, ' . $table . '.language FROM ' . $tableData . ' LEFT OUTER JOIN ' . $table . ' ON ' . $tableData . '.pid = ' . $table . '.id WHERE ' . $tableData . '.alias = ? OR ' . $tableData . '.id = ?')->limit(1)->execute($varAlias, (int)$varAlias);

        $newAlias = '';

        if ($objItem->numRows) {
            // get all items with the same fallback item
            $fallback = !$objItem->fallback ? $objItem->mainLanguage : $objItem->id;
            // select alias
            $objTranslation = $this->Database->prepare('SELECT ' . $tableData . '.alias, ' . $tableData . '.id, ' . $tableData . '.mainLanguage, ' . $table . '.language FROM ' . $tableData . ' LEFT OUTER JOIN ' . $table . ' ON ' . $tableData . '.pid = ' . $table . '.id WHERE ' . $table . '.language = ? AND (' . $tableData . '.id = ? OR ' . $tableData . '.mainLanguage = ?)')->execute($strLanguage, $fallback, $fallback);

            if ($objTranslation->numRows) {
                $newAlias = $objTranslation->alias ? $objTranslation->alias : $objTranslation->id;
            }
        }


        // Pass the new alias to ChangeLanguage
        $event->getUrlParameterBag()->setUrlAttribute('items', $newAlias);
    }

    /**
     * @param $arrGet
     * @param $strLanguage
     * @return mixed
     */
    public function translateUrlParametersBackwardsCompatible($arrGet, $strLanguage)
    {
        global $objPage;

        if (!\Config::get('useAutoItem')) return $arrGet;
        $alias = \Input::get('auto_item');

        if (!$alias) return $arrGet;

        if (isset($objPage->addTranslateUrl) && $objPage->addTranslateUrl == '1') {
            $this->strTable = $objPage->translateUrl;
        }

        if (!$this->strTable) return $arrGet;

        $table = $this->strTable;
        $tableData = $this->strTable . '_data';

        // get current item
        $currentItemDB = $this->Database->prepare('SELECT ' . $tableData . '.*, ' . $table . '.fallback, ' . $table . '.language FROM ' . $tableData . ' LEFT OUTER JOIN ' . $table . ' ON ' . $tableData . '.pid = ' . $table . '.id WHERE ' . $tableData . '.alias = ? OR ' . $tableData . '.id = ?')->limit(1)->execute($alias, (int)$alias);

        if ($currentItemDB->numRows) {
            // get all items with the same fallback item
            $fallback = !$currentItemDB->fallback ? $currentItemDB->mainLanguage : $currentItemDB->id;
            // select alias
            $translationDB = $this->Database->prepare('SELECT ' . $tableData . '.alias, ' . $tableData . '.id, ' . $tableData . '.mainLanguage, ' . $table . '.language FROM ' . $tableData . ' LEFT OUTER JOIN ' . $table . ' ON ' . $tableData . '.pid = ' . $table . '.id WHERE ' . $table . '.language = ? AND (' . $tableData . '.id = ? OR ' . $tableData . '.mainLanguage = ?)')->execute($strLanguage, $fallback, $fallback);
            if ($translationDB->numRows) {
                $arrGet['url']['items'] = $translationDB->alias ? $translationDB->alias : $translationDB->id;
            }
        }
        return $arrGet;
    }

}