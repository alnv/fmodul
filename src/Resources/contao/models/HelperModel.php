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
use Contao\Database;
use Contao\PageModel;

/**
 * Class HelperModel
 * @package FModule
 */
class HelperModel
{
    /**
     * @return bool
     */
    public static function previewMode()
    {
        if (BE_USER_LOGGED_IN) return true;
        return false;
    }

    /**
     * @param $tablename
     * @param $alias
     * @param null $lang
     * @param array $item
     * @param array $wrapper
     * @return string
     */
    public static function getHrefAttributes($tablename, $alias, $lang = null, $item = array(), $wrapper = array())
    {
        $tableWrapper = $tablename;
        $tableData = $tableWrapper . '_data';
        $Database = Database::getInstance();
        $currentItemDB = array();
        $strHrefLang = '';

        if (empty($item) || empty($wrapper)) {
            $currentItemDB = $Database->prepare('SELECT ' . $tableData . '.*, ' . $tableWrapper . '.fallback, ' . $tableWrapper . '.language FROM ' . $tableData . ' LEFT OUTER JOIN ' . $tableWrapper . ' ON ' . $tableData . '.pid = ' . $tableWrapper . '.id WHERE ' . $tableData . '.alias = ? OR ' . $tableData . '.id = ?')->limit(1)->execute($alias, (int)$alias);
            $currentItemDB = $currentItemDB->row();
        }

        if (!empty($item) && !empty($wrapper) && empty($currentItemDB)) {
            $currentItemDB['id'] = $item['id'];
            $currentItemDB['alias'] = $item['alias'];
            $currentItemDB['mainLanguage'] = $item['mainLanguage'];
            $currentItemDB['fallback'] = $wrapper['fallback'];
            $currentItemDB['language'] = $wrapper['language'];
        }

        if (!empty($currentItemDB)) {
            // get all items with the same fallback item
            $fallback = !$currentItemDB['fallback'] ? $currentItemDB['mainLanguage'] : $currentItemDB['id'];

            // select alias
            $translationDB = $Database->prepare('SELECT ' . $tableData . '.*, ' . $tableData . '.mainLanguage, ' . $tableWrapper . '.language, ' . $tableWrapper . '.fallback, ' . $tableWrapper . '.rootPage, ' . $tableWrapper . '.addDetailPage FROM ' . $tableData . ' LEFT OUTER JOIN ' . $tableWrapper . ' ON ' . $tableData . '.pid = ' . $tableWrapper . '.id WHERE ' . $tableData . '.id = ? OR ' . $tableData . '.mainLanguage = ?')->execute($fallback, (int)$fallback);
            while ($translationDB->next()) {
                $url = '/';
                if (!$translationDB->addDetailPage) continue;

                // default
                if ($translationDB->source == 'default') {
                    $objParent = PageModel::findWithDetails($translationDB->rootPage);
                    if (!static::pageIsEnable($objParent)) continue;
                    $domain = ($objParent->rootUseSSL ? 'https://' : 'http://') . ($objParent->domain ?: \Environment::get('host')) . TL_PATH . '/';
                    $strUrl = $domain . \Controller::generateFrontendUrl($objParent->row(), ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/%s' : '/items/%s'), $objParent->language);
                    $url = static::getLink($translationDB, $strUrl);
                }

                // external
                if ($translationDB->source == 'external') {
                    $url = $translationDB->url;
                }

                // internal
                if ($translationDB->source == 'internal') {
                    $objParent = PageModel::findWithDetails($translationDB->jumpTo);
                    if (!static::pageIsEnable($objParent)) continue;
                    $domain = ($objParent->rootUseSSL ? 'https://' : 'http://') . ($objParent->domain ?: \Environment::get('host')) . TL_PATH . '/';
                    $url = $domain . \Controller::generateFrontendUrl($objParent->row());
                }

                $strHrefLang .= '<link rel="alternate" hreflang="' . $translationDB->language . '" href="' . $url . '">';
            }
        }

        return $strHrefLang;
    }

    /**
     * @param $objPage
     * @return bool
     */
    public static function pageIsEnable(PageModel $objPage)
    {
        $return = true;
        $time = method_exists('Date', 'floorToMinute') ? \Date::floorToMinute() : time();
        if ($objPage === null) {
            $return = false;
        }

        if (!$objPage->published || ($objPage->start != '' && $objPage->start > $time) || ($objPage->stop != '' && $objPage->stop <= ($time + 60))) {
            $return = false;
        }

        return $return;
    }

    /**
     * @param $objItem
     * @param $strUrl
     * @param string $strBase
     * @return string
     * @throws \Exception
     */
    public static function getLink($objItem, $strUrl, $strBase = '')
    {
        // switch
        switch ($objItem->source) {
            // Link to an external page
            case 'external':
                return $objItem->url;
                break;

            // Link to an internal page
            case 'internal':
                if ($objItem->jumpTo) {
                    $objPage = PageModel::findWithDetails($objItem->jumpTo);
                    $domain = ($objPage->rootUseSSL ? 'https://' : 'http://') . ($objPage->domain ?: \Environment::get('host')) . TL_PATH . '/';
                    return $domain . \Controller::generateFrontendUrl($objPage->row(), '', $objPage->language);
                }
                break;

            // Link to an article
            case 'article':
                if (($objArticle = \ArticleModel::findByPk($objItem->articleId, array('eager' => true))) !== null && ($objPid = $objArticle->getRelated('pid')) !== null) {
                    return $strBase . ampersand(\Controller::generateFrontendUrl($objPid->row(), '/articles/' . ((!\Config::get('disableAlias') && $objArticle->alias != '') ? $objArticle->alias : $objArticle->id)));
                }
                break;
        }

        // Link to the default page
        return $strBase . sprintf($strUrl, (($objItem->alias != '' && !\Config::get('disableAlias')) ? $objItem->alias : $objItem->id));
    }

    /**
     * @param $rowField
     * @return array
     */
    public static function setGoogleMap($rowField)
    {
        $template = DiverseFunction::parseTemplateName($rowField['mapTemplate']);
        $zoom = $rowField['mapZoom'] ? $rowField['mapZoom'] : 15;
        $scrollWheel = $rowField['mapScrollWheel'] ? 'true' : 'false';
        $mapType = $rowField['mapType'] ? $rowField['mapType'] : 'ROADMAP';
        $styles = $rowField['mapStyle'] ? $rowField['mapStyle'] : '';

        $mapSettings = array(
            'fieldID' => $rowField['fieldID'],
            'title' => mb_convert_encoding($rowField['title'], 'UTF-8'),
            'description' => mb_convert_encoding($rowField['description'], 'UTF-8'),
            'template' => $template,
            'mapScrollWheel' => $scrollWheel,
            'mapZoom' => $zoom,
            'mapType' => $mapType,
            'mapStyle' => $styles,
            'mapMarker' => $rowField['mapMarker'],
            'mapInfoBox' => $rowField['mapInfoBox']
        );

        return $mapSettings;
    }

    /**
     * @param $item
     * @param $allowedGroups
     * @return bool
     */
    public static function sortOutProtected($item, $allowedGroups)
    {
        if (BE_USER_LOGGED_IN) return false;
        if (FE_USER_LOGGED_IN && $item['guests'] == '1') return true;
        if (FE_USER_LOGGED_IN && $item['protected'] == '1') {
            $dataGroup = deserialize($item['groups']);
            if (!is_array($dataGroup) || empty($dataGroup) || count(array_intersect($dataGroup, $allowedGroups)) < 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $arrFilter
     * @return array
     */
    public static function generateSQLQueryFromFilterArray($arrFilter)
    {
        $qStr = '';
        $qTextSearch = '';
        $isFulltextSearch = false;
        $searchSettings = array();

        foreach ($arrFilter as $field) {

            if ($field['enable']) {
                switch ($field['type']) {
                    case 'simple_choice':
                        $qStr .= QueryModel::simpleChoiceQuery($field);
                        break;
                    case 'date_field':
                        $qStr .= QueryModel::dateFieldQuery($field);
                        break;
                    case 'search_field':
                        $qStr .= QueryModel::searchFieldQuery($field);
                        break;
                    case 'multi_choice':
                        $qStr .= QueryModel::multiChoiceQuery($field);
                        break;
                    case 'toggle_field':
                        $qStr .= QueryModel::toggleFieldQuery($field);
                        break;
                    case 'fulltext_search':
                        $isValue = QueryModel::isValue($field['value']);
                        if ($isValue) {
                            $isFulltextSearch = true;
                            $qTextSearch = $field['value'];
                            $searchSettings = array(
                                'fields' =>  $field['fullTextSearchFields'] ? $field['fullTextSearchFields'] : 'title,description',
                                'orderBy' => $field['fullTextSearchOrderBy'] ? $field['fullTextSearchOrderBy'] : 'title'
                            );
                        }
                        break;
                }
            }
        }

        return array(
            'qStr' => $qStr,
            'isFulltextSearch' => $isFulltextSearch,
            '$qTextSearch' => $qTextSearch,
            'searchSettings' => $searchSettings
        );
    }

    /**
     * @param $start
     * @param $stop
     * @return bool
     */
    static public function outSideScope($start, $stop)
    {
        if ($start != '' || $stop != '') {
            $currentTime = (int)date('U');
            if ($currentTime < (int)$start) {
                return false;
            }
            if ($currentTime > (int)$stop && (int)$stop != 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * @param $arrValues
     * @return array
     */
    public static function replaceInsertTagsInArray($arrValues)
    {
        $values = array();
        foreach($arrValues as $key => $arrValue)
        {
            if(is_array($arrValue))
            {
                foreach($arrValue as $strValue)
                {
                    $values[$key][] = static::_replaceInsertTags($strValue);
                }
            }else{
                $values[] = static::_replaceInsertTags($arrValue);
            }

        }
        return $values;
    }

    /**
     * @param $strValue
     * @return string
     */
    private static function _replaceInsertTags($strValue)
    {
        if(is_string($strValue))
        {
            return \Controller::replaceInsertTags($strValue);
        }
        return $strValue;
    }
}