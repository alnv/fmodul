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

use Contao\FrontendTemplate;
use Contao\Input;
use Contao\Config;
use Contao\Module;
use Contao\BackendTemplate;
use Contao\FilesModel;

/**
 * Class ModuleDetailView
 * @package FModule
 */
class ModuleDetailView extends Module
{
    /**
     * @var string
     */
    protected $strTemplate = 'mod_fmodule_detail';

    /**
     * @var array
     */
    protected $markerCache = array();

    /**
     * @var bool
     */
    protected $loadMapScript = false;

    /**
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . $this->name . ' ###';
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            return $objTemplate->parse();
        }

        $this->import('FrontendUser', 'User');

        if (!isset($_GET['auto_item']) && Config::get('useAutoItem') && isset($_GET['auto_item'])) {
            Input::setGet('auto_item', Input::get('auto_item'));
        }

        if ($this->f_doNotSet_404 == '1' && !Input::get('auto_item')) {
            global $objPage;
            $objPage->noSearch = 1;
            $objPage->cache = 0;
            return '';
        }
        return parent::generate();
    }

    /**
     *
     */
    protected function compile()
    {

        global $objPage;

        $listID = $this->f_list_field;
        $detailTemplate = $this->f_detail_template;
        $listModuleDB = $this->Database->prepare('SELECT * FROM tl_module WHERE id = ?')->execute($listID)->row();
        $tablename = $listModuleDB['f_select_module'];
        $wrapperID = $listModuleDB['f_select_wrapper'];
        $moduleDB = $this->Database->prepare('SELECT tl_fmodules.id AS moduleID, tl_fmodules.*, tl_fmodules_filters.*  FROM tl_fmodules LEFT JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ? ORDER BY tl_fmodules_filters.sorting')->execute($tablename);
        $alias = Input::get('auto_item');
        $isAlias = QueryModel::isValue($alias);

        if (!$isAlias) {
            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($objPage->id);
            exit;
        }

        $doNotSetByID = array('orderBy', 'sorting_fields', 'pagination');
        $doNotSetByType = array('legend_end', 'legend_start', 'wrapper_field');
        $fieldWidgets = array();
        $moduleArr = array();
        $mapFields = array();

        //
        while ($moduleDB->next()) {
            if (in_array($moduleDB->fieldID, $doNotSetByID) || in_array($moduleDB->type, $doNotSetByType)) {
                continue;
            }

            $modArr = $moduleDB->row();

            // map
            if ($moduleDB->type == 'map_field') {

                $mapFields[] = HelperModel::setGoogleMap($modArr);

                // set loadMapScript to true
                $this->loadMapScript = true;

                // load map libraries
                if(!$GLOBALS['loadGoogleMapLibraries']) $GLOBALS['loadGoogleMapLibraries'] = $modArr['mapInfoBox'] ? true : false;
            }

            if ($moduleDB->type == 'widget') {

                $tplName = $moduleDB->widgetTemplate;
                $tpl = '';
                if (!$tplName) {
                    $tplNameType = explode('.', $moduleDB->widget_type)[0];
                    $tplNameArr = $this->getTemplateGroup('fm_field_' . $tplNameType);
                    $tpl = current($tplNameArr);
                    $tpl = $this->parseTemplateName($tpl);
                }

                $fieldWidgets[$moduleDB->fieldID] = array(
                    'fieldID' => $moduleDB->fieldID,
                    'widgetType' => $moduleDB->widget_type,
                    'widgetTemplate' => $moduleDB->widgetTemplate ? $moduleDB->widgetTemplate : $tpl
                );
            }
            $moduleArr[$moduleDB->fieldID] = $modArr;
        }

        $strResult = '';
        $objTemplate = new FrontendTemplate($detailTemplate);
        $qProtectedStr = ' AND published = "1"';
        if (HelperModel::previewMode()) $qProtectedStr = '';
        $itemDB = $this->Database->prepare('SELECT * FROM ' . $tablename . '_data WHERE pid = ? AND alias = ? ' . $qProtectedStr . '')->execute($wrapperID, $alias)->row();
        $wrapperDB = $this->Database->prepare('SELECT * FROM ' . $tablename . ' WHERE id = ?')->execute($wrapperID)->row();

        if (count($itemDB) < 1) {
            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($objPage->id);
            exit;
        }
        if (HelperModel::sortOutProtected($itemDB, $this->User->groups)) {
            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate($objPage->id);
            exit;
        }

        // image
        $imagePath = $this->generateSingeSrc($itemDB);
        if ($imagePath) {
            $itemDB['singleSRC'] = $imagePath;
        }

        // size
        $imgSize = false;

        // Override the default image size
        if ($this->imgSize != '') {
            $size = deserialize($this->imgSize);

            if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2])) {
                $imgSize = $this->imgSize;
            }
        }

        if ($imgSize) {
            $itemDB['size'] = $imgSize;
        }

        //set css and id
        $itemDB['cssID'] = deserialize($itemDB['cssID']);
        $itemDB['itemID'] = $itemDB['cssID'][0];
        $itemDB['itemCSS'] = $itemDB['itemCSS'][1] ? ' ' . $itemDB['itemCSS'][1] : '';

        $objCte = \ContentModel::findPublishedByPidAndTable($itemDB['id'], $tablename . '_data');

        $detail = array();
        $teaser = array();

        if ($objCte !== null) {
            $intCount = 0;
            $intLast = $objCte->count() - 1;

            while ($objCte->next()) {

                $arrCss = array();
                $objRow = $objCte->current();

                if ($intCount == 0 || $intCount == $intLast) {
                    if ($intCount == 0) {
                        $arrCss[] = 'first';
                    }

                    if ($intCount == $intLast) {
                        $arrCss[] = 'last';
                    }
                }

                $objRow->classes = $arrCss;

                if ($objRow->fview == 'list') {

                    $teaser[] = $this->getContentElement($objRow, $this->strColumn);

                } else {

                    $detail[] = $this->getContentElement($objRow, $this->strColumn);
                }

                ++$intCount;
            }
        }

        // seo
        $descriptionColName = $this->fm_seoDescription ? $this->fm_seoDescription : 'description';
        $pageTitleColName = $this->fm_seoPageTitle ? $this->fm_seoPageTitle : 'title';
        $seoDescription = strip_tags($itemDB[$descriptionColName]);
        $objPage->description = $seoDescription;
        $objPage->pageTitle = $itemDB[$pageTitleColName];

        // author
        $authorDB = null;
        if ($itemDB['author']) {
            $authorDB = $this->Database->prepare('SELECT * FROM tl_user WHERE id = ?')->execute($itemDB['author'])->row();
            unset($authorDB['password']);
            unset($authorDB['session']);
        }

        $itemDB['teaser'] = $teaser;
        $itemDB['detail'] = $detail;
        $itemDB['author'] = $authorDB;
        $itemDB['date'] = $itemDB['date'] ? date($objPage->dateFormat, $itemDB['date']) : '';
        $itemDB['time'] = $itemDB['time'] ? date($objPage->timeFormat, $itemDB['time']) : '';
        $itemDB['filter'] = $moduleArr;

        if (!empty($fieldWidgets)) {

            $arrayAsValue = array('list.blank', 'list.keyValue', 'table.blank');

            foreach ($fieldWidgets as $widget) {
                $id = $widget['fieldID'];
                $tplName = $widget['widgetTemplate'];
                $type = $widget['widgetType'];
                $value = $itemDB[$id];

                if (in_array($type, $arrayAsValue)) {
                    $value = unserialize($value);
                }

                $objFieldTemplate = new FrontendTemplate($tplName);
                $objFieldTemplate->setData(array(
                    'value' => $value,
                    'type' => $type,
                    'item' => $itemDB
                ));

                $itemDB[$id] = $objFieldTemplate->parse();
            }
        }

        // create marker path
        if ($itemDB['addMarker'] && $itemDB['markerSRC']) {
            if ($this->markerCache[$itemDB['markerSRC']]) {
                $itemDB['markerSRC'] = $this->markerCache[$itemDB['markerSRC']];
            } else {
                $markerDB = $this->Database->prepare('SELECT * FROM tl_files WHERE uuid = ?')->execute($itemDB['markerSRC']);
                if ($markerDB->count()) {
                    $pathInfo = $markerDB->row()['path'];
                    if ($pathInfo) {
                        $this->markerCache[$itemDB['markerSRC']] = $pathInfo;
                        $itemDB['markerSRC'] = $pathInfo;
                    }
                }
            }
        }

        // map
        if (!empty($mapFields)) {
            foreach ($mapFields as $map) {
                $objMapTemplate = new FrontendTemplate($map['template']);
                $itemDB['mapSettings'] = $map;
                $objMapTemplate->setData($itemDB);
                $itemDB[$map['fieldID']] = $objMapTemplate->parse();
            }
        }

        $objTemplate->setData($itemDB);

        //enclosure
        $objTemplate->enclosure = array();

        if ($itemDB['addEnclosure']) {
            $this->addEnclosuresToTemplate($objTemplate, $itemDB);
        }

        //add image
        if ($itemDB['addImage']) {
            $this->addImageToTemplate($objTemplate, array(
                'singleSRC' => $itemDB['singleSRC'],
                'alt' => $itemDB['alt'],
                'size' => $itemDB['size'],
                'fullsize' => $itemDB['fullsize'],
                'caption' => $itemDB['caption'],
                'title' => $itemDB['title']
            ));
        }

        $strResult .= $objTemplate->parse();
        $this->Template->result = $strResult;

        //allow comments
        $this->Template->allowComments = $wrapperDB['allowComments'];
        if ($wrapperDB['allowComments']) {
            $this->import('Comments');
            $arrNotifies = array();
            if ($wrapperDB['notify'] != 'notify_author') {
                $arrNotifies[] = $GLOBALS['TL_ADMIN_EMAIL'];
            }

            if ($wrapperDB['notify'] != 'notify_admin') {
                if ($authorDB != null && $authorDB['email'] != '') {
                    $arrNotifies[] = $authorDB['email'];
                }
            }

            $objConfig = new \stdClass();
            $objConfig->perPage = $wrapperDB['perPage'];
            $objConfig->order = $wrapperDB['sortOrder'];
            $objConfig->template = $this->com_template;
            $objConfig->requireLogin = $wrapperDB['requireLogin'];
            $objConfig->disableCaptcha = $wrapperDB['disableCaptcha'];
            $objConfig->bbcode = $wrapperDB['bbcode'];
            $objConfig->moderate = $wrapperDB['moderate'];
            $this->Comments->addCommentsToTemplate($this->Template, $objConfig, $tablename . '_data', $itemDB['id'], $arrNotifies);
        }

        // set js files
        if ($this->loadMapScript) {
            $language = $objPage->language ? $objPage->language : 'en';
            $GLOBALS['TL_HEAD']['mapJS'] = DiverseFunction::setMapJs($language);
        }
    }

    /**
     * @param $templateName
     * @return mixed
     */
    public function parseTemplateName($templateName)
    {
        return DiverseFunction::parseTemplateName($templateName);
    }

    /**
     * @param $row
     * @return bool|void
     */
    private function generateSingeSrc($row)
    {
        if ($row['singleSRC'] != '') {

            $objModel = FilesModel::findByUuid($row['singleSRC']);

            if ($objModel && is_file(TL_ROOT . '/' . $objModel->path)) {

                return $objModel->path;

            }
        }
        return null;
    }
}