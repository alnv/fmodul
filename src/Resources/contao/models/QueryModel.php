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
use Contao\Input;
use Contao\Model;

/**
 * Class QueryModel
 * @package FModule
 */
class QueryModel
{

    /**
     * @var string
     */
    static public $strTaxonomyQuery = '';

    /**
     * @var array
     */
    static public $arrTaxonomiesFields = array();

    /**
     * @param $query
     * @return string
     */
    static public function setupTaxonomyFieldQueryArray($query)
    {
        if($query['dataFromTaxonomy'] == '1')
        {
            static::$arrTaxonomiesFields['arrSpecie'][] = $query;
        }

        if($query['reactToTaxonomy'] == '1')
        {
            static::$arrTaxonomiesFields['arrTags'][] = $query;
        }
    }

    /**
     *
     */
    static public function taxonomyFieldQueryBuilder()
    {
        foreach (static::$arrTaxonomiesFields as $type => $arrTaxonomies)
        {

            // specie single
            if($type == 'arrSpecie' && count($arrTaxonomies) == 1)
            {
                foreach ($arrTaxonomies as $intIndex => $arrTaxonomy)
                {
                    $strQuery = static::simpleChoiceQuery($arrTaxonomy);
                    if($strQuery)
                    {
                        static::$strTaxonomyQuery .= $strQuery;
                    }
                }
            }

            // species multiple
            if($type == 'arrSpecie' && count($arrTaxonomies) > 1)
            {
                $strQuery = ' AND (';

                foreach ($arrTaxonomies as $intIndex => $arrTaxonomy)
                {
                    $strBind = $arrTaxonomy['negate'] ? '!=' : '=';
                    $strOperator = '';
                    if($intIndex > 0)
                    {
                        $strOperator = 'OR';
                    }
                    $strQuery .= ' '.$strOperator.' ' . $arrTaxonomy['fieldID'] . ' ' . $strBind . ' "' . $arrTaxonomy['value'] . '"';
                }

                $strQuery .= ')';

                static::$strTaxonomyQuery .= $strQuery;
            }

            // tags multiple
            if($type == 'arrTags' && count($arrTaxonomies) > 1)
            {
                $strQuery = ' AND (';
                $strOperator = '';
                foreach ($arrTaxonomies as $intIndex => $arrTaxonomy)
                {
                    $arrSubQueries = array();

                    if($intIndex > 0)
                    {
                        $strOperator = ' OR';
                    }

                    //
                    $arrValues = $arrTaxonomy['value'];
                    $strLike = $arrTaxonomy['negate'] ? 'NOT LIKE' : 'LIKE';
                    $strBind = '';
                    if(!is_array($arrValues))
                    {
                        $arrValues = explode(',', $arrValues);
                    }

                    if($strOperator)
                    {
                        $arrSubQueries[] = $strOperator;
                    }

                    foreach ($arrValues as $intNum => $value) {

                        if ($intNum > 0) {
                            $strBind = 'OR';
                        }

                        $arrSubQueries[] = $strBind.' '.$arrTaxonomy['fieldID'] . ' ' . $strLike . ' "%' . $value . '%"';
                    }
                    $strQuery .= implode('', $arrSubQueries);
                }

                $strQuery .= ')';
                static::$strTaxonomyQuery .= $strQuery;
            }

            // tags single
            if($type == 'arrTags' && count($arrTaxonomies) == 1)
            {
                foreach ($arrTaxonomies as $intIndex => $arrTaxonomy)
                {
                    $strQuery = static::multiChoiceQuery($arrTaxonomy);
                    if($strQuery)
                    {
                        static::$strTaxonomyQuery .= $strQuery;
                    }
                }
            }
        }
    }

    /**
     * @param $arrQuery
     * @return string
     */
    static public function multiChoiceQuery($arrQuery)
    {
        $strLike = $arrQuery['negate'] ? 'NOT LIKE' : 'LIKE';
        $arrValues = $arrQuery['value'];
        $strBind = ' AND (';
        $arrSql = array();
        
        if (!is_array($arrValues)) {
            $arrValues = explode(',', $arrValues);
        }

        if (count($arrValues) == 1) {
            $strBind = 'AND';
        }

        foreach ($arrValues as $intIndex => $value) {

            if ($intIndex > 0) {
                
                $strBind = 'OR';
            }
            
            if ( $arrQuery['negate'] ) {

                $arrSql[] = ' ' . $strBind . ' ' . $arrQuery['fieldID'] . ' ' . $strLike . ' "%' . $value . '%"';
            }
            
            else {

                $arrSql[] = ' ' . $strBind . ' ' . sprintf( 'FIND_IN_SET( "%s", LOWER(CAST( %s AS CHAR)))',$value, $arrQuery['fieldID'] );
            }
        }

        $arrSql[] = (count($arrValues) <= 1 ? '' : ' )');

        return implode('', $arrSql);
    }

    /**
     * @param $arrQuery
     * @return string
     */
    static public function simpleChoiceQuery($arrQuery)
    {
        $strBind = $arrQuery['negate'] ? '!=' : '=';
        return ' AND ' . $arrQuery['fieldID'] . ' ' . $strBind . ' "' . $arrQuery['value'] . '"';
    }

    /**
     * @param $value
     * @param $type
     * @return bool
     */
    static public function isValue($value, $type = 'undefined')
    {

        if ($value && is_string($value)) {
            $str = trim($value);
            if ($str) {
                return true;
            }
        }

        if ($value && is_array($value)) {
            $arr = $value[0];
            if ($arr) {
                return true;
            }
        }

        return false;

    }

