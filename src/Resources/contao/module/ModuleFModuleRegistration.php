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

use Contao\Module;

/**
 * Class ModuleFModuleRegistration
 * @package FModule
 */
class ModuleFModuleRegistration extends Module
{
    /**
     * @var string
     */
    protected $strTemplate = 'sign_default';

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

        $this->fm_editable_fields = deserialize($this->fm_editable_fields);

        // Return if there are no editable fields
        if (!is_array($this->fm_editable_fields) || empty($this->fm_editable_fields)) {
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

        // needed for options
        \Input::setGet('id', $this->f_select_wrapper);

        $GLOBALS['TL_LANGUAGE'] = $objPage->language;

        // get fields
        $tablename = $this->f_select_module;
        $tableData = $tablename . '_data';
        $moduleDCA = DCACreator::getInstance();
        $arrModule = $moduleDCA->getModuleByTableName($tablename);
        $dcaData = DCAModuleData::getInstance();
        $dcaFields = $dcaData->setFields($arrModule);

        // set tpl
        if ($this->fm_sign_template != '') {
            /** @var \FrontendTemplate|object $objTemplate */
            $objTemplate = new \FrontendTemplate($this->fm_sign_template);

            $this->Template = $objTemplate;
            $this->Template->setData($this->arrData);
        }

        //
        $this->Template->tableless = $this->tableless;
        $this->Template->fields = '';
        $this->Template->tableless = $this->tableless;
        $objCaptcha = null;
        $doNotSubmit = false;

        //
        $arrGroups = array(
            'teaser' => array(),
            'date' => array(),
            'image' => array(),
            'enclosure' => array(),
            'map' => array(),
            'expert' => array()
        );

        // load language
        \System::loadLanguageFile('tl_fmodules_language_pack');

        // Captcha
        if (!$this->disableCaptcha) {
            $arrCaptcha = array
            (
                'id' => 'registration',
                'label' => $GLOBALS['TL_LANG']['MSC']['securityQuestion'],
                'type' => 'captcha',
                'mandatory' => true,
                'required' => true,
                'tableless' => $this->tableless
            );

            /** @var \FormCaptcha $strClass */
            $strClass = $GLOBALS['TL_FFL']['captcha'];

            // Fallback to default if the class is not defined
            if (!class_exists($strClass)) {
                $strClass = 'FormCaptcha';
            }

            /** @var \FormCaptcha $objCaptcha */
            $objCaptcha = new $strClass($arrCaptcha);

            if (\Input::post('FORM_SUBMIT') == 'fm_registration') {
                $objCaptcha->validate();

                if ($objCaptcha->hasErrors()) {
                    $doNotSubmit = true;
                }
            }
        }

        $arrValidData = array();
        $arrFields = array();
        $hasUpload = false;
        $i = 0;

        foreach ($this->fm_editable_fields as $field) {

            $arrData = $dcaFields[$field];

            if (!isset($arrData['eval']['fmEditable']) && $arrData['eval']['fmEditable'] != true) {
                continue;
            }

            // Map checkboxWizards to regular checkbox widgets
            if ($arrData['inputType'] == 'checkboxWizard') {
                $arrData['inputType'] = 'checkbox';
            }

            // Map fileTrees to upload widgets (see #8091)
            if ($arrData['inputType'] == 'fileTree') {
                $arrData['inputType'] = 'upload';
            }

            /** @var \Widget $strClass */
            $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

            // Continue if the class is not defined
            if (!class_exists($strClass)) {
                continue;
            }

            $arrData['eval']['tableless'] = $this->tableless;
            $arrData['eval']['required'] = $arrData['eval']['mandatory'];

            $objWidget = new $strClass($strClass::getAttributesFromDca($arrData, $field, $arrData['default'], '', '', $this));

            $objWidget->storeValues = true;
            $objWidget->rowClass = 'row_' . $i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');

            if($arrData['inputType'] == 'upload')
            {
                $objWidget->storeFile = $this->fm_storeFile;
                $objWidget->uploadFolder = $this->fm_uploadFolder;
                $objWidget->useHomeDir = $this->fm_useHomeDir;
                $objWidget->doNotOverwrite = $this->fm_doNotOverwrite;
                $objWidget->extensions = $this->fm_extensions;
                $objWidget->maxlength = $this->fm_maxlength;
            }

            // Increase the row count if its a password field
            if ($objWidget instanceof \FormPassword) {
                $objWidget->rowClassConfirm = 'row_' . ++$i . ((($i % 2) == 0) ? ' even' : ' odd');
            }

            // Validate input
            if (\Input::post('FORM_SUBMIT') == 'fm_registration') {

                $objWidget->validate();

                $varValue = $objWidget->value;

                $rgxp = $arrData['eval']['rgxp'];

                // Convert date formats into timestamps (check the eval setting first -> #3063)
                if ($varValue != '' && in_array($rgxp, array('date', 'time', 'datim')))
                {
                    try
                    {
                        $objDate = new \Date($varValue, \Date::getFormatFromRgxp($rgxp));
                        $varValue = $objDate->tstamp;
                    }
                    catch (\OutOfBoundsException $e)
                    {
                        $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue));
                    }
                }

                // Make sure that unique fields are unique (check the eval setting first -> #3063)
                if ($arrData['eval']['unique'] && $varValue != '' && !$this->Database->isUniqueValue($tableData, $field, $varValue))
                {
                    $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $arrData['label'][0] ?: $field));
                }

                // Save callback
                if ($objWidget->submitInput() && !$objWidget->hasErrors() && is_array($arrData['save_callback']))
                {
                    foreach ($arrData['save_callback'] as $callback)
                    {
                        try
                        {
                            if (is_array($callback))
                            {
                                $this->import($callback[0]);
                                $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, null);
                            }
                            elseif (is_callable($callback))
                            {
                                $varValue = $callback($varValue, null);
                            }
                        }
                        catch (\Exception $e)
                        {
                            $objWidget->class = 'error';
                            $objWidget->addError($e->getMessage());
                        }
                    }
                }
                // Store the current value
                if ($objWidget->hasErrors())
                {
                    $doNotSubmit = true;
                }

                elseif ($objWidget->submitInput())
                {
                    // Set the correct empty value (see #6284, #6373)
                    if ($varValue === '')
                    {
                        $varValue = $objWidget->getEmptyValue();
                    }
                    // Encrypt the value (see #7815)
                    if ($arrData['eval']['encrypt'])
                    {
                        $varValue = \Encryption::encrypt($varValue);
                    }
                    // Set the new value
                    $arrValidData[$field] = $varValue;
                }

                // store file
                $Files = $_SESSION['FILES'];
                if($Files && $Files[$field])
                {
                    $strRoot = TL_ROOT . '/';
                    $strUuid = $Files[$field]['uuid'];
                    $strFile = substr($Files[$field]['tmp_name'], strlen($strRoot));
                    if($strUuid === null)
                    {
                        $strUuid = \StringUtil::binToUuid(\Dbafs::addResource($strFile)->uuid);
                    }
                    $arrValidData[$field] = $strUuid;
                }
            }

            if ($objWidget instanceof \uploadable) {
                $hasUpload = true;
            }

            $temp = $objWidget->parse();

            $this->Template->fields .= $temp;
            $arrFields[$arrData['eval']['fmGroup']][$field] .= $temp;

            ++$i;

        }

        // Captcha
        if (!$this->disableCaptcha) {
            $objCaptcha->rowClass = 'row_' . $i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');
            $strCaptcha = $objCaptcha->parse();

            $this->Template->fields .= $strCaptcha;
            $arrFields['captcha']['captcha'] .= $strCaptcha;
        }

        $this->Template->rowLast = 'row_' . ++$i . ((($i % 2) == 0) ? ' even' : ' odd');
        $this->Template->enctype = $hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
        $this->Template->hasError = $doNotSubmit;

        // Create new entity if there are no errors
        if (\Input::post('FORM_SUBMIT') == 'fm_registration' && !$doNotSubmit) {
            $this->createNewEntity($arrValidData);
        }

        $this->Template->teaserDetails = $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['teaserData'];
        $this->Template->dateDetails = $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['dateDetails'];
        $this->Template->imageDetails = $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['imageDetails'];
        $this->Template->enclosureDetails = $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['enclosureDetails'];
        $this->Template->expertDetails = $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['expertDetails'];
        $this->Template->mapDetails = $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['mapDetails'];
        $this->Template->otherDetails = $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['otherDetails'];
        $this->Template->captchaDetails = $GLOBALS['TL_LANG']['MSC']['securityQuestion'];

        // Add the groups
        foreach ($arrFields as $k => $v) {
            $this->Template->$k = $v; // backwards compatibility

            $key = $k . (($k == 'teaser') ? 'Data' : 'Details');
            $arrGroups[$GLOBALS['TL_LANG']['tl_fmodules_language_pack'][$key]] = $v;
        }

        $this->Template->categories = $arrGroups;
        $this->Template->formId = 'fm_registration';
        $this->Template->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['register']);
        $this->Template->action = \Environment::get('indexFreeRequest');
        $this->Template->captcha = $arrFields['captcha']['captcha']; // backwards compatibility

    }

    /**
     * @param $arrData
     */
    protected function createNewEntity($arrData)
    {
        $arrData['tstamp'] = time();
        var_dump($arrData);
        exit;
    }

}