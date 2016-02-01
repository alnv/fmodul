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
        $bind = $query['negate'] ? '!=' : '==';
        return ' AND ' . $query['fieldID'] . ' ' . $bind . ' "' . $query['value'] . '"';
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


    static public function dateFieldQuery($query)
    {
        return 'd';
    }

    static public function searchFieldQuery($query)
    {
        return 'sf';
    }

    /**
     * @param $query
     * @return string
     */
    static public function toggleFieldQuery($query)
    {
        $bind = $query['negate'] ? '!=' : '==';
        return ' AND ' . $query['fieldID'] . ' ' . $bind . ' "1"';
    }

}