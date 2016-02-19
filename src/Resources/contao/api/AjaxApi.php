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

use Contao\Input;

/**
 * Class AjaxApi
 * @package FModule
 */
class AjaxApi extends FModuleAjaxApi
{
    /**
     * F Module Ajax Api
     * More: http://fmodul.alexandernaumov.de/ressourcen.html
     */
    public function getAjaxResponse()
    {
        $action = Input::get('do');

        if($action)
        {
            switch($action) {
                case 'getEntities':
                    $this->getEntities();
                    break;
                case 'getDetail':
                    $this->getDetail();
                    break;
                case 'getAutoCompletion':
                    $this->getAutoCompletion();
                    break;
                default:
                    $this->getDefault();
                    break;
            }

        }else{

            header('HTTP/1.1 500 Internal Server');
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(array("No method defined"));
            exit;

        }
    }
}
