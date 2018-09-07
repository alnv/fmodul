<?php namespace FModule;


/**
 * Class GalleryGenerator
 * @package FModule
 */
class GalleryGenerator extends \Frontend{

    /**
     * @var
     */
    public $id;

    /**
     * @var
     */
    public $perPage;

    /**
     * @var
     */
    public $perRow;

    /**
     * @var
     */
    public $numberOfItems;

    /**
     * @var
     */
    public $sortBy;

    /**
     * @var
     */
    public $orderSRC;

    /**
     * @var
     */
    public $metaIgnore;

    /**
     * @var
     */
    public $size;

    /**
     * @var
     */
    public $fullsize;

    /**
     * @var
     */
    public $galleryTpl;

    /**
     * @var
     */
    protected $multiSRC;

    /**
     * @var
     */
    protected $objFiles;

    /**
     * @var array
     */
    protected $arrData = array();

    /**
     * @param $multiSRC
     */
    public function getAllImages($multiSRC) {
        $this->multiSRC = $multiSRC;
        $arrMultiSRC = $multiSRC ? deserialize($multiSRC) : array();
        if(!$this->objFiles && is_array($arrMultiSRC)) {
            $this->objFiles = \FilesModel::findMultipleByUuids($arrMultiSRC);
        }
    }

