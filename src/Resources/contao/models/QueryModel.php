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
     * @param $query
     * @return string
     */
    static public function simpleChoiceQuery($query)
    {
        $bind = $query['negate'] ? '!=' : '=';
        return ' AND ' . $query['fieldID'] . ' ' . $bind . ' "' . $query['value'] . '"';
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
    static public function multiChoiceQuery($query)
    {

        $like = $query['negate'] ? 'NOT LIKE' : 'LIKE';
        $values = $query['value'];
        $bind = ' AND (';
        $sql = [];

        if (is_string($values)) {
            $values = explode(',', $values);
        }

        if (is_array($values)) {

            if (count($values) == 1) {
                $bind = 'AND';
            }

            foreach ($values as $n => $value) {
                if ($n > 0) {
                    $bind = 'OR';
                }

                $sql[] = ' ' . $bind . ' ' . $query['fieldID'] . ' ' . $like . ' "%' . $value . '%"';

            }

            $sql[] = (count($values) <= 1 ? '' : ' )');
        }

        return implode('', $sql);

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
            return ' AND ' . $query['fieldID'] . ' LIKE ' . $likeValue . ' OR ' . $query['fieldID'] . ' = "' . $searchValue . '"';

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