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
     *
     */
    public function generate()
    {

        //
        if (TL_MODE == 'BE') {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . $this->name . ' ###';
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            return $objTemplate->parse();

        }

        $this->import('FrontendUser', 'User');

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
        $doNotSetByType = array('legend_end', 'legend_start', 'wrapper_field', 'map_field');
        $moduleDB = $this->Database->prepare('SELECT tl_fmodules.id AS moduleID, tl_fmodules.*, tl_fmodules_filters.*  FROM tl_fmodules LEFT JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ? ORDER BY tl_fmodules_filters.sorting')->execute($tablename);
        $fieldsArr = array();
        $fieldWidgets = array();
        $this->tablename = $tablename;

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

            if ($val) {
                $modArr['enable'] = true;
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

        // if preview mode
        if (HelperModel::previewMode()) {
            $qProtectedStr = '';
        }

        // all items in list
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
		//exit;
        //pagination
        $total = count($itemsArr);
        $paginationStr = $this->createPagination($total);
        $paginationStr = $paginationStr ? $paginationStr : '';
        $this->Template->pagination = $paginationStr;


        $strResults = '';
        $objTemplate = new FrontendTemplate($this->f_list_template);

        for ($i = $this->listViewOffset; $i < $this->listViewLimit; $i++) {

            $item = $itemsArr[$i];
			
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

            //set data
            $objTemplate->setData($item);

            // set last first classes
            if ($i == 0) {
                $item['cssClass'] .= ' first';
            }
            if ($i == ($this->listViewLimit - 1)) {
                $item['cssClass'] .= ' last';
            }

            // set enclosure
            $objTemplate->enclosure = array();
            if ($item['addEnclosure']) {
                $this->addEnclosuresToTemplate($objTemplate, $item);
            }

            //set image
            if ($item['addImage']) {
                $this->addImageToTemplate($objTemplate, $item);
            }

            $strResults .= $objTemplate->parse();

        }

        $this->Template->results = ($total < 1 ? '<p class="no-results">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>' : $strResults);

    }

    /**
     * @param $mode
     * @return mixed
     */
    protected function setValuesForAutoPageAttribute($return)
    {
        global $objPage;

        $alias =  $objPage->alias;

        if($return['type'] == 'multi_choice')
        {

            $language = Config::get('addLanguageToUrl') ? $objPage->language : '';
            $alias = Environment::get('requestUri');
            $alias = explode('/', $alias);
            $alias = array_filter($alias);
            $alias = array_values($alias);

            if($language && $alias[0] && $language == $alias[0])
            {
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
     * @param $usesTemplates
     * @return mixed
     */
    public function parseTemplateName($usesTemplates)
    {
        $arrReplace = array('#', '<', '>', '(', ')', '\\', '=');
        $arrSearch = array('&#35;', '&#60;', '&#62;', '&#40;', '&#41;', '&#92;', '&#61;');
        $strVal = str_replace($arrSearch, $arrReplace, $usesTemplates);
        $strVal = str_replace(' ', '', $strVal);
        return preg_replace('/[\[{\(].*[\]}\)]/U', '', $strVal);
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
     * @param $filterValues
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
            if($filterValue['set']['ignore'])
            {
                continue;
            }
            $taxonomies[$filterValue['fieldID']] = $filterValue;
        }

        foreach ($taxonomyFromPage as $filterValue) {
            if($filterValue['set']['ignore'])
            {
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
     * @param $items
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

        if($getPagination == '0' && !is_null($getPagination))
        {
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