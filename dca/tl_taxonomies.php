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

// tl_taxonomies
$GLOBALS['TL_DCA']['tl_taxonomies'] = array(

    // config
    'config' => array(
        'label' => &$GLOBALS['TL_LANG']['tl_taxonomies']['taxonomy'],
        'dataContainer' => 'Table',
        'enableVersioning' => true,
        'onload_callback' => array
        (
            array('tl_taxonomies_fmodule', 'checkPermission')
        ),
        'sql' => array
        (
            'keys' => array
            (
                'id' => 'primary',
                'pid' => 'index'
            )
        )
    ),

    // list
    'list' => array(

        // sorting
        'sorting' => array
        (
            'mode' => 5,
            'icon' => 'system/modules/fmodule/assets/tag.png',
            'paste_button_callback' => array('tl_taxonomies_fmodule', 'pasteTaxonomy'),
            'panelLayout' => 'filter,search'
        ),
        'label' => array
        (
            'fields' => array('name'),
            'format' => '%s',
            'label_callback' => array('tl_taxonomies_fmodule', 'addIcon')
        ),
        'global_operations' => array
        (
            'all' => array
            (
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            )
        ),
        'operations' => array
        (
            'edit' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_taxonomies']['edit'],
                'href' => 'act=edit',
                'icon' => 'edit.gif',
            ),
            'copy' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_taxonomies']['copy'],
                'href' => 'act=paste&amp;mode=copy&amp;childs=1',
                'icon' => 'copy.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
                'button_callback' => array('tl_taxonomies_fmodule', 'copyWithSubTaxonomies')
            ),
            'cut' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_taxonomies']['cut'],
                'href' => 'act=paste&amp;mode=cut',
                'icon' => 'cut.gif',
                'attributes' => 'onclick="Backend.getScrollOffset()"',
                'button_callback' => array('tl_taxonomies_fmodule', 'cutTaxonomy')
            ),
            'delete' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_taxonomies']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"',
            ),
            'toggle' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_taxonomies']['toggle'],
                'icon' => 'visible.gif',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => array('tl_taxonomies_fmodule', 'toggleIcon')
            ),
            'show' => array
            (
                'label' => &$GLOBALS['TL_LANG']['tl_taxonomies']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            ),
        )
    ),

    // Palettes
    'palettes' => array
    (
        'default' => '{general_legend},name,alias,description,published'
    ),

    // fields
    'fields' => array(
        'id' => array
        (
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ),
        'pid' => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ),
        'tstamp' => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'sorting' => array
        (
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ),
        'name' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_taxonomies']['name'],
            'inputType' => 'text',
            'exclude' => true,
            'search' => true,
            'eval' => array('tl_class' => 'w50', 'mandatory' => true),
            'sql' => "varchar(255) NOT NULL default ''"
        ),
        'alias' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_taxonomies']['alias'],
            'inputType' => 'text',
            'exclude' => true,
            'eval' => array('rgxp' => 'alias', 'maxlength' => 128, 'tl_class' => 'w50', 'doNotCopy' => true),
            'save_callback' => array(array('tl_taxonomies_fmodule', 'generateAlias')),
            'sql' => "varchar(128) COLLATE utf8_bin NOT NULL default ''"
        ),
        'description' => array(
            'label' => &$GLOBALS['TL_LANG']['tl_taxonomies']['description'],
            'inputType' => 'textarea',
            'exclude' => true,
            'sql' => "mediumtext NULL"
        ),
        'published' => array
        (
            'label' => &$GLOBALS['TL_LANG']['tl_taxonomies']['published'],
            'exclude' => true,
            'inputType' => 'checkbox',
            'eval' => array('submitOnChange' => true, 'doNotCopy' => true),
            'sql' => "char(1) NOT NULL default '1'"
        ),
    )
);

/**
 * Class tl_taxonomies_fmodule
 */
