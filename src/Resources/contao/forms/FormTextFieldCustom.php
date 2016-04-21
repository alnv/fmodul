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
 * Class FormTextFieldCustom
 * @package FModule
 */
class FormTextFieldCustom extends \Widget
{

    /**
     * Submit user input
     *
     * @var boolean
     */
    protected $blnSubmitInput = true;

    /**
     * Add a for attribute
     *
     * @var boolean
     */
    protected $blnForAttribute = true;

    /**
     * Template
     *
     * @var string
     */
    protected $strTemplate = 'form_textfield';

    /**
     * The CSS class prefix
     *
     * @var string
     */
    protected $strPrefix = 'widget widget-text';

    /**
     * Disable the for attribute if the "multiple" option is set
     *
     * @param array $arrAttributes
     */
    public function __construct($arrAttributes = null)
    {
        parent::__construct($arrAttributes);
        if ($this->multiple) {
            $this->blnForAttribute = false;
        }
    }


    /**
     * Add specific attributes
     *
     * @param string $strKey The attribute key
     * @param mixed $varValue The attribute value
     */
    public function __set($strKey, $varValue)
    {
        switch ($strKey) {
            case 'maxlength':
                if ($varValue > 0) {
                    $this->arrAttributes['maxlength'] = $varValue;
                }
                break;

            case 'mandatory':
                if ($varValue) {
                    $this->arrAttributes['required'] = 'required';
                } else {
                    unset($this->arrAttributes['required']);
                }
                parent::__set($strKey, $varValue);
                break;

            case 'min':
            case 'max':
            case 'step':
            case 'placeholder':
                $this->arrAttributes[$strKey] = $varValue;
                break;

            default:
                parent::__set($strKey, $varValue);
                break;
        }
    }


    /**
     * Return a parameter
     *
     * @param string $strKey The parameter key
     *
     * @return mixed The parameter value
     */
    public function __get($strKey)
    {
        switch ($strKey) {
            case 'value':
                // Hide the Punycode format (see #2750)
                if ($this->rgxp == 'url') {
                    return \Idna::decode($this->varValue);
                } elseif ($this->rgxp == 'email' || $this->rgxp == 'friendly') {
                    return \Idna::decodeEmail($this->varValue);
                } else {
                    return $this->varValue;
                }
                break;

            case 'type':
                // Use the HTML5 types (see #4138) but not the date, time and datetime types (see #5918)
                if ($this->hideInput) {
                    return 'password';
                }

                if ($this->strFormat != 'xhtml') {
                    switch ($this->rgxp) {
                        case 'digit':
                            // Allow floats (see #7257)
                            if (!isset($this->arrAttributes['step'])) {
                                $this->addAttribute('step', 'any');
                            }
                        // NO break; here

                        case 'natural':
                            return 'number';
                            break;

                        case 'phone':
                            return 'tel';
                            break;

                        case 'email':
                            return 'email';
                            break;

                        case 'url':
                            return 'url';
                            break;
                    }
                }

                return 'text';
                break;

            default:
                return parent::__get($strKey);
                break;
        }
    }


    /**
     * Trim the values
     *
     * @param mixed $varInput The user input
     *
     * @return mixed The validated user input
     */
    protected function validator($varInput)
    {
        if (is_array($varInput)) {
            return parent::validator($varInput);
        }

        // Convert to Punycode format (see #5571)
        if ($this->rgxp == 'url') {
            $varInput = \Idna::encodeUrl($varInput);
        } elseif ($this->rgxp == 'email' || $this->rgxp == 'friendly') {
            $varInput = \Idna::encodeEmail($varInput);
        }

        return parent::validator($varInput);
    }


    /**
     * Generate the widget and return it as string
     *
     * @return string The widget markup
     */
    public function generate()
    {

        $strType = $this->hideInput ? 'password' : 'text';

        if (!$this->multiple) {
            // Hide the Punycode format (see #2750)
            if ($this->rgxp == 'url') {
                $this->value = \Idna::decode($this->value);
            } elseif ($this->rgxp == 'email' || $this->rgxp == 'friendly') {
                $this->value = \Idna::decodeEmail($this->value);
            }

            return sprintf('<input type="%s" name="%s" id="ctrl_%s" class="text%s%s" value="%s"%s%s',
                $strType,
                $this->strName,
                $this->strId,
                ($this->hideInput ? ' password' : ''),
                (($this->strClass != '') ? ' ' . $this->strClass : ''),
                specialchars($this->value),
                $this->getAttributes(),
                $this->strTagEnding) . $this->addSubmit();
        }

        // Return if field size is missing
        if (!$this->size) {
            return '';
        }

        if (!is_array($this->value)) {
            $this->value = array($this->value);
        }

        $arrFields = array();

        for ($i = 0; $i < $this->size; $i++) {

            $arrFields[] = sprintf('<input type="%s" name="%s[]" id="ctrl_%s" class="text_%s" value="%s"%s%s',
                $strType,
                $this->strName,
                $this->strId . '_' . $i,
                $this->size,
                specialchars(@$this->value[$i]), // see #4979
                $this->getAttributes(),
                $this->strTagEnding);
        }

        return sprintf('<div id="ctrl_%s"%s>%s</div>',
            $this->strId,
            (($this->strClass != '') ? ' class="' . $this->strClass . '"' : ''),
            implode(' ', $arrFields)) . $this->addSubmit();

    }
}
