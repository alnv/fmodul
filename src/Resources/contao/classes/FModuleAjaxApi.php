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
 * Class AjaxApiFModule
 * @package FModule
 */
class FModuleAjaxApi extends \Frontend
{

    /**
     * @var
     */
    protected $tablename;

    /**
     * @var
     */
    protected $listViewLimit;

    /**
     * @var
     */
    protected $listViewOffset = 0;

    /**
     * @var
     */
    protected $perPage = 0;

    /**
     * @var array
     */
    protected $markerCache = array();
    
    /**
     * @var array
     */
    protected $doNotSetByID = array('orderBy', 'sorting_fields', 'pagination');

    /**
     * @var array
     */
    protected $doNotSetByType = array('legend_end', 'legend_start', 'wrapper_field', 'map_field');

    /**
     * return list view
     * filter allowed
     */
    public function getEntities()
    {

        $strTableName = \Input::get('tablename');
        $strTableData = $strTableName . '_data';
        $strWrapperID = \Input::get('wrapperID');
        $dateFormat = \Input::get('dateFormat') ? \Input::get('dateFormat') : \Config::get('dateFormat');
        $timeFormat = \Input::get('timeFormat') ? \Input::get('timeFormat') : \Config::get('timeFormat');
        $strTemplate = \Input::get('template') ? \Input::get('template') : 'fmodule_teaser';
        $this->tablename = $strTableData;
        $arrResults = [];

        $arrModuleData = $this->getModule($strTableName, $strWrapperID);
        $arrFields = $arrModuleData['arrFields'];
        $fieldWidgets = $arrModuleData['arrWidgets'];
        $mapFields = $arrModuleData['mapFields'];
        $arrCleanOptions = $arrModuleData['arrCleanOptions'];

        if(!$strTableName || !$strWrapperID) {
            $this->sendFailState("no back end module found");
        }

        if (!$this->Database->tableExists($strTableName)) {
            $this->sendFailState("no table found");
        }

        // get wrapper
        $wrapperDB = $this->Database->prepare('SELECT * FROM ' . $strTableName . ' WHERE id = ?')->execute($strWrapperID)->row();

        // get fields and create query
        $qResult = HelperModel::generateSQLQueryFromFilterArray($arrFields);
        $qStr = $qResult['qStr'];
        $qTextSearch = $qResult['isFulltextSearch'] ? $qResult['$qTextSearch'] : '';

        //get text search results
        $textSearchResults = array();
        if ($qTextSearch) {
            $textSearchResults = QueryModel::getTextSearchResult($qTextSearch, $strTableName, $strWrapperID, $qResult['searchSettings']);
        }

        $addDetailPage = $wrapperDB['addDetailPage'];
        $rootDB = $this->Database->prepare('SELECT * FROM ' . $strTableName . ' JOIN tl_page ON tl_page.id = ' . $strTableName . '.rootPage WHERE ' . $strTableName . '.id = ?')->execute($strWrapperID)->row();
        $qOrderByStr = $this->getOrderBy();
        $qProtectedStr = ' AND published = "1"';

        // get list
        $objList = $this->Database->prepare('SELECT * FROM ' . $strTableData . ' WHERE pid = ' . $strWrapperID . $qProtectedStr . $qStr . $qOrderByStr)->query();

        $arrItems = array();

        while ($objList->next()) {

            $arrItem = $objList->row();

            if (HelperModel::sortOutProtected($arrItem, $this->User->groups)) {
                continue;
            }

            if (!HelperModel::outSideScope($arrItem['start'], $arrItem['stop'])) {
                continue;
            }

            // image
            $imagePath = $this->generateSingeSrc($objList);

            if ($imagePath) {
                $arrItem['singleSRC'] = $imagePath;
            }

            if ($arrItem['size']) {
                $arrItem['size'] = deserialize($arrItem['size']);
            }

            if ($arrItem['cssID']) {
                $arrItem['cssID'] = deserialize($arrItem['cssID']);
            }

            if($arrItem['addGallery'] && $arrItem['multiSRC']) {
                $objGallery = new GalleryGenerator();
                $objGallery->id = $arrItem['id'];
                $objGallery->sortBy = $arrItem['sortBy'];
                $objGallery->orderSRC = $arrItem['orderSRC'];
                $objGallery->metaIgnore = $arrItem['metaIgnore'];
                $objGallery->numberOfItems = $arrItem['numberOfItems'];
                $objGallery->perPage = $arrItem['perPageGallery'];
                $objGallery->perRow = $arrItem['perRow'];
                $objGallery->size = $arrItem['size'];
                $objGallery->fullsize = $arrItem['fullsize'];
                $objGallery->galleryTpl = $arrItem['galleryTpl'];
                $objGallery->getAllImages($arrItem['multiSRC']);
                $arrItem['gallery'] = $objGallery->renderGallery();
            }

            // create href
            $arrItem['href'] = null;

            if ($addDetailPage == '1' && $objList->source == 'default') {
                // reset target
                $arrItem['target'] = '';
                $arrItem['href'] = $this->generateUrl($rootDB, $arrItem['alias']); // $listDB->alias
            }

            if ($arrItem['source'] == 'external') {
                $arrItem['href'] = $arrItem['url'];
            }

            if ($arrItem['source'] == 'internal') {
                // reset target
                $arrItem['target'] = '';

                $jumpToDB = $this->Database->prepare('SELECT * FROM tl_page WHERE id = ?')->execute($objList->jumpTo)->row();
                $strTaxonomyUrl = \Config::get('taxonomyDisable') ? '' : $this->generateTaxonomyUrl();
                $arrItem['href'] = $this->generateFrontendUrl($jumpToDB, $strTaxonomyUrl);
            }

            // check for text search
            if ($qTextSearch) {
                if (!$textSearchResults[$arrItem['id']]) {
                    continue;
                }
            }

            //
            $arrItems[] = $arrItem;
        }

        if( \Input::get('orderBy') && mb_strtoupper(\Input::get('orderBy'), 'UTF-8') == 'RAND') {
            shuffle($arrItems);
        }

        $total = count($arrItems);
        $this->listViewLimit = $total;
        $this->createPagination($total);
        $objTemplate = new \FrontendTemplate($strTemplate);
        $strResults = '';

        for ($i = $this->listViewOffset; $i < $this->listViewLimit; $i++) {

            $item = $arrItems[$i];

            // set css and id
            $item['cssID'] = deserialize($item['cssID']);
            $item['itemID'] = $item['cssID'][0];
            $item['itemCSS'] = $item['cssID'][1] ? ' ' . $item['cssID'][1] : '';

            // set date format
            $date = date('Y-m-d', $item['date']);
            $time = date('H:i', $item['time']);
            $dateTime = $time ? $date . ' ' . $time : $date;
            $item['dateTime'] = $dateTime;
            $item['date'] = $item['date'] ? date($dateFormat, $item['date']) : '';
            $item['time'] = $item['time'] ? date($timeFormat, $item['time']) : '';

            // set more
            $item['more'] = $GLOBALS['TL_LANG']['MSC']['more'];

            // get list view ce
            $objCte = ContentModelExtend::findPublishedByPidAndTable($item['id'], $strTableName . '_data', array('fview' => 'list'));
            $arrElements = array();
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
                    $arrElements[] = $this->getContentElement($objRow, $this->strColumn);
                    ++$intCount;
                }
            }
            $item['teaser'] = $arrElements;