    /**
     * @return string
     */
    public function renderGallery() {

        /** @var \PageModel $objPage */
        global $objPage;

        $strGalleryTemplate = new \FrontendTemplate('ce_gallery');
        $strGalleryTemplate->class = 'ce_gallery';
        $objFiles = $this->objFiles;
        $images = array();
        $auxDate = array();

        if(!$objFiles->count()) {
            return '';
        }

        while ($objFiles->next()) {

            // Continue if the files has been processed or does not exist
            if (isset($images[$objFiles->path]) || !file_exists(TL_ROOT . '/' . $objFiles->path))
            {
                continue;
            }

            // Single files
            if ($objFiles->type == 'file')
            {
                $objFile = new \File($objFiles->path, true);

                if (!$objFile->isImage)
                {
                    continue;
                }

                $arrMeta = $this->getMetaData($objFiles->meta, $objPage->language);

                if (empty($arrMeta))
                {
                    if ($this->metaIgnore)
                    {
                        continue;
                    }
                    elseif ($objPage->rootFallbackLanguage !== null)
                    {
                        $arrMeta = $this->getMetaData($objFiles->meta, $objPage->rootFallbackLanguage);
                    }
                }

                // Use the file name as title if none is given
                if ($arrMeta['title'] == '')
                {
                    $arrMeta['title'] = specialchars($objFile->basename);
                }

                // Add the image
                $images[$objFiles->path] = array
                (
                    'id'        => $objFiles->id,
                    'uuid'      => $objFiles->uuid,
                    'name'      => $objFile->basename,
                    'singleSRC' => $objFiles->path,
                    'alt'       => $arrMeta['alt'],
                    'imageUrl'  => $arrMeta['link'],
                    'caption'   => $arrMeta['caption']
                );

                if ( !version_compare( VERSION, '4.0', '>=' ) && !$images[$objFiles->path]['alt'] ) {

                    $images[$objFiles->path]['alt'] = $arrMeta['title'];
                }

                $auxDate[] = $objFile->mtime;
            }

            // Folders
            else
            {
                $objSubfiles = \FilesModel::findByPid($objFiles->uuid);

                if ($objSubfiles === null)
                {
                    continue;
                }

                while ($objSubfiles->next())
                {
                    // Skip subfolders
                    if ($objSubfiles->type == 'folder')
                    {
                        continue;
                    }

                    $objFile = new \File($objSubfiles->path, true);

                    if (!$objFile->isImage)
                    {
                        continue;
                    }

                    $arrMeta = $this->getMetaData($objSubfiles->meta, $objPage->language);

                    if (empty($arrMeta))
                    {
                        if ($this->metaIgnore)
                        {
                            continue;
                        }
                        elseif ($objPage->rootFallbackLanguage !== null)
                        {
                            $arrMeta = $this->getMetaData($objSubfiles->meta, $objPage->rootFallbackLanguage);
                        }
                    }

                    // Use the file name as title if none is given
                    if ($arrMeta['title'] == '')
                    {
                        $arrMeta['title'] = specialchars($objFile->basename);
                    }

                    // Add the image
                    $images[$objSubfiles->path] = array
                    (
                        'id'        => $objSubfiles->id,
                        'uuid'      => $objSubfiles->uuid,
                        'name'      => $objFile->basename,
                        'singleSRC' => $objSubfiles->path,
                        'alt'       => $arrMeta['alt'],
                        'imageUrl'  => $arrMeta['link'],
                        'caption'   => $arrMeta['caption']
                    );

                    if ( !version_compare( VERSION, '4.0', '>=' ) && !$images[$objFiles->path]['alt'] ) {

                        $images[$objFiles->path]['alt'] = $arrMeta['title'];
                    }

                    $auxDate[] = $objFile->mtime;
                }
            }
        }
        switch ($this->sortBy) {

            case 'name_asc':
                uksort($images, 'basename_natcasecmp');
                break;

            case 'name_desc':
                uksort($images, 'basename_natcasercmp');
                break;

            case 'date_asc':
                array_multisort($images, SORT_NUMERIC, $auxDate, SORT_ASC);
                break;

            case 'date_desc':
                array_multisort($images, SORT_NUMERIC, $auxDate, SORT_DESC);
                break;

            case 'custom':
                if ($this->orderSRC != '')
                {
                    $tmp = deserialize($this->orderSRC);

                    if (!empty($tmp) && is_array($tmp))
                    {
                        // Remove all values
                        $arrOrder = array_map(function(){}, array_flip($tmp));

                        // Move the matching elements to their position in $arrOrder
                        foreach ($images as $k=>$v)
                        {
                            if (array_key_exists($v['uuid'], $arrOrder))
                            {
                                $arrOrder[$v['uuid']] = $v;
                                unset($images[$k]);
                            }
                        }

                        // Append the left-over images at the end
                        if (!empty($images))
                        {
                            $arrOrder = array_merge($arrOrder, array_values($images));
                        }

                        // Remove empty (unreplaced) entries
                        $images = array_values(array_filter($arrOrder));
                        unset($arrOrder);
                    }
                }
                break;

            case 'random':
                shuffle($images);
                break;
        }

        $images = array_values($images);

        $strPaginationTemplate = '';

        // Limit the total number of items (see #2652)
        if ($this->numberOfItems > 0)
        {
            $images = array_slice($images, 0, $this->numberOfItems);
        }

        $offset = 0;
        $total = count($images);
        $limit = $total;

        // Paginate the result of not randomly sorted (see #8033)
        if ($this->perPage > 0 && $this->sortBy != 'random')
        {
            // Get the current page
            $id = 'page_g' . $this->id;
            $page = (\Input::get($id) !== null) ? \Input::get($id) : 1;

            // Do not index or cache the page if the page number is outside the range
            if ($page < 1 || $page > max(ceil($total/$this->perPage), 1))
            {
                /** @var \PageError404 $objHandler */
                $objHandler = new $GLOBALS['TL_PTY']['error_404']();
                $objHandler->generate($objPage->id);
            }

            // Set limit and offset
            $offset = ($page - 1) * $this->perPage;
            $limit = min($this->perPage + $offset, $total);

            $objPagination = new \Pagination($total, $this->perPage, \Config::get('maxPaginationLinks'), $id);
            $strGalleryTemplate->pagination = $objPagination->generate("\n  ");
        }

        $rowcount = 0;
        $colwidth = floor(100/$this->perRow);
        $intMaxWidth = (TL_MODE == 'BE') ? floor((640 / $this->perRow)) : floor((\Config::get('maxImageWidth') / $this->perRow));
        $strLightboxId = 'lightbox[lb' . $this->id . ']';
        $body = array();

        // Rows
        for ($i=$offset; $i<$limit; $i=($i+$this->perRow))
        {
            $class_tr = '';

            if ($rowcount == 0)
            {
                $class_tr .= ' row_first';
            }

            if (($i + $this->perRow) >= $limit)
            {
                $class_tr .= ' row_last';
            }

            $class_eo = (($rowcount % 2) == 0) ? ' even' : ' odd';

            // Columns
            for ($j=0; $j<$this->perRow; $j++)
            {
                $class_td = '';

                if ($j == 0)
                {
                    $class_td .= ' col_first';
                }

                if ($j == ($this->perRow - 1))
                {
                    $class_td .= ' col_last';
                }

                $objCell = new \stdClass();
                $key = 'row_' . $rowcount . $class_tr . $class_eo;

                // Empty cell
                if (!is_array($images[($i+$j)]) || ($j+$i) >= $limit)
                {
                    $objCell->colWidth = $colwidth . '%';
                    $objCell->class = 'col_'.$j . $class_td;
                }
                else
                {
                    // Add size and margin
                    $images[($i+$j)]['size'] = $this->size;
                    $images[($i+$j)]['fullsize'] = $this->fullsize;

                    $this->addImageToTemplate($objCell, $images[($i+$j)], $intMaxWidth, $strLightboxId);

                    // Add column width and class
                    $objCell->colWidth = $colwidth . '%';
                    $objCell->class = 'col_'.$j . $class_td;
                }

                $body[$key][$j] = $objCell;
            }

            ++$rowcount;
        }

        $strTemplate = 'gallery_default';

        // Use a custom template
        if (TL_MODE == 'FE' && $this->galleryTpl != '')
        {
            $strTemplate = $this->galleryTpl;
        }

        $this->setDataContainer();

        $objTemplate = new \FrontendTemplate($strTemplate);
        $objTemplate->setData($this->arrData);
        $objTemplate->body = $body;
        $strGalleryTemplate->images = $objTemplate->parse();
        
        return $strGalleryTemplate->parse();
    }

    /**
     * set arrData
     */
    private function setDataContainer(){
        // add arrData
        $this->arrData = array(
            'id' => $this->id,
            'perRow' => $this->perRow,
            'perPage' => $this->perPage,
            'numberOfItems' => $this->numberOfItems,
            'sortBy' => $this->sortBy,
            'metaIgnore' => $this->metaIgnore,
            'galleryTpl' => $this->galleryTpl,
            'multiSRC' => $this->multiSRC,
            'orderSRC' => $this->orderSRC,
            'classes' => array('first', 'last'),
            'typePrefix' => 'ce_',
            'hl' => 'h1'
        );
    }

}