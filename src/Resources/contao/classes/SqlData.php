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

class SqlData
{

    //
    public static function insertColFilterInput($tablename, $colname)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " ADD " . $colname . " text NULL")->execute();
    }

    public static function renameColFilterInput($tablename, $oldcol, $newcol)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " CHANGE " . $oldcol . " " . $newcol . " text NULL")->execute();
    }

    //
    public static function insertColTogglefield($tablename, $colname)
    {
        Database::getInstance()->prepare("ALTER TABLE ". $tablename ." ADD ".$colname." char(1) NOT NULL default ''")->execute();
    }

    public static function renameColTogglefield($tablename, $oldcol, $newcol)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " CHANGE " . $oldcol . " " . $newcol . " char(1) NOT NULL default ''")->execute();
    }

    //
    public static function insertColSearchField($tablename, $colname)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " ADD " . $colname . " text NULL")->execute();
    }

    public static function renameColSearchField($tablename, $oldcol, $newcol)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " CHANGE " . $oldcol . " " . $newcol . " text NULL")->execute();
    }

    //
    public static function insertColDateField($tablename, $colname)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " ADD " . $colname . " int(10) unsigned NULL")->execute();
    }

    public static function renameColDateField($tablename, $oldcol, $newcol)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " CHANGE " . $oldcol . " " . $newcol . " int(10) unsigned NULL")->execute();
    }

    //
    public static function insertColSelectOptions($tablename, $colname)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " ADD " . $colname . " text NULL")->execute();
    }

    public static function renameColSelectOptions($tablename, $oldcol, $newcol)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " CHANGE " . $oldcol . " " . $newcol . " text NULL")->execute();
    }


    public static function deleteCol($tablename, $col)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " DROP COLUMN " . $col . "")->execute();
    }

}