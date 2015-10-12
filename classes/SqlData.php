<?php namespace FModule;

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2015 Leo Feyer
 *
 * @package   F Modul
 * @author    Alexander Naumov http://www.alexandernaumov.de
 * @license   GNU GENERAL PUBLIC LICENSE
 * @copyright 2015 Alexander Naumov
 */

use Contao\Database;

class SqlData
{

    //
    public static function insertColFilterInput($tablename, $colname)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " ADD " . $colname . " blob NULL")->execute();
    }

    public static function renameColFilterInput($tablename, $oldcol, $newcol)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " CHANGE " . $oldcol . " " . $newcol . " blob NULL")->execute();
    }

    //
    public static function insertColSearchField($tablename, $colname)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " ADD " . $colname . " mediumtext NOT NULL default ''")->execute();
    }

    public static function renameColSearchField($tablename, $oldcol, $newcol)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " CHANGE " . $oldcol . " " . $newcol . " mediumtext NOT NULL default ''")->execute();
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
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " ADD " . $colname . " blob NULL")->execute();
    }

    public static function renameColSelectOptions($tablename, $oldcol, $newcol)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " CHANGE " . $oldcol . " " . $newcol . " blob NULL")->execute();
    }


    public static function deleteCol($tablename, $col)
    {
        Database::getInstance()->prepare("ALTER TABLE " . $tablename . " DROP COLUMN " . $col . "")->execute();
    }

}