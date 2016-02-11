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

class HelperModel
{
    /**
     * @return bool
     */
    public static function previewMode()
    {
        if (BE_USER_LOGGED_IN) {
            return true;
        }

        return false;
    }

    /**
     * @param $item
     * @return bool
     */
    public static function sortOutProtected($item, $allowedGroups)
    {

        if (BE_USER_LOGGED_IN) {
            return false;
        }

        if (FE_USER_LOGGED_IN && $item['guests'] == '1') {
            return true;
        }

        if (FE_USER_LOGGED_IN && $item['protected'] == '1') {

            $dataGroup= deserialize( $item['groups'] );
            if (!is_array($dataGroup) || empty($dataGroup) || count(array_intersect($dataGroup, $allowedGroups)) < 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $filterArr
     * @return array
     */
    public static function generateSQLQueryFromFilterArray($filterArr)
    {
        $qStr = '';
        $qTextSearch = '';
        $isFulltextSearch = false;

        foreach ($filterArr as $field) {

            if ($field['enable']) {
                switch ($field['type']) {
                    case 'simple_choice':
                        $qStr .= QueryModel::simpleChoiceQuery($field);
                        break;
                    case 'date_field':
                        $qStr .= QueryModel::dateFieldQuery($field);
                        break;
                    case 'search_field':
                        $qStr .= QueryModel::searchFieldQuery($field);
                        break;
                    case 'multi_choice':
                        $qStr .= QueryModel::multiChoiceQuery($field);
                        break;
                    case 'toggle_field':
                        $qStr .= QueryModel::toggleFieldQuery($field);
                        break;
                    case 'fulltext_search':
                        $isValue = QueryModel::isValue($field['value']);
                        if ($isValue) {
                            $isFulltextSearch = true;
                            $qTextSearch = $field['value'];
                        }
                        break;
                }
            }
        }

        return array(
            'qStr' => $qStr,
            'isFulltextSearch' => $isFulltextSearch,
            '$qTextSearch' => $qTextSearch,
        );
    }

    /**
     * @param $start
     * @param $stop
     * @return bool
     */
    static public function outSideScope($start, $stop)
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

}