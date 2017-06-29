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

class ProSearchApi {

	public function setCustomIcon($table, $db, $dataArr, $dca) {

        $iconName = '';
		
		if($table == 'tl_fmodules') {

            $iconName = 'system/modules/fmodule/assets/icon.png';
        }
        
        if($table == 'tl_fmodules_filters') {

            $iconName = 'system/modules/fmodule/assets/filter.png';
        }
        
        return $iconName;
	}


	public function setCustomShortcut($table, $db, $dataArr, $dca) {

		$shortcut = '';

        if($table == 'tl_fmodules') {

            $shortcut = 'fm';
        }
        
        if($table == 'tl_fmodules_filters') {

            $shortcut = 'ff';
        }

        return $shortcut;
	}
}