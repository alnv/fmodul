<?php namespace FModule {

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

    /**
     *
     */
    class ContentModelExtend extends \Model
    {

        protected static $strTable = 'tl_content';

        public static function findPublishedByPidAndTable($intPid, $strParentTable, array $arrOptions = array())
        {
            $t = static::$strTable;

            if ($arrOptions['fview'] == 'list') {
                $arrColumns = array("$t.pid=? AND $t.ptable=? AND fview='list'");

            } else {

                $arrColumns = array("$t.pid=? AND $t.ptable=? AND fview='detail'");

            }

            if (!BE_USER_LOGGED_IN) {
                $time = \Date::floorToMinute();
                $arrColumns[] = "($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'" . ($time + 60) . "') AND $t.invisible=''";
            }

            if (!isset($arrOptions['order'])) {
                $arrOptions['order'] = "$t.sorting";
            }

            return static::findBy($arrColumns, array($intPid, $strParentTable), $arrOptions);
        }
    }
}