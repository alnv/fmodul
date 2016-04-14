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

use Contao\Cache;
use Contao\Frontend;
use Contao\Input;
use Contao\FrontendTemplate;

/**
 * Class FModuleInsertTags
 * @package FModule
 */
class FModuleInsertTags extends Frontend
{
    /**
     * @var bool
     */
    protected $loadMapScript = false;

    /**
     * @param $strTag
     * @return bool|string
     */
    public function setHooks($strTag)
    {
        $arrSplit = explode('::', $strTag);

        // generate Template
        if (($arrSplit[0] == 'fm_template' || $arrSplit[0] == 'fmTemplate') && $arrSplit) {
            return $this->generateTemplate($arrSplit);
        }

        // generate Field
        if (($arrSplit[0] == 'fm_field' || $arrSplit[0] == 'fmField') && $arrSplit) {
            return $this->generateField($arrSplit);
        }

        // Generate URL
        if (($arrSplit[0] == 'fm_url' || $arrSplit[0] == 'fmUrl') && count($arrSplit) > 2) {
            return $this->getUrlFromItem($arrSplit);
        }
        // Count Items
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
     *
     * {{fmField::fm_tablename::8::title}}
     *
     * @param $arrSplit
     * @return string
     */
    private function generateField($arrSplit)
    {
        if ($arrSplit[1] && $arrSplit[2] && $arrSplit[3]) {

            $tablename = $arrSplit[1];
            $tableData = $tablename . '_data';
            $id = $arrSplit[2];
            $field = $arrSplit[3];
            $cacheID = md5($arrSplit[1] . $arrSplit[2]);

            // check if table exist
            if (!$this->Database->tableExists($tableData)) {
                return 'table do not exist';
            }

            // get field from cache
            $cachedItem = Cache::get($cacheID);
            if ($cachedItem) {
                return $this->getField($cachedItem, $field);
            }

            // get data
            $qProtectedStr = 'AND published = "1"';

            //  check for preview mode
            if (HelperModel::previewMode()) $qProtectedStr = '';

            // q
            $itemDB = $this->Database->prepare('SELECT * FROM ' . $tablename . '_data WHERE id = ? ' . $qProtectedStr . ' LIMIT 1')->execute($id);

            // find and set map
            $maps = $this->findMapAndSet($tablename);

            //
            while ($itemDB->next()) {
                $item = $this->parseItem($itemDB, $tablename);

                if (!$item) return '';

                // parse map
                if (!empty($maps)) {
                    foreach ($maps as $map) {
                        $objMapTemplate = new FrontendTemplate($map['template']);
                        $item['mapSettings'] = $map;
                        $objMapTemplate->setData($item);
                        $item[$map['fieldID']] = $objMapTemplate->parse();
                    }
                }

                Cache::set($cacheID, $item);

                return $this->getField($item, $field);

            }

        }

        return 'no valid arguments';
    }

    /**
     * @param $item
     * @return mixed
     */
    private function getField($item, $field)
    {
        // objPage
        global $objPage;

        if (!$item[$field]) {
            return 'field do not exist';
        }

        // set js files
        if ($this->loadMapScript) {
            $language = $objPage->language ? $objPage->language : 'en';
            $GLOBALS['TL_HEAD']['mapJS'] = DiverseFunction::setMapJs($language);
        }

        return $item[$field];
    }

    /**
     *
     * {{fmView::fm_tablename::8::fm_view::hl=h2&class=myClass&id=jsID}}
     *
     * @param $arrSplit
     * @return string
     */
    private function generateTemplate($arrSplit)
    {
        if ($arrSplit[1] && $arrSplit[2]) {

            // objPage
            global $objPage;

            $this->import('FrontendUser', 'User');

            // tablename
            $tablename = $arrSplit[1];

            // id
            $id = $arrSplit[2];

            // template fm_view
            $template = $arrSplit[3] ? $arrSplit[3] : 'fm_view';

            // get parameter & parse parameter
            $params = $arrSplit[4] ? $arrSplit[4] : '';
            parse_str($params, $qRow);
            $headline = $qRow['hl'] ? $qRow['hl'] : 'h3';
            $className = $qRow['class'] ? $qRow['class'] . ' ' : '';
            $jsID = $qRow['id'] ? $qRow['id'] : '';

            // check if table exist
            if (!$this->Database->tableExists($tablename)) {
                return 'table do not exist';
            }

            // get data
            $qProtectedStr = 'published = "1"';

            //  check for preview mode
            if (HelperModel::previewMode()) $qProtectedStr = '';

            // build query
            $sqlQuery = 'SELECT * FROM ' . $tablename . '_data WHERE id = ' . $id . ' AND ' . $qProtectedStr . ' LIMIT 1';
            if ($id == 'RAND') {
                $sqlQuery = 'SELECT * FROM ' . $tablename . '_data WHERE ' . $qProtectedStr . ' ORDER BY RAND() LIMIT 1';
            }

            $maps = $this->findMapAndSet($tablename);

            // search for item
            $viewDB = $this->Database->prepare($sqlQuery)->execute();

            // check if item exist
            if (!$viewDB->count()) {
                return 'no item found';
            }

            // prepare for rendering
            $strTemplate = '';
            $objTemplate = new FrontendTemplate($template);

            // render
            while ($viewDB->next()) {

                // parse item
                $item = $this->parseItem($viewDB, $tablename);

                if (!$item) return '';

                // parse css & id
                $cssClass = $item['cssID'][1] ? $item['cssID'][1] . ' ' : '';
                $item['cssClass'] = $cssClass . $className;
                $item['jsID'] = $jsID ? $jsID : $item['cssID'][0];
                $item['templateName'] = $template;
                $item['tableName'] = $tablename;

                // parse headline
                $item['hl'] = $headline;

                // parse image
                if ($item['addImage']) {
                    $item['fullsize'] = ''; // disable full size
                    $objTemplate->addImageToTemplate($objTemplate, $item);
                    $item['picture'] = $objTemplate->picture;
                }

                // parse enclosure
                $objTemplate->enclosure = array();
                if ($item['addEnclosure']) {
                    $objTemplate->addEnclosuresToTemplate($objTemplate, $item);
                }
                $item['enclosure'] = $objTemplate->enclosure;

                // parse map
                if (!empty($maps)) {
                    foreach ($maps as $map) {
                        $objMapTemplate = new FrontendTemplate($map['template']);
                        $item['mapSettings'] = $map;
                        $objMapTemplate->setData($item);
                        $item[$map['fieldID']] = $objMapTemplate->parse();
                    }
                }

                // set data to template
                $objTemplate->setData($item);

                // parse template
                $strTemplate .= $objTemplate->parse();
            }

            // set js files
            if ($this->loadMapScript) {
                $language = $objPage->language ? $objPage->language : 'en';
                $GLOBALS['TL_HEAD']['mapJS'] = DiverseFunction::setMapJs($language);
            }

            // return template
            return $strTemplate;
        }

        return 'no valid arguments';
    }

    /**
     * @param $viewDB
     * @param $tablename
     * @return mixed
     */
    private function parseItem($viewDB, $tablename)
    {

        global $objPage;

        // get wrapper
        $wrapper = $this->Database->prepare('SELECT * FROM ' . $tablename . ' WHERE id = ?')->execute($viewDB->pid)->row();

        // get href
        $objParent = \PageModel::findWithDetails($wrapper['rootPage']);
        $domain = ($objParent->rootUseSSL ? 'https://' : 'http://') . ($objParent->domain ?: \Environment::get('host')) . TL_PATH . '/';
        $strUrl = $domain . $this->generateFrontendUrl($objParent->row(), ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/%s' : '/items/%s'), $objParent->language);
        $url = $this->getLink($viewDB, $strUrl);

        // cast item obj to array
        $item = $viewDB->row();

        // check permission
        if (HelperModel::sortOutProtected($item, $this->User->groups)) {
            return false;
        }

        // check scope
        if (!HelperModel::outSideScope($item['start'], $item['stop'])) {
            return false;
        }

        // parse cssID
        $cssID = deserialize($item['cssID']);
        $item['cssID'] = $cssID;

        // parse date
        $date = date('Y-m-d', $item['date']);
        $time = date('H:i', $item['time']);
        $dateTime = $time ? $date . ' ' . $time : $date;
        $item['dateTime'] = $dateTime;

        $item['date'] = $item['date'] ? date($objPage->dateFormat, $item['date']) : '';
        $item['time'] = $item['time'] ? date($objPage->timeFormat, $item['time']) : '';

        // parse href
        $item['href'] = $url;

        // parse details
        $objCte = ContentModelExtend::findPublishedByPidAndTable($item['id'], $tablename . '_data', array('fview' => 'detail'));
        $detailStr = '';
        if ($objCte !== null) {
            $intCount = 0;
            $intLast = $objCte->count() - 1;

            while ($objCte->next()) {
                $arrCss = array();
                $objRow = $objCte->current();

                if ($intCount == 0 || $intCount == $intLast) {
                    if ($intCount == 0) {
                        $arrCss[] = 'first';
                    }

                    if ($intCount == $intLast) {
                        $arrCss[] = 'last';
                    }
                }

                $objRow->classes = $arrCss;
                $detailStr .= $this->getContentElement($objRow, $this->strColumn);
                ++$intCount;
            }
        }
        $item['detail'] = $detailStr;

        // parse list
        $objCte = ContentModelExtend::findPublishedByPidAndTable($item['id'], $tablename . '_data', array('fview' => 'list'));
        $teaserStr = '';
        if ($objCte !== null) {
            $intCount = 0;
            $intLast = $objCte->count() - 1;

            while ($objCte->next()) {
                $arrCss = array();
                $objRow = $objCte->current();

                if ($intCount == 0 || $intCount == $intLast) {
                    if ($intCount == 0) {
                        $arrCss[] = 'first';
                    }

                    if ($intCount == $intLast) {
                        $arrCss[] = 'last';
                    }
                }

                $objRow->classes = $arrCss;
                $teaserStr .= $this->getContentElement($objRow, $this->strColumn);
                ++$intCount;
            }
        }
        $item['teaser'] = $teaserStr;

        // parse image
        $imagePath = $this->generateSingeSrc($viewDB);
        if ($imagePath) {
            $item['singleSRC'] = $imagePath;
        }

        // parse marker
        if ($item['addMarker'] && $item['markerSRC']) {
            $markerDB = $this->Database->prepare('SELECT * FROM tl_files WHERE uuid = ?')->execute($item['markerSRC']);
            if ($markerDB->count()) {
                $pathInfo = $markerDB->row()['path'];
                if ($pathInfo) {
                    $item['markerSRC'] = $pathInfo;
                }
            }
        }

        // parse more
        $item['more'] = $GLOBALS['TL_LANG']['MSC']['more'];

        return $item;
    }

    /**
     * @param $tablename
     * @return array
     */
    private function findMapAndSet($tablename)
    {
        // get map_field
        $moduleDB = $this->Database->prepare('SELECT tl_fmodules.id AS moduleID, tl_fmodules.*, tl_fmodules_filters.*  FROM tl_fmodules LEFT JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ? ORDER BY tl_fmodules_filters.sorting')->execute($tablename);
        $maps = array();

        while ($moduleDB->next()) {

            $module = $moduleDB->row();

            // map
            if ($moduleDB->type == 'map_field') {

                // set map settings
                $maps[] = HelperModel::setGoogleMap($module);

                // set loadMapScript to true
                $this->loadMapScript = true;

                // load map libraries
                if (!$GLOBALS['loadGoogleMapLibraries']) $GLOBALS['loadGoogleMapLibraries'] = $module['mapInfoBox'] ? true : false;
            }

        }

        return $maps;
    }

    /**
     * @param $row
     * @return bool|void
     */
    private function generateSingeSrc($row)
    {
        if ($row->singleSRC != '') {
            $objModel = \FilesModel::findByUuid($row->singleSRC);
            if ($objModel && is_file(TL_ROOT . '/' . $objModel->path)) {
                return $objModel->path;
            }
        }
        return null;
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