    /**
     * @param $query
     * @return string
     */
    static public function dateFieldQuery($query)
    {
        global $objPage;

        $format = $query['addTime'] ? $objPage->datimFormat : $objPage->dateFormat;
        $unix = strtotime($query['value']);

        if ($unix == false) {
            return '';
        }

        //wrapper
        $btw = Input::get($query['fieldID'] . '_btw') ? Input::get($query['fieldID'] . '_btw') : '';
        $value = $query['value'] == '' ? strtotime(date($format)) : $unix;

        if ($btw) {
            $fromValue = $value;
            $toValue = strtotime($btw);

            if ($toValue == false) {
                return '';
            }

            return ' AND ' . $query['fieldID'] . ' BETWEEN ' . $fromValue . ' AND ' . $toValue . '';
        }

        $bind = static::getOperator($query['operator']);

        return ' AND ' . $query['fieldID'] . ' ' . $bind . ' ' . $value . '';
    }

    /**
     * @param $query
     * @return string
     */
    static public function searchFieldQuery($query)
    {

        $bind = 'LIKE';
        $searchValue = $query['value'];
        $isNum = false;

        //wrapper
        $btw = Input::get($query['fieldID'] . '_btw') ? Input::get($query['fieldID'] . '_btw') : '';

        if ($btw) {
            $fromValue = (float)$searchValue;
            $toValue = (float)$btw;
            return ' AND ' . $query['fieldID'] . ' BETWEEN ' . $fromValue . ' AND ' . $toValue . '';
        }

        if ($query['isInteger'] == '1' && $query['operator'] != '' && is_numeric($searchValue)) {

            $bind = static::getOperator($query['operator']);
            $searchValue = (float)$searchValue;
            $isNum = true;
        }

        if (!$isNum) {

            $likeValue = '"%' . $searchValue . '%"';
            return ' AND (' . $query['fieldID'] . ' LIKE ' . $likeValue . ' OR ' . $query['fieldID'] . ' = "' . $searchValue . '")';

        }

        return ' AND ' . $query['fieldID'] . ' ' . $bind . ' ' . $searchValue . '';
    }

    /**
     * @param $query
     * @return string
     */
    static public function toggleFieldQuery($query)
    {
        $bind = $query['value'] != 'skip' ? '=' : '!=';
        return ' AND ' . $query['fieldID'] . ' ' . $bind . ' "1"';
    }

    /**
     * @param string $searchStr
     * @param $tablename
     * @param $wrapperID
     * @param $searchSettings
     * @return \Database\Result|object
     */
    static public function textSearch($searchStr = '', $tablename, $wrapperID, $searchSettings)
    {
        // get fields for q
        $fieldsStr = 'title,description';
        $orderByStr = 'title';

        if (!empty($searchSettings)) {
            $fieldsStr = $searchSettings['fields'] ? $searchSettings['fields'] : $fieldsStr;
            $orderByStr = $searchSettings['orderBy'] ? $searchSettings['orderBy'] : $orderByStr;
        }

        $query = '';
        $fieldsArr = explode(',', $fieldsStr);
        $prepareValue = array($wrapperID);

        if (is_array($fieldsArr)) {
            foreach ($fieldsArr as $n => $field) {
                $operator = $n != 0 ? ' OR ' : ' AND ';
                $query .= $operator . $field . ' LIKE ?';
                $prepareValue[] = "%$searchStr%";
            }
        }

        $searchDB = Database::getInstance();
        $sqlStr = "SELECT * FROM " . $tablename . "_data WHERE pid = ? ";
        $sqlStr .= $query;
        $sqlStr .= " ORDER BY "
            . "CASE "
            . "WHEN (LOCATE(?, title) = 0) THEN 10 "
            . "WHEN " . $orderByStr . " = ? THEN 1 "
            . "WHEN " . $orderByStr . " LIKE ? THEN 2 "
            . "WHEN " . $orderByStr . " LIKE ? THEN 3 "
            . "WHEN " . $orderByStr . " LIKE ? THEN 4 "
            . "WHEN " . $orderByStr . " LIKE ? THEN 5 "
            . "ELSE 6 "
            . "END ";

        $prepareValue[] = $searchStr; // Kein Match
        $prepareValue[] = $searchStr; // Genau
        $prepareValue[] = "$searchStr %"; // Passender Match
        $prepareValue[] = "%$searchStr"; // Anfang
        $prepareValue[] = "$searchStr%"; // Ende
        $prepareValue[] = "%$searchStr%"; // Irgendwo

        return $searchDB->prepare($sqlStr)->execute($prepareValue);
    }

    /**
     * @param string $searchStr
     * @param $tablename
     * @param $wrapperID
     * @param array $searchSettings
     * @return array
     */
    static public function getTextSearchResult($searchStr = '', $tablename, $wrapperID, $searchSettings = array())
    {
        $resultsDB = static::textSearch($searchStr, $tablename, $wrapperID, $searchSettings);
        $results = array();
        if ($resultsDB != null && $resultsDB->count() > 0) {
            while ($resultsDB->next()) {
                $results[$resultsDB->id] = $resultsDB->alias;
            }
        }
        return $results;
    }

    /**
     * @param $str
     * @return string
     */
    static public function getOperator($str)
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