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

use Contao\Database;
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

        $value = $query['value'] == '' ? strtotime(date($format)) : $unix;
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

        if ($query['isInteger'] == '1' && $query['operator'] != '' && is_numeric($searchValue)) {

            $bind = static::getOperator($query['operator']);
            $searchValue = (int)$searchValue;
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
     * @return \Database\Result|object
     */
    static public function textSearch($searchStr = '', $tablename, $wrapperID)
    {
        $searchDB = Database::getInstance();
        $sqlStr = "SELECT * FROM " . $tablename . "_data WHERE pid = ? ";
        $sqlStr .= " AND description LIKE ? OR title LIKE ? ORDER BY "
            . "CASE "
            . "WHEN (LOCATE(?, title) = 0) THEN 10 "  // 1 "Köl" matches "Kolka" -> sort it away
            . "WHEN title = ? THEN 1 "                // 2 "word" Sortier genaue Matches nach oben ( Berlin vor Berlingen für "Berlin")
            . "WHEN title LIKE ? THEN 2 "             // 3 "word "    Sortier passende Matches nach oben ( "Berlin Spandau" vor Berlingen für "Berlin")
            . "WHEN title LIKE ? THEN 3 "             // 4 "word%"    Sortier Anfang passt
            . "WHEN title LIKE ? THEN 4 "             // 4 "%word"    Sortier Ende passt
            . "WHEN title LIKE ? THEN 5 "             // 5 "%word%"   Irgendwo getroffen
            . "ELSE 6 "
            . "END ";
        return $searchDB->prepare($sqlStr)->execute($wrapperID, "%$searchStr%", "%$searchStr%", $searchStr, $searchStr, "$searchStr %", "%$searchStr", "$searchStr%", "%$searchStr%");
    }

    /**
     * @param string $searchStr
     * @param $tablename
     * @param $wrapperID
     * @return array
     */
    static public function getTextSearchResult($searchStr = '', $tablename, $wrapperID)
    {
        $resultsDB = static::textSearch($searchStr, $tablename, $wrapperID);
        $results = array();
        if( $resultsDB != null && $resultsDB->count() > 0 )
        {
            while($resultsDB->next())
            {
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