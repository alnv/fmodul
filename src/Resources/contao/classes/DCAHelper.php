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
use Contao\Backend;
use Contao\Image;
use Contao\Input;

/**
 * Class DCAHelper
 * @package FModule
 */
class DCAHelper extends Backend
{

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }

    /**
     * @param $field
     * @param $arrTable
     * @param string $wrapperID
     * @return array
     */
    public function getOptions($field, $arrTable, $wrapperID = '')
    {
        $options = array();
        $hasOptions = array('multi_choice', 'simple_choice', 'taxonomy_field');
        $table = '';

        if (is_array($arrTable)) {
            $table = $arrTable['tablename'];
        }

        if (is_string($arrTable)) {
            $table = $arrTable;
        }

        if (!$field['fieldID'] || !$table) {
            return $options;
        }

        $strDo = Input::get('do');
        $moduleName = substr($table, 3, strlen($table));
        if (TL_MODE == 'BE' && $strDo && $strDo != $moduleName) {
            return $options;
        }

        if (!in_array($field['type'], $hasOptions)) {
            return $options;
        }

        if ($field['fieldID'] == 'address_country') {
            return $this->getCountries();
        }

        // set data id or wrapper id
        $id = Input::get('id');
        if (Input::get('act') && Input::get('act') == 'editAll') $wrapperID = Input::get('id');
        if ($wrapperID) $id = $wrapperID;

        // create where query
        $subQuery = '';
        if ($id) {
            $subQuery = ' WHERE id = (SELECT pid FROM ' . $table . '_data WHERE id = "' . $id . '")';
        }
        if ($wrapperID) {
            $subQuery = ' WHERE id = "' . $wrapperID . '"';
        }

        $optionsDB = $this->Database->prepare('SELECT * FROM ' . $table . $subQuery)->execute();
        $option = array();

        while ($optionsDB->next()) {

            $arrOption = $optionsDB->row();

            $option = $optionsDB->row()[$field['fieldID']] ? deserialize($arrOption[$field['fieldID']]) : array();

            if (empty($option) && $field['dataFromTaxonomy'] == '1') $option = isset($arrOption['select_taxonomy_' . $field['fieldID']]) ? $arrOption['select_taxonomy_' . $field['fieldID']] : '';

            if ( empty($option) && $field['reactToTaxonomy'] == '1' && $field['reactToField'] ) {

                $option = isset($arrOption['select_taxonomy_' . $field['reactToField']]) ? $arrOption['select_taxonomy_' . $field['reactToField']] : '';
            }
        }

        // species
        if ( $field['dataFromTaxonomy'] == '1' && is_string( $option ) && $option ) {

            $speciesDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE pid = ? ORDER BY sorting')->execute($option);

            if ( !$speciesDB->numRows ) return $options;

            while ($speciesDB->next()) {

                if ( !$speciesDB->alias ) continue;

                $options[$speciesDB->alias] = $speciesDB->name ? $speciesDB->name : $speciesDB->alias;
            }

            return $options;
        }

        // tags
        if ( $field['reactToTaxonomy'] == '1' && $field['reactToField'] && is_string( $option ) && $option ) {

            $speciesDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE pid = ? ORDER BY sorting')->execute($option);

            if (!$speciesDB->count()) {

                return $options;
            }

            while ($speciesDB->next()) {

                if ( !$speciesDB->id ) continue;

                $objTags = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE pid = ? ORDER BY sorting')->execute( $speciesDB->id );

                if ( !$objTags->numRows ) continue;

                while ( $objTags->next() ) {

                    $options[$objTags->alias] = $objTags->name ? $objTags->name : $objTags->alias;
                }
            }

            return $options;
        }

        if ($field['dataFromTable'] == '1') {

            if (!$option['table']) {
                return $options;
            }

            if (!$this->Database->tableExists($option['table'])) {
                return $options;
            }

            if (!$option['col'] || !$option['title']) {
                return $options;
            }

            // create order by query
            // only for f modules tables
            $strOrderByQuery = $this->generateOrderByQuery($option['table']);
            $dataFromTableDB = $this->Database->prepare('SELECT ' . $option['col'] . ', ' . $option['title'] . ' FROM ' . $option['table'] . $strOrderByQuery)->execute(); // @todo where q mit pid hinzufügen

            while ($dataFromTableDB->next()) {
                $k = $dataFromTableDB->row()[$option['col']];
                $v = $dataFromTableDB->row()[$option['title']];
                $options[$k] = $v;
            }

            return $options;
        }

        if ( is_array( $option ) ) {
            
            foreach ( $option as $value ) {
            
                if ( !isset($value['value']) || !isset($value['value']) ) {

                    continue;
                }
            
                $options[$value['value']] = $value['label'];
            }
        }

        return $options;
    }

    /**
     * @param $strTable
     * @return string
     */
    private function generateOrderByQuery($strTable)
    {
        $strOrderByQuery = '';
        $strTablePrefix = substr($strTable, 0, 2);

        if ($strTablePrefix == 'fm') {
            $strTableSuffix = substr($strTable, -4);
            $strTableName = $strTable;
            
            if($strTableSuffix == 'data') {
                $intStartPos = count($strTable) - 1;
                $strTableName = substr($strTable, $intStartPos, -5);
            }

            $moduleDB = $this->Database->prepare('SELECT * FROM tl_fmodules WHERE tablename = ?')->limit(1)->execute($strTableName);

            // no table found return empty str
            if(!$moduleDB->count()) {
                return $strOrderByQuery;
            }

            $arrModule = $moduleDB->row();
            $arrSortingField = explode('.', $arrModule['sorting']);
            $strSortingField = is_array($arrSortingField) ? $arrSortingField[0] : 'id';
            $strOrderBy = $arrModule['orderBy'] ? strtoupper($arrModule['orderBy']) : 'DESC';

            // generate query
            $strOrderByQuery .= ' ORDER BY ' . $strSortingField . ' ' . $strOrderBy;
        }

        return $strOrderByQuery;
    }

    /**
     * @param $state
     * @param $label
     * @param $fieldID
     * @param bool $noHTML
     * @return string
     */
    public function getToggleIcon($state, $label, $fieldID, $noHTML = false)
    {
        $src = $state ? 'files/fmodule/assets/' . $fieldID . '.' : 'files/fmodule/assets/' . $fieldID . '_.';
        $temp = $state ? 'files/fmodule/assets/' . $fieldID . '_.' : 'files/fmodule/assets/' . $fieldID . '.';
        $allowedFormat = array('gif', 'png', 'svg');

        foreach ($allowedFormat as $format) {
            if (is_file(TL_ROOT . '/' . $src . $format) && !$noHTML) {
                return Image::getHtml($src . $format, $label, 'data-src="' . $temp . $format . '" data-state="' . ($state ? 1 : 0) . '"');
            }

            if (is_file(TL_ROOT . '/' . $src . $format) && $noHTML) {
                return $src . $format;
            }
        }

        $icon = $state ? 'featured.gif' : 'featured_.gif';
        $nIcon = $state ? 'featured_.gif' : 'featured.gif';
        $temp = 'system/themes/' . Backend::getTheme() . '/images/' . $nIcon;
        $src = 'system/themes/' . Backend::getTheme() . '/images/' . $icon;

        if ($noHTML) return $src;
        return Image::getHtml($src, $label, 'data-src="' . $temp . '" data-state="' . ($state ? 1 : 0) . '"');
    }

    /**
     * @param $fields
     * @return bool
     */
    public function isLegend($fields)
    {
        $legendsFound = 0;
        foreach ($fields as $field) {
            if ($field['type'] == 'legend_start' || $field['type'] == 'legend_end') {
                $legendsFound += 1;
            }
        }

        if ($legendsFound > 0 && $legendsFound % 2 == 0) {
            return true;
        }

        return false;
    }
}