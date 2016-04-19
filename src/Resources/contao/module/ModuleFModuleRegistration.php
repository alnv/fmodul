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
    protected $strTemplate = 'mod_fmodule_registration';

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
        $moduleDCA = DCACreator::getInstance();
        $arrModule = $moduleDCA->getModuleByTableName($tablename);
        $dcaData = DCAModuleData::getInstance();
        $dcaFields = $dcaData->setFields($arrModule);

        //
        $this->tableless = true;
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
        if (!$this->disableCaptcha)
        {
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
            if (!class_exists($strClass))
            {
                $strClass = 'FormCaptcha';
            }

            /** @var \FormCaptcha $objCaptcha */
            $objCaptcha = new $strClass($arrCaptcha);

            if (\Input::post('FORM_SUBMIT') == 'fm_registration')
            {
                $objCaptcha->validate();

                if ($objCaptcha->hasErrors())
                {
                    $doNotSubmit = true;
                }
            }
        }

        $arrUser = array();
        $arrFields = array();
        $hasUpload = false;
        $i = 0;

        foreach ($this->fm_editable_fields as $field) {

            $arrData = $dcaFields[$field];

            if(!isset($arrData['eval']['fmEditable']) && $arrData['eval']['fmEditable'] != true)
            {
                continue;
            }

            // Map checkboxWizards to regular checkbox widgets
            if ($arrData['inputType'] == 'checkboxWizard')
            {
                $arrData['inputType'] = 'checkbox';
            }

            // Map fileTrees to upload widgets (see #8091)
            if ($arrData['inputType'] == 'fileTree')
            {
                $arrData['inputType'] = 'upload';
            }

            /** @var \Widget $strClass */
            $strClass = $GLOBALS['TL_FFL'][$arrData['inputType']];

            // Continue if the class is not defined
            if (!class_exists($strClass))
            {
                continue;
            }

            $arrData['eval']['tableless'] = $this->tableless;
            $arrData['eval']['required'] = $arrData['eval']['mandatory'];

            $objWidget = new $strClass($strClass::getAttributesFromDca($arrData, $field, $arrData['default'], '', '', $this));

            $objWidget->storeValues = true;
            $objWidget->rowClass = 'row_' . $i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');

            // Increase the row count if its a password field
            if ($objWidget instanceof \FormPassword)
            {
                $objWidget->rowClassConfirm = 'row_' . ++$i . ((($i % 2) == 0) ? ' even' : ' odd');
            }

            // Validate input
            if (\Input::post('FORM_SUBMIT') == 'fm_registration')
            {
                //
            }

            if ($objWidget instanceof \uploadable)
            {
                $hasUpload = true;
            }

            $temp = $objWidget->parse();

            $this->Template->fields .= $temp;
            $arrFields[$arrData['eval']['fmGroup']][$field] .= $temp;

            ++$i;

        }

        // Captcha
        if (!$this->disableCaptcha)
        {
            $objCaptcha->rowClass = 'row_'.$i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');
            $strCaptcha = $objCaptcha->parse();

            $this->Template->fields .= $strCaptcha;
            $arrFields['captcha']['captcha'] .= $strCaptcha;
        }

        $this->Template->rowLast = 'row_' . ++$i . ((($i % 2) == 0) ? ' even' : ' odd');
        $this->Template->enctype = $hasUpload ? 'multipart/form-data' : 'application/x-www-form-urlencoded';
        $this->Template->hasError = $doNotSubmit;

        // Create new user if there are no errors
        if (\Input::post('FORM_SUBMIT') == 'fm_registration' && !$doNotSubmit)
        {
            // create new item
            var_dump(\Input::post('title'));
            exit;
        }

        /*
        $this->Template->loginDetails = $GLOBALS['TL_LANG']['tl_member']['loginDetails'];
        $this->Template->addressDetails = $GLOBALS['TL_LANG']['tl_member']['addressDetails'];
        $this->Template->contactDetails = $GLOBALS['TL_LANG']['tl_member']['contactDetails'];
        $this->Template->personalData = $GLOBALS['TL_LANG']['tl_member']['personalData'];
        */

        $this->Template->captchaDetails = $GLOBALS['TL_LANG']['MSC']['securityQuestion'];

        // Add the groups
        /*
        foreach ($arrFields as $k=>$v)
        {
            $this->Template->$k = $v; // backwards compatibility

            $key = $k . (($k == 'personal') ? 'Data' : 'Details');
            $arrGroups[$GLOBALS['TL_LANG']['tl_member'][$key]] = $v;
        }
        */

        $this->Template->categories = $arrGroups;
        $this->Template->formId = 'fm_registration';
        $this->Template->slabel = specialchars($GLOBALS['TL_LANG']['MSC']['register']);
        $this->Template->action = \Environment::get('indexFreeRequest');
        $this->Template->captcha = $arrFields['captcha']['captcha']; // backwards compatibility

    }
}