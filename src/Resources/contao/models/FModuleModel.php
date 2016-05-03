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

/**
 * Class FModuleModel
 * @package FModule
 */
abstract class FModuleModel extends \Contao\Model
{
    /**
     * Table name
     * @var string
     */
    protected static $strTable = '';


    /**
     * @param $tablename
     */
    public static function setTable($tablename)
    {
        static::$strTable = $tablename;
    }

    /**
     * @param \Contao\Database\Result $objResult
     * @param string $strTable
     * @return \Contao\Database\Result
     */
    protected static function createCollectionFromDbResult(\Contao\Database\Result $objResult, $strTable)
    {
        return $objResult;
    }

}