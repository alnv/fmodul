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


/**
 * Class ModuleListView
 * @package FModule
 */
class ModuleListView extends Module
{

    /**
     * @var string
     */
    protected $strTemplate = 'mod_fmodule_list';

    /**
     * @var
     */
    public $tablename;

    /**
     * @var
     */
    public $listViewLimit;

    /**
     * @var
     */
    public $listViewOffset = 0;

    /**
     * @var array
     */
    protected $markerCache = array();

    /**
     * @var bool
     */
    protected $loadMapScript = false;

    /**
     * @var bool
     */
    protected $loadLibraries = false;

    /**
     * @var null
     */
    protected $feViewID = null;

    /**
     *
     */
    public function generate()
    {
        // backend view
        if (TL_MODE == 'BE') {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . $this->name . ' ###';
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            return $objTemplate->parse();
        }

        $this->import('FrontendUser', 'User');

        // set fe view id
        $this->feViewID = md5($this->id);

        // change template
        if (TL_MODE == 'FE' && $this->fm_addMap) {
            $this->strTemplate = 'mod_fmodule_map';
        }

        //auto_page Attribute
        if (!isset($_GET['item']) && Config::get('useAutoItem') && isset($_GET['auto_item'])) {
            Input::setGet('item', Input::get('auto_item'));
        }
        return parent::generate();
    }

    /**
     *
     */
    protected function compile()
    {

        global $objPage;

        $f_display_mode = deserialize($this->f_display_mode);
        $page_taxonomy = deserialize($objPage->page_taxonomy);
        $taxonomyFromFE = is_array($f_display_mode) ? $f_display_mode : array();
        $taxonomyFromPage = is_array($page_taxonomy) ? $page_taxonomy : array();
        $tablename = $this->f_select_module;
        $wrapperID = $this->f_select_wrapper;
        $doNotSetByID = array('orderBy', 'sorting_fields', 'pagination');
        $doNotSetByType = array('legend_end', 'legend_start', 'wrapper_field');
        $moduleDB = $this->Database->prepare('SELECT tl_fmodules.id AS moduleID, tl_fmodules.*, tl_fmodules_filters.*  FROM tl_fmodules LEFT JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ? ORDER BY tl_fmodules_filters.sorting')->execute($tablename);
        $fieldsArr = array();
        $fieldWidgets = array();
        $this->tablename = $tablename;
        $mapFields = array();

        // map view settings
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

        while ($moduleDB->next()) {

            if (in_array($moduleDB->fieldID, $doNotSetByID) || in_array($moduleDB->type, $doNotSetByType)) {
                continue;
            }

            $modArr = $moduleDB->row();
            $getFilter = $this->getFilter($moduleDB->fieldID, $moduleDB->type);
            $modArr['value'] = $getFilter['value'];
            $modArr['operator'] = $getFilter['operator'];
            $modArr['overwrite'] = null;
            $modArr['active'] = null;

            if ($moduleDB->fieldID == 'auto_page' || $moduleDB->autoPage) {
                $modArr = $this->setValuesForAutoPageAttribute($modArr);
            }

            $val = QueryModel::isValue($modArr['value'], $moduleDB->type);

            if ($val) $modArr['enable'] = true;

            // map
            if ($moduleDB->type == 'map_field') {

                // set map settings
                $mapFields[] = HelperModel::setGoogleMap($modArr);

                // set loadMapScript to true
                $this->loadMapScript = true;

                // load map libraries
                $this->loadLibraries = $modArr['mapInfoBox'] ? true : false;
            }

            // field
            if ($moduleDB->type == 'widget') {
                $tplName = $moduleDB->widgetTemplate;
                $tpl = '';
                if (!$tplName) {
                    $tplNameType = explode('.', $moduleDB->widget_type)[0];
                    $tplNameArr = $this->getTemplateGroup('fm_field_' . $tplNameType);
                    $tpl = current($tplNameArr);
                    $tpl = $this->parseTemplateName($tpl);
                }
                $fieldWidgets[$moduleDB->fieldID] = array(
                    'fieldID' => $moduleDB->fieldID,
                    'widgetType' => $moduleDB->widget_type,
                    'widgetTemplate' => $moduleDB->widgetTemplate ? $moduleDB->widgetTemplate : $tpl
                );
            }

            $fieldsArr[$moduleDB->fieldID] = $modArr;
        }

        if (!empty($taxonomyFromFE) || !empty($taxonomyFromPage)) {
            $fieldsArr = $this->setFilterValues($taxonomyFromFE, $taxonomyFromPage, $fieldsArr);
        }

        $qResult = HelperModel::generateSQLQueryFromFilterArray($fieldsArr);
        $qStr = $qResult['qStr'];
        $qTextSearch = $qResult['isFulltextSearch'] ? $qResult['$qTextSearch'] : '';

        //get text search results
        $textSearchResults = array();
        if ($qTextSearch) {
            $textSearchResults = QueryModel::getTextSearchResult($qTextSearch, $tablename, $wrapperID);
        }

        // get list view
        $wrapperDB = $this->Database->prepare('SELECT addDetailPage, title, id, rootPage FROM ' . $tablename . ' WHERE id = ?')->execute($wrapperID)->row();
        $addDetailPage = $wrapperDB['addDetailPage'];
        $rootDB = $this->Database->prepare('SELECT * FROM ' . $tablename . ' JOIN tl_page ON tl_page.id = ' . $tablename . '.rootPage WHERE ' . $tablename . '.id = ?')->execute($wrapperID)->row();
        $qOrderByStr = $this->getOrderBy();
        $qProtectedStr = ' AND published = "1"';

        //  preview mode
        if (HelperModel::previewMode()) $qProtectedStr = '';

        // get all items
        $listDB = $this->Database->prepare('SELECT * FROM ' . $tablename . '_data WHERE pid = ' . $wrapperID . $qProtectedStr . $qStr . $qOrderByStr)->query();

        // image size
        $imgSize = false;

        // Override the default image size
        if ($this->imgSize != '') {
            $size = deserialize($this->imgSize);

            if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                $imgSize = $this->imgSize;
            }
        }

