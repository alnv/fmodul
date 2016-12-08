<?php namespace FModule;

/**
 * Class Initialize
 * @package FModule
 */
class Initialize {

    // init classes
    public function getClasses(){

        // only in backend
        if (TL_MODE == 'BE') {

            // order is important
            \BackendUser::getInstance();
            \Config::getInstance();
            \Database::getInstance();
            \Environment::getInstance();
            \Input::getInstance();

            // init language files and start f module
            if ( \Database::getInstance()->tableExists('tl_fmodules') ) {

                $saveLanguage = $_SESSION['fm_language'] ? $_SESSION['fm_language'] : 'de';

                \Backend::loadLanguageFile('tl_fmodules_language_pack', $saveLanguage);

                $dcaCreator = new DCACreator();
                $dcaCreator->loadModules();
                $dcaCreator->createLabels();
            }
        }
    }
}