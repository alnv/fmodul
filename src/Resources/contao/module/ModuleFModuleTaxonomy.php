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

/**
 * Class ModuleFModuleTaxonomy
 * @package FModule
 */
class ModuleFModuleTaxonomy extends \Module
{
    /**
     * @var string
     */
    protected $strTemplate = 'mod_taxonomies';

    /**
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE') {
            $objTemplate = new \BackendTemplate('be_wildcard');
            $objTemplate->wildcard = '### ' . $this->name . ' ###';
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;
            return $objTemplate->parse();
        }

        return parent::generate();
    }

    /**
     *
     */
    protected function compile()
    {

        global $objPage;

        $taxonomyID = $this->fm_taxonomy ? $this->fm_taxonomy : '';
        $rootTaxonomyDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE ( id = ? OR pid = ? ) AND published = "1"')->execute($taxonomyID, $taxonomyID);
        $redirectID = $this->fm_taxonomy_page ? $this->fm_taxonomy_page : $objPage->id;

        if(!$rootTaxonomyDB->count())
        {
            // no taxonomies found
        }

        // source page
        $objPage = $this->Database->prepare('SELECT * FROM tl_page WHERE id = ? ORDER BY sorting')->execute($redirectID);

        // parse template
        $arrTaxonomies = array();
        $arrParent = null;
        while($rootTaxonomyDB->next())
        {
            $item = $rootTaxonomyDB->row();

            if($item['pid'] == '0')
            {
                $arrParent = $item;
                continue;
            }

            // set into taxonomies
            $arrTaxonomies[] = $item;
            unset($item);
        }

        $parentAlias = $arrParent['alias'] ? $arrParent['alias'] : 'taxonomy';

        for($i = 0; $i < count($arrTaxonomies); $i++)
        {
            $arrTaxonomies[$i]['href'] = $this->generateFrontendUrl($objPage->row(), '/' . $parentAlias . '/' . $arrTaxonomies[$i]['alias']);
        }

        // get tags
        $parentTagAlias = \Input::get($parentAlias);
        $arrTags = array();
        if($parentTagAlias)
        {
            $rootTagDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE ( alias = ? OR id = ? ) AND published = "1"')->limit(1)->execute($parentTagAlias, (int)$parentTagAlias);
            if($rootTagDB->count())
            {
                $arrRootTag = $rootTagDB->row();
                $tagsDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE pid = ? AND published = "1"')->execute($arrRootTag['id']);
                if($tagsDB->count())
                {
                    while($tagsDB->next())
                    {
                        $item = $tagsDB->row();
                        $item['href'] = $this->generateFrontendUrl($objPage->row(), '/' . $parentAlias . '/' . $arrRootTag['alias'] . '/tag/' . $item['alias']);
                        $arrTags[] = $item;
                        unset($item);
                    }
                }
            }

            \Input::get('tag'); //
        }

        $this->Template->taxonomies = $arrTaxonomies;
        $this->Template->tags = $arrTags;
    }
}