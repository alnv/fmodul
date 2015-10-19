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

/**
 *
 */
use Contao\Automator;
use Contao\Database;
use Contao\DcaLoader;
use Contao\Environment;
use Contao\Frontend;
use Contao\Input;
use Contao\tl_user_extend;
use Contao\tl_user_group_extend;

/**
 * Class FModule
 * @package FModule
 */
class FModule extends Frontend
{
    /**
     * @param $arrPages
     * @param int $intRoot
     * @param bool|false $blnIsSitemap
     */
    public function getSearchablePages($arrPages, $intRoot = 0, $blnIsSitemap = false)
    {

        $arrRoot = array();

        if ($intRoot > 0) {
            $arrRoot = $this->Database->getChildRecords($intRoot, 'tl_page');
        }

        $arrProcessed = array();
        $time = method_exists(Date, 'floorToMinute') ? \Date::floorToMinute() : time();
        $fmodulesDB = $this->Database->prepare('SELECT * FROM tl_fmodules')->execute();

        while ($fmodulesDB->next()) {

            $tablename = $fmodulesDB->tablename;
            $fmoduleDB = $this->Database->prepare('SELECT * FROM ' . $tablename . '')->execute();

            while ($fmoduleDB->next()) {

                $wrapper = $fmoduleDB->row();

                if (!is_array($wrapper) || empty($wrapper) || $wrapper['addDetailPage'] != '1') {
                    continue;
                }

                if (!empty($arrRoot) && !in_array($wrapper['rootPage'], $arrRoot)) {
                    continue;
                }

                if (!isset($arrProcessed[$wrapper['rootPage']])) {

                    $objParent = \PageModel::findWithDetails($wrapper['rootPage']);

                    if ($objParent === null) {
                        continue;
                    }

                    if (!$objParent->published || ($objParent->start != '' && $objParent->start > $time) || ($objParent->stop != '' && $objParent->stop <= ($time + 60))) {
                        continue;
                    }

                    if ($objParent->sitemap == 'map_never') {

                        continue;
                    }

                    $domain = ($objParent->rootUseSSL ? 'https://' : 'http://') . ($objParent->domain ?: \Environment::get('host')) . TL_PATH . '/';

                    $arrProcessed[$wrapper['rootPage']] = $domain . $this->generateFrontendUrl($objParent->row(), ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/%s' : '/items/%s'), $objParent->language);

                }

                $strUrl = $arrProcessed[$wrapper['rootPage']];
                $dataDB = $this->Database->prepare('SELECT * FROM ' . $tablename . '_data WHERE pid = ?')->execute($wrapper['id']);

                if ($dataDB->count() > 0) {
                    while ($dataDB->next()) {
                        $arrPages[] = $this->getLink($dataDB, $strUrl);
                    }
                }

            }

        }

        return $arrPages;

    }

    /**
     * @param $objItem
     * @param $strUrl
     * @param string $strBase
     * @return string
     * @throws \Exception
     */
    protected function getLink($objItem, $strUrl, $strBase = '')
    {
        switch ($objItem->source) {
            // Link to an external page
            case 'external':
                return $objItem->url;
                break;

            // Link to an internal page
            case 'internal':
                if (($objTarget = $objItem->getRelated('jumpTo')) !== null) {
                    return $strBase . $this->generateFrontendUrl($objTarget->row());
                }
                break;

            // Link to an article
            case 'article':
                if (($objArticle = \ArticleModel::findByPk($objItem->articleId, array('eager' => true))) !== null && ($objPid = $objArticle->getRelated('pid')) !== null) {
                    return $strBase . ampersand($this->generateFrontendUrl($objPid->row(), '/articles/' . ((!\Config::get('disableAlias') && $objArticle->alias != '') ? $objArticle->alias : $objArticle->id)));
                }
                break;
        }

        // Link to the default page
        return $strBase . sprintf($strUrl, (($objItem->alias != '' && !\Config::get('disableAlias')) ? $objItem->alias : $objItem->id));
    }

    /**
     * @param $objUser
     */
    public function setLanguage($objUser)
    {
        if (TL_MODE == 'BE') {
            $_SESSION['fm_language'] = $objUser->language;
        }
    }

    /**
     * @param $strTag
     * @return bool|string
     */
    public function fm_hooks($strTag)
    {

        $arrSplit = explode('::', $strTag);

        if (count($arrSplit) > 2 && $arrSplit[0] == 'fm_url') {
            return $this->getUrlFromItem($arrSplit);
        }

        return false;
    }


    public function createUserGroupDCA($strName)
    {

        if (TL_MODE == 'BE') {

            if( version_compare(VERSION, '4.0', '>=' ) )
            {
                DcaLoader::loadDataContainer('tl_user');
                DcaLoader::loadDataContainer('tl_user_group');
            }

            if ($strName == 'tl_user')
            {
                $this->createFModuleUserDCA();
            }
            if($strName == 'tl_user_group')
            {
                $this->createFModuleUserGroupDCA();
            }
        }
    }

    /**
     * @param $arrSplit
     * @return bool|string
     */
    private function getUrlFromItem($arrSplit)
    {

        if( $arrSplit[1] && $arrSplit[2] )
        {
            $tablename = $arrSplit[1];
            $tablename_data = $tablename.'_data';
            $id = $arrSplit[2];

            if( !$this->Database->tableExists($tablename) || !$this->Database->tableExists($tablename_data))
            {
                return false;
            }

            $dataDB = $this->Database->prepare('SELECT * FROM ' . $tablename_data . ' WHERE id = ?')->execute($id);

            if( $dataDB->count() < 1 )
            {
                return false;
            }

            $item = $dataDB->row();

            $pid = $item['pid'];

            $wrapperDB = $this->Database->prepare('SELECT * FROM ' . $tablename . ' WHERE id = ?')->execute($pid);

            if($wrapperDB->count() < 1)
            {
                return false;
            }

            $wrapper = $wrapperDB->row();

            if( $wrapper['addDetailPage'] != '1' )
            {
                return false;
            }

            $objParent = \PageModel::findWithDetails($wrapper['rootPage']);

            if ($objParent === null) {
                return false;
            }

            $domain = ($objParent->rootUseSSL ? 'https://' : 'http://') . ($objParent->domain ?: \Environment::get('host')) . TL_PATH . '/';

            $strUrl = $domain . $this->generateFrontendUrl($objParent->row(), ((\Config::get('useAutoItem') && !\Config::get('disableAlias')) ? '/%s' : '/items/%s'), $objParent->language);

            $url = $this->getLink($dataDB, $strUrl);

            return $url;

        }

        return false;

    }

    /**
     *
     */
    public function createFModuleUserGroupDCA()
    {


        if (!$this->Database->tableExists('tl_fmodules')) {
            return;
        }

        $fmodulesDB = $this->Database->prepare('SELECT * FROM tl_fmodules')->execute();

        while ($fmodulesDB->next()) {

            $cleanName = $fmodulesDB->name;
            $modname = substr($fmodulesDB->tablename, 3, strlen($fmodulesDB->tablename));

            $GLOBALS['TL_LANG']['tl_user_group'][$modname . '_legend'] = sprintf($GLOBALS['TL_LANG']['tl_user_group']['fm_dyn_legend'], $cleanName);

            $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('fmodulesp;', 'fmodulesp;{' . $modname . '_legend},' . $modname . ',' . $modname . 'p;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);

            $GLOBALS['TL_DCA']['tl_user_group']['fields'][$modname] = array(

                'label' => &$GLOBALS['TL_LANG']['tl_user_group']['fields']['select_wrapper'],
                'exclude' => false,
                'inputType' => 'checkbox',
                'foreignKey' => $fmodulesDB->tablename . '.title',
                'eval' => array('multiple' => true),
                'sql' => "blob NULL"

            );

            $GLOBALS['TL_DCA']['tl_user_group']['fields'][$modname . 'p'] = array
            (

                'label' => &$GLOBALS['TL_LANG']['tl_user_group']['fields']['select_fields'],
                'exclude' => false,
                'inputType' => 'checkbox',
                'options' => array('create', 'delete'),
                'reference' => &$GLOBALS['TL_LANG']['MSC'],
                'eval' => array('multiple' => true),
                'sql' => "blob NULL"

            );
        }
    }

    /**
     *
     */
    public function createFModuleUserDCA()
    {

        if (!$this->Database->tableExists('tl_fmodules')) {
            return;
        }

        $fmodulesDB = $this->Database->prepare('SELECT * FROM tl_fmodules')->execute();

        while ($fmodulesDB->next()) {

            $cleanName = $fmodulesDB->name;
            $modname = substr($fmodulesDB->tablename, 3, strlen($fmodulesDB->tablename));

            $GLOBALS['TL_LANG']['tl_user'][$modname . '_legend'] = sprintf($GLOBALS['TL_LANG']['tl_user']['fm_dyn_legend'], $cleanName);

            $GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('fmodulesp;', 'fmodulesp;{' . $modname . '_legend},' . $modname . ',' . $modname . 'p;', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
            $GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('fmodulesp;', 'fmodulesp;{' . $modname . '_legend},' . $modname . ',' . $modname . 'p;', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);

            $GLOBALS['TL_DCA']['tl_user']['fields'][$modname] = array(

                'label' => &$GLOBALS['TL_LANG']['tl_user']['fields']['select_wrapper'],
                'exclude' => false,
                'inputType' => 'checkbox',
                'foreignKey' => $fmodulesDB->tablename . '.title',
                'eval' => array('multiple' => true),
                'sql' => "blob NULL"

            );

            $GLOBALS['TL_DCA']['tl_user']['fields'][$modname . 'p'] = array
            (

                'label' => &$GLOBALS['TL_LANG']['tl_user']['fields']['select_fields'],
                'exclude' => false,
                'inputType' => 'checkbox',
                'options' => array('create', 'delete'),
                'reference' => &$GLOBALS['TL_LANG']['MSC'],
                'eval' => array('multiple' => true),
                'sql' => "blob NULL"

            );

        }
    }

    /**
     * @param $tablename
     * @param $fieldname
     * @param string $value
     * @param string $limit
     * @return array|void
     * @throws \Exception
     */
    public function getAutoCompleteAjax()
    {

        $tablename = Input::get('tablename');
        $fieldname = Input::get('fieldname');
        $pid = Input::get('pid');
        $value = Input::get('value');
        $limit = Input::get('limit') ? Input::get('limit') : '10';

        if( !strpos($tablename, '_data') && substr($tablename, 0, 3) != 'tl_'){
            $tablename = $tablename . '_data';
        }

        if (!$this->Database->tableExists($tablename)) {
            return;
        }

        $arrDB = $this->Database->prepare('SELECT ' . $fieldname . ' FROM ' . $tablename . ' WHERE ' . $fieldname . ' LIKE "%' . $value . '%" AND pid = "'.$pid.'" LIMIT ' . $limit . '')->query();
        $return = array();

        while ($arrDB->next()) {
            $return[] = $arrDB->row()[$fieldname];
        }

        echo json_encode($return);
        exit;

    }


    public function getAutoCompleteFromSearchField($tablename, $fieldname, $pid, $value = '')
    {

        if( !strpos($tablename, '_data') && substr($tablename, 0, 3) != 'tl_'){
            $tablename = $tablename . '_data';
        }

        if (!$this->Database->tableExists($tablename)) {
            return;
        }

        $valueQueryStr = '';
        if($value != '')
        {
            $valueQueryStr = ' AND '.$fieldname.' LIKE "%' . substr($value, 0, 3) . '%"';
        }

        $arrDB = $this->Database->prepare('SELECT ' . $fieldname . ' FROM ' . $tablename . ' WHERE pid = "'.$pid.'"'.$valueQueryStr.'')->query();
        $return = array();

        while ($arrDB->next()) {
            $return[] = $arrDB->row()[$fieldname];
        }

        return $return;
    }


}