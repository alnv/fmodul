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
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Pagination;
use Contao\Config;

/**
 * Class AjaxApiFModule
 * @package FModule
 */
class AjaxApiFModule extends Frontend
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
     *
     */
    public function getEntities()
    {

        $tablename = Input::get('tablename');
        $wrapperID = Input::get('wrapperID');

        $results = array();

        if (!$tablename || !$wrapperID) {
            $this->sendFailState("No Back end Module found");
        }

        if (!$this->Database->tableExists($tablename)) {
            $this->sendFailState($tablename . " do not exist");
        }

        $this->import('FrontendUser', 'User');

        global $objPage;

        $dataTable = $tablename . '_data';
        $this->tablename = $dataTable;
        $template = Input::get('template') ? Input::get('template') : 'fmodule_teaser';
        $doNotSetByID = array('orderBy', 'sorting_fields', 'pagination');
        $doNotSetByType = array('legend_end', 'legend_start', 'wrapper_field');
        $moduleDB = $this->Database->prepare('SELECT tl_fmodules.id AS moduleID, tl_fmodules.*, tl_fmodules_filters.*  FROM tl_fmodules LEFT JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ? ORDER BY tl_fmodules_filters.sorting')->execute($tablename);
        $fieldsArr = array();

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

            $val = QueryModel::isValue($modArr['value'], $moduleDB->type);

            if ($val) {
                $modArr['enable'] = true;
            }

            // field
            if ($moduleDB->type == 'widget') {
                $fieldWidgets[$moduleDB->fieldID] = array(
                    'fieldID' => $moduleDB->fieldID,
                    'widgetType' => $moduleDB->widget_type,
                    'widgetTemplate' => $moduleDB->widgetTemplate
                );
            }

            $fieldsArr[$moduleDB->fieldID] = $modArr;

        }

        $qPid = ' AND pid = "' . $wrapperID . '"';
        $qResult = HelperModel::generateSQLQueryFromFilterArray($fieldsArr);
        $qStr = $qResult['qStr'];
        $qTextSearch = $qResult['isFulltextSearch'] ? $qResult['$qTextSearch'] : '';

        //get text search results
        $textSearchResults = array();
        if ($qTextSearch) {
            $textSearchResults = QueryModel::getTextSearchResult($qTextSearch, $tablename, $wrapperID);
        }
        $qOrderBY = $this->getOrderBY();

        $resultsDB = $this->Database->prepare('SELECT * FROM ' . $dataTable . ' WHERE published = "1"' . $qPid . $qStr . $qOrderBY.'')->query();
        $wrapperDB = $this->Database->prepare('SELECT addDetailPage, title, id, rootPage FROM ' . $tablename . ' WHERE id = ?')->execute($wrapperID)->row();
        $addDetailPage = $wrapperDB['addDetailPage'];
        $rootDB = $this->Database->prepare('SELECT * FROM ' . $tablename . ' JOIN tl_page ON tl_page.id = ' . $tablename . '.rootPage WHERE ' . $tablename . '.id = ?')->execute($wrapperID)->row();

        while ($resultsDB->next()) {

            if (HelperModel::sortOutProtected($resultsDB->row(), $this->User->groups)) {
                continue;
            }

            if (!HelperModel::outSideScope($resultsDB->start, $resultsDB->stop)) {
                continue;
            }

            $imagePath = $this->generateSingeSrc($resultsDB);

            if ($imagePath) {
                $resultsDB->singleSRC = $imagePath;
            }

            $resultsDB->href = null;

            if ($addDetailPage == '1' && $resultsDB->source == 'default') {
                // reset target
                $resultsDB->target = '';
                $resultsDB->href = $this->generateUrl($rootDB, $resultsDB->alias);
            }

            if ($resultsDB->source == 'external') {
                $resultsDB->href = $resultsDB->url;
            }

            if ($resultsDB->source == 'internal') {
                // reset target
                $resultsDB->target = '';
                $jumpToDB = $this->Database->prepare('SELECT * FROM tl_page WHERE id = ?')->execute($resultsDB->jumpTo)->row();
                $resultsDB->href = $this->generateFrontendUrl($jumpToDB);
            }

            // check for textsearch
            if ($qTextSearch) {
                if (!$textSearchResults[$resultsDB->id]) {
                    continue;
                }
            }

            $results[] = $resultsDB->row();
        }

        //pagination
        $total = count($results);
        $this->listViewLimit = $total;
        $this->createPagination($total);

        $jsonReturnData = array('entities' => array(), 'html' => '', 'labels' => array('noResults' => $GLOBALS['TL_LANG']['MSC']['noResult']));
        $objTemplate = new FrontendTemplate($template);

        for ($i = $this->listViewOffset; $i < $this->listViewLimit; $i++) {

            $item = $results[$i];

            //set css and id
            $item['cssID'] = deserialize($item['cssID']);
            $item['itemID'] = $item['cssID'][0];
            $item['itemCSS'] = ' ' . $item['cssID'][1];

            // set date format
            $item['date'] = $item['date'] ? date($objPage->dateFormat, $item['date']) : '';
            $item['time'] = $item['time'] ? date($objPage->timeFormat, $item['time']) : '';

            //set more
            $item['more'] = $GLOBALS['TL_LANG']['MSC']['more'];

            $objCte = ContentModelExtend::findPublishedByPidAndTable($item['id'], $dataTable, array('fview' => 'list'));
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

            $objTemplate->setData($item);

            // set last first classes
            if ($i == $this->listViewOffset) {
                $item['cssClass'] .= ' first';
            }
            if ($i == ($this->listViewLimit - 1)) {
                $item['cssClass'] .= ' last';
            }

            // set enclosure
            $objTemplate->enclosure = array();
            if ($item['addEnclosure']) {
                $this->addEnclosuresToTemplate($objTemplate, $item);
                $item['enclosure'] = $objTemplate->enclosure;
            }

            //set image
            if ($item['addImage']) {
                $this->addImageToTemplate($objTemplate, $item);
            }

            $jsonReturnData['html'] .= $objTemplate->parse();
            $jsonReturnData['entities'][] = $item;

        }

        header('Content-type: application/json');
        echo json_encode($jsonReturnData, 512);
        exit;
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
     * @param $items
     * @return null|string
     */
    public function createPagination($total = 0)
    {

        if(Input::get('perPage'))
        {
            $perPage = (int)Input::get('perPage');
            $this->perPage = $perPage;
        }

        if ($this->perPage > 0) {

            $page = (Input::get('page') !== null) ? (int) Input::get('page') : 1;

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
    protected function getOrderBY()
    {
        $orderBYOptions = array('ASC', 'DESC', 'RAND');
        $orderBY = Input::get('orderBy');
        $orderBYStr = 'DESC';

        if ($orderBY && in_array(mb_strtoupper($orderBY, 'UTF-8'), $orderBYOptions)) {
            if (mb_strtoupper($orderBY, 'UTF-8') == 'RAND') {
                return 'ORDER BY RAND()';
            }
            $orderBYStr = mb_strtoupper($orderBY, 'UTF-8');
        }

        $sortingField = Input::get('sorting_fields');
        $sortingFields = array();
        if ($sortingField && is_string($sortingField)) {
            $sortingFields[] = $sortingField;
        }

        if ($sortingField && is_array($sortingField)) {
            $sortingFields = $sortingField;
        }
        $temp = [];
        foreach ($sortingFields as $field) {
            if ($this->Database->fieldExists($field, $this->tablename)) {
                $temp[] = $field;
            }
        }

        $sorting = array_filter($temp);
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