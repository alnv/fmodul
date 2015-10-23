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

use Contao\Input;

/**
 *
 */
class ModuleDetailView extends \Contao\Module
{
    /**
     * @var string
     */
    protected $strTemplate = 'mod_fmodule_detail';

    /**
     * @return string
     */
    public function generate()
    {
        /**
         *
         */
        if (TL_MODE == 'BE') {

            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### '. $this->name .' ###';
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            return $objTemplate->parse();

        }

        $this->import('FrontendUser', 'User');

        /**
         *
         */
        if (!isset($_GET['fitem']) && \Config::get('useAutoItem') && isset($_GET['auto_item'])) {

            \Input::setGet('fitem', \Input::get('auto_item'));

        }

        /**
         *
         */
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
        $alias = Input::get('fitem');

        if (!$alias && $alias == '') {
            return;
        }


        $strResult = '';
        $objTemplate = new \FrontendTemplate($detailTemplate);

        $itemDB = $this->Database->prepare('SELECT * FROM ' . $tablename . '_data WHERE pid = ? AND alias = ?')->execute($wrapperID, $alias)->row();
        $wrapperDB = $this->Database->prepare('SELECT * FROM '.$tablename.' WHERE id = ?')->execute($wrapperID)->row();

        //throw 404
        if (count($itemDB) < 1) {
            $objHandler = new $GLOBALS['TL_PTY']['error_404']();
            $objHandler->generate($objPage->id);
        }

        if ($this->sortOutProtected($itemDB)) {
            $objHandler = new $GLOBALS['TL_PTY']['error_403']();
            $objHandler->generate($objPage->id);
        }

        //image
        $imagePath = $this->generateSingeSrc($itemDB);

        if ($imagePath) {

            $itemDB['singleSRC'] = $imagePath;

        }

        // size
        $imgSize = false;

        // Override the default image size
        if ($this->imgSize != '')
        {
            $size = deserialize($this->imgSize);

            if ($size[0] > 0 || $size[1] > 0 || is_numeric($size[2]))
            {
                $imgSize = $this->imgSize;
            }
        }

        if ($imgSize)
        {
            $itemDB['size'] = $imgSize;
        }


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


        $seoDescription = Input::stripSlashes(strip_tags($itemDB['description']));

        $objPage->description = $seoDescription;
        $objPage->pageTitle = $itemDB['title'];

        $authorDB = null;
        if($itemDB['author'])
        {
            $authorDB = $this->Database->prepare('SELECT * FROM tl_user WHERE id = ?')->execute($itemDB['author'])->row();
        }

        $itemDB['teaser'] = $teaser;
        $itemDB['detail'] = $detail;
        $itemDB['author'] = $authorDB;

        $objTemplate->setData($itemDB);

        //enclosure
        $objTemplate->enclosure = array();

        if ( $itemDB['addEnclosure'] )
        {

            $this->addEnclosuresToTemplate($objTemplate, $itemDB);
        }

        //add image
        if( $itemDB['addImage'] )
        {
            $this->addImageToTemplate($objTemplate, $itemDB);
        }


        $strResult .= $objTemplate->parse();

        $this->Template->result = $strResult;

        //allow comments
        $this->Template->allowComments = $wrapperDB['allowComments'];
        if( $wrapperDB['allowComments'] )
        {
            $this->import('Comments');
            $arrNotifies = array();

            if( $wrapperDB['notify'] != 'notify_author' )
            {
                $arrNotifies[] = $GLOBALS['TL_ADMIN_EMAIL'];
            }

            if( $wrapperDB['notify'] != 'notify_admin' )
            {
                if($authorDB != null && $authorDB['email'] != '')
                {
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

            $this->Comments->addCommentsToTemplate($this->Template, $objConfig, $tablename.'_data', $itemDB['id'], $arrNotifies);

        }

    }

    /**
     * @param $item
     * @return bool
     */
    protected function sortOutProtected($item)
    {

        if (BE_USER_LOGGED_IN) {

            return false;

        }

        if (FE_USER_LOGGED_IN && $item['guests'] == '1') {

            return true;

        }

        if (FE_USER_LOGGED_IN && $item['protected'] == '1') {

            $groups = deserialize($item['groups']);

            if (!is_array($groups) || empty($groups) || count(array_intersect($groups, $this->User->groups)) < 1) {

                return true;

            }

        }

        return false;
    }

    /**
     * @param $row
     * @return bool|void
     */
    private function generateSingeSrc($row)
    {
        if ($row['singleSRC'] != '') {

            $objModel = \FilesModel::findByUuid($row['singleSRC']);

            if ($objModel && is_file(TL_ROOT . '/' . $objModel->path)) {

                return $objModel->path;

            }

            return;

        }

        return;

    }

}