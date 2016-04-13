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
use Contao\Input;

/**
 * Class FModuleInsertTags
 * @package FModule
 */
class FModuleInsertTags extends Frontend
{
    /**
     * @param $strTag
     * @return bool|string
     */
    public function setHooks($strTag)
    {
        $arrSplit = explode('::', $strTag);
        // get url
        if (($arrSplit[0] == 'fm_url' || $arrSplit[0] == 'fmUrl') && count($arrSplit) > 2) {
            return $this->getUrlFromItem($arrSplit);
        }
        // count items
        if (($arrSplit[0] == 'fm_count' || $arrSplit[0] == 'fmCount') && $arrSplit[1]) {

            $tablename = $arrSplit[1] . '_data';
            $qPid = $arrSplit[2] ? ' AND pid = "' . $arrSplit[2] . '"' : '';
            $q = $arrSplit[3] ? Input::decodeEntities($arrSplit[3]) : '';
            $q = str_replace('[&]', '&', $q);

            if ($q) {
                $filterArr = $this->getFilterFields($q);
                $qResult = HelperModel::generateSQLQueryFromFilterArray($filterArr);
                $q = $qResult['qStr'];
            }

            if ($this->Database->tableExists($tablename)) {
                return $this->Database->prepare('SELECT id FROM ' . $tablename . ' WHERE published = "1"' . $qPid . $q . '')->query()->count();
            }

            return 0;
        }
        return false;
    }

    /**
     * @param $arrSplit
     * @return bool|string
     */
    private function getUrlFromItem($arrSplit)
    {
        if ($arrSplit[1] && $arrSplit[2]) {

            $tablename = $arrSplit[1];
            $tablename_data = $tablename . '_data';
            $id = $arrSplit[2];

            if (!$this->Database->tableExists($tablename) || !$this->Database->tableExists($tablename_data)) return false;
            $dataDB = $this->Database->prepare('SELECT * FROM ' . $tablename_data . ' WHERE id = ?')->execute($id);

            if ($dataDB->count() < 1) return false;
            $item = $dataDB->row();
            $pid = $item['pid'];
            $wrapperDB = $this->Database->prepare('SELECT * FROM ' . $tablename . ' WHERE id = ?')->execute($pid);

            if ($wrapperDB->count() < 1) return false;
            $wrapper = $wrapperDB->row();

            if ($wrapper['addDetailPage'] != '1') return false;
            $objParent = \PageModel::findWithDetails($wrapper['rootPage']);

            if ($objParent === null) return false;
            $domain = ($objParent->rootUseSSL ? 'https://' : 'http://') . ($objParent->domain ?: \Environment::get('host')) . TL_PATH . '/';
            $strUrl = $domain . $this->generateFrontendUrl($objParent->row(), ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/%s' : '/items/%s'), $objParent->language);
            $url = $this->getLink($dataDB, $strUrl);
            return $url;

        }

        return false;
    }

    /**
     * @param $objItem
     * @param $strUrl
     * @param string $strBase
     * @return string
     * @throws \Exception
     */
    private function getLink($objItem, $strUrl, $strBase = '')
    {
        // switch
        switch ($objItem->source) {
            // Link to an external page
            case 'external':
                return $objItem->url;
                break;

            // Link to an internal page
            case 'internal':
                if (($objTarget = $objItem->getRelated('jumpTo')) !== null) {
                    return $strBase . $this->generateFrontendUrl($objTarget->row());
                }
                break;

            // Link to an article
            case 'article':
                if (($objArticle = \ArticleModel::findByPk($objItem->articleId, array('eager' => true))) !== null && ($objPid = $objArticle->getRelated('pid')) !== null) {
                    return $strBase . ampersand($this->generateFrontendUrl($objPid->row(), '/articles/' . ((!\Config::get('disableAlias') && $objArticle->alias != '') ? $objArticle->alias : $objArticle->id)));
                }
                break;
        }

        // Link to the default page
        return $strBase . sprintf($strUrl, (($objItem->alias != '' && !\Config::get('disableAlias')) ? $objItem->alias : $objItem->id));
    }

    /**
     * @param $q
     * @return array
     */
    private function getFilterFields($q)
    {
        $notSupportedTypes = array('legend_start', 'legend_end', 'fulltext_search', 'widget');
        $notSupportedID = array('orderBy', 'sorting_fields', 'sorting_fields', 'pagination');
        parse_str($q, $qRow);
        $qArr = array();

        foreach ($qRow as $k => $v) {
            $qArr[$k] = $v;
        }

        if (empty($qArr)) return array();

        $allFiltersDB = $this->Database->prepare('SELECT * FROM tl_fmodules_filters')->execute();
        $filterArr = array();

        while ($allFiltersDB->next()) {

            $tname = $allFiltersDB->fieldID;

            if (in_array($tname, $notSupportedID) || in_array($allFiltersDB->type, $notSupportedTypes)) {
                continue;
            }

            if ($qArr[$tname] || $allFiltersDB->type == 'toggle_field') {
                $filterArr[$tname] = $allFiltersDB->row();
                $filterArr[$tname]['value'] = $qArr[$tname];
                $filterArr[$tname]['enable'] = true;
                $filterArr[$tname]['operator'] = $qArr[$tname . '_int'] ? $qArr[$tname . '_int'] : '';
            }

            if ($allFiltersDB->type == 'wrapper_field' && ($allFiltersDB->from_field == $allFiltersDB->to_field)) {
                $fname = $allFiltersDB->from_field . '_btw';
                Input::setGet($fname, $qArr[$fname]);
            }

            if ($allFiltersDB->type == 'toggle_field' && !$qArr[$tname]) {
                $filterArr[$tname]['value'] = 'skip';
            }
        }
        return $filterArr;
    }
}