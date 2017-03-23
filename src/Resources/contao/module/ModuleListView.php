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


use Contao\Config;
use Contao\Environment;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Module;
use Contao\Pagination;


class ModuleListView extends Module
{

    protected $strTemplate = 'mod_fmodule_list';
    public $tablename;
    public $listViewLimit;
    public $listViewOffset = 0;
    protected $markerCache = array();
    protected $loadMapScript = false;
    protected $feViewID = null;
    protected $strAutoItem = '';
    protected $strTaxonomy = '';
    protected $strSpecie = '';
    protected $strTag = array();
    protected $blnLocatorInvoke = false;
    protected $strOrderBy = '';


    public function generate() {


        if (TL_MODE == 'BE') {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . $this->name . ' ###';
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            return $objTemplate->parse();
        }

        $this->import('FrontendUser', 'User');

        $this->feViewID = md5($this->id);

        if (TL_MODE == 'FE' && $this->fm_addMap) {

            $this->strTemplate = 'mod_fmodule_map';
        }

        if (!isset($_GET['item']) && Config::get('useAutoItem') && isset($_GET['auto_item'])) {

            Input::setGet('item', Input::get('auto_item'));
        }

        return parent::generate();
    }


    protected function compile() {

        global $objPage;
        
        $f_display_mode = deserialize($this->f_display_mode);
        $page_taxonomy = deserialize($objPage->page_taxonomy);
        $this->fm_orderBy = deserialize( $this->fm_orderBy );
        $taxonomyFromFE = is_array($f_display_mode) ? $f_display_mode : array();
        $taxonomyFromPage = is_array($page_taxonomy) ? $page_taxonomy : array();
        $tablename = $this->f_select_module;
        $wrapperID = $this->f_select_wrapper;
        $doNotSetByID = array('orderBy', 'sorting_fields', 'pagination');
        $doNotSetByType = array('legend_end', 'legend_start', 'wrapper_field');
        $moduleDB = $this->Database->prepare('SELECT tl_fmodules.id AS moduleID, tl_fmodules.*, tl_fmodules_filters.*  FROM tl_fmodules LEFT JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ? ORDER BY tl_fmodules_filters.sorting')->execute($tablename);
        $arrFields = array();
        $fieldWidgets = array();
        $this->tablename = $tablename;
        $mapFields = array();
        $geoLocatorValues = array();
        $arrCleanOptions = array();

        $mapSettings = array();

        if ($this->fm_addMap) {

            $mapSettings['mapZoom'] = $this->fm_mapZoom;
            $mapSettings['mapMarker'] = $this->fm_mapMarker;
            $mapSettings['mapInfoBox'] = $this->fm_mapInfoBox;
            $mapSettings['mapType'] = $this->fm_mapType;
            $mapSettings['mapStyle'] = $this->fm_mapStyle;
            $mapSettings['mapScrollWheel'] = $this->fm_mapScrollWheel ? 'true' : 'false';
            $mapSettings['lat'] = $this->fm_center_lat ? $this->fm_center_lat : '0';
            $mapSettings['lng'] = $this->fm_center_lng ? $this->fm_center_lng : '0';
        }

        $wrapperDB = $this->Database->prepare('SELECT addDetailPage, title, id, rootPage FROM ' . $tablename . ' WHERE id = ?')->execute($wrapperID)->row();
        $arrDetailSettings = [];

        if ( $this->fm_detailView ) {

            $objModule = $this->Database->prepare( 'SELECT * FROM tl_module WHERE id = ? AND type = ?' )->limit(1)->execute( $this->fm_detailView, 'fmodule_fe_detail' );

            if ( $objModule->numRows ) {

                $arrDetailSettings = $objModule->row();
            }
        }

        $blnDetailView = false;

        if (\Input::get('auto_item')) {

            $taxonomyItemDB = $this->Database->prepare('SELECT * FROM ' . $tablename . '_data WHERE published = "1" AND pid = ? AND (alias = ? OR id = ?)')->limit(1)->execute($wrapperID, \Input::get('auto_item'), (int)\Input::get('auto_item'));

            if ($taxonomyItemDB->count()) {

                $blnDetailView = true;
            }
        }

        $this->strAutoItem = !$blnDetailView ? '' : \Input::get('auto_item');
        $this->strSpecie = !$blnDetailView ? \Input::get('auto_item') : \Input::get('specie');
        $this->strTag = !$blnDetailView ? \Input::get('specie') : \Input::get('tags');

        while ($moduleDB->next()) {

            $arrModule = $moduleDB->row();

            if (in_array($arrModule['fieldID'], $doNotSetByID) || in_array($arrModule['type'], $doNotSetByType)) {

                continue;
            }

            $getFilter = $this->getFilter($arrModule['fieldID'], $arrModule['type']);
            $arrModule['value'] = $getFilter['value'];
            $arrModule['operator'] = $getFilter['operator'];
            $arrModule['overwrite'] = null;
            $arrModule['active'] = null;

            if ($arrModule['fieldID'] == 'auto_page' || $arrModule['autoPage']) {

                $arrModule = $this->setValuesForAutoPageAttribute($arrModule);
            }

            if ($arrModule['dataFromTaxonomy'] == '1' && !\Config::get('taxonomyDisable')) {

                $arrModule['type'] = 'taxonomy_field';
                $arrModule = $this->setValuesForTaxonomySpecieAttribute($arrModule);
            }
            if ($arrModule['reactToTaxonomy'] == '1' && !\Config::get('taxonomyDisable')) {

                $arrModule['type'] = 'taxonomy_field';
                $arrModule = $this->setValuesForTaxonomyTagsAttribute($arrModule);
            }

            $val = QueryModel::isValue($arrModule['value'], $arrModule['type']);
            if ($val) $arrModule['enable'] = true;

            if (($arrModule['type'] === 'search_field' && $arrModule['isInteger']) || $arrModule['type'] === 'date_field') {

                $btw = Input::get($arrModule['fieldID'] . '_btw') ? Input::get($arrModule['fieldID'] . '_btw') : '';
                $btwHasValue = QueryModel::isValue($btw, $arrModule['type']);

                if ($btwHasValue && !$val) {

                    $arrModule['enable'] = true;
                    $arrModule['value'] = 0;
                }
            }

            if ($arrModule['type'] == 'map_field') {

                $mapFields[] = HelperModel::setGoogleMap($arrModule);

                $this->loadMapScript = true;

                if (!$GLOBALS['loadGoogleMapLibraries']) $GLOBALS['loadGoogleMapLibraries'] = $arrModule['mapInfoBox'] ? true : false;
            }

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

            if ($arrModule['type'] == 'simple_choice' || $arrModule['type'] == 'multi_choice' || $arrModule['type'] == 'taxonomy_field' ) {

                $dcaHelper = new DCAHelper();
                $arrCleanOptions[$arrModule['fieldID']] = $dcaHelper->getOptions($arrModule, $tablename, $wrapperID);
            }

            if ($arrModule['type'] == 'geo_locator') {

                if ($arrModule['value']) $geoLocatorValues[$arrModule['locatorType']] = $arrModule['value'];

                if ($arrModule['locatorType'] == 'geo_distance') {

                    $geoLocatorValues['geoDistanceDelimiter'] = $arrModule['geoDistanceDelimiter'] ? $arrModule['geoDistanceDelimiter'] : ',';
                }

                continue;
            }

            $arrFields[$arrModule['fieldID']] = $arrModule;
        }

        if (!empty($taxonomyFromFE) || !empty($taxonomyFromPage)) {

            $arrFields = $this->setFilterValues($taxonomyFromFE, $taxonomyFromPage, $arrFields);
        }

        $arrLongLatCords = array();
        $strDistanceField = "";
        $strHavingQuery = "";

        if ($geoLocatorValues['geo_street'] || $geoLocatorValues['geo_zip'] || $geoLocatorValues['geo_city']) {

            $this->blnLocatorInvoke = true;

            $strFECountry = $this->fm_geoLocatorCountry ? $this->fm_geoLocatorCountry : '';
            $strCountry = $geoLocatorValues['geo_country'] ? $geoLocatorValues['geo_country'] : $strFECountry;

            $strGeoAddress = sprintf('%s %s %s %s, %s',
                ($geoLocatorValues['geo_street'] ? $geoLocatorValues['geo_street'] : ''),
                ($geoLocatorValues['geo_zip'] ? $geoLocatorValues['geo_zip'] : ''),
                ($geoLocatorValues['geo_city'] ? $geoLocatorValues['geo_city'] : ''),
                ($geoLocatorValues['geo_state'] ? $geoLocatorValues['geo_state'] : ''),
                $strCountry
            );

            $strGeoAddress = trim($strGeoAddress);

            if (!empty($strGeoAddress)) {

                $objGeoCords = GeoCoding::getInstance();
                $arrLongLatCords = $objGeoCords->getGeoCords($strGeoAddress, $strCountry);
            }

            if ($arrLongLatCords['lat'] == '0' && $arrLongLatCords['lng'] == '0') {

                $arrLongLatCords = array();
            }

            if (!empty($arrLongLatCords)) {

                $strDistance = $geoLocatorValues['geo_distance'] ? $geoLocatorValues['geo_distance'] : '';

                if(\Input::get('_distance')) {

                    $strDistance = \Input::get('_distance');
                }

                if($this->fm_adaptiveZoomFactor && !empty($mapSettings)) {

                    $intDistance = (int) $strDistance;

                    $strZoom = '12';

                    if($intDistance >= 25 && $intDistance <= 50) {
                        $strZoom = '10';
                    }

                    if($intDistance > 50 && $intDistance <= 100) {
                        $strZoom = '8';
                    }

                    if($intDistance > 100) {
                        $strZoom = '7';
                    }

                    $mapSettings['mapZoom'] = $strDistance ? $strZoom : $mapSettings['mapZoom'];
                    $mapSettings['lat'] = (string)$arrLongLatCords['lat'];
                    $mapSettings['lng'] = (string)$arrLongLatCords['lng'];
                }

                $strDistanceField = "3956 * 1.6 * 2 * ASIN(SQRT( POWER(SIN((" . $arrLongLatCords['lat'] . "-abs(geo_latitude)) * pi()/180 / 2),2) + COS(" . $arrLongLatCords['lat'] . " * pi()/180 ) * COS( abs(geo_latitude) *  pi()/180) * POWER(SIN((" . $arrLongLatCords['lng'] . "-geo_longitude) *  pi()/180 / 2), 2) )) AS _distance";
                $strHavingQuery = $strDistance ? " HAVING _distance < " . $strDistance . "" : "";
            }
        }

        $selectedFields = $strDistanceField ? '*, ' . $strDistanceField : '*';
        $qResult = HelperModel::generateSQLQueryFromFilterArray($arrFields);
        $qStr = $qResult['qStr'];
        $qTextSearch = $qResult['isFulltextSearch'] ? $qResult['$qTextSearch'] : '';
        $textSearchResults = array();

        if ($qTextSearch) {

            $textSearchResults = QueryModel::getTextSearchResult($qTextSearch, $tablename, $wrapperID, $qResult['searchSettings']);
        }

        $arrRootPage = [];
        $qOrderByStr = $this->getOrderBy();

        if ( $wrapperDB['addDetailPage'] ) {

            $strPageID = $wrapperDB['rootPage'];

            if ( !empty( $arrDetailSettings ) && is_array( $arrDetailSettings ) ) {

                if ( $arrDetailSettings['fm_addMasterPage'] && $arrDetailSettings['fm_masterPage'] ) {

                    $strPageID = $arrDetailSettings['fm_masterPage'];
                }
            }

            $arrRootPage = \PageModel::findWithDetails( $strPageID )->row();

            if ( !is_array( $arrRootPage ) ) {

                $arrRootPage = [];
            }
        }

        $strPidWhereStatement = 'pid = ' . $wrapperID;
        $qProtectedStr = ' AND published = "1"';

        if ( $this->fm_addMap && $this->fm_showAllItems ) {

            $strPidWhereStatement = '';
            $qProtectedStr = 'published = "1"';
        }

        if (HelperModel::previewMode()) $qProtectedStr = '';

        $listDB = $this->Database->prepare('SELECT ' . $selectedFields . ' FROM ' . $tablename . '_data WHERE ' . $strPidWhereStatement . $qProtectedStr . $qStr . $strHavingQuery . $qOrderByStr)->query();
        $imgSize = false;

        if ($this->imgSize != '') {

            $size = deserialize($this->imgSize);

            if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {

                $imgSize = $this->imgSize;
            }
        }

        $arrItems = array();

        while ($listDB->next()) {

            $arrItem = $listDB->row();

            if (HelperModel::sortOutProtected($arrItem, $this->User->groups)) {
                continue;
            }

            if (!HelperModel::outSideScope($arrItem['start'], $arrItem['stop'])) {
                continue;
            }

            $imagePath = $this->generateSingeSrc($listDB);

            if ($imagePath) {
                $arrItem['singleSRC'] = $imagePath;
            }

            if ($imgSize) {
                $arrItem['size'] = $imgSize;
            }

            $arrItem['href'] = null;

            if ($wrapperDB['addDetailPage'] && $listDB->source == 'default') {

                $arrItem['target'] = '';
                $arrItem['href'] = $this->generateUrl( $arrRootPage, $arrItem['alias'] );
            }

            if ($arrItem['source'] == 'external') {
                $arrItem['href'] = $arrItem['url'];
            }

            if ($arrItem['source'] == 'internal') {

                $arrItem['target'] = '';
                $jumpToDB = $this->Database->prepare('SELECT * FROM tl_page WHERE id = ?')->execute($listDB->jumpTo)->row();
                $strTaxonomyUrl = \Config::get('taxonomyDisable') ? '' : $this->generateTaxonomyUrl();
                $arrItem['href'] = $this->generateFrontendUrl($jumpToDB, $strTaxonomyUrl);
            }

            if ($qTextSearch) {

                if (!$textSearchResults[$arrItem['id']]) {

                    continue;
                }
            }

            $arrItems[] = $arrItem;
        }


        if ( $this->fm_randomSorting || ( \Input::get('orderBy') && in_array( \Input::get('orderBy'), [ 'rand', 'RAND' ] ) ) ) {

            shuffle( $arrItems );
        }

        $total = count($arrItems);
        $strPagination = $this->createPagination($total);
        $this->Template->pagination = $strPagination;
        $strResults = '';
        $template = $this->fm_addMap ? $this->fm_map_template : $this->f_list_template;
        $objTemplate = new FrontendTemplate($template);

        for ($i = $this->listViewOffset; $i < $this->listViewLimit; $i++) {

            $item = $arrItems[$i];

            if ( $this->fm_addMap ) {

                $item['geo_latitude'] = $item['geo_latitude'] ? $item['geo_latitude'] : '0';
                $item['geo_longitude'] = $item['geo_longitude'] ? $item['geo_longitude'] : '0';
                $item['title'] = mb_convert_encoding($item['title'], 'UTF-8');
                $item['description'] = mb_convert_encoding($item['description'], 'UTF-8');
                $item['info'] = mb_convert_encoding($item['info'], 'UTF-8');
            }


            if ( $item['addGallery'] && $item['multiSRC'] && !$this->fm_disableGallery ) {

                $objGallery = new GalleryGenerator();
                $objGallery->id = $item['id'];
                $objGallery->sortBy = $item['sortBy'];
                $objGallery->orderSRC = $item['orderSRC'];
                $objGallery->metaIgnore = $item['metaIgnore'];
                $objGallery->numberOfItems = $item['numberOfItems'];
                $objGallery->perPage = $item['perPageGallery'];
                $objGallery->perRow = $item['perRow'];
                $objGallery->size = $item['size'];
                $objGallery->fullsize = $item['fullsize'];
                $objGallery->galleryTpl = $item['galleryTpl'];
                $objGallery->getAllImages($item['multiSRC']);
                $item['gallery'] = $objGallery->renderGallery();
            }

            $item['cssID'] = deserialize($item['cssID']);
            $item['itemID'] = $item['cssID'][0];
            $item['itemCSS'] = $item['cssID'][1] ? ' ' . $item['cssID'][1] : '';

            $date = date('Y-m-d', $item['date']);
            $time = date('H:i', $item['time']);
            $dateTime = $time ? $date . ' ' . $time : $date;
            $item['dateTime'] = $dateTime;
            $item['date'] = $item['date'] ? date($objPage->dateFormat, $item['date']) : '';
            $item['time'] = $item['time'] ? date($objPage->timeFormat, $item['time']) : '';

            if($item['_distance']) {

                $item['_distance'] = number_format($item['_distance'], 2, $geoLocatorValues['geoDistanceDelimiter'], $geoLocatorValues['geoDistanceDelimiter']);
                $item['_distanceLabel'] = $GLOBALS['TL_LANG']['MSC']['fm_distance'];
            }

            $item['more'] = $GLOBALS['TL_LANG']['MSC']['more'];

            $objCte = ContentModelExtend::findPublishedByPidAndTable($item['id'], $tablename . '_data', array('fview' => 'list'));
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
            $item['cssClass'] = $i % 2 ? ' even' : ' odd';

            if (!empty($fieldWidgets)) {

                $arrayAsValue = array('list.blank', 'list.keyValue', 'table.blank');

                foreach ($fieldWidgets as $widget) {

                    $id = $widget['fieldID'];
                    $tplName = $widget['widgetTemplate'];
                    $type = $widget['widgetType'];
                    $value = $item[$id];

                    if (in_array($type, $arrayAsValue)) $value = deserialize($value);

                    $objFieldTemplate = new FrontendTemplate($tplName);
                    $objFieldTemplate->setData(array(

                        'value' => $value,
                        'type' => $type,
                        'item' => $item
                    ));

                    $item[$id] = $objFieldTemplate->parse();
                }
            }

            if ($i == 0) {

                $item['cssClass'] .= ' first';
            }

            if ($i == ($this->listViewLimit - 1)) {

                $item['cssClass'] .= ' last';
            }

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

            if (!empty($mapFields)) {

                foreach ($mapFields as $map) {

                    $objMapTemplate = new FrontendTemplate($map['template']);
                    $item['mapSettings'] = $map;
                    $objMapTemplate->setData($item);
                    $item[$map['fieldID']] = $objMapTemplate->parse();
                }
            }

            if (!empty($mapSettings)) {

                $item['mapSettings'] = $mapSettings;
            }

            $item['feViewID'] = $this->feViewID;

            if (!empty($arrCleanOptions)) {

                $item['cleanOptions'] = $arrCleanOptions;

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

            $item['floatClass'] = 'float_' . $item['floating'];

            $objTemplate->setData($item);
            $strTitle = $item['title'];

            if ($item['addImage']) {

                $this->addImageToTemplate($objTemplate, array(

                    'singleSRC' => $item['singleSRC'],
                    'alt' => $item['alt'],
                    'title' => $item['imgTitle'],
                    'size' => $item['size'],
                    'fullsize' => $item['fullsize'],
                    'caption' => $item['caption']
                ));
            }

            $objTemplate->enclosure = array();

            if ($item['addEnclosure']) {

                $this->addEnclosuresToTemplate($objTemplate, $item);
            }

            $objTemplate->title = $strTitle; // fix title bug
            $objTemplate->addBefore = $item['floatClass'] == 'float_below' ? false : true;

            $strResults .= $objTemplate->parse();
        }

        if (!empty($mapSettings)) {

            $this->Template->mapSettings = $mapSettings;
            $this->loadMapScript = true;

            if (!$GLOBALS['loadGoogleMapLibraries']) $GLOBALS['loadGoogleMapLibraries'] = $mapSettings['mapInfoBox'] ? true : false;
        }

        if ($this->loadMapScript) {

            $language = $objPage->language ? $objPage->language : 'en';
            $GLOBALS['TL_HEAD']['mapJS'] = DiverseFunction::setMapJs($language);
        }

        $this->Template->feViewID = $this->feViewID;
        $this->Template->results = ( $total < 1 ? '<p class="no-results">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>' : $strResults );
    }


    protected function setValuesForAutoPageAttribute($return) {

        global $objPage;

        $alias = $objPage->alias;

        if ($return['type'] == 'multi_choice') {

            $language = Config::get('addLanguageToUrl') ? $objPage->language : '';
            $alias = Environment::get('requestUri');
            $alias = explode('/', $alias);
            $alias = array_filter($alias);
            $alias = array_values($alias);

            if ($language && $alias[0] && $language == $alias[0]) {
                array_shift($alias);
            }
        }

        $return['value'] = $alias;

        return $return;
    }


    protected function setValuesForTaxonomySpecieAttribute($return) {

        if(\Input::get($return['fieldID'])) {

            $this->strSpecie = \Input::get($return['fieldID']);
        }

        if ($this->strSpecie && is_string($this->strSpecie)) {

            $return['value'] = $this->strSpecie;
        }

        return $return;
    }


    protected function setValuesForTaxonomyTagsAttribute($return) {

        if ( \Input::get($return['fieldID']) ) {

            $this->strTag = \Input::get($return['fieldID']);
        }

        if ( is_string($this->strTag) ) {

            $this->strTag = explode(',', $this->strTag);
        }

        if ( $this->strTag && is_array($this->strTag) ) {

            $return['value'] = $this->strTag;
        }

        return $return;
    }


    public function parseTemplateName($templateName) {

        return DiverseFunction::parseTemplateName($templateName);
    }


    public function getOrderBy() {

        $arrReturn = [];
        $strInputOrderBy = \Input::get('orderBy') ? \Input::get('orderBy') : 'DESC';
        $varInputSortingFields = \Input::get('sorting_fields') ? \Input::get('sorting_fields') : '';

        if ( !empty( $this->fm_orderBy ) && is_array( $this->fm_orderBy ) ) {

            foreach ( $this->fm_orderBy as $arrOrderBy ) {

                if ( $arrOrderBy['key'] && $arrOrderBy['value'] ) {

                    $arrReturn[ $arrOrderBy['key'] ] =  sprintf( '%s %s', $arrOrderBy['key'], $arrOrderBy['value'] );
                }
            }
        }

        if ( !empty( $varInputSortingFields ) && is_array( $varInputSortingFields ) ) {

            foreach ( $varInputSortingFields as $strField ) {

                if ( in_array( $strInputOrderBy, [ 'ASC', 'DESC', 'asc', 'desc' ] ) ) {

                    $arrReturn[ $strField ] =  sprintf( '%s %s', $strField, mb_strtoupper( $strInputOrderBy, 'UTF-8' ) );
                }
            }
        }

        if ( $varInputSortingFields && is_string( $varInputSortingFields ) ) {

            if ( in_array( $strInputOrderBy, [ 'ASC', 'DESC', 'asc', 'desc' ] ) ) {

                $arrReturn[ $varInputSortingFields ] =  sprintf( '%s %s', $varInputSortingFields, mb_strtoupper( $strInputOrderBy, 'UTF-8' ) );
            }
        }

        if( $this->blnLocatorInvoke && $this->fm_orderByDistance ) {

            $strDistanceOrderBy = $this->fm_orderByDistance ? $this->fm_orderByDistance : 'DESC';

            if ( in_array( $strDistanceOrderBy, [ 'ASC', 'DESC', 'asc', 'desc' ] ) ) {

                $arrReturn[ '_distance' ] =  sprintf( '%s %s', '_distance', mb_strtoupper( $strDistanceOrderBy, 'UTF-8' ) );
            }
        }

        if ( empty( $arrReturn ) ) {

            return '';
        }

        $arrReturn = array_unique( $arrReturn );
        
        return ' ORDER BY ' . implode( ',', $arrReturn );
    }


    public function setFilterValues($taxonomyFromFE, $taxonomyFromPage, $return) {

        $taxonomies = array();

        foreach ($taxonomyFromFE as $filterValue) {

            if ($filterValue['set']['ignore']) {
                continue;
            }

            $taxonomies[$filterValue['fieldID']] = $filterValue;
        }

        foreach ($taxonomyFromPage as $filterValue) {

            if ($filterValue['set']['ignore']) {
                continue;
            }

            if ($filterValue['active'] == '1') {
                $taxonomies[$filterValue['fieldID']] = $filterValue;
            }
        }

        foreach ($taxonomies as $filterValue) {

            $return = $this->taxonomyValueSetter($filterValue, $return);
        }

        return $return;

    }


    protected function taxonomyValueSetter($filterValue, $return) {

        $return[$filterValue['fieldID']]['overwrite'] = $filterValue['set']['overwrite'];
        $return[$filterValue['fieldID']]['active'] = $filterValue['active'];

        $value = QueryModel::isValue($return[$filterValue['fieldID']]['value'], $return[$filterValue['fieldID']]['type']);

        if (!$value && $filterValue['active']) {

            $return[$filterValue['fieldID']]['value'] = ($filterValue['set']['filterValue'] ? $filterValue['set']['filterValue'] : '');
            $return[$filterValue['fieldID']]['operator'] = ($filterValue['set']['selected_operator'] ? $filterValue['set']['selected_operator'] : '');

            if (is_null(Input::get($filterValue['fieldID'])) && $return[$filterValue['fieldID']]['type'] == 'toggle_field') {

                $return[$filterValue['fieldID']]['value'] = $return[$filterValue['fieldID']]['value'] ? '1' : 'skip';
            }
        }

        if ($filterValue['set']['overwrite']) {

            $return[$filterValue['fieldID']]['value'] = ($filterValue['set']['filterValue'] ? $filterValue['set']['filterValue'] : '');
            $return[$filterValue['fieldID']]['operator'] = ($filterValue['set']['selected_operator'] ? $filterValue['set']['selected_operator'] : '');

            if ($return[$filterValue['fieldID']]['type'] == 'toggle_field') {

                $return[$filterValue['fieldID']]['value'] = $return[$filterValue['fieldID']]['value'] == '1' ? '1' : 'skip';
            }

        }

        $val = QueryModel::isValue($return[$filterValue['fieldID']]['value'], $return[$filterValue['fieldID']]['type']);

        if ($val) {

            $return[$filterValue['fieldID']]['enable'] = true;
        }

        return $return;
    }


    public function getFilter($fieldID, $type) {

        $getFilter = Input::get($fieldID) ? Input::get($fieldID) : '';
        $getOperator = Input::get($fieldID . '_int') ? Input::get($fieldID . '_int') : '';

        if ($type == 'multi_choice' && !is_array($getFilter)) {

            $getFilter = explode(',', $getFilter);
        }

        if ($type == 'toggle_field' && is_null(Input::get($fieldID)) == false && Input::get($fieldID) != '1') {

            $getFilter = 'skip';
        }

        return array(
            'value' => $getFilter,
            'operator' => $getOperator
        );
    }


    public function createPagination($total = 0) {

        global $objPage;

        $this->listViewLimit = $total;
        $getPagination = \Input::get('pagination');

        if ($getPagination) {

            if (is_array($getPagination)) {
                $this->f_perPage = $getPagination[0];
            }

            if (is_string($getPagination)) {
                $this->f_perPage = $getPagination;
            }

        }

        if ($getPagination == '0' && !is_null($getPagination)) {
            $this->f_perPage = $getPagination;
        }

        if ($this->f_limit_page > 0) {

            $total = min($this->f_limit_page, $total);
            $this->listViewLimit = $total;
        }
        
        if ($this->f_perPage > 0 && $this->strOrderBy != 'RAND') {
            $id = 'page_e' . $this->id;
            $page = (\Input::get($id) !== null) ? \Input::get($id) : 1;

            if ($page < 1 || $page > max(ceil($total / $this->f_perPage), 1)) {
                $objHandler = new $GLOBALS['TL_PTY']['error_404']();
                $objHandler->generate($objPage->id);
            }

            $this->listViewOffset = ($page - 1) * $this->f_perPage;
            $this->listViewLimit = min($this->f_perPage + $this->listViewOffset, $total);
            $objPagination = new Pagination($total, $this->f_perPage, Config::get('maxPaginationLinks'), $id);
            return $objPagination->generate("\n  ");
        }

        return '';
    }


    private function generateSingeSrc($row) {

        if ($row->singleSRC != '') {

            $objModel = \FilesModel::findByUuid($row->singleSRC);

            if ($objModel && is_file(TL_ROOT . '/' . $objModel->path)) {

                return $objModel->path;
            }
        }

        return null;
    }


    private function generateUrl($objTarget, $alias) {

        $strTaxonomyUrl = \Config::get('taxonomyDisable') ? '' : $this->generateTaxonomyUrl();
        return $this->generateFrontendUrl($objTarget, '/' . $alias . $strTaxonomyUrl);
    }


    private function generateTaxonomyUrl() {

        $strTaxonomyUrl = '';

        if ($this->strTag && is_array($this->strTag)) $this->strTag = implode(',', $this->strTag);

        if ($this->strSpecie && $this->fm_use_specieUrl) {
            $strTaxonomyUrl .= '/' . $this->strSpecie;
        }

        if ($this->strTag && $this->fm_use_specieUrl && $this->fm_use_tagsUrl) {
            $strTaxonomyUrl .= '/' . $this->strTag;
        }

        return $strTaxonomyUrl;
    }
}