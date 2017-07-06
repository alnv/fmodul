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
        $rootTaxonomiesDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE ( id = ? OR pid = ? ) AND published = "1" ORDER BY sorting')->execute($taxonomyID, $taxonomyID);
        $blnDetailView = false;
        $redirectID = $this->fm_taxonomy_page ? $this->fm_taxonomy_page : $objPage->id;
        $objPageDB = $this->Database->prepare('SELECT * FROM tl_page WHERE id = ? ORDER BY sorting')->execute($redirectID);
        $taxonomies = array('species' => array(), 'tags' => array());
        $dataTable = $this->f_select_module ? $this->f_select_module . '_data' : 'tl_taxonomies';
        $wrapperID = $this->f_select_wrapper ? $this->f_select_wrapper : null;
        $currentTaxonomyPID = '';

        // set param values
        $setAutoItems = array('auto_item' => '', 'specie' => '', 'tags' => array());
        foreach ($setAutoItems as $param => $value) {
            $setAutoItems[$param] = \Input::get($param);
        }

        // check if list or detail page
        $strWhereQuery = '';
        if ($wrapperID) {
            $strWhereQuery .= ' AND pid = "' . $wrapperID . '"';
        }
        $itemDB = $this->Database->prepare('SELECT * FROM ' . $dataTable . ' WHERE published = "1" AND ( alias = ? OR id = ? )'.$strWhereQuery)->limit(1)->execute($setAutoItems['auto_item'], (int)$setAutoItems['auto_item']);
        if ($itemDB->count()) {
            $blnDetailView = true;
        }

        // set params variables
        $this->strAutoItem = !$blnDetailView ? '' : \Input::get('auto_item');
        $this->strSpecie = !$blnDetailView ? \Input::get('auto_item') : \Input::get('specie');
        $this->strTag = !$blnDetailView ? \Input::get('specie') : \Input::get('tags');

        // get species
        while ($rootTaxonomiesDB->next()) {

            $arrTaxonomy = $rootTaxonomiesDB->row();

            if($this->strSpecie && $arrTaxonomy['alias'] == $this->strSpecie)
            {
                $currentTaxonomyPID = $arrTaxonomy['pid'];
            }

            if ($rootTaxonomiesDB->pid == '0') {
                $taxonomies['taxonomy'][] = $arrTaxonomy;
                continue;
            }

            $taxonomies['species'][] = $arrTaxonomy;
        }

        // allow multiple values
        if (is_string($this->strTag)) {
            $this->strTag = explode(',', $this->strTag);
        }

        $rootSpeciesDB = null;
        if ($this->strSpecie && $currentTaxonomyPID == $taxonomyID) {
            $rootSpeciesDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE pid = (SELECT id FROM tl_taxonomies WHERE alias = ?) AND published = "1" ORDER BY sorting')->execute($this->strSpecie);
        }

        if ($rootSpeciesDB) {
            while ($rootSpeciesDB->next()) {
                $taxonomies['tags'][] = $rootSpeciesDB->row();
            }
        }

        $arrPage = $objPageDB->row();

        // parse taxonomies
        foreach ($taxonomies as $param => $taxonomy) {
            for ($i = 0; $i < count($taxonomy); $i++) {

                $taxonomy[$i]['css'] = $param;
                $taxonomy[$i]['name'] = FModuleLabel::translate( $taxonomy[$i]['alias'], $taxonomy[$i]['name'] );
                
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

        if ($type == 'species') {
            return $this->parseSpecies($arrItem, $arrPage);
        }

        if ($type == 'tags') {
            return $this->parseTags($arrItem, $arrPage);
        }

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
        if ($this->strSpecie === $arrItem['alias']) {
            $arrItem['css'] .= ' active';
        }

        $strAlias = $this->strAutoItem ? $this->strAutoItem . '/' : '';
        
        // href
        $arrItem['href'] = $this->generateFrontendUrl($arrPage, '/' . $strAlias . $arrItem['alias']); // $this->generateFrontendUrl($arrPage, ($this->strAutoItem ? '/' . $this->strAutoItem . '' : '') . '/' . $arrItem['alias']);

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
        if (is_array($this->strTag) && in_array($arrItem['alias'], $this->strTag)) {
            $arrItem['css'] .= ' active';
        }

        $strAlias = $this->strAutoItem ? $this->strAutoItem . '/' : '';

        // href
        $arrItem['href'] = $this->generateFrontendUrl($arrPage, '/' . $strAlias . $this->strSpecie . '/' . $arrItem['alias']); //$this->generateFrontendUrl($arrPage, ($this->strAutoItem ? '/' . $this->strAutoItem . '' : '') . '/' . $this->strSpecie . '/' . $arrItem['alias']);

        return $arrItem;
    }
}