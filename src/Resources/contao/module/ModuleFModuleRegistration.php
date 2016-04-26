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

use Contao\Controller;
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
     * @var string
     */
    protected $strTableData = '';

    /**
     * @var string
     */
    protected $strTableName = '';

    /**
     * @var array
     */
    protected $dcaFields = array();

    /**
     * @var string
     */
    protected $strPid = '';

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
        $this->strTableName = $this->f_select_module;
        $this->strTableData = $this->strTableName . '_data';
        $this->strPid = $this->f_select_wrapper;
        $moduleDCA = DCACreator::getInstance();
        $arrModule = $moduleDCA->getModuleByTableName($this->strTableName);
        $dcaData = DCAModuleData::getInstance();
        $this->dcaFields = $dcaData->setFields($arrModule);

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

            $arrData = $this->dcaFields[$field];
            $arrData = $this->convertWidgetToField($arrData);

            if (!isset($arrData['eval']['fmEditable']) && $arrData['eval']['fmEditable'] != true) continue;

            $strClass = $this->fieldClassExist($arrData['inputType']);

            if ($strClass == false) continue;

            $objWidget = new $strClass($strClass::getAttributesFromDca($arrData, $field, $arrData['default'], '', '', $this));
            $objWidget->storeValues = true;
            $objWidget->rowClass = 'row_' . $i . (($i == 0) ? ' row_first' : '') . ((($i % 2) == 0) ? ' even' : ' odd');

            if ($arrData['inputType'] == 'upload') {
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
                $varValue = $this->decodeValue($varValue);
                $varValue = $this->replaceInsertTags($varValue);
                $rgxp = $arrData['eval']['rgxp'];

                // Convert date formats into timestamps (check the eval setting first -> #3063)
                if ($varValue != '' && in_array($rgxp, array('date', 'time', 'datim'))) {
                    try {
                        $objDate = new \Date($varValue, \Date::getFormatFromRgxp($rgxp));
                        $varValue = $objDate->tstamp;
                    } catch (\OutOfBoundsException $e) {
                        $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue));
                    }
                }

                // Make sure that unique fields are unique (check the eval setting first -> #3063)
                if ($arrData['eval']['unique'] && $varValue != '' && !$this->Database->isUniqueValue($this->strTableData, $field, $varValue)) {
                    $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $arrData['label'][0] ?: $field));
                }

                // Save callback
                if ($objWidget->submitInput() && !$objWidget->hasErrors() && is_array($arrData['save_callback'])) {
                    foreach ($arrData['save_callback'] as $callback) {
                        try {
                            if (is_array($callback)) {
                                $this->import($callback[0]);
                                $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, null);
                            } elseif (is_callable($callback)) {
                                $varValue = $callback($varValue, null);
                            }
                        } catch (\Exception $e) {
                            $objWidget->class = 'error';
                            $objWidget->addError($e->getMessage());
                        }
                    }
                }

                // Store the current value
                if ($objWidget->hasErrors()) {
                    $doNotSubmit = true;
                } elseif ($objWidget->submitInput()) {
                    // Set the correct empty value (see #6284, #6373)
                    if ($varValue === '') {
                        $varValue = $objWidget->getEmptyValue();
                    }
                    // Encrypt the value (see #7815)
                    if ($arrData['eval']['encrypt']) {
                        $varValue = \Encryption::encrypt($varValue);
                    }
                    // Set the new value
                    $arrValidData[$field] = $varValue;
                }

                // store file
                $Files = $_SESSION['FILES'];
                if ($Files && isset($Files[$field]) && $this->fm_storeFile) {

                    $strRoot = TL_ROOT . '/';
                    $strUuid = $Files[$field]['uuid'];
                    $strFile = substr($Files[$field]['tmp_name'], strlen($strRoot));
                    $arrFiles = \FilesModel::findByPath($strFile);

                    if ($arrFiles !== null) {
                        $strUuid = $arrFiles->uuid;
                    }

                    $arrValidData[$field] = $strUuid;
                }
                // reset session
                if ($Files && isset($Files[$field]))
                {
                    unset($_SESSION['FILES'][$field]);
                }
            }

            if ($objWidget instanceof \uploadable) {
                $hasUpload = true;
            }

            $temp = $objWidget->parse(); // $objWidget->generate();

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
        $this->Template->authorDetails = $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['authorDetails'];
        $this->Template->sourceDetails = $GLOBALS['TL_LANG']['tl_fmodules_language_pack']['sourceDetails'];
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
     * @param $varValue
     * @return mixed|string
     */
    private function decodeValue($varValue)
    {
        if (class_exists('StringUtil')) {
            $varValue = \StringUtil::decodeEntities($varValue);
        } else {
            // backwards compatible
            $varValue = \Input::decodeEntities($varValue);
        }
        return $varValue;
    }


    /**
     * @param $inputType
     * @return bool|\Widget
     */
    private function fieldClassExist($inputType)
    {
        /** @var \Widget $strClass */
        $strClass = $GLOBALS['TL_FFL'][$inputType];

        if ($inputType == 'text') {
            $strClass = '\FModule\FormTextFieldCustom';
        }

        // Continue if the class is not defined
        if (!class_exists($strClass)) {
            return false;
        }

        return $strClass;
    }

    /**
     * @param $arrData
     * @return mixed
     */
    protected function convertWidgetToField($arrData)
    {
        // Map checkboxWizards to regular checkbox widgets
        if ($arrData['inputType'] == 'checkboxWizard') {
            $arrData['inputType'] = 'checkbox';
        }

        // Map fileTrees to upload widgets (see #8091)
        if ($arrData['inputType'] == 'fileTree') {
            $arrData['inputType'] = 'upload';
        }

        $arrData['eval']['tableless'] = $this->tableless;
        $arrData['eval']['required'] = $arrData['eval']['mandatory'];

        return $arrData;
    }

    /**
     * @param $arrData
     * @return mixed
     */
    protected function createGeoCoding($arrData)
    {

        $countries = $this->getCountries();
        $address_street = $arrData['address_street'] ? $arrData['address_location'] : '';
        $address_addition = $arrData['address_addition'] ? $arrData['address_location'] : '';
        $address_location = $arrData['address_location'] ? $arrData['address_location'] : '';
        $address_zip = $arrData['address_zip'] ? $arrData['address_zip'] : '';
        $address_country = $arrData['address_country'] ? $countries[$arrData['address_country']] : '';
        $geo_address = '';

        if ($address_location || $address_zip || $address_country) {
            $geo_address = $address_street . ' ' . $address_addition . ' ' . $address_zip . ' ' . $address_location . ' ' . $address_country;
        }

        if (!$geo_address) {
            $geo_address = $arrData['geo_address'] ? $arrData['geo_address'] : '';
        }

        //
        $cords = array();

        //
        if ($geo_address) {
            $geoCoding = GeoCoding::getInstance();
            $cords = $geoCoding->getGeoCords($geo_address, $address_country);
        }

        if (!empty($cords)) {
            $arrData['geo_latitude'] = $cords['lat'] ? $cords['lat'] : '';
            $arrData['geo_longitude'] = $cords['lng'] ? $cords['lng'] : '';
        }

        return $arrData;
    }

    /**
     * @param $arrData
     * @throws \Exception
     */
    protected function createNewEntity($arrData)
    {
        $tableData = $this->strTableData;

        // set default values
        $arrData['tstamp'] = time();
        $arrData['pid'] = $this->strPid;
        $arrData['alias'] = $this->generateAlias($arrData['alias'], $arrData);

        // search for geo cords
        $arrData = $this->createGeoCoding($arrData);

        // set default values from fe
        if ($this->fm_defaultValues) {

            $defaultValues = $this->fm_defaultValues ? deserialize($this->fm_defaultValues) : array();

            foreach ($defaultValues as $defaultValue) {

                $col = $defaultValue['key'];

                // parse value
                $value = $defaultValue['value'];
                $value = $this->decodeValue($value);
                $value = $this->replaceInsertTags($value);
                $dcaData = $this->dcaFields[$col];
                $dcaData = $this->convertWidgetToField($dcaData);

                \Input::setPost($col, $value); // check if get or post

                $strClass = $this->fieldClassExist($dcaData['inputType']);

                if ($strClass == false) {
                    continue;
                }

                // validate
                $objWidget = new $strClass($strClass::getAttributesFromDca($dcaData, $col, $dcaData['default'], '', '', $this));
                $objWidget->storeValues = true;
                $objWidget->validate();
                $varValue = $objWidget->value;
                $rgxp = $dcaData['eval']['rgxp'];

                // Convert date formats into timestamps (check the eval setting first -> #3063)
                if ($varValue != '' && in_array($rgxp, array('date', 'time', 'datim'))) {
                    try {
                        $objDate = new \Date($varValue, \Date::getFormatFromRgxp($rgxp));
                        $varValue = $objDate->tstamp;
                    } catch (\OutOfBoundsException $e) {
                        $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['invalidDate'], $varValue));
                    }
                }

                // Make sure that unique fields are unique (check the eval setting first -> #3063)
                if ($dcaData['eval']['unique'] && $varValue != '' && !$this->Database->isUniqueValue($this->strTableData, $col, $varValue)) {
                    $objWidget->addError(sprintf($GLOBALS['TL_LANG']['ERR']['unique'], $dcaData['label'][0] ?: $col));
                }

                // Save callback
                if ($objWidget->submitInput() && !$objWidget->hasErrors() && is_array($dcaData['save_callback'])) {
                    foreach ($dcaData['save_callback'] as $callback) {
                        try {
                            if (is_array($callback)) {
                                $this->import($callback[0]);
                                $varValue = $this->{$callback[0]}->{$callback[1]}($varValue, null);
                            } elseif (is_callable($callback)) {
                                $varValue = $callback($varValue, null);
                            }
                        } catch (\Exception $e) {
                            $objWidget->class = 'error';
                            $objWidget->addError($e->getMessage());
                        }
                    }
                }

                if (!$objWidget->hasErrors()) {
                    // Set the correct empty value (see #6284, #6373)
                    if ($varValue === '') {
                        $varValue = $objWidget->getEmptyValue();
                    }
                    // Encrypt the value (see #7815)
                    if ($dcaData['eval']['encrypt']) {
                        $varValue = \Encryption::encrypt($varValue);
                    }
                    // Set the new value
                    $arrData[$col] = $varValue;
                }

            }
        }

        // set author
        if (!$arrData['author']) {
            $arrData['author'] = $this->fm_EntityAuthor;
        }

        // generate sql query
        $values = array();
        $cols = array();
        $placeholder = array();

        $arrCheckBoxes = array('markerSRC' => 'addMarker', 'singleSRC' => 'addImage', 'enclosure' => 'addEnclosure'); // nur ein Hack

        foreach ($arrData as $col => $value) {

            $eval = $this->dcaFields[$col]['eval'];

            // activate palette in BE
            if ($arrCheckBoxes[$col] && $value) {
                $cols[] = $arrCheckBoxes[$col];
                $values[] = '1';
                $placeholder[] = '?';
            }

            $cols[] = $col;

            // check for multiple values
            if (isset($eval['multiple']) && $eval['multiple'] == true && isset($eval['csv'])) {
                // delimiter
                $delimiter = $eval['csv'];
                if ($delimiter === ',' && is_array($value)) {
                    $value = implode($delimiter, $value);
                }
            }

            // exception for cssID
            if ($col == 'cssID') {
                $value = explode(',', $value);
                $value = serialize($value);
            }

            $values[] = $value;
            $placeholder[] = '?';
        }

        $strCols = implode(',', $cols);
        $strPlaceholder = implode(',', $placeholder);
        $strQuery = 'INSERT INTO ' . $tableData . ' (' . $strCols . ') VALUES (' . $strPlaceholder . ')';

        // create new entity
        $this->Database->prepare($strQuery)->execute($values);

        // send Notification
        if ($this->fm_addNotificationEmail) {
            $this->sendNotification($arrData);
        }

        // send Confirmation
        if ($this->fm_addConfirmationEmail) {
            $this->sendConfirmation($arrData);
        }

        // Check whether there is a jumpTo page
        if ($this->jumpTo) {
            $objPage = \PageModel::findWithDetails($this->jumpTo);
            $this->jumpToOrReload($objPage->row());
        }

        $this->reload();

    }

    /**
     * @param $varValue
     * @param array $arrData
     * @return string
     * @throws \Exception
     */
    protected function generateAlias($varValue, $arrData = array())
    {
        $autoAlias = false;

        if (!$arrData['title'] || empty($arrData)) {
            return 'Alias-' . substr(md5(time()), 12);
        }

        // Generate alias if there is none
        if ($varValue == '') {
            $autoAlias = true;
            $varValue = \StringUtil::generateAlias($arrData['title']);
        }

        $table = $this->strTableData;
        $pid = $this->strPid;

        $objAlias = null;

        if ($table && $pid) {
            $objAlias = $this->Database->prepare("SELECT id FROM " . $table . " WHERE alias = ? AND pid = ?")->execute($varValue, $pid);
        }

        // Check whether the alias exists
        if ($objAlias && $objAlias->numRows > 1 && !$autoAlias) {
            throw new \Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
        }

        // Add hash to alias
        if ($objAlias && $objAlias->numRows && $autoAlias) {
            $varValue .= '-' . substr(md5(time()), 12);
        }

        return $varValue;
    }

    /**
     * @param $arrData
     */
    protected function sendNotification($arrData)
    {
        // set
        $name = $this->fm_notificationEmailName ? $this->replaceInsertTags($this->fm_notificationEmailName) : '';
        $subject = $this->fm_notificationEmailSubject ? $this->replaceInsertTags($this->fm_notificationEmailSubject) : '';
        $adminEmail = $this->getAdminEmailFromContext($this->fm_sendNotificationToAdmin);
        $strToEmails = $this->fm_notificationEmailList ? $this->fm_notificationEmailList : '';
        $fromEmail = $this->fm_notificationSender ? $this->fm_notificationSender : $this->getAdminEmail();

        $toEmails = array();
        $arrToEmails = explode(',', $strToEmails);

        foreach ($arrToEmails as $email) {
            $toEmails[] = $email;
        }

        if ($adminEmail) {
            $toEmails[] = $adminEmail;
        }

        // clean sendTo emails
        $toEmails = array_filter($toEmails);
        $toEmails = array_unique($toEmails);

        $body = '';
        foreach ($arrData as $key => $value) {
            // parse array
            if (is_array($value)) {
                $value = implode(',', $value);
            }

            // replace insert tags
            $value = $this->replaceInsertTags($value);

            $body .= $key . ': ' . $value . '</br>';
        }

        // send email
        $objEmail = new \Email();
        $objEmail->from = $fromEmail;
        $objEmail->fromName = $name;
        $objEmail->subject = $subject;
        $objEmail->html = $body;
        $objEmail->sendTo($toEmails);

    }

    /**
     * @param $arrData
     * @return null
     */
    protected function sendConfirmation($arrData)
    {
        // set
        $name = $this->fm_confirmationEmailName ? $this->replaceInsertTags($this->fm_confirmationEmailName) : '';
        $subject = $this->fm_confirmationEmailSubject ? $this->replaceInsertTags($this->fm_confirmationEmailSubject) : '';
        $adminEmail = $this->getAdminEmailFromContext($this->fm_sendConfirmationToAdmin);
        $strToEmails = $this->fm_confirmationEmailList ? $this->fm_confirmationEmailList : '';
        $fromEmail = $this->fm_confirmationSender ? $this->fm_confirmationSender : $this->getAdminEmail();
        $recipient = $this->fm_confirmationRecipientEmail ? $arrData[$this->fm_confirmationRecipientEmail] : '';
        $body = $this->fm_confirmationBody ? $this->replaceInsertTags($this->fm_confirmationBody) : '';

        $toEmails = array();
        $ccEmails = array();
        $arrToEmails = explode(',', $strToEmails);

        foreach ($arrToEmails as $email) {
            $ccEmails[] = $email;
        }

        if ($adminEmail) {
            $ccEmails[] = $adminEmail;
        }

        if ($recipient) {
            $toEmails[] = $recipient;
        }

        // break up if no recipient given
        if (empty($toEmails)) {
            return null;
        }

        // clean sendTo emails
        $toEmails = array_filter($toEmails);
        $toEmails = array_unique($toEmails);

        // clean sendBcc emails
        $ccEmails = array_filter($ccEmails);
        $ccEmails = array_unique($ccEmails);

        // send email
        $objEmail = new \Email();
        $objEmail->from = $fromEmail;
        $objEmail->fromName = $name;
        $objEmail->subject = $subject;
        $objEmail->html = $body;
        $objEmail->sendBcc($ccEmails);
        $objEmail->sendTo($toEmails);

    }

    /**
     * @param $adminEmail
     * @return mixed|null|string
     */
    private function getAdminEmailFromContext($adminEmail)
    {
        $return = '';

        if ($adminEmail) {
            $return = \Config::get('adminEmail');
        }

        return $return;
    }

    /**
     * @return mixed|null
     */
    private function getAdminEmail()
    {
        return \Config::get('adminEmail');
    }
}