class tl_taxonomies_fmodule extends \Backend
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
     * Check permissions to edit table tl_taxonomies
     * @return null
     */
    public function checkPermission()
    {

        if ($this->User->isAdmin) {
            return null;
        }

        $root = array(0);
        if (is_array($this->User->taxonomies) || !empty($this->User->taxonomies)) {
            $root = $this->User->taxonomies;
        }

        $GLOBALS['TL_DCA']['tl_taxonomies']['list']['sorting']['root'] = $root;

        if (!$this->User->hasAccess('create', 'taxonomiesp')) {
            $GLOBALS['TL_DCA']['tl_taxonomies']['config']['closed'] = true;
        }

        if (Input::get('act') && Input::get('act') != 'paste') {

            switch (Input::get('act')) {
                case 'create':
                case 'select':
                    // Allow
                    break;
                case 'edit':
                    if (!in_array(Input::get('id'), $root)) {
                        $arrNew = $this->Session->get('new_records');
                        if (is_array($arrNew['tl_taxonomies']) && in_array(Input::get('id'), $arrNew['tl_taxonomies'])) {
                            // Add permissions on user level
                            if ($this->User->inherit == 'custom' || !$this->User->groups[0]) {
                                $objUser = $this->Database->prepare("SELECT taxonomies, taxonomiesp FROM tl_user WHERE id=?")
                                    ->limit(1)
                                    ->execute($this->User->id);

                                $arrTaxonomiesp = deserialize($objUser->taxonomiesp);

                                if (is_array($arrTaxonomiesp) && in_array('create', $arrTaxonomiesp)) {
                                    $arrTaxonomies = deserialize($objUser->taxonomies);
                                    $arrTaxonomies[] = Input::get('id');

                                    $this->Database->prepare("UPDATE tl_user SET taxonomies=? WHERE id=?")
                                        ->execute(serialize($arrTaxonomies), $this->User->id);
                                }
                            } // Add permissions on group level
                            elseif ($this->User->groups[0] > 0) {
                                $objGroup = $this->Database->prepare("SELECT taxonomies, taxonomiesp FROM tl_user_group WHERE id=?")
                                    ->limit(1)
                                    ->execute($this->User->groups[0]);

                                $arrTaxonomiesp = deserialize($objGroup->taxonomiesp);

                                if (is_array($arrTaxonomiesp) && in_array('create', $arrTaxonomiesp)) {
                                    $arrTaxonomies = deserialize($objGroup->taxonomies);
                                    $arrTaxonomies[] = Input::get('id');

                                    $this->Database->prepare("UPDATE tl_user_group SET taxonomies=? WHERE id=?")
                                        ->execute(serialize($arrTaxonomies), $this->User->groups[0]);
                                }
                            }

                            // Add new element to the user object
                            $root[] = Input::get('id');
                            $this->User->taxonomies = $root;
                        }
                    }
                    break;
                case 'copy':
                case 'delete':
                case 'show':
                    if (!in_array(Input::get('id'), $root) || (Input::get('act') == 'delete' && !$this->User->hasAccess('delete', 'taxonomiesp'))) {
                        $this->log('Not enough permissions to ' . Input::get('act') . ' Taxonomy  ID "' . Input::get('id') . '"', __METHOD__, TL_ERROR);
                        $this->redirect('contao/main.php?act=error');
                    }
                    break;
                case 'editAll':
                case 'deleteAll':
                case 'overrideAll':
                    $session = $this->Session->getData();
                    if (Input::get('act') == 'deleteAll' && !$this->User->hasAccess('delete', 'taxonomiesp')) {
                        $session['CURRENT']['IDS'] = array();
                    } else {
                        $session['CURRENT']['IDS'] = array_intersect($session['CURRENT']['IDS'], $root);
                    }
                    $this->Session->setData($session);
                    break;
                default:
                    if (strlen(Input::get('act'))) {
                        $this->log('Not enough permissions to ' . Input::get('act') . ' Taxonomy ', __METHOD__, TL_ERROR);
                        $this->redirect('contao/main.php?act=error');
                    }
                    break;
            }
        }
    }

    /**
     * @param $varValue
     * @param DataContainer $dc
     * @return string
     * @throws Exception
     */
    public function generateAlias($varValue, \DataContainer $dc)
    {

        // Generate an alias if there is none
        if ($varValue == '') {
            $varValue = StringUtil::generateAlias($dc->activeRecord->name);
        }

        $objAlias = $this->Database->prepare("SELECT id FROM tl_taxonomies WHERE (id=? OR alias=?) AND pid = ?")
            ->execute($dc->id, $varValue, $dc->activeRecord->pid);

        // Check whether the Taxonomy alias exists
        if ($objAlias->numRows > 1) {
            $varValue .= '-' . $dc->id;
        }

        return $varValue;
    }

    /**
     * @param DataContainer $dc
     * @param $row
     * @param $table
     * @param $cr
     * @param null $arrClipboard
     * @return string
     */
    public function pasteTaxonomy(\DataContainer $dc, $row, $table, $cr, $arrClipboard = null)
    {
        $disablePA = false;
        $disablePI = false;

        // Disable all buttons if there is a circular reference
        if ($arrClipboard !== false && ($arrClipboard['mode'] == 'cut' && ($cr == 1 || $arrClipboard['id'] == $row['id']) || $arrClipboard['mode'] == 'cutAll' && ($cr == 1 || in_array($row['id'], $arrClipboard['id'])))) {
            $disablePA = true;
            $disablePI = true;
        }

        // Prevent adding non-root pages on top-level
        if (Input::get('mode') != 'create' && $row['pid'] == 0) {
            $objPage = $this->Database->prepare("SELECT * FROM " . $table . " WHERE id=?")
                ->limit(1)
                ->execute(Input::get('id'));

            if ($objPage->type != 'root') {
                $disablePA = true;

                if ($row['id'] == 0) {
                    $disablePI = true;
                }
            }
        }

        $return = '';

        // Return the buttons
        $imagePasteAfter = Image::getHtml('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id']));
        $imagePasteInto = Image::getHtml('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id']));

        if ($row['id'] > 0) {
            $return = $disablePA ? Image::getHtml('pasteafter_.gif') . ' ' : '<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=1&amp;pid=' . $row['id'] . (!is_array($arrClipboard['id']) ? '&amp;id=' . $arrClipboard['id'] : '')) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteafter'][1], $row['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteAfter . '</a> ';
        }

        return $return . ($disablePI ? Image::getHtml('pasteinto_.gif') . ' ' : '<a href="' . $this->addToUrl('act=' . $arrClipboard['mode'] . '&amp;mode=2&amp;pid=' . $row['id'] . (!is_array($arrClipboard['id']) ? '&amp;id=' . $arrClipboard['id'] : '')) . '" title="' . specialchars(sprintf($GLOBALS['TL_LANG'][$table]['pasteinto'][1], $row['id'])) . '" onclick="Backend.getScrollOffset()">' . $imagePasteInto . '</a> ');

    }

    /**
     * @param $row
     * @param $label
     * @param DataContainer|null $dc
     * @param string $imageAttribute
     * @param bool $blnReturnImage
     * @param bool $blnProtected
     * @return string
     */
    public function addIcon($row, $label, DataContainer $dc = null, $imageAttribute = '', $blnReturnImage = false, $blnProtected = false)
    {
        return $this->addTaxonomyIcon($row, $label, $dc, $imageAttribute, $blnReturnImage, $blnProtected);
    }

    /**
     *
     * @param array $row
     * @param string $label
     * @param \DataContainer $dc
     * @param string $imageAttribute
     * @param boolean $blnReturnImage
     * @param boolean $blnProtected
     *
     * @return string
     */
    public function addTaxonomyIcon($row, $label, \DataContainer $dc = null, $imageAttribute = '', $blnReturnImage = false, $blnProtected = false)
    {
        $image = 'system/modules/fmodule/assets/tag-edit.png';
        $imageAttribute = trim($imageAttribute . ' data-icon="edit.gif" data-icon-disabled="header.gif" ');

        // Mark root pages
        if ($row['pid'] == '0') {
            $label = '<strong>' . $label . '</strong>';
        }

        // Return the image
        return \Image::getHtml($image, '', $imageAttribute) . ' <span>' . $label . '</span>';
    }

    /**
     * Return the "toggle visibility" button
     *
     * @param array $row
     * @param string $href
     * @param string $label
     * @param string $title
     * @param string $icon
     * @param string $attributes
     *
     * @return string
     */
    public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
    {
        if (strlen(Input::get('tid'))) {
            $this->toggleVisibility(Input::get('tid'), (Input::get('state') == 1), (@func_get_arg(12) ?: null));
            $this->redirect($this->getReferer());
        }

        // Check permissions AFTER checking the tid, so hacking attempts are logged
        if (!$this->User->hasAccess('tl_taxonomies::published', 'alexf')) {
            return '';
        }

        $href .= '&amp;tid=' . $row['id'] . '&amp;state=' . ($row['published'] ? '' : 1);

        if (!$row['published']) {
            $icon = 'invisible.gif';
        }

        $objPage = $this->Database->prepare("SELECT * FROM tl_taxonomies WHERE id=?")
            ->limit(1)
            ->execute($row['id']);

        if (!$this->User->hasAccess($row['type'], 'alpty') || !$this->User->isAllowed(BackendUser::CAN_EDIT_PAGE, $objPage->row())) {
            return Image::getHtml($icon) . ' ';
        }

        return '<a href="' . $this->addToUrl($href) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label, 'data-state="' . ($row['published'] ? 1 : 0) . '"') . '</a> ';
    }


    /**
     *
     * @param integer $intId
     * @param boolean $blnVisible
     * @param DataContainer $dc
     */
    public function toggleVisibility($intId, $blnVisible, DataContainer $dc = null)
    {
        // Set the ID and action
        Input::setGet('id', $intId);
        Input::setGet('act', 'toggle');

        if ($dc) {
            $dc->id = $intId; // see #8043
        }

        $this->checkPermission();

        // Check the field access
        if (!$this->User->hasAccess('tl_taxonomies::published', 'alexf')) {
            $this->log('Not enough permissions to publish/unpublish Taxonomy ID "' . $intId . '"', __METHOD__, TL_ERROR);
            $this->redirect('contao/main.php?act=error');
        }

        $objVersions = new Versions('tl_taxonomies', $intId);
        $objVersions->initialize();

        // Trigger the save_callback
        if (is_array($GLOBALS['TL_DCA']['tl_taxonomies']['fields']['published']['save_callback'])) {
            foreach ($GLOBALS['TL_DCA']['tl_taxonomies']['fields']['published']['save_callback'] as $callback) {
                if (is_array($callback)) {
                    $this->import($callback[0]);
                    $blnVisible = $this->{$callback[0]}->{$callback[1]}($blnVisible, ($dc ?: $this));
                } elseif (is_callable($callback)) {
                    $blnVisible = $callback($blnVisible, ($dc ?: $this));
                }
            }
        }

        // Update the database
        $this->Database->prepare("UPDATE tl_taxonomies SET tstamp=" . time() . ", published='" . ($blnVisible ? '1' : '') . "' WHERE id=?")
            ->execute($intId);

        $objVersions->create();
        $this->log('A new version of record "tl_taxonomies.id=' . $intId . '" has been created' . $this->getParentEntries('tl_taxonomies', $intId), __METHOD__, TL_GENERAL);
    }

    /**
     * @param array
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @param string
     * @return string
     */
    public function copyWithSubTaxonomies($row, $href, $label, $title, $icon, $attributes, $table)
    {
        if ($GLOBALS['TL_DCA'][$table]['config']['closed']) {
            return '';
        }

        return '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
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
    public function cutTaxonomy($row, $href, $label, $title, $icon, $attributes)
    {
        return '<a href="' . $this->addToUrl($href . '&amp;id=' . $row['id']) . '" title="' . specialchars($title) . '"' . $attributes . '>' . Image::getHtml($icon, $label) . '</a> ';
    }
}