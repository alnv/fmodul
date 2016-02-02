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
    static public function isValue($value, $type)
    {

        if( $type == 'toggle_field' && $value == '0')
        {
            return true;
        }

        if( $value && is_string($value) )
        {
            $str = trim($value);

            if( $str )
            {
                return true;
            }

        }

        if( $value && is_array($value) )
        {
            $arr = $value[0];

            if( $arr )
            {
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

        if( is_array( $values ) )
        {

            if ( count($values) == 1 )
            {
                $bind = 'AND';
            }

            foreach( $values as $n => $value )
            {
                if( $n > 0)
                {
                    $bind = 'OR';
                }

                $sql[] = ' ' . $bind . ' ' . $query['fieldID'] . ' ' . $like . ' "%' . $value . '%"';

            }

            $sql[] = ( count($values) <= 1  ? '' : ' )' );
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

        $value = $query['value'] == '' ? strtotime( date($format) ) : $unix;
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

            $bind = static::getOperator( $query['operator'] );
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
        $bind = $query['value'] ? '=' : '!=';
        return ' AND ' . $query['fieldID'] . ' '.$bind.' "1"';
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