            // set odd and even classes
            $item['cssClass'] = $i % 2 ? ' even' : ' odd';

            //field
            if (!empty($fieldWidgets)) {
                $arrayAsValue = array('list.blank', 'list.keyValue', 'table.blank');
                foreach ($fieldWidgets as $widget) {
                    $id = $widget['fieldID'];
                    $tplName = $widget['widgetTemplate'];
                    $type = $widget['widgetType'];
                    $value = $item[$id];
                    if (in_array($type, $arrayAsValue)) $value = deserialize($value); // unserialize
                    $objFieldTemplate = new \FrontendTemplate($tplName);
                    $objFieldTemplate->setData(array(
                        'value' => $value,
                        'type' => $type,
                        'item' => $item
                    ));
                    $item[$id] = $objFieldTemplate->parse();
                }
            }

            // set last first classes
            if ($i == 0) {
                $item['cssClass'] .= ' first';
            }
            if ($i == ($this->listViewLimit - 1)) {
                $item['cssClass'] .= ' last';
            }

            // create marker path
            if ($item['addMarker'] && $item['markerSRC']) {
                if ($this->markerCache[$item['markerSRC']]) {
                    $item['markerSRC'] = $this->markerCache[$item['markerSRC']];
                } else {
                    $markerDB = $this->Database->prepare('SELECT * FROM tl_files WHERE uuid = ?')->execute($item['markerSRC']);
                    if ($markerDB->count()) {
                        $pathInfo = $markerDB->row()['path'];
                        if ($pathInfo) {
                            $this->markerCache[$item['markerSRC']] = $pathInfo;
                            $item['markerSRC'] = $pathInfo;
                        }
                    }
                }
            }

            // map settings from field
            if (!empty($mapFields)) {
                foreach ($mapFields as $map) {
                    $objMapTemplate = new \FrontendTemplate($map['template']);
                    $item['mapSettings'] = $map;
                    $objMapTemplate->setData($item);
                    $item[$map['fieldID']] = $objMapTemplate->parse();
                }
            }

            // mapSettings
            if (!empty($mapSettings)) {
                $item['mapSettings'] = $mapSettings;
            }

            // set clean options
            if (!empty($arrCleanOptions)) {

                $item['cleanOptions'] = $arrCleanOptions;

                // overwrite clean options
                foreach ($arrCleanOptions as $fieldID => $options) {
                    if ($item[$fieldID] && is_string($item[$fieldID])) {
                        $arrValues = explode(',', $item[$fieldID]);
                        $arrValuesAsString = array();
                        $arrValuesAsArray = array();
                        if (is_array($arrValues)) {
                            foreach ($arrValues as $val) {
                                $arrValuesAsArray[$val] = $options[$val];
                                $arrValuesAsString[] = $options[$val];
                            }
                        }
                        $item[$fieldID . 'AsArray'] = $arrValuesAsArray;
                        $item[$fieldID] = implode(', ', $arrValuesAsString);
                    }
                }
            }

            //set data
            $objTemplate->setData($item);

            //set image
            if ($item['addImage']) {
                $this->addImageToTemplate($objTemplate, array(
                    'singleSRC' => $item['singleSRC'],
                    'alt' => $item['alt'],
                    'size' => $item['size'],
                    'fullsize' => $item['fullsize'],
                    'caption' => $item['caption'],
                    'title' => $item['title']
                ));
            }

            // set enclosure
            $objTemplate->enclosure = array();
            if ($item['addEnclosure']) {
                $this->addEnclosuresToTemplate($objTemplate, $item);
            }

            $arrResults[] = $item;

            $strResults .= $objTemplate->parse();
        }

        $arrData = array('arrData' => $arrResults, 'strTemplate' => $strResults, 'arrFields' => $arrModuleData, 'arrWrapper' => $wrapperDB, 'arrLabels' => array('noResults' => $GLOBALS['TL_LANG']['MSC']['noResult']));
        header('Content-type: application/json');
        echo json_encode($arrData, 512);
        exit;
    }

    /**
     * return detail view
     */
    public function getDetail()
    {

        $strTableName = \Input::get('tablename');
        $strWrapperID = \Input::get('wrapperID');
        $strDataTable = $strTableName . '_data';
        $dateFormat = \Input::get('dateFormat') ? \Input::get('dateFormat') : \Config::get('dateFormat');
        $timeFormat = \Input::get('timeFormat') ? \Input::get('timeFormat') : \Config::get('timeFormat');
        $template = \Input::get('template') ? \Input::get('template') : 'fmodule_full';
        $alias = \Input::get('alias');
        $id = \Input::get('id');

        if (!$strTableName || !$strWrapperID) {
            $this->sendFailState("no back end module found");
        }

        if (!$this->Database->tableExists($strTableName)) {
            $this->sendFailState("table do not exist");
        }


        $arrModuleData = $this->getModule($strTableName, $strWrapperID);
        $arrFields = $arrModuleData['arrFields'];
        $fieldWidgets = $arrModuleData['arrWidgets'];
        $mapFields = $arrModuleData['mapFields'];
        $arrCleanOptions = $arrModuleData['arrCleanOptions'];

        $strResult = '';
        $objTemplate = new \FrontendTemplate($template);
        $qProtectedStr = ' AND published = "1"';
        $arrItem = $this->Database->prepare('SELECT * FROM ' . $strDataTable . ' WHERE pid = ? AND (alias = ? OR id = ?) OR (alias = ? OR id = ?)' . $qProtectedStr . '')->execute($strWrapperID, $alias, (int)$alias, $id, (int)$id)->row();
        $arrWrapper = $this->Database->prepare('SELECT * FROM ' . $strTableName . ' WHERE id = ?')->execute($strWrapperID)->row();

        // image
        $imagePath = $this->generateSingeSrc($arrItem);
        if ($imagePath) {
            $arrItem['singleSRC'] = $imagePath;
        }

        //set css and id
        $arrItem['cssID'] = deserialize($arrItem['cssID']);
        $arrItem['itemID'] = $arrItem['cssID'][0];
        $arrItem['itemCSS'] = $arrItem['cssID'][1] ? ' ' . $arrItem['cssID'][1] : '';
        $arrItem['cssClass'] = '';

        $objCte = \ContentModel::findPublishedByPidAndTable($arrItem['id'], $strTableName . '_data');

        $detail = array();
        $teaser = array();

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

                if ($objRow->fview == 'list') {

                    $teaser[] = $this->getContentElement($objRow, $this->strColumn);

                } else {

                    $detail[] = $this->getContentElement($objRow, $this->strColumn);
                }

                ++$intCount;
            }
        }

        // author
        $authorDB = null;
        if ($arrItem['author']) {
            $authorDB = $this->Database->prepare('SELECT * FROM tl_user WHERE id = ?')->execute($arrItem['author'])->row();
            unset($authorDB['password']);
            unset($authorDB['session']);
        }

        $arrItem['teaser'] = $teaser;
        $arrItem['detail'] = $detail;
        $arrItem['author'] = $authorDB;
        $arrItem['date'] = $arrItem['date'] ? date($dateFormat, $arrItem['date']) : '';
        $arrItem['time'] = $arrItem['time'] ? date($timeFormat, $arrItem['time']) : '';
        $arrItem['filter'] = $arrFields;

        if (!empty($fieldWidgets)) {

            $arrayAsValue = array('list.blank', 'list.keyValue', 'table.blank');

            foreach ($fieldWidgets as $widget) {
                $id = $widget['fieldID'];
                $tplName = $widget['widgetTemplate'];
                $type = $widget['widgetType'];
                $value = $arrItem[$id];
                if (in_array($type, $arrayAsValue)) $value = deserialize($value);
                $objFieldTemplate = new \FrontendTemplate($tplName);
                $objFieldTemplate->setData(array(
                    'value' => $value,
                    'type' => $type,
                    'item' => $arrItem
                ));
                $arrItem[$id] = $objFieldTemplate->parse();
            }
        }

        // create marker path
        if ($arrItem['addMarker'] && $arrItem['markerSRC']) {
            if ($this->markerCache[$arrItem['markerSRC']]) {
                $arrItem['markerSRC'] = $this->markerCache[$arrItem['markerSRC']];
            } else {
                $markerDB = $this->Database->prepare('SELECT * FROM tl_files WHERE uuid = ?')->execute($arrItem['markerSRC']);
                if ($markerDB->count()) {
                    $pathInfo = $markerDB->row()['path'];
                    if ($pathInfo) {
                        $this->markerCache[$arrItem['markerSRC']] = $pathInfo;
                        $arrItem['markerSRC'] = $pathInfo;
                    }
                }
            }
        }

        // add gallery
        if($arrItem['addGallery'] && $arrItem['multiSRC']) {
            $objGallery = new GalleryGenerator();
            $objGallery->id = $arrItem['id'];
            $objGallery->sortBy = $arrItem['sortBy'];
            $objGallery->orderSRC = $arrItem['orderSRC'];
            $objGallery->metaIgnore = $arrItem['metaIgnore'];
            $objGallery->numberOfItems = $arrItem['numberOfItems'];
            $objGallery->perPage = $arrItem['perPageGallery'];
            $objGallery->perRow = $arrItem['perRow'];
            $objGallery->size = $arrItem['size'];
            $objGallery->fullsize = $arrItem['fullsize'];
            $objGallery->galleryTpl = $arrItem['galleryTpl'];
            $objGallery->getAllImages($arrItem['multiSRC']);
            $arrItem['gallery'] = $objGallery->renderGallery();
        }

        // map
        if (!empty($mapFields)) {
            foreach ($mapFields as $map) {
                $objMapTemplate = new \FrontendTemplate($map['template']);
                $arrItem['mapSettings'] = $map;
                $objMapTemplate->setData($arrItem);
                $arrItem[$map['fieldID']] = $objMapTemplate->parse();
            }
        }

        // set clean options
        if (!empty($arrCleanOptions)) {
            $arrItem['cleanOptions'] = $arrCleanOptions;
            // overwrite clean options
            foreach ($arrCleanOptions as $fieldID => $options) {
                if ($arrItem[$fieldID] && is_string($arrItem[$fieldID])) {
                    $arrValues = explode(',', $arrItem[$fieldID]);
                    $arrValuesAsString = array();
                    $arrValuesAsArray = array();
                    if (is_array($arrValues)) {
                        foreach ($arrValues as $val) {
                            $arrValuesAsArray[$val] = $options[$val];
                            $arrValuesAsString[] = $options[$val];
                        }
                    }
                    $arrItem[$fieldID . 'AsArray'] = $arrValuesAsArray;
                    $arrItem[$fieldID] = implode(', ', $arrValuesAsString);
                }
            }
        }

        // set data
        $objTemplate->setData($arrItem);

        // enclosure
        $objTemplate->enclosure = array();

        if ($arrItem['addEnclosure']) {
            $this->addEnclosuresToTemplate($objTemplate, $arrItem);
        }

        // add image
        if ($arrItem['addImage']) {
            $this->addImageToTemplate($objTemplate, array(
                'singleSRC' => $arrItem['singleSRC'],
                'alt' => $arrItem['alt'],
                'size' => $arrItem['size'],
                'fullsize' => $arrItem['fullsize'],
                'caption' => $arrItem['caption'],
                'title' => $arrItem['title']
            ));
        }

        $strResult .= $objTemplate->parse();
        $arrData = [ 'arrData' =>  $arrItem, 'mapFields' => $mapFields, 'arrWrapper' => $arrWrapper, 'strTemplate' => $strResult, 'arrGoBack' => [
            'referer' => 'javascript:history.go(-1)',
            'back' =>  $GLOBALS['TL_LANG']['MSC']['goBack']
        ]];

        header('Content-type: application/json');
        echo json_encode($arrData, 512);
        exit;

    }

    /**
     * @return array
     */
    public function getAutoCompletion()
    {

        //options
        $tablename = \Input::get('tablename');
        $fieldID = \Input::get('fieldID');
        $wrapperID = \Input::get('wrapperID');

        $dateFormat = \Input::get('dateFormat') ? \Input::get('dateFormat') : \Config::get('dateFormat');
        $timeFormat = \Input::get('timeFormat') ? \Input::get('timeFormat') : \Config::get('timeFormat');
	
		$autoCompletion = new AutoCompletion();
		$results = $autoCompletion->getAutoCompletion($tablename, $wrapperID, $fieldID, $dateFormat, $timeFormat);
		
		if(is_string($results))
		{
			$this->sendFailState($results);
		}
		
		header('Content-type: application/json');
        echo json_encode($results, 512);
        exit;
		
    }

    /**
     *
     */
    public function getDefault()
    {
        $this->sendFailState('No method found');
    }

    /**
     * @param $imgSize
     * @return mixed
     */
    protected function setImageSize($imgSize)
    {
        if ($imgSize) {
            $size = deserialize($imgSize);

            if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                return $size;
            }
        }

        return $imgSize;
    }

    /**
     * @param $strTableName
     * @param string $strWrapperID
     * @return array
     */
    protected function getModule($strTableName, $strWrapperID = '')
    {

        $objModule = $this->Database->prepare('SELECT tl_fmodules.id AS moduleID, tl_fmodules.*, tl_fmodules_filters.*  FROM tl_fmodules LEFT JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ? ORDER BY tl_fmodules_filters.sorting')->execute($strTableName);
        $arrFields = array();
        $widgetsArr = array();
        $mapFields = array();
        $arrCleanOptions = array();

        while ($objModule->next()) {

            $arrModule = $objModule->row();

            if (in_array($arrModule['fieldID'], $this->doNotSetByID) || in_array($arrModule['type'], $this->doNotSetByType)) {
                continue;
            }

            $getFilter = $this->getFilter($arrModule['fieldID'], $arrModule['type']);
            $arrModule['value'] = $getFilter['value'];
            $arrModule['operator'] = $getFilter['operator'];
            $arrModule['overwrite'] = null;
            $arrModule['active'] = null;

            $val = QueryModel::isValue($arrModule['value'], $arrModule['type']);
            if ($val) $arrModule['enable'] = true;

            // check if has an wrapper
            if (($arrModule['type'] === 'search_field' && $arrModule['isInteger']) || $arrModule['type'] === 'date_field') {
                $btw = \Input::get($arrModule['fieldID'] . '_btw') ? \Input::get($arrModule['fieldID'] . '_btw') : '';
                $btwHasValue = QueryModel::isValue($btw, $arrModule['type']);
                if ($btwHasValue && !$val) {
                    $arrModule['enable'] = true;
                    $arrModule['value'] = 0;
                }
            }

            // map
            if ($arrModule['type'] == 'map_field') {

                // set map settings
                $mapFields[] = HelperModel::setGoogleMap($arrModule);

                // set loadMapScript to true
                $this->loadMapScript = true;

                // load map libraries
                if (!$GLOBALS['loadGoogleMapLibraries']) $GLOBALS['loadGoogleMapLibraries'] = $arrModule['mapInfoBox'] ? true : false;
            }

            // field
            if ($arrModule['type'] == 'widget') {
                $tplName = $arrModule['widgetTemplate'];
                $tpl = '';
                if (!$tplName) {
                    $tplNameType = explode('.', $arrModule['widget_type'])[0];
                    $tplNameArr = $this->getTemplateGroup('fm_field_' . $tplNameType);
                    $tpl = current($tplNameArr);
                    $tpl = $this->parseTemplateName($tpl);
                }
                $fieldWidgets[$arrModule['fieldID']] = array(
                    'fieldID' => $arrModule['fieldID'],
                    'widgetType' => $arrModule['widget_type'],
                    'widgetTemplate' => $arrModule['widgetTemplate'] ? $arrModule['widgetTemplate'] : $tpl
                );
            }

            // has options
            if ($arrModule['type'] == 'simple_choice' || $arrModule['type'] == 'multi_choice') {
                $dcaHelper = new DCAHelper();
                $arrCleanOptions[$arrModule['fieldID']] = $dcaHelper->getOptions($arrModule, $strTableName, $strWrapperID);
            }

            $arrFields[$arrModule['fieldID']] = $arrModule;

        }

        return array('arrFields' => $arrFields, 'arrWidgets' => $widgetsArr, 'mapFields' => $mapFields, 'arrCleanOptions' => $arrCleanOptions);
    }

    /**
     * @param $fieldID
     * @param $type
     * @return array
     */
    protected function getFilter($fieldID, $type)
    {
        $getFilter = \Input::get($fieldID) ? \Input::get($fieldID) : '';
        $getOperator = \Input::get($fieldID . '_int') ? \Input::get($fieldID . '_int') : '';

        if ($type == 'multi_choice' && !is_array($getFilter)) {
            $getFilter = explode(',', $getFilter);
        }

        if ($type == 'toggle_field' && is_null(\Input::get($fieldID)) == false && \Input::get($fieldID) != '1') {
            $getFilter = 'skip';
        }

        return array(
            'value' => $getFilter,
            'operator' => $getOperator
        );


    }

    /**
     * @param $templateName
     * @return mixed
     */
    public function parseTemplateName($templateName)
    {
        return DiverseFunction::parseTemplateName($templateName);
    }

    /**
     * @param int $total
     */
    protected function createPagination($total = 0)
    {
        // options
        // perPage
        // page
        if (\Input::get('perPage')) {
            $perPage = (int)\Input::get('perPage');
            $this->perPage = $perPage;
        }

        if ($this->perPage > 0) {

            $page = (\Input::get('page') !== null) ? (int)\Input::get('page') : 1;

            if ($page < 1 || $page > max(ceil($total / $this->perPage), 1)) {

                $this->sendFailState('Page not found', '404');
            }

            $this->listViewOffset = ($page - 1) * $this->perPage;
            $this->listViewLimit = min($this->perPage + $this->listViewOffset, $total);

        }

    }

    /**
     * @return string
     */
    protected function getOrderBy()
    {

        $orderBYOptions = array('asc', 'desc');
        $orderBY = \Input::get('orderBy');
        $orderBYStr = 'desc';

        if ($orderBY && in_array(mb_strtoupper($orderBY, 'UTF-8'), $orderBYOptions)) {
            $orderBYStr = mb_strtoupper($orderBY, 'UTF-8');
        }

        $sortingField = \Input::get('sorting_fields');
        $sortingFields = array();

        if ($sortingField && is_string($sortingField)) {
            $sortingFields[] = $sortingField;
        }

        if ($sortingField && is_array($sortingField)) {
            $sortingFields = $sortingField;
        }

        $arrTemp = [];

        foreach ($sortingFields as $arrField) {
            if ($this->Database->fieldExists($arrField, $this->tablename)) {
                $arrTemp[] = $arrField;
            }
        }

        $sorting = array_filter($arrTemp);

        if (empty($sorting)) {
            $sorting[] = 'id';
        }

        $sorting = implode(',', $sorting);

        return ' ORDER BY ' . $sorting . ' ' . $orderBYStr;

    }

    /**
     * @param $message
     */
    protected function sendFailState($message, $code = '500')
    {
        header('HTTP/1.1 500 Internal Server');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('message' => $message, 'code' => $code), 512);
        exit;
    }

    /**
     * @param $row
     * @return bool|void
     */
    protected function generateSingeSrc($row)
    {

        if (is_array($row)) {
            $singleSrc = $row['singleSRC'];
        } else {
            $singleSrc = $row->singleSRC;
        }

        if ($singleSrc != '') {

            $objModel = \FilesModel::findByUuid($singleSrc);

            if ($objModel && is_file(TL_ROOT . '/' . $objModel->path)) {

                return $objModel->path;

            }
        }

        return null;

    }

    /**
     * @param $objTarget
     * @param $alias
     * @return string
     */
    protected function generateUrl($objTarget, $alias)
    {
        return $this->generateFrontendUrl($objTarget, '/' . $alias);
    }

}