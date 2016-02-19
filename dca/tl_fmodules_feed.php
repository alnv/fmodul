<?php

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
 * Table tl_fmodules_feed
 */
$GLOBALS['TL_DCA']['tl_fmodules_feed'] = array
(

    // Config
    'config' => array
    (
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'onload_callback' => array
        (
            array('tl_fmodules_feed', 'checkPermission'),
            array('tl_fmodules_feed', 'generateFeed')
        ),
        'onsubmit_callback' => array
        (
            array('tl_fmodules_feed', 'scheduleUpdate')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'alias' => 'index'
            )
        )
    ),

    // List
    'list' => array
    (
        'sorting' => array
        (
            'mode' => 1,
            'fields' => array('title'),
            'flag' => 1,
            'panelLayout' => 'filter;search,limit'
        ),
        'label' => array
        (
            'fields' => array('title'),
            'format' => '%s'
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ),
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif'
            ),
            'copy' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['copy'],
                'href' => 'act=copy',
                'icon' => 'copy.gif'
            ),
            'delete' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ),
            'show' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            )
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default' => '{title_legend},title,alias,language;{wrappers_legend},fmodule,wrappers;{config_legend},format,source,maxItems,feedBase,description'
    ),

    // Fields
    'fields' => array
    (
        'id' => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'tstamp' => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'title' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['title'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => array('mandatory' => true, 'maxlength' => 255),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'alias' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['alias'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => array('mandatory' => true, 'rgxp' => 'alias', 'unique' => true, 'maxlength' => 128, 'tl_class' => 'w50'),
            'save_callback' => array
            (
                array('tl_fmodules_feed', 'checkFeedAlias')
            ),
            'sql' => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ),
        'language' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['language'],
            'exclude' => true,
            'search' => true,
            'filter' => true,
            'inputType' => 'text',
            'eval' => array('mandatory' => true, 'maxlength' => 32, 'tl_class' => 'w50'),
            'sql' => "varchar(32) NOT NULL default ''"
        ),
        'fmodule' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['fmodule'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'select',
            'eval' => array('mandatory' => true, 'maxlength' => 255, 'submitOnChange' => true),
            'options_callback' => array('tl_fmodules_feed', 'getFModules'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'wrappers' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['wrappers'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'checkbox',
            'options_callback' => array('tl_fmodules_feed', 'getWrappers'),
            'eval' => array('multiple' => true, 'mandatory' => true),
            'sql' => "blob NULL"
        ),
        'format' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['format'],
            'default' => 'rss',
            'exclude' => true,
            'filter' => true,
            'inputType' => 'select',
            'options' => array('rss' => 'RSS 2.0', 'atom' => 'Atom'),
            'eval' => array('tl_class' => 'w50'),
            'sql' => "varchar(32) NOT NULL default ''"
        ),
        'source' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['source'],
            'default' => 'source_teaser',
            'exclude' => true,
            'inputType' => 'select',
            'options' => array('source_teaser', 'source_text'),
            'reference' => &$GLOBALS['TL_LANG']['tl_fmodules_feed'],
            'eval' => array('tl_class' => 'w50'),
            'sql' => "varchar(32) NOT NULL default ''"
        ),
        'maxItems' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['maxItems'],
            'default' => 25,
            'exclude' => true,
            'inputType' => 'text',
            'eval' => array('mandatory' => true, 'rgxp' => 'natural', 'tl_class' => 'w50'),
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ),
        'feedBase' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['feedBase'],
            'default' => Environment::get('base'),
            'exclude' => true,
            'search' => true,
            'inputType' => 'text',
            'eval' => array('trailingSlash' => true, 'rgxp' => 'url', 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'description' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_fmodules_feed']['description'],
            'exclude' => true,
            'search' => true,
            'inputType' => 'textarea',
            'eval' => array('style' => 'height:60px', 'tl_class' => 'clr'),
            'sql' => "text NULL"
        )
    )
);

if(\Contao\Input::get('do'))
{
    $GLOBALS['TL_DCA']['tl_fmodules_feed']['config']['backlink'] = 'do='.(\Contao\Input::get('do'));
}

/**
 * Class tl_fmodules_feed
 */
class tl_fmodules_feed extends Backend
{

    /**
     * Import the back end user object
     */
    public function __construct()
    {
        parent::__construct();
        $this->import('BackendUser', 'User');
    }


    /**
     * Check permissions to edit table tl_news_archive
     */
    public function checkPermission()
    {

        if ($this->User->isAdmin) {
            return;
        }

        // Set the root IDs
        if (!is_array($this->User->fmodulesfeed) || empty($this->User->fmodulesfeed)) {
            $root = array(0);
        } else {
            $root = $this->User->fmodulesfeed;
        }

        $GLOBALS['TL_DCA']['tl_fmodules_feed']['list']['sorting']['root'] = $root;

        // Check permissions to add feeds
        if (!$this->User->hasAccess('create', 'fmodulesfeedp')) {
            $GLOBALS['TL_DCA']['tl_fmodules_feed']['config']['closed'] = true;
        }

        // Check current action
        switch (Input::get('act')) {
            case 'create':
            case 'select':
                // Allow
                break;

            case 'edit':
                // Dynamically add the record to the user profile
                if (!in_array(Input::get('id'), $root)) {
                    $arrNew = $this->Session->get('new_records');

                    if (is_array($arrNew['tl_fmodules_feed']) && in_array(Input::get('id'), $arrNew['tl_fmodules_feed'])) {
                        // Add permissions on user level
                        if ($this->User->inherit == 'custom' || !$this->User->groups[0]) {
                            $objUser = $this->Database->prepare("SELECT newsfeeds, newsfeedp FROM tl_user WHERE id=?")
                                ->limit(1)
                                ->execute($this->User->id);

                            $arrNewsfeedp = deserialize($objUser->newsfeedp);

                            if (is_array($arrNewsfeedp) && in_array('create', $arrNewsfeedp)) {
                                $arrFmodulefeeds = deserialize($objUser->fmodulesfeed);
                                $arrFmodulefeeds[] = Input::get('id');

                                $this->Database->prepare("UPDATE tl_user SET fmodulesfeed=? WHERE id=?")
                                    ->execute(serialize($arrFmodulefeeds), $this->User->id);
                            }
                        } // Add permissions on group level
                        elseif ($this->User->groups[0] > 0) {
                            $objGroup = $this->Database->prepare("SELECT fmodulesfeed, fmodulesfeedp FROM tl_user_group WHERE id=?")
                                ->limit(1)
                                ->execute($this->User->groups[0]);

                            $arrFmodulefeedp = deserialize($objGroup->fmodulesfeedp);

                            if (is_array($arrFmodulefeedp) && in_array('create', $arrFmodulefeedp)) {
                                $arrFmodulefeeds = deserialize($objGroup->fmodulesfeed);
                                $arrFmodulefeeds[] = Input::get('id');

                                $this->Database->prepare("UPDATE tl_user_group SET fmodulesfeed=? WHERE id=?")
                                    ->execute(serialize($arrFmodulefeeds), $this->User->groups[0]);
                            }
                        }

                        // Add new element to the user object
                        $root[] = Input::get('id');
                        $this->User->fmodulesfeed = $root;
                    }
                }
            // No break;

            case 'copy':
            case 'delete':
            case 'show':
                if (!in_array(Input::get('id'), $root) || (Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'fmodulesfeedp'))) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' F Module feed ID "' . Input::get('id') . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
                $session = $this->Session->getData();
                if (Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'fmodulesfeedp')) {
                    $session['CURRENT']['IDS'] = array();
                } else {
                    $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                }
                $this->Session->setData($session);
                break;

            default:
                if (strlen(Input::get('act'))) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' F Module feeds', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }

    }

    /**
     *
     */
    public function generateFeed()
    {

        $session = $this->Session->get('fmodules_feed_updater');

        if (!is_array($session) || empty($session)) {
            return;
        }

        $this->import('FModule');

        foreach ($session as $table) {
            $this->FModule->generateFeedsByArchive($table);
        }

        $this->import('Automator');
        $this->Automator->generateSitemap();

        $this->Session->set('fmodules_feed_updater', null);

    }

    /**
     * @param DataContainer $dc
     */
    public function scheduleUpdate(DataContainer $dc)
    {
        $table = $dc->activeRecord->fmodule;

        // Return if there is no ID
        if (!$table) {
            return;
        }

        // Store the ID in the session
        $session = $this->Session->get('fmodules_feed_updater');
        $session[] = $table;
        $this->Session->set('fmodules_feed_updater', array_unique($session));

    }

    /**
     * @return array
     */
    public function getFModules()
    {
        $return = array('no-value' => '-');
        $fmodulesDB = $this->Database->prepare('SELECT id, tablename, name FROM tl_fmodules')->execute();
        while($fmodulesDB->next())
        {
            $return[$fmodulesDB->tablename] = $fmodulesDB->name;
        }
        return $return;
    }

    /**
     * @param $dc
     * @return array
     */
    public function getWrappers($dc)
    {
        $tablename = $dc->activeRecord->fmodule;
        $return = array();

        if( $tablename && $tablename != 'no-value' )
        {
            $wrappersDB = $this->Database->prepare('SELECT * FROM '.$tablename.'')->execute();

            if($wrappersDB->count() > 0)
            {
                while($wrappersDB->next())
                {
                    $return[$wrappersDB->id] = $wrappersDB->title.' ('.$wrappersDB->info.')';
                }
            }
        }

        return $return;

    }


    /**
     * Check the RSS-feed alias
     *
     * @param mixed $varValue
     * @param DataContainer $dc
     *
     * @return mixed
     *
     * @throws Exception
     */
    public function checkFeedAlias($varValue, DataContainer $dc)
    {

        // No change or empty value
        if ($varValue == $dc->value || $varValue == '') {
            return $varValue;
        }

        $varValue = standardize($varValue); // see #5096

        $this->import('Automator');
        $arrFeeds = $this->Automator->purgeXmlFiles(true);

        // Alias exists
        if (array_search($varValue, $arrFeeds) !== false) {
            throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        return $varValue;

    }
}