        $itemsArr = array();
        while ($listDB->next()) {

            if (HelperModel::sortOutProtected($listDB->row(), $this->User->groups)) {
                continue;
            }

            if (!HelperModel::outSideScope($listDB->start, $listDB->stop)) {
                continue;
            }

            $imagePath = $this->generateSingeSrc($listDB);
            if ($imagePath) {
                $listDB->singleSRC = $imagePath;
            }

            if ($imgSize) {
                $listDB->size = $imgSize;
            }

            $listDB->href = null;

            if ($addDetailPage == '1' && $listDB->source == 'default') {
                // reset target
                $listDB->target = '';
                $listDB->href = $this->generateUrl($rootDB, $listDB->alias);
            }

            if ($listDB->source == 'external') {
                $listDB->href = $listDB->url;
            }

            if ($listDB->source == 'internal') {

                // reset target
                $listDB->target = '';
                $jumpToDB = $this->Database->prepare('SELECT * FROM tl_page WHERE id = ?')->execute($listDB->jumpTo)->row();
                $listDB->href = $this->generateFrontendUrl($jumpToDB);
            }

            // check for textsearch
            if ($qTextSearch) {
                if (!$textSearchResults[$listDB->id]) {
                    continue;
                }
            }

            //
            $itemsArr[] = $listDB->row();

        }

        //pagination
        $total = count($itemsArr);
        $paginationStr = $this->createPagination($total);
        $paginationStr = $paginationStr ? $paginationStr : '';
        $this->Template->pagination = $paginationStr;


        $strResults = '';
        $template = $this->fm_addMap ? $this->fm_map_template : $this->f_list_template;
        $objTemplate = new FrontendTemplate($template);

        for ($i = $this->listViewOffset; $i < $this->listViewLimit; $i++) {

            $item = $itemsArr[$i];

            // parse value if map is enabled
            if ($this->fm_addMap) {
                $item['geo_latitude'] = $item['geo_latitude'] ? $item['geo_latitude'] : '0';
                $item['geo_longitude'] = $item['geo_longitude'] ? $item['geo_longitude'] : '0';
                $item['title'] = mb_convert_encoding($item['title'], 'UTF-8');
                $item['description'] = mb_convert_encoding($item['description'], 'UTF-8');
                $item['info'] = mb_convert_encoding($item['info'], 'UTF-8');
            }

            //set css and id
            $item['cssID'] = deserialize($item['cssID']);
            $item['itemID'] = $item['cssID'][0];
            $item['itemCSS'] = ' ' . $item['cssID'][1];

            // set date format
            $item['date'] = $item['date'] ? date($objPage->dateFormat, $item['date']) : '';
            $item['time'] = $item['time'] ? date($objPage->timeFormat, $item['time']) : '';

            //set more
            $item['more'] = $GLOBALS['TL_LANG']['MSC']['more'];

            // get list view ce
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

            // set odd and even classes
            $item['cssClass'] = $i % 2 ? 'even' : 'odd';

            //field
            if (!empty($fieldWidgets)) {

                $arrayAsValue = array('list.blank', 'list.keyValue', 'table.blank');
                foreach ($fieldWidgets as $widget) {
                    $id = $widget['fieldID'];
                    $tplName = $widget['widgetTemplate'];
                    $type = $widget['widgetType'];
                    $value = $item[$id];

                    if (in_array($type, $arrayAsValue)) {
                        $value = unserialize($value);
                    }

                    $objFieldTemplate = new FrontendTemplate($tplName);
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
                    $objMapTemplate = new FrontendTemplate($map['template']);
                    $item['mapSettings'] = $map;
                    $objMapTemplate->setData($item);
                    $item[$map['fieldID']] = $objMapTemplate->parse();
                }
            }

            if (!empty($mapSettings)) {
                $item['mapSettings'] = $mapSettings;
            }

            // set fe view id
            $item['feViewID'] = $this->feViewID;

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

            $strResults .= $objTemplate->parse();
        }

        // set map settings
        if (!empty($mapSettings)) {

            // set map settings array to template
            $this->Template->mapSettings = $mapSettings;

            // set loadMapScript to true
            $this->loadMapScript = true;

            // load map libraries
            $this->loadLibraries = $mapSettings['mapInfoBox'] ? true : false;
        }

        // set js files
        if ($this->loadMapScript && !isset($GLOBALS['TL_HEAD']['mapJS'])) {
            $language = $objPage->language ? $objPage->language : 'en';
            $GLOBALS['TL_HEAD']['mapJS'] = DiverseFunction::setMapJs($this->loadLibraries, $language);
        }
        $this->Template->feViewID = $this->feViewID;
        $this->Template->results = ($total < 1 ? '<p class="no-results">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>' : $strResults);
    }

    /**
     * @param $return
     * @return mixed
     */
    protected function setValuesForAutoPageAttribute($return)
    {
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

    /**
     * @return string
     */
    public function getOrderBy()
    {

        $orderByFromListView = mb_strtoupper($this->f_orderby, 'UTF-8');
        $orderBy = Input::get('orderBy') ? Input::get('orderBy') : $orderByFromListView;
        $isValue = QueryModel::isValue($orderBy);
        $allowedOrderByItems = array('asc', 'desc', 'rand', 'ACS', 'DESC', 'RAND');

        if ($isValue && is_array($orderBy)) {

            $orderBy = $orderBy[0];
        }

        if ($isValue && in_array($orderBy, $allowedOrderByItems)) {
            $orderBy = mb_strtoupper($orderBy, 'UTF-8');
        }

        if (!$orderBy) {
            $orderBy = 'DESC';
        }

        $sorting = $this->getSortingField();
        $qOrderByStr = ' ORDER BY ' . $sorting . ' ' . $orderBy;

        if ($orderBy == 'RAND') {
            $qOrderByStr = ' ORDER BY RAND()';
        }

        return $qOrderByStr;

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
     * @return array|string
     */
    public function getSortingField()
    {
        $sortingFromViewList = deserialize($this->f_sorting_fields) ? deserialize($this->f_sorting_fields) : array('id');
        $sortingFromGET = Input::get('sorting_fields');
        $isValue = QueryModel::isValue($sortingFromGET);
        $sortingFields = array();

        if ($isValue) {
            if (is_array($sortingFromGET)) {
                $sortingFields = $sortingFromGET;
            }

            if (is_string($sortingFromGET)) {
                $sortingFields[] = $sortingFromGET;
            }

            $temp = array();

            foreach ($sortingFields as $field) {
                if ($this->Database->fieldExists($field, $this->tablename)) {
                    $temp[] = $field;
                }
            }

            $sortingFields = $temp;

        }

        if (count($sortingFields) > 0) {
            $sortingFields = array_filter($sortingFields);
            return implode(',', $sortingFields);
        }

        if (count($sortingFromViewList) > 0 && is_array($sortingFromViewList)) {
            $sortingFromViewList = array_filter($sortingFromViewList);
            return implode(',', $sortingFromViewList);
        }

        return 'id';

    }

    /**
     * @param $taxonomyFromFE
     * @param $taxonomyFromPage
     * @param $return
     * @return mixed
     */
    public function setFilterValues($taxonomyFromFE, $taxonomyFromPage, $return)
    {

        $taxonomies = array();

        // nachdem 1.4.2 update ändern!
        // die fieldID wird als key übergeben. daher kann man eine schleife sparen
        // erstmal weglassen wegen der kompatibilität
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

    /**
     * @param $filterValue
     * @param $return
     * @return mixed
     */
    protected function taxonomyValueSetter($filterValue, $return)
    {

        $return[$filterValue['fieldID']]['overwrite'] = $filterValue['set']['overwrite'];
        $return[$filterValue['fieldID']]['active'] = $filterValue['active'];

        $value = QueryModel::isValue($return[$filterValue['fieldID']]['value'], $return[$filterValue['fieldID']]['type']);

        if (!$value && $filterValue['active']) {

            $return[$filterValue['fieldID']]['value'] = ($filterValue['set']['filterValue'] ? $filterValue['set']['filterValue'] : '');
            $return[$filterValue['fieldID']]['operator'] = ($filterValue['set']['selected_operator'] ? $filterValue['set']['selected_operator'] : '');

            //exception for toggle field
            if (is_null(Input::get($filterValue['fieldID'])) && $return[$filterValue['fieldID']]['type'] == 'toggle_field') {
                $return[$filterValue['fieldID']]['value'] = $return[$filterValue['fieldID']]['value'] ? '1' : 'skip';
            }
        }

        if ($filterValue['set']['overwrite']) {

            $return[$filterValue['fieldID']]['value'] = ($filterValue['set']['filterValue'] ? $filterValue['set']['filterValue'] : '');
            $return[$filterValue['fieldID']]['operator'] = ($filterValue['set']['selected_operator'] ? $filterValue['set']['selected_operator'] : '');

            //exception for toggle field
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


    /**
     * @param $fieldID
     * @param $type
     * @return array
     */
    public function getFilter($fieldID, $type)
    {
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

    /**
     * @param int $total
     * @return null|string
     */
    public function createPagination($total = 0)
    {
        global $objPage;
        $this->listViewLimit = $total;
        $getPagination = Input::get('pagination');

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

        if ($this->f_perPage > 0) {
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

        return null;

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
     * @param $objTarget
     * @param $alias
     * @return string
     */
    private function generateUrl($objTarget, $alias)
    {
        return $this->generateFrontendUrl($objTarget, '/' . $alias);
    }


}