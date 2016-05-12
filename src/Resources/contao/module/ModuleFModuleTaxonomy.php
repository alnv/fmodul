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
     * @var string
     */
    protected $strAutoItem = '';

    /**
     * @var string
     */
    protected $strTaxonomy= '';

    /**
     * @var string
     */
    protected $strSpecie = '';

    /**
     * @var string
     */
    protected $strTag = array();

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
        $rootTaxonomiesDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE ( id = ? OR pid = ? ) AND published = "1"')->execute($taxonomyID, $taxonomyID);
        $isListView = false;
        $redirectID = $this->fm_taxonomy_page ? $this->fm_taxonomy_page : $objPage->id;
        $objPageDB = $this->Database->prepare('SELECT * FROM tl_page WHERE id = ? ORDER BY sorting')->execute($redirectID);
        $taxonomies = array('taxonomy' => array(), 'species' => array(), 'tags' => array());

        // set param values
        $setAutoItems = array('auto_item'=> '', 'taxonomy' => '', 'specie' => '', 'tags' => array());
        foreach($setAutoItems as $param => $value)
        {
            $setAutoItems[$param] = \Input::get($param);
        }

        while($rootTaxonomiesDB->next())
        {
            if($setAutoItems['auto_item'] === $rootTaxonomiesDB->alias)
            {
                $isListView = true;
            }

            if($rootTaxonomiesDB->pid == '0')
            {
                $taxonomies['taxonomy'][] = $rootTaxonomiesDB->row();
                continue;
            }

            $taxonomies['species'][] = $rootTaxonomiesDB->row();
        }

        // set params variables
        $this->strAutoItem = $isListView ? '' : \Input::get('auto_item');
        $this->strTaxonomy = $isListView ? \Input::get('auto_item') : \Input::get('taxonomy');
        $this->strSpecie = $isListView ? \Input::get('taxonomy') : \Input::get('specie');
        $this->strTag = $isListView ? \Input::get('specie') : \Input::get('tags');

        // allow multiple values
        $this->strTag = explode(',', $this->strTag);

        //
        $rootSpeciesDB = null;
        if($this->strSpecie)
        {
            $rootSpeciesDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE pid = (SELECT id FROM tl_taxonomies WHERE alias = ?) AND published = "1"')->execute($this->strSpecie);
        }

        if($rootSpeciesDB)
        {
            while($rootSpeciesDB->next())
            {
                $taxonomies['tags'][] = $rootSpeciesDB->row();
            }
        }

        $arrPage  = $objPageDB->row();

        // parse taxonomies
        foreach($taxonomies as $param => $taxonomy)
        {
            for($i = 0; $i < count($taxonomy); $i++)
            {
                $taxonomy[$i]['css'] = $param;
                $taxonomies[$param][$i] = $this->parseTaxonomiesArrays($param, $taxonomy[$i], $arrPage);
            }
        }

        $this->Template->rootTaxonomy = $taxonomies['taxonomy'][0];
        $this->Template->taxonomies = $taxonomies;

    }

    /**
     * @param $type
     * @param $arrItem
     * @param array $arrPage
     * @return mixed
     */
    private function parseTaxonomiesArrays($type, $arrItem, $arrPage = array())
    {
        if($type == 'taxonomy')
        {
            return $this->parseTaxonomy($arrItem, $arrPage);
        }

        if($type == 'species')
        {
            return $this->parseSpecies($arrItem, $arrPage);
        }

        if($type == 'tags')
        {
            return $this->parseTags($arrItem, $arrPage);
        }

        return $arrItem;
    }

    /**
     * @param $arrItem
     * @param $arrPage
     * @return mixed
     */
    private function parseTaxonomy($arrItem, $arrPage)
    {
        // no taxonomy found
        if(!$this->strTaxonomy)
        {
            $this->strTaxonomy = $arrItem['alias'];
        }

        // css
        if($this->strTaxonomy === $arrItem['alias'])
        {
            $arrItem['css'] .= ' active';
        }

        // href
        $arrItem['href'] = $this->generateFrontendUrl($arrPage, ($this->strAutoItem ? '/' . $this->strAutoItem . '' : '') . '/' . $arrItem['alias']);

        return $arrItem;
    }

    /**
     * @param $arrItem
     * @param $arrPage
     * @return mixed
     */
    private function parseSpecies($arrItem, $arrPage)
    {
        // css
        if($this->strSpecie === $arrItem['alias'])
        {
            $arrItem['css'] .= ' active';
        }

        // href
        $arrItem['href'] = $this->generateFrontendUrl($arrPage, ($this->strAutoItem ? '/' . $this->strAutoItem . '' : '') . '/' . $this->strTaxonomy . '/' . $arrItem['alias']);

        return $arrItem;
    }

    /**
     * @param $arrItem
     * @param $arrPage
     * @return mixed
     */
    private function parseTags($arrItem, $arrPage)
    {
        // css
        if(is_array($this->strTag) && in_array($arrItem['alias'], $this->strTag))
        {
            $arrItem['css'] .= ' active';
        }

        // href
        $arrItem['href'] = $this->generateFrontendUrl($arrPage, ($this->strAutoItem ? '/' . $this->strAutoItem . '' : '') . '/' . $this->strTaxonomy . '/' . $this->strSpecie . '/' . $arrItem['alias'] );

        return $arrItem;
    }
}