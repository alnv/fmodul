<?php namespace FModule;

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   F Modul
 * @author    Alexander Naumov http://www.alexandernaumov.de
 * @license   commercial
 * @copyright 2015 Alexander Naumov
 */

use Contao\Cache;
use Contao\Input;
use Contao\Pagination;
use Contao\Search;
use Contao\System;


/**
 *
 */
class ModuleListView extends \Contao\Module
{
    /**
     *
     */
    protected $strTemplate = 'mod_fmodule_list';

    /**
     *
     */
    public function generate()
    {

        /**
         *
         */
        if (TL_MODE == 'BE') {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '. $this->name .' ###';
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            return $objTemplate->parse();

        }

        $this->import('FrontendUser', 'User');

        /**
         *
         */
        if (!isset($_GET['fitem']) && \Config::get('useAutoItem') && isset($_GET['auto_item'])) {

            \Input::setGet('fitem', \Input::get('auto_item'));

        }

        return parent::generate();

    }

    /**
     *
     */
    protected function compile()
    {

        global $objPage;

        $modeFields = deserialize($this->f_display_mode);
        $tablename = $this->f_select_module;
        $wrapperID = $this->f_select_wrapper;
        $orderBy = mb_strtoupper($this->f_orderby, 'UTF-8');
        $sortingFields = deserialize($this->f_sorting_fields);

        //  set default sorting field title
        if( !is_array($sortingFields) || count($sortingFields) < 1 )
        {
            $sortingFields = array( 'title' );
        }

        $moduleDB = $this->Database->prepare('SELECT tl_fmodules.id AS moduleID, tl_fmodules.*, tl_fmodules_filters.*  FROM tl_fmodules LEFT JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ?')->execute($tablename);

        // no module selected
        if ($moduleDB->count() <= 0) {
            return;
        }

        //$alias = Input::get('fitem');

        $filterCollection = array();
        $input = array();

        $imgSize = false;

        // Override the default image size
        if ($this->imgSize != '')
        {
            $size = deserialize($this->imgSize);

            if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2]))
            {
                $imgSize = $this->imgSize;
            }
        }

        // set format
        while ($moduleDB->next()) {

            $filterCollection[$moduleDB->fieldID] = array(
                'type' => $moduleDB->type,
                'fieldID' => $moduleDB->fieldID,
                'title' => $moduleDB->title,
                'isInteger' => $moduleDB->isInteger,
                'negate' => $moduleDB->negate,
                'isFuzzy' => $moduleDB->isFuzzy,
                'value' => '',
                'overwrite' => null,
                'active' => null,
                'id' => $moduleDB->id,
            );


        }


        //set default mode
        if (is_array($modeFields)) {

            foreach ($modeFields as $modeField) {

                $filterCollection[$modeField['fieldID']]['value'] = ($modeField['set']['filterValue'] ? $modeField['set']['filterValue'] : '');
                $filterCollection[$modeField['fieldID']]['operator'] = ($modeField['set']['selected_operator'] ? $modeField['set']['selected_operator'] : '');
                $filterCollection[$modeField['fieldID']]['overwrite'] = $modeField['set']['overwrite'];
                $filterCollection[$modeField['fieldID']]['active'] = $modeField['active'];

            }

        }

        //set get values
        foreach ($filterCollection as $filter) {

            $get = Input::get($filter['fieldID']);

            if($filter['fieldID'] == 'auto_page')
            {
                $get = $objPage->alias;
            }

            $get_operator = Input::get($filter['fieldID'] . '_int');

            if ($get && $get != '' || $get_operator && $get_operator != '') {

                if ($filter['active']) {

                    if ($filter['type'] == 'multi_choice' ) {

                        $get = explode(',', $get);
                    }

                    $filter['value'] = ($filter['overwrite'] ? $filter['value'] : $get);
                    $filter['operator'] = ($filter['overwrite'] ? $filter['operator'] : $get_operator);


                } else {

                    $filter['value'] = $get;
                    $filter['operator'] = $get_operator;

                }

            }

            if ($get || $filter['active']) {

                $input[] = $filter;

            }

        }

        // create queries
        $sqlQueriesArr = [];
        $searchQuery = '';
        $isFuzzy = false;
        foreach ($input as $query) {

            switch ($query['type']) {

                case 'simple_choice':

                    $sqlQueriesArr[] = $this->simpleChoiceQuery($query);
                    break;

                case 'date_field':

                    $sqlQueriesArr[] = $this->dateFieldQuery($query);
                    break;

                case 'search_field':

                    $sqlQueriesArr[] = $this->searchFieldQuery($query);
                    break;

                case 'multi_choice':

                    $sqlQueriesArr[] = $this->multiChoiceQuery($query);
                    break;

                case 'fulltext_search':
                    $searchQuery = $query['value'];
                    $isFuzzy = ( $query['isFuzzy'] == '1' ? true : false );
                    break;
            }

        }

        $sqlQueriesStr = implode('', $sqlQueriesArr);
        $sortingFields = implode(',', $sortingFields);

        $wrapperDB = $this->Database->prepare('SELECT addDetailPage, title, id, rootPage FROM ' . $tablename . ' WHERE id = ?')->execute($wrapperID)->row();
        $addDetailPage = $wrapperDB['addDetailPage'];
        $rootDB = $this->Database->prepare('SELECT * FROM ' . $tablename . ' JOIN tl_page ON tl_page.id = ' . $tablename . '.rootPage WHERE ' . $tablename . '.id = ?')->execute($wrapperID)->row();

        $listDB = $this->Database->prepare('SELECT * FROM ' . $tablename . '_data
        WHERE pid = ' . $wrapperID . '
        AND published = "1" ' . $sqlQueriesStr . '
        ORDER BY ' . $sortingFields . ' ' . $orderBy . '')->query();

        $strResults = '';
        $objTemplate = new \FrontendTemplate($this->f_list_template);

        $itemsArr = array();

        /**
         * search
         */
        $foundArr = array();
        if( $searchQuery != '' && $addDetailPage == '1' )
        {
            $search = Search::searchFor($searchQuery, false, array($wrapperDB['rootPage']), 0, 0, $isFuzzy);
            while($search->next())
            {
                $foundArr[$search->url] = $search->relevance;
            }
        }

        while ($listDB->next()) {

            // Gast und Gruppenrechte
            if ($this->sortOutProtected($listDB->row())) {
                continue;
            }

            // Von - Bis
            if (!$this->outSideScope($listDB->start, $listDB->stop)) {
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

            /**
             * search
             */
            if ( $searchQuery != '' && $addDetailPage == '1' ) {

                if (!$foundArr[$listDB->href]) {
                    continue;
                }

                $listDB->relevance = $foundArr[$listDB->href];

            }

            $itemsArr[] = $listDB->row();

        }

        /*
         * search
         */
        if ( $searchQuery != '' && $addDetailPage == '1' )
        {
            usort($itemsArr, array('ModuleListView', 'sortByRelevance'));
        }

        // pagination
        $total = count($itemsArr);
        $limit = $total;
        $offset = 0;

        if ($this->f_limit_page > 0) {
            $total = min($this->f_limit_page, $total);
            $limit = $total;
        }

        if ($this->f_perPage > 0) {
            $id = 'page_e' . $this->id;
            $page = (\Input::get($id) !== null) ? \Input::get($id) : 1;

            if ($page < 1 || $page > max(ceil($total / $this->f_perPage), 1)) {
                $objHandler = new $GLOBALS['TL_PTY']['error_404']();
                $objHandler->generate($objPage->id);
            }

            $offset = ($page - 1) * $this->f_perPage;
            $limit = min($this->f_perPage + $offset, $total);

            $objPagination = new \Pagination($total, $this->f_perPage, \Config::get('maxPaginationLinks'), $id);
            $this->Template->pagination = $objPagination->generate("\n  ");

        }

        //parse
        for ($i = $offset; $i < $limit; $i++) {

            $arrElements = array();
            $item = $itemsArr[$i];

            //get css and id
            $item['cssID'] = deserialize($item['cssID']);
            $item['itemID'] = $item['cssID'][0];
            $item['itemCSS'] = ' ' . $item['cssID'][1];

            //add more
            $item['more'] = $GLOBALS['TL_LANG']['MSC']['more'];

            // CTE Elemente
            $objCte = ContentModelExtend::findPublishedByPidAndTable($item['id'], $tablename . '_data', array('fview' => 'list'));

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
            $objTemplate->setData($item);

            //enclosure
            $objTemplate->enclosure = array();

            if ( $item['addEnclosure'] )
            {

                $this->addEnclosuresToTemplate($objTemplate, $item);
            }


            //add image
            if( $item['addImage'] )
            {
                $this->addImageToTemplate($objTemplate, $item);
            }



            $strResults .= $objTemplate->parse();

        }

        $this->Template->results = ($total < 1 ? '<p class="no-results">' . $GLOBALS['TL_LANG']['MSC']['noResult'] . '</p>' : $strResults);

    }

    /**
     * search
     */
    public function sortByRelevance($a, $b)
    {
        return $a['relevance'] <= $b['relevance'];
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

            return;

        }

        return;

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

    /**
     * @param $data
     * @return string
     */
    private function simpleChoiceQuery($data)
    {

        $operator = '=';

        if( $data['negate'] == '1' )
        {
            $operator = '!=';
        }

        return ' AND ' . $data['fieldID'] . ' '.$operator.' "' . $data['value'] . '"';
    }

    /**
     * @param $data
     * @return string
     */
    private function multiChoiceQuery($data)
    {

        $likeOperator = 'LIKE';

        if( $data['negate'] == '1' )
        {
            $likeOperator = 'NOT LIKE';
        }

        $sql = [];
        $operator = "AND (";
        $values = $data['value'];

        if(is_string($values))
        {
            $values = explode(',', $values);
        }

        if ( is_array($values) ) {

            if (count($values) <= 1) {
                $operator = "AND";
            }

            foreach ($values as $key => $value) {
                if ($key > 0) {
                    $operator = "OR";
                }

                $sql[] = ' ' . $operator . ' ' . $data['fieldID'] . ' '.$likeOperator.' "%' . $value . '%"';
            }

            $sql[] = (count($values) <= 1 ? '' : ')');

        }

        return implode('', $sql);
    }

    /**
     * @param $data
     * @return string
     */
    private function dateFieldQuery($data)
    {

        $v = $data['value'] == '' ? strtotime(date('Y-m-d')) : strtotime($data['value']);
        $operator = $this->getOperator($data['operator']);
        return ' AND ' . $data['fieldID'] . ' ' . $operator . ' ' . $v . '';

    }

    /**
     * @param $data
     * @return string
     */
    private function searchFieldQuery($data)
    {

        $operator = 'LIKE';

        if ($data['isInteger'] == '1' && $data['operator'] != '') {

            $operator = $this->getOperator( $data['operator'] );

        }

        $words = explode(' ', $data['value']);

        $sql = [];

        $query = "AND (";

        if (is_array($words)) {

            if (count($words) <= 1) {
                $query = "AND";
            }

            foreach ($words as $key => $value) {

                $v = $data['isInteger'] == '1' ? (int)$value : $value;

                if ($operator == 'LIKE') {
                    $v = '"%' . $v . '%"';
                }

                if ($key > 0) {

                    $query = "OR";
                }

                $sql[] = ' ' . $query . ' ' . $data['fieldID'] . ' ' . $operator . ' ' . $v . '';
            }

            $sql[] = (count($words) <= 1 ? '' : ')');

        }

        return implode('', $sql);
    }

    /**
     * @param $start
     * @param $stop
     * @return bool
     */
    public function outSideScope($start, $stop)
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
     * @param $item
     * @return bool
     */
    protected function sortOutProtected($item)
    {

        if (BE_USER_LOGGED_IN) {

            return false;

        }

        if (FE_USER_LOGGED_IN && $item['guests'] == '1') {

            return true;

        }

        if (FE_USER_LOGGED_IN && $item['protected'] == '1') {

            $groups = deserialize($item['groups']);

            if (!is_array($groups) || empty($groups) || count(array_intersect($groups, $this->User->groups)) < 1) {

                return true;

            }

        }

        return false;
    }


    protected function getOperator($str)
    {

        $return = '=';

        switch ($str) {

            case 'gte':
                $return = '>=';
                break;

            case 'gt':
                $return = '>';
                break;

            case 'lt':
                $return = '<';
                break;

            case 'lte':
                $return = '<=';
                break;
        }

        return $return;

    }

}