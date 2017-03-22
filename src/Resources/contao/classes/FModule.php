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
use Contao\Date;
use Contao\Environment;
use Contao\File;
use Contao\Frontend;
use Contao\Input;
use Contao\Model;
use Contao\PageModel;
use Contao\Config;

/**
 * Class FModule
 * @package FModule
 */
class FModule extends Frontend
{


    public function generateFeed($table)
    {
        $objFeed = $this->Database->prepare('SELECT * FROM tl_fmodules_feed WHERE fmodule = ?')->execute($table);

        if ($objFeed === null) return;

        $objFeed->feedName = $objFeed->alias ?: 'fmodules' . $objFeed->id;

        // Delete XML file
        if (\Input::get('act') == 'delete') {

            $this->import('Files');
            $this->Files->delete($objFeed->feedName . '.xml');

        } // Update XML file

        else {

            $this->generateFiles($objFeed->row());
            $this->log('Generated F Module feed "' . $objFeed->feedName . '.xml"', __METHOD__, TL_CRON);
        }
    }


    public function generateFeeds()
    {
        $this->import('Automator');
        $this->Automator->purgeXmlFiles();
        $objFeed = $this->Database->prepare('SELECT * FROM tl_fmodules_feed')->execute();

        if ($objFeed !== null) {

            while ($objFeed->next()) {

                $objFeed->feedName = $objFeed->alias ?: 'fmodules' . $objFeed->id;
                $this->generateFiles($objFeed->row());
                $this->log('Generated F Module feed "' . $objFeed->feedName . '.xml"', __METHOD__, TL_CRON);
            }
        }
    }


    public function generateFeedsByArchive($table)
    {
        $objFeed = $this->Database->prepare('SELECT * FROM tl_fmodules_feed WHERE fmodule = ?')->execute($table);

        if ($objFeed !== null) {

            while ($objFeed->next()) {

                $objFeed->feedName = $objFeed->alias ?: 'fmodules' . $objFeed->id;
                
                // Update the XML file
                $this->generateFiles($objFeed->row());
                $this->log('Generated F Module feed "' . $objFeed->feedName . '.xml"', __METHOD__, TL_CRON);
            }
        }
    }


    protected function generateFiles($arrFeed)
    {
        $arrArchives = deserialize($arrFeed['wrappers']);

        if (!is_array($arrArchives) || empty($arrArchives)) return null;

        $strType = ($arrFeed['format'] == 'atom') ? 'generateAtom' : 'generateRss';
        $strLink = $arrFeed['feedBase'] ?: Environment::get('base');
        $strFile = $arrFeed['feedName'];
        $objFeed = new \Feed($strFile);
        $objFeed->link = $strLink;
        $objFeed->title = $arrFeed['title'];
        $objFeed->description = $arrFeed['description'];
        $objFeed->language = $arrFeed['language'];
        $objFeed->published = $arrFeed['tstamp'];

        if ($arrFeed['maxItems'] > 0) {
            $objArticle = $this->findPublishedByPids($arrArchives, $arrFeed['maxItems'], $arrFeed['fmodule'] . '_data');
        } else {
            $objArticle = $this->findPublishedByPids($arrArchives, 0, $arrFeed['fmodule'] . '_data');
        }

        if ($objArticle !== null) {
            $arrUrls = array();
            $strUrl = '';
            while ($objArticle->next()) {
                $pid = $objArticle->pid;
                $wrapperDB = $this->Database->prepare('SELECT * FROM ' . $arrFeed['fmodule'] . ' WHERE id = ?')->execute($pid)->row();
                if ($wrapperDB['addDetailPage'] == '1') {
                    $rootPage = $wrapperDB['rootPage'];
                    if (!isset($arrUrls[$rootPage])) {
                        $objParent = PageModel::findWithDetails($rootPage);
                        if ($objParent === null) {
                            $arrUrls[$rootPage] = false;
                        } else {
                            $arrUrls[$rootPage] = $this->generateFrontendUrl($objParent->row(), ((Config::get('useAutoItem') && !Config::get('disableAlias')) ? '/%s' : '/items/%s'), $objParent->language);
                        }
                    }
                    $strUrl = $arrUrls[$rootPage];
                }
                $authorName = '';
                if ($objArticle->author) {
                    $authorDB = $this->Database->prepare('SELECT * FROM tl_user WHERE id = ?')->execute($objArticle->author)->row();
                    $authorName = $authorDB['name'];
                }
                $objItem = new \FeedItem();
                $objItem->title = $objArticle->title;

                $objItem->link = HelperModel::getLink($objArticle, $strUrl, $strLink);

                $objItem->published = $objArticle->date ? $objArticle->date : $arrFeed['tstamp'];
                $objItem->author = $authorName;

                // Prepare the description
                if ($arrFeed['source'] == 'source_text') {
                    $strDescription = '';
                    $objElement = ContentModelExtend::findPublishedByPidAndTable($objArticle->id, $arrFeed['fmodule'] . '_data', array('fview' => 'detail'));
                    if ($objElement !== null) {
                        // Overwrite the request (see #7756)
                        $strRequest = Environment::get('request');
                        Environment::set('request', $objItem->link);
                        while ($objElement->next()) {
                            $strDescription .= $this->getContentElement($objElement->current());
                        }
                        Environment::set('request', $strRequest);
                    }
                } else {
                    $strDescription = '';
                    $objElement = ContentModelExtend::findPublishedByPidAndTable($objArticle->id, $arrFeed['fmodule'] . '_data', array('fview' => 'list'));

                    if ($objElement !== null) {
                        // Overwrite the request (see #7756)
                        $strRequest = Environment::get('request');
                        Environment::set('request', $objItem->link);
                        while ($objElement->next()) {
                            $strDescription .= $this->getContentElement($objElement->current());
                        }
                        Environment::set('request', $strRequest);
                    }

                    if (!$strDescription) {
                        $strDescription = $objArticle->description;
                    }
                }

                $strDescription = $this->replaceInsertTags($strDescription, false);
                $objItem->description = $this->convertRelativeUrls($strDescription, $strLink);

                // Add the article image as enclosure
                if ($objArticle->addImage) {
                    $objFile = \FilesModel::findByUuid($objArticle->singleSRC);
                    if ($objFile !== null) {
                        $objItem->addEnclosure($objFile->path);
                    }
                }

                // Enclosures
                if ($objArticle->addEnclosure) {
                    $arrEnclosure = deserialize($objArticle->enclosure, true);
                    if (is_array($arrEnclosure)) {
                        $objFile = \FilesModel::findMultipleByUuids($arrEnclosure);
                        if ($objFile !== null) {
                            while ($objFile->next()) {
                                $objItem->addEnclosure($objFile->path);
                            }
                        }
                    }
                }
                $objFeed->addItem($objItem);
            }
        }
        File::putContent('share/' . $strFile . '.xml', $this->replaceInsertTags($objFeed->$strType(), false));
    }


    public function findPublishedByPids($arrPids, $intLimit = 0, $tablename)
    {
        if (!is_array($arrPids) || empty($arrPids)) return null;
        if (!$tablename || $tablename == 'no-value') return null;
        $sql = 'SELECT * FROM ' . $tablename . ' WHERE ';
        $sql .= '' . $tablename . '.pid IN(' . implode(',', array_map('intval', $arrPids)) . ') ';
        if (!BE_USER_LOGGED_IN || TL_MODE == 'BE') {
            $time = Date::floorToMinute();
            $sql .= 'AND (' . $tablename . '.start="" OR ' . $tablename . '.start <= ' . $time . ') AND (' . $tablename . '.stop="" OR ' . $tablename . '.stop > ' . ($time + 60) . ') AND ' . $tablename . '.published = "1" ';
        }
        $sql .= 'ORDER BY ' . $tablename . '.date DESC ';
        if ($intLimit > 0) {
            $sql .= 'LIMIT ' . $intLimit . '';
        }
        $findBy = $this->Database->prepare($sql)->execute();
        return $findBy;
    }


    public function purgeOldFeeds()
    {
        $arrFeeds = array();
        $objFeeds = $this->Database->prepare('SELECT * FROM tl_fmodules_feed')->execute();

        if ($objFeeds !== null) {
            while ($objFeeds->next()) {
                $arrFeeds[] = $objFeeds->alias ?: 'fmodules' . $objFeeds->id;
            }
        }

        return $arrFeeds;
    }


    public function getSearchablePages( $arrPages, $intRoot = 0, $blnIsSitemap = false )
    {
        $arrRoot = [];

        if ($intRoot > 0) {

            $arrRoot = $this->Database->getChildRecords( $intRoot, 'tl_page' );
        }

        $dteTime = method_exists( Date, 'floorToMinute' ) ? \Date::floorToMinute() : time();
        $objModules = $this->Database->prepare( 'SELECT * FROM tl_module WHERE type = ?' )->execute( 'fmodule_fe_detail' );

        if ( !$objModules->numRows ) return $arrPages;

        while ( $objModules->next() ) {

            if ( !$objModules->f_list_field ) continue;

            $objListView = $this->Database->prepare( 'SELECT * FROM tl_module WHERE id = ?' )->limit(1)->execute( $objModules->f_list_field );

            $strModule = $objListView->f_select_module;
            $strWrapperID = $objListView->f_select_wrapper;
            $strMasterPageID = $objModules->fm_addMasterPage ? $objModules->fm_masterPage : '';

            if ( !$strModule || !$strWrapperID ) continue;

            if ( !$strMasterPageID ) {

                if ( $this->Database->tableExists( $strModule ) ) {

                    $objWrapper = $this->Database->prepare( sprintf( 'SELECT * FROM %s WHERE id = ? AND addDetailPage = ?', $strModule ) )->limit(1)->execute( $strWrapperID, '1' );
                    $strMasterPageID = $objWrapper->rootPage ? $objWrapper->rootPage : '';
                }
            }

            if ( !$strMasterPageID ) continue;

            if ( !empty( $arrRoot ) && !in_array( $strMasterPageID, $arrRoot ) ) continue;

            if ( !isset( $arrProcessed[ $strMasterPageID ] ) ) {

                $objParent = \PageModel::findWithDetails( $strMasterPageID );

                if ($objParent === null) continue;

                if ( !$objParent->published || ( $objParent->start != '' && $objParent->start > $dteTime ) || ( $objParent->stop != '' && $objParent->stop <= ( $dteTime + 60 ) ) ) {

                    continue;
                }

                if ( $objParent->sitemap == 'map_never' ) continue;

                $strDomain = ( $objParent->rootUseSSL ? 'https://' : 'http://' ) . ( $objParent->domain ?: \Environment::get('host') ) . TL_PATH . '/';
                $arrProcessed[ $strMasterPageID ] = $strDomain . $this->generateFrontendUrl( $objParent->row(), ( (\Config::get('useAutoItem') && !\Config::get('disableAlias') ) ? '/%s' : '/items/%s' ), $objParent->language );
            }

            $strUrl = $arrProcessed[ $strMasterPageID ];
            $objEntities = $this->Database->prepare('SELECT * FROM ' . $strModule . '_data WHERE pid = ?')->execute( $strWrapperID );

            if ( !$objEntities->numRows ) continue;

            while ( $objEntities->next() ) {

                $arrPages[] = HelperModel::getLink( $objEntities, $strUrl );
            }
        }

        return $arrPages;
    }


    protected function getLink($objItem, $strUrl, $strBase = '')
    {
        // backwards
        return HelperModel::getLink($objItem, $strUrl, $strBase);
    }


    public function setLanguage($objUser)
    {
        if (TL_MODE == 'BE') {
            $_SESSION['fm_language'] = $objUser->language;
        }
    }


    public function createUserGroupDCA($strName)
    {
        if ($strName == 'tl_user') {
            $this->createFModuleUserDCA();
        }

        if ($strName == 'tl_user_group') {
            $this->createFModuleUserGroupDCA();
        }
    }


    public function createFModuleUserGroupDCA()
    {
        if (!$this->Database->tableExists('tl_fmodules')) return null;

        $fmodulesDB = $this->Database->prepare('SELECT * FROM tl_fmodules')->execute();

        while ($fmodulesDB->next()) {

            $cleanName = $fmodulesDB->name;
            $modname = substr($fmodulesDB->tablename, 3, strlen($fmodulesDB->tablename));

            if (!$this->permissionFieldExist($modname)) return null;

            $GLOBALS['TL_LANG']['tl_user_group'][$modname . '_legend'] = sprintf($GLOBALS['TL_LANG']['tl_user_group']['fm_dyn_legend'], $cleanName);
            $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default'] = str_replace('formp;', 'formp;{' . $modname . '_legend},' . $modname . ',' . $modname . 'p;', $GLOBALS['TL_DCA']['tl_user_group']['palettes']['default']);
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


    public function createFModuleUserDCA()
    {
        if (!$this->Database->tableExists('tl_fmodules')) return null;

        $fmodulesDB = $this->Database->prepare('SELECT * FROM tl_fmodules')->execute();

        while ($fmodulesDB->next()) {

            $cleanName = $fmodulesDB->name;
            $modname = substr($fmodulesDB->tablename, 3, strlen($fmodulesDB->tablename));

            if (!$this->permissionFieldExist($modname)) return null;

            $GLOBALS['TL_LANG']['tl_user'][$modname . '_legend'] = sprintf($GLOBALS['TL_LANG']['tl_user']['fm_dyn_legend'], $cleanName);
            $GLOBALS['TL_DCA']['tl_user']['palettes']['extend'] = str_replace('formp;', 'formp;{' . $modname . '_legend},' . $modname . ',' . $modname . 'p;', $GLOBALS['TL_DCA']['tl_user']['palettes']['extend']);
            $GLOBALS['TL_DCA']['tl_user']['palettes']['custom'] = str_replace('formp;', 'formp;{' . $modname . '_legend},' . $modname . ',' . $modname . 'p;', $GLOBALS['TL_DCA']['tl_user']['palettes']['custom']);
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


    private function permissionFieldExist($fieldname)
    {
        if (!$this->Database->fieldExists($fieldname, 'tl_user') || !$this->Database->fieldExists($fieldname . 'p', 'tl_user')) {
            return false;
        }

        if (!$this->Database->fieldExists($fieldname, 'tl_user_group') || !$this->Database->fieldExists($fieldname . 'p', 'tl_user_group')) {
            return false;
        }

        return true;
    }


    public function getAutoCompleteAjax()
    {

        $tablename = Input::get('tablename');
        $fieldname = Input::get('fieldname');
        $pid = Input::get('pid');
        $value = Input::get('value');
        $limit = Input::get('limit') ? Input::get('limit') : '10';

        if (!strpos($tablename, '_data') && substr($tablename, 0, 3) != 'tl_') {
            $tablename = $tablename . '_data';
        }

        if (!$this->Database->tableExists($tablename)) {
            return;
        }

        $arrDB = $this->Database->prepare('SELECT ' . $fieldname . ' FROM ' . $tablename . ' WHERE ' . $fieldname . ' LIKE "%' . $value . '%" AND pid = "' . $pid . '" LIMIT ' . $limit . '')->query();
        $return = array();

        while ($arrDB->next()) {
            $return[] = $arrDB->row()[$fieldname];
        }

        echo json_encode($return);
        exit;
    }


    public function getAutoCompleteFromSearchField($tablename, $fieldname, $pid, $value = '')
    {

        if (!strpos($tablename, '_data') && substr($tablename, 0, 3) != 'tl_') {
            $tablename = $tablename . '_data';
        }

        if (!$this->Database->tableExists($tablename)) {
            return null;
        }

        $arrDB = $this->Database->prepare('SELECT ' . $fieldname . ' FROM ' . $tablename . ' WHERE pid = ?')->execute($pid);
        $return = array();

        while ($arrDB->next()) {
            $val = $arrDB->row()[$fieldname];
            if (!$val || $val == '' || $val == ' ') {
                continue;
            }
            $return[] = $val;
        }

        return array_unique($return);
    }
}