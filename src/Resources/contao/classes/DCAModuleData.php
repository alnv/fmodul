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
use Contao\Input;
use Contao\Image;
use Contao\StringUtil;
use Contao\DataContainer;

/**
 * Class DCAModule
 * @package FModule
 */
class DCAModuleData extends ViewContainer
{

    /**
     * @var
     */
    protected $name;

    /**
     * @var
     */
    protected $parent;

    /**
     * @var
     */
    protected $id;

    /**
     * @var
     */
    protected $pid;

    /**
     * @var
     */
    protected $fields = array();

    /**
     * @var
     */
    private $doNotSetByType = array('wrapper_field', 'legend_start', 'legend_end', 'fulltext_search', 'map_field');

    /**
     * @var
     */
    private $doNotSetByID = array('orderBy', 'sorting_fields', 'pagination');

    /**
     * @var null
     */
    static private $instance = null;

    /**
     * @return DCAModuleData|null
     */
    static public function getInstance()
    {
        if(self::$instance == null)
        {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * @param $dcaname
     * @param $parentdcaname
     */
    public function init($dcaname, $parentdcaname)
    {
        $this->name = $dcaname;
        $this->parent = $parentdcaname;
        $this->getIDs();
    }

    /**
     * @param $fieldname
     * @return bool
     */
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

    /**
     * @param $dc
     */
    public function checkPermission($dc)
    {

        $modname = substr($dc->table, 3, strlen($dc->table));
        $modname = str_replace('_data', '', $modname);
        $allowedFields = $modname;

        if (!$this->permissionFieldExist($modname)) {
            return;
        }

        if ($this->User->isAdmin) {
            return;
        }

        if (!is_array($this->User->$allowedFields) || empty($this->User->$allowedFields)) {
            $root = array(0);
        } else {
            $root = $this->User->$allowedFields;
        }

        $id = strlen(Input::get('id')) ? Input::get('id') : CURRENT_ID;

        switch (Input::get('act')) {
            case 'paste':
                // Allow
                break;

            case 'create':
                if (!strlen(Input::get('pid')) || !in_array(Input::get('pid'), $root)) {
                    $this->log('Not enough permissions to create F Module items in ' . $modname . ' Wrapper ID "' . Input::get('pid') . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'cut':
            case 'copy':
                if (!in_array(Input::get('pid'), $root)) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' F Module item ID "' . $id . '" to ' . $modname . ' Wrapper ID "' . Input::get('pid') . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }

            case 'edit':
            case 'show':
            case 'delete':
            case 'toggle':
            case 'feature':
                $objArchive = $this->Database->prepare("SELECT pid FROM " . $dc->table . " WHERE id=?")
                    ->limit(1)
                    ->execute($id);

                if ($objArchive->numRows < 1) {
                    $this->log('Invalid F Module item ID "' . $id . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }

                if (!in_array($objArchive->pid, $root)) {
                    $this->log('Not enough permissions to ' . Input::get('act') . ' F Module item ID "' . $id . '" of ' . $modname . ' Wrapper ID "' . $objArchive->pid . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;

            case 'select':
            case 'editAll':
            case 'deleteAll':
            case 'overrideAll':
            case 'cutAll':
            case 'copyAll':
                if (!in_array($id, $root)) {
                    $this->log('Not enough permissions to access ' . $modname . ' Wrapper ID "' . $id . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }

                $objArchive = $this->Database->prepare("SELECT id FROM " . $dc->table . " WHERE pid=?")
                    ->execute($id);

                if ($objArchive->numRows < 1) {
                    $this->log('Invalid F Module ID "' . $id . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }

                $session = $this->Session->getData();
                $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $objArchive->fetchEach('id'));
                $this->Session->setData($session);
                break;

            default:
                if (strlen(Input::get('act'))) {
                    $this->log('Invalid command "' . Input::get('act') . '"', __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                } elseif (!in_array($id, $root)) {
                    $this->log('Not enough permissions to access ' . $modname . ' Wrapper ID ' . $id, __METHOD__, TL_ERROR);
                    $this->redirect('contao/main.php?act=error');
                }
                break;
        }
    }

    /**
     *
     */
    public function getIDs()
    {
        if (Input::get('act') == 'edit' && Input::get('table') == $this->name) {
            $id = Input::get('id');
            $idsDB = $this->Database->prepare("SELECT id, pid FROM " . $this->name . " WHERE id = ?")->execute($id)->row();
            $this->id = $idsDB['id'];
            $this->pid = $idsDB['pid'];
        }

    }

    /**
     * @param $detailPage
     * @return array
     */
    public function setConfig($detailPage)
    {
        $parent = $this->parent;
        $config = array(
            'dataContainer' => 'Table',
            'ptable' => $parent,
            'ctable' => array('tl_content'),
            'enableVersioning' => true,
            'onload_callback' => array
            (
                array('DCAModuleData', 'checkPermission'),
                array('DCAModuleData', 'generateFeed')
            ),
            'onsubmit_callback' => array
            (
                array('DCAModuleData', 'scheduleUpdate'),
                array('DCAModuleData', 'saveGeoCoding')
            ),
            'sql' => array(
                'keys' => array
                (
                    'id' => 'primary',
                    'pid' => 'index'
                )
            )
        );
        if ($detailPage) {
            $config['ctable'] = array('tl_content');
        }
        return $config;
    }


    /**
     * @param $moduleObj
     * @return array
     */
    public function setList($moduleObj)
    {
        $sortingField = $moduleObj['sorting'];
        $sortingType = $moduleObj['sortingType'];
        $orderBy = $moduleObj['orderBy'];
        $fields = $moduleObj['fields'];
        $flag = 1;
        $mode = 1;
        $arrFlag = explode('.', $sortingType);
        $arrField = explode('.', $sortingField);
				
		if($arrField[0] && $arrField[0] == 'id')
		{
			$mode = 0;
		}
		
        if ($arrFlag[1]) {
            $flag = (int)$arrFlag[1];
        }

        if ($orderBy == 'desc') {
            $flag += 1;
        }

        $list = array(	
            'sorting' => array(
                'mode' => $mode,
                'flag' => $flag,
                'fields' => array($arrField[0]),
                'headerFields' => array('title', 'info', 'id'),
                'panelLayout' => 'search;filter;limit',
                'child_record_callback' => array('DCAModuleData', 'listData')
            ),
            'label' => array(
                'fields' => array('title', 'info'),
                'format' => '%s <span style="color: #c2c2c2">[%s]</span>'
            ),
            'global_operations' => array(
                'all' => array
                (
                    'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                    'href' => 'act=select',
                    'class' => 'header_edit_all',
                    'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
                )
            ),
            'operations' => array(
                'editheader' => array
                (
                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['itemheader'],
                    'href' => 'act=edit',
                    'icon' => 'header.gif'
                ),
                'editList' => array
                (
                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['editList'],
                    'href' => 'table=tl_content&view=list',
                    'icon' => $GLOBALS['FM_AUTO_PATH'] . 'page.png'
                ),
                'editDetail' => array(

                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['editDetail'],
                    'href' => 'table=tl_content&view=detail',
                    'icon' => $GLOBALS['FM_AUTO_PATH'] . 'detail.png'
                ),
                'copy' => array
                (
                    'label' => &$GLOBALS['TL_LANG']['tl_fmodules_language_pack']['copy'],
                    'href' => 'act=copy',
                    'icon' => 'copy.gif'
                ),
                'delete' => array
                (
                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['delete'],
                    'href' => 'act=delete',
                    'icon' => 'delete.gif',
                    'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['deleteMsg'] . '\'))return false;Backend.getScrollOffset()"'
                ),
                'toggle' => array
                (
                    'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['toggle'],
                    'icon' => 'visible.gif',
                    'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                    'button_callback' => array('DCAModuleData', 'toggleIcon')
                )
            )
        );
        foreach ($fields as $field) {
            if ($field['fieldID'] && $field['type'] == 'toggle_field') {
                $list['operations'][$field['fieldID']] = array(
                    'label' => array($field['title'], $field['description']),
                    'icon' => $this->getToggleIcon('1', $field['description'], $field['fieldID'], true),
                    'href' => $field['fieldID'],
                    'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleFMField(this)"',
                    'button_callback' => array('DCAModuleData', 'iconFeatured')
                );
            }
        }
        $list['operations']['show'] = array(
            'label' => $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['show'],
            'href' => 'act=show',
            'icon' => 'show.gif'
        );
        return $list;
    }
	
	/**
     * @param $arrRow
     * @return string
     */	
	public function listData($arrRow)
	{
		$span = '<span style="color: #c2c2c2">['.$arrRow['info'].']</span>';
		if(!$arrRow['info'])
		{
			$span = '<span style="color: #c2c2c2">['.$arrRow['id'].']</span>';
		}
		if(strlen($arrRow['info']) > 24)
		{
			$subStrInfo = substr($arrRow['info'], 0, 24);
			$span = '<span style="color: #c2c2c2">['.$subStrInfo.'…]</span>';
		}
		return $arrRow['title'].' '.$span;
	}
	
    /**
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @param $attributes
     * @return string
     */
    public function iconFeatured($row, $href, $label, $title, $icon, $attributes)
    {
        $field = $href;
        if (strlen(Input::get('fid'))) {

            $id = Input::get('fid');
            $state = Input::get('state');
            $col = Input::get('col');

            $this->toggleFMField($id, ($state == 1), $col);
            $this->redirect($this->getReferer());

        }
        $href = '&amp;fid=' . $row['id'] . '&amp;col=' . $field . '&amp;state=' . ($row[$field] ? '' : 1);
        $imageHTML = $this->getToggleIcon($row[$field], $label, $field, false);
        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . $imageHTML . '</a> ';
    }

    /**
     * Feature/unfeature a news item
     *
     * @param integer $intId
     * @param boolean $blnVisible
     *
     * @return string
     */
    public function toggleFMField($intId, $blnVisible, $field)
    {
        $table = Input::get('table');
        $field = $field ? $field : Input::post('col');

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA'][$table]['fields'][$field]['save_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$table]['fields'][$field]['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, $this);
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, $this);
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE " . $table . " SET tstamp=" . time() . ", " . $field . "='" . ($blnVisible ? '1' : '') . "' WHERE id=?")->execute($intId);
    }

    /**
     * @param $paletteBuilder
     * @param $fields
     * @return array
     */
    protected function palettesCollector($paletteBuilder, $fields = array())
    {
        $palettes = array();
        $paletteBuilder = $paletteBuilder ? deserialize($paletteBuilder) : array();
        $palettes[] = 'generalPalette';
        $defaultPalettes = array('sourcePalette', 'protectedPalette', 'expertPalette', 'publishPalette');

        // add palettes from builder into $palettes var
        if (!empty($paletteBuilder)) {
            foreach ($paletteBuilder as $palette) {
                $palettes[] = $palette;
            }
        }

        // set custom fields
        if (!empty($fields)) {
            if (DCAHelper::isLegend($fields)) {
                foreach ($fields as $field) {
                    if ($field['type'] != 'legend_start') {
                        continue;
                    }
                    $palettes[] = $field['fieldID'];
                }
            }
            if (!DCAHelper::isLegend($fields)) {
                $palettes[] = 'metaPalette';
            }
        }
        $palettes = array_merge($palettes, $defaultPalettes);
        return $palettes;
    }

    /**
     * @param $moduleDB
     * @return array
     */
    public function setPalettes($moduleDB)
    {
        // get all palettes
        $palettes = $this->palettesCollector($moduleDB['paletteBuilder'], $moduleDB['fields']);
        $returnPalette = array(
            '__selector__' => array(),
            'default' => '',
            'subPalettes' => array()
        );

        //build palettes
        foreach ($palettes as $palette) {

            $getPalette = $this->{$palette}($moduleDB['fields']);
            $paletteData = $getPalette ? $getPalette : array();

            if (!empty($paletteData)) {

                // set palette string
                $returnPalette['default'] .= $paletteData['palette'];

                // set selectors
                if ($paletteData['__selector__']) {
                    $returnPalette['__selector__'][] = $paletteData['__selector__'];
                }

                // set subpallets
                if ($paletteData['subPalettes'] && $paletteData['__selector__']) {

                    if(!is_array($paletteData['subPalettes']))
                    {
                        $returnPalette['subPalettes'][$paletteData['__selector__']] = $paletteData['subPalettes'];
                    }else{
                        foreach($paletteData['subPalettes'] as $k => $v)
                        {
                            $returnPalette['subPalettes'][$k] = $v;
                        }
                    }
                }

            }
        }

        return $returnPalette;
    }


    /**
     * @param $moduleObj
     * @return array
     */
    public function setFields($moduleObj)
    {

        $fields = $moduleObj['fields'];

        // get dca fields
        $arr = $this->dcaDataFields();

        // set input fields
        if(is_array($fields))
        {
            foreach ($fields as $field) {

                // skip if field id is empty
                if (!$field['fieldID']) {
                    continue;
                }

                // skip if field id or type is not allowed
                if (in_array($field['fieldID'], $this->doNotSetByID) || in_array($field['type'], $this->doNotSetByType)) {
                    continue;
                }

                // get field from view
                switch ($field['type']) {
                    case 'widget':
                        $arr[$field['fieldID']] = $this->getWidgetField($field);
                        break;
                    case 'simple_choice':
                        $options = $this->getOptions($field, $moduleObj);
                        $arr[$field['fieldID']] = $this->getSimpleChoiceField($field, $options);
                        break;
                    case 'multi_choice':
                        $options = $this->getOptions($field, $moduleObj);
                        $arr[$field['fieldID']] = $this->getMultiChoiceField($field, $options);
                        break;
                    case 'search_field':
                        $arr[$field['fieldID']] = $this->getSearchField($field);
                        break;
                    case 'date_field':
                        $arr[$field['fieldID']] = $this->getDateField($field);
                        break;
                    case 'toggle_field':
                        $arr[$field['fieldID']] = $this->getToggleField($field);
                        break;
                }

            }
        }
        $this->fields = $arr;
        return $arr;
    }


    /**
     * @param $varValue
     * @param $dc
     * @return string
     * @throws \Exception
     */
    public function generateAlias($varValue, $dc)
    {
        $autoAlias = false;

        // create alias if no dca defined
        if($dc === null)
        {
            $strValue = $varValue ? $varValue : '';
            return $strValue;
        }

        // Generate alias if there is none
        if ($varValue == '') {
            $autoAlias = true;
            $varValue = StringUtil::generateAlias($dc->activeRecord->title);
        }

        $table = Input::get('table');
        $pid = $dc->activeRecord->pid;

        $objAlias = $this->Database->prepare("SELECT id FROM " . $table . " WHERE alias = ? AND pid = ?")->execute($varValue, $pid);

        // Check whether the alias exists
        if ($objAlias->numRows > 1 && !$autoAlias) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));

        }

        // Add ID to alias
        if ($objAlias->numRows && $autoAlias) {
            $varValue .= '-' . $dc->id;
        }

        return $varValue;
    }

    /**
     * @return null
     */
    public function createCols()
    {
        if(!$this->name)
        {
            return null;
        }

        foreach($this->fields as $colname => $field)
        {
            if(!$field['sql'])
            {
               continue;
            }

            if(!$this->Database->fieldExists($colname, $this->name))
            {
                $this->Database->prepare('ALTER TABLE ' . $this->name . ' ADD ' . $colname . ' ' . $field['sql'])->execute();
            }
        }
    }

    /**
     * @return void
     */
    public function createTable()
    {
        $defaultCols = "id int(10) unsigned NOT NULL auto_increment, tstamp int(10) unsigned NOT NULL default '0', pid int(10) unsigned NOT NULL default '0'";

        if( $this->name && !$this->Database->tableExists($this->name) )
        {
            $this->Database->prepare("CREATE TABLE IF NOT EXISTS " . $this->name . " (" . $defaultCols . ", PRIMARY KEY (id))")->execute();
        }

        if(!empty($this->fields))
        {
            $this->createCols();
        }
    }

    /**
     * @param DataContainer $dca
     * @return null
     */
    public function saveGeoCoding(DataContainer $dca)
    {
        if(!$dca->activeRecord)
        {
            return null;
        }

        $geo_address = '';
        $countries = $this->getCountries();
        $address_street = $dca->activeRecord->address_street ? $dca->activeRecord->address_street : '';
        $address_addition = $dca->activeRecord->address_addition ? $dca->activeRecord->address_addition : '';
        $address_location = $dca->activeRecord->address_location ? $dca->activeRecord->address_location : '';
        $address_zip = $dca->activeRecord->address_zip ? $dca->activeRecord->address_zip : '';
        $address_country = $dca->activeRecord->address_country ? $countries[$dca->activeRecord->address_country] : '';

        //
        if($address_location || $address_zip || $address_country)
        {
            $geo_address = $address_street .' '. $address_addition .' '. $address_zip .' '. $address_location .' '. $address_country;
        }

        //
        if(!$geo_address)
        {
            $geo_address = $dca->activeRecord->geo_address ? $dca->activeRecord->geo_address : '';
        }

        //
        $cords = array();

        //
        if($geo_address)
        {
            $geoCoding = new GeoCoding();
            $cords =$geoCoding->getGeoCords($geo_address, $address_country);
        }

        if(!empty($cords))
        {
            $tableName = $dca->table ? $dca->table : Input::get('table');
            $id = $dca->id ? $dca->id : Input::get('id');
            $lat = $cords['lat'] ? $cords['lat'] : '';
            $lng = $cords['lng'] ? $cords['lng'] : '';
            if(!$tableName || !$id)
            {
                return null;
            }
            $this->Database->prepare('UPDATE '.$tableName.' SET geo_latitude=?,geo_longitude=? WHERE id = ?')->execute($lat, $lng, $id);
        }
    }

    /**
     * @param $row
     * @param $href
     * @param $label
     * @param $title
     * @param $icon
     * @param $attributes
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {

        if (strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';

    }

    /**
     * @param $intId
     * @param $blnVisible
     * @param null $dc
     */
    public function toggleVisibility($intId, $blnVisible, $dc = null)
    {

        $table = Input::get('table');

        // Check permissions to edit
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA'][$table]['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA'][$table]['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->$callback[0]->$callback[1]($blnVisible, ($dc ?: $this));
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, ($dc ?: $this));
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE " . $table . " SET tstamp=" . time() . ", published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")->execute($intId);
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
     * @param $dc
     */
    public function scheduleUpdate($dc)
    {
        $table = Input::get('table');

        // Return if there is no ID
        if (!$table) {
            return;
        }

        if (substr($table, -5) != '_data') {
            return;
        }

        $table = substr($table, 0, (strlen($table) - 5));

        // Store the ID in the session
        $session = $this->Session->get('fmodules_feed_updater');
        $session[] = $table;
        $this->Session->set('fmodules_feed_updater', array_unique($session));

    }

}