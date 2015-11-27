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
use Contao\ContentModel;
use Contao\Database;
use Contao\Date;
use Contao\DcaLoader;
use Contao\Environment;
use Contao\FeedItem;
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
    /**
     * @var array
     */
    public $validSums = array(
        'ace73f5761f137a394516b1c4e4e2d9f',
        'ed34fd458b6bc5445f449118fb4e538c',
        '5b5739c14b95ed61acaad8470d9a127a',
        'b4694b9f70c86e72e77b730d5a99c66e',
        '88a60c2ab6be50b7de232bc63ea0eb57',
        '9171af5b32cd85d30992b37dc2ec7e04',
        '45648e4e598aa9526370e8a4c17b6be9',
        'fd9b1a37dd21c8d554deb27aa3986e75',
        'a633005b52284d23fc84d993670cb679',
        '7c386d224477717a261e1189ce732f1d',
        '61522559db862e895c1d99f5fe6b57bf',
        'dda5b1de99c476d3e8891ffce5a05bea',
        '9d815fe15d183e69e72d069a8d5f2700',
        'd12c7a51abfd6c11eadfad9bc16ea75d',
        '0c717d749710a9f81c01d7ce0eed5f84',
        'aa427e7a22231e8970e5d4d81a3ef193',
        'aa0ce817cd4a954e44cf7b55c3d47440',
        '2297d4d539db8cb975ebc6dc0a1e1e45',
        'dd1885c7d95d00343bb3edc3d8490563',
        '952a9a390aab497cd595dd2df576185c',
        'da1548a6c36aacff29cc5c8ccc3eab04',
        'dae0479dedb21ec54ac72a3e8d010025',
        '36d8a0457be757b18390d824bde23c6a',
        'af4846d50d1bf211dcfe7ff6d2bdc075',
        '5798e05453f4c36a0fc29fdbe3b5f712',
        '6545cf85edcebe8021454500f447ffe4',
        'ce54d8b93763d2bb0c740eafa27fa2c6',
        '96f66989b6b1aaf165b7ec9babdca73a',
        '0db41153e571f068c23d2bb8aa75cdc7',
        'e9bc100319609f53978b57a000826fd4',
        'dcaf3b7b556c50b9833be71f84de1745',
        'c60d176c44c566f7ccc7dc2954d7c81d',
        '6f897c8c297273784027285265ed37ee',
        'e56dfc9fcaaa275dda065e170e0334a8',
        '657b3fc877b61f6000a788dd84ade55e',
        '4eb3301c6ab1804170b91886177cf8ee',
        'b69ff655d208e345dccab9cdb2104b9e',
        'df60957c8ccf1e0c09da5f2971ae1c3f',
        'efa17853bd56a40d650f919542163eb1',
        'c06b0936ef1cfe5ef33b98766337fe28',
        'f118697ed80da1280982bc0f8d3de87a',
        '163d3a51d03f030b42907aedb555529f',
        '748248bcf59261ee660cd60d5a577f7b',
        '66e710a17d8f97547b3606ebf6a793b0',
        'c0482b203d6f8d5c11fcd6b80b170549',
        '5827b7b948dc7408bfef9685c016e800',
        '02d45cbbf7e47822d73ebf1801af7969',
        'e1bacf40933324da2fd7494ff92b8bb9',
        'bff143674c71fe88301cf48ec092bfec',
        '6063d5b265ea1bc0a714f5b957004868',
    );

    /**
     * @param $intId
     */
    public function generateFeed($table)
    {
        $objFeed = $this->Database->prepare('SELECT * FROM tl_fmodules_feed WHERE fmodule = ?')->execute($table);

        if ($objFeed === null)
        {
            return;
        }

        $objFeed->feedName = $objFeed->alias ?: 'fmodules' . $objFeed->id;

        // Delete XML file
        if (\Input::get('act') == 'delete')
        {
            $this->import('Files');
            $this->Files->delete($objFeed->feedName . '.xml');
        }

        // Update XML file
        else
        {
            $this->generateFiles($objFeed->row());
            $this->log('Generated F Module feed "' . $objFeed->feedName . '.xml"', __METHOD__, TL_CRON);
        }
    }

    /**
     *
     */
    public function generateFeeds()
    {
        $this->import('Automator');
        $this->Automator->purgeXmlFiles();

        $objFeed = $this->Database->prepare('SELECT * FROM tl_fmodules_feed')->execute();

        if ($objFeed !== null)
        {
            while ($objFeed->next())
            {
                $objFeed->feedName = $objFeed->alias ?: 'fmodules' . $objFeed->id;
                $this->generateFiles($objFeed->row());
                $this->log('Generated F Module feed "' . $objFeed->feedName . '.xml"', __METHOD__, TL_CRON);
            }
        }
    }

    /**
     * @param $intId
     */
    public function generateFeedsByArchive($table)
    {
        $objFeed = $this->Database->prepare('SELECT * FROM tl_fmodules_feed WHERE fmodule = ?')->execute($table);

        if ($objFeed !== null)
        {
            while ($objFeed->next())
            {
                $objFeed->feedName = $objFeed->alias ?: 'fmodules' . $objFeed->id;

                // Update the XML file
                $this->generateFiles($objFeed->row());
                $this->log('Generated F Module feed "' . $objFeed->feedName . '.xml"', __METHOD__, TL_CRON);
            }
        }
    }

    /**
     * @param $arrFeed
     */
    protected function generateFiles($arrFeed)
    {
        $arrArchives = deserialize($arrFeed['wrappers']);

        if (!is_array($arrArchives) || empty($arrArchives))
        {
            return;
        }

        $strType = ($arrFeed['format'] == 'atom') ? 'generateAtom' : 'generateRss';
        $strLink = $arrFeed['feedBase'] ?: Environment::get('base');
        $strFile = $arrFeed['feedName'];

        $objFeed = new \Feed($strFile);
        $objFeed->link = $strLink;
        $objFeed->title = $arrFeed['title'];
        $objFeed->description = $arrFeed['description'];
        $objFeed->language = $arrFeed['language'];
        $objFeed->published = $arrFeed['tstamp'];

        if ($arrFeed['maxItems'] > 0)
        {
            $objArticle = $this->findPublishedByPids($arrArchives, $arrFeed['maxItems'], $arrFeed['fmodule'].'_data');
        }
        else
        {
            $objArticle = $this->findPublishedByPids($arrArchives, 0, $arrFeed['fmodule'].'_data');
        }

        if ($objArticle !== null) {
            $arrUrls = array();

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
                            $arrUrls[$rootPage] = $this->generateFrontendUrl($objParent->row(), (( Config::get('useAutoItem') && ! Config::get('disableAlias')) ? '/%s' : '/items/%s'), $objParent->language);
                        }
                    }


                    $strUrl = $arrUrls[$rootPage];

                }
                $authorName = '';

                if($objArticle->author)
                {
                    $authorDB = $this->Database->prepare('SELECT * FROM tl_user WHERE id = ?')->execute($objArticle->author)->row();
                    $authorName = $authorDB['name'];
                }

                $objItem = new \FeedItem();

                $objItem->title = $objArticle->title;
                $objItem->link = $this->getLink($objArticle, $strUrl, $strLink);
                $objItem->published = $objArticle->date ?  $objArticle->date : $arrFeed['tstamp'];
                $objItem->author = $authorName;

                // Prepare the description
                if ($arrFeed['source'] == 'source_text')
                {
                    $strDescription = '';

                    $objElement = ContentModelExtend::findPublishedByPidAndTable($objArticle->id, $arrFeed['fmodule'].'_data', array('fview' => 'detail'));

                    if ($objElement !== null)
                    {
                        // Overwrite the request (see #7756)
                        $strRequest = Environment::get('request');

                        Environment::set('request', $objItem->link);

                        while ($objElement->next())
                        {
                            $strDescription .= $this->getContentElement($objElement->current());
                        }

                        Environment::set('request', $strRequest);
                    }
                }
                else
                {
                    $strDescription = '';

                    $objElement = ContentModelExtend::findPublishedByPidAndTable($objArticle->id, $arrFeed['fmodule'].'_data', array('fview' => 'list'));

                    if ($objElement !== null)
                    {
                        // Overwrite the request (see #7756)
                        $strRequest = Environment::get('request');

                        Environment::set('request', $objItem->link);

                        while ($objElement->next())
                        {
                            $strDescription .= $this->getContentElement($objElement->current());
                        }

                        Environment::set('request', $strRequest);
                    }

                    if(!$strDescription)
                    {
                        $strDescription = $objArticle->description;
                    }
                }

                $strDescription = $this->replaceInsertTags($strDescription, false);
                $objItem->description = $this->convertRelativeUrls($strDescription, $strLink);

                // Add the article image as enclosure
                if ($objArticle->addImage)
                {
                    $objFile = \FilesModel::findByUuid($objArticle->singleSRC);

                    if ($objFile !== null)
                    {
                        $objItem->addEnclosure($objFile->path);
                    }
                }

                // Enclosures
                if ($objArticle->addEnclosure)
                {
                    $arrEnclosure = deserialize($objArticle->enclosure, true);

                    if (is_array($arrEnclosure))
                    {
                        $objFile = \FilesModel::findMultipleByUuids($arrEnclosure);

                        if ($objFile !== null)
                        {
                            while ($objFile->next())
                            {
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

    public function findPublishedByPids($arrPids, $intLimit=0, $tablename)
    {

        if (!is_array($arrPids) || empty($arrPids)) {
            return null;
        }

        if (!$tablename || $tablename == 'no-value') {
            return null;
        }

        $sql = 'SELECT * FROM '.$tablename.' WHERE ';
        $sql .= ''.$tablename.'.pid IN('.implode(',', array_map('intval', $arrPids)).') ';

        if (!BE_USER_LOGGED_IN || TL_MODE == 'BE') {
            $time = Date::floorToMinute();
            $sql .= 'AND ('.$tablename.'.start="" OR '.$tablename.'.start <= '.$time.') AND ('.$tablename.'.stop="" OR '.$tablename.'.stop > '.($time + 60).') AND '.$tablename.'.published = "1" ';
        }

        $sql .= 'ORDER BY '.$tablename.'.date DESC ';

        if($intLimit > 0)
        {
            $sql .= 'LIMIT '.$intLimit.'';
        }

        $findBy = $this->Database->prepare($sql)->execute();

        return $findBy;

    }

    /**
     *
     */
    public function purgeOldFeeds()
    {
        $arrFeeds = array();
        $objFeeds = $this->Database->prepare('SELECT * FROM tl_fmodules_feed')->execute();

        if ($objFeeds !== null)
        {
            while ($objFeeds->next())
            {
                $arrFeeds[] = $objFeeds->alias ?: 'fmodules' . $objFeeds->id;
            }
        }

        return $arrFeeds;
    }

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

            if(!$this->permissionFieldExist($modname))
            {
                return;
            }

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

            if(!$this->permissionFieldExist($modname))
            {
                return;
            }

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

    /**
     * @param $fieldname
     * @return bool
     */
    private function permissionFieldExist($fieldname)
    {
        if(!$this->Database->fieldExists($fieldname, 'tl_user') || !$this->Database->fieldExists($fieldname.'p', 'tl_user'))
        {
            return false;
        }

        if(!$this->Database->fieldExists($fieldname, 'tl_user_group') || !$this->Database->fieldExists($fieldname.'p', 'tl_user_group'))
        {
            return false;
        }

        return true;
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