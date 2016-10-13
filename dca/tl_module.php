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

// config
$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = array('tl_module_fmodule', 'setFEModule');
$GLOBALS['TL_DCA']['tl_module']['config']['onsubmit_callback'][] = array('tl_module_fmodule', 'saveGeoCoding');

// module palette

// list
$GLOBALS['TL_DCA']['tl_module']['palettes']['fmodule_fe_list'] = '{title_legend},name,headline,type,f_select_module,f_select_wrapper;{fm_mode_legend},f_display_mode;{fm_map_legend:hide},fm_addMap;{fm_geo_legend:hide},fm_addGeoLocator;{taxonomy_url_legend:hide},fm_use_specieUrl,fm_use_tagsUrl;{fm_sort_legend},f_sorting_fields,f_orderby,f_limit_page,f_perPage;{template_legend},f_list_template,customTpl;{image_legend:hide},imgSize;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

// form
$GLOBALS['TL_DCA']['tl_module']['palettes']['fmodule_fe_formfilter'] = '{title_legend},name,headline,type;{list_view_legend},f_list_field;{form_fields_legend},f_form_fields;{form_settings_legend},f_reset_button,fm_disable_submit,f_active_options,fm_related_options;{fm_redirect_legend:hide},fm_redirect_source;{template_legend},f_form_template,customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

// detail
$GLOBALS['TL_DCA']['tl_module']['palettes']['fmodule_fe_detail'] = '{title_legend},name,headline,type,f_list_field,f_doNotSet_404;{fm_seo_legend},fm_overwrite_seoSettings;{template_legend},f_detail_template,customTpl;{image_legend:hide},imgSize;{comment_legend:hide},com_template;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

// sign
$GLOBALS['TL_DCA']['tl_module']['palettes']['fmodule_fe_registration'] = '{title_legend},name,headline,type,f_select_module,f_select_wrapper;{config_legend},fm_editable_fields,disableCaptcha,fm_extensions,fm_maxlength,fm_EntityAuthor;{redirect_legend:hide},jumpTo;{store_legend:hide},fm_storeFile;{fm_notification_legend:hide},fm_addNotificationEmail;{fm_confirmation_legend:hide},fm_addConfirmationEmail;{defaultValues_legend:hide},fm_defaultValues;{protected_legend:hide},protected;{template_legend},fm_sign_template,tableless;{expert_legend:hide},guests,cssID,space';

//taxonomy
$GLOBALS['TL_DCA']['tl_module']['palettes']['fmodule_fe_taxonomy'] = '{title_legend},name,headline,type;{taxonomy_legend},fm_taxonomy,f_select_module,f_select_wrapper;{fm_redirect_legend:hide},fm_taxonomy_page;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID,space';

// selector
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'f_set_filter';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'f_set_sorting';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'fm_addMap';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'fm_overwrite_seoSettings';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'fm_redirect_source';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'fm_storeFile';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'fm_addNotificationEmail';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'fm_addConfirmationEmail';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'fm_related_options';
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'fm_addGeoLocator';

// sub palettes
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['f_set_filter'] = 'f_filter_fields';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['f_set_sorting'] = 'f_sorting_fields,f_sorting_orderby';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['fm_addMap'] = 'fm_center_address,fm_center_lat,fm_center_lng,fm_map_template,fm_mapZoom,fm_mapType,fm_mapScrollWheel,fm_mapMarker,fm_mapInfoBox,fm_mapStyle';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['fm_overwrite_seoSettings'] = 'fm_seoPageTitle,fm_seoDescription,fm_seoHrefLang';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['fm_redirect_source_siteID'] = 'fm_redirect_jumpTo';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['fm_redirect_source_siteURL'] = 'fm_redirect_url';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['fm_storeFile'] = 'fm_uploadFolder,fm_useHomeDir,fm_doNotOverwrite';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['fm_addNotificationEmail'] = 'fm_notificationEmailSubject,fm_notificationSender,fm_notificationEmailName,fm_notificationEmailList,fm_sendNotificationToAdmin';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['fm_addConfirmationEmail'] = 'fm_confirmationEmailSubject,fm_confirmationSender,fm_confirmationEmailName,fm_confirmationEmailList,fm_confirmationRecipientEmail,fm_sendConfirmationToAdmin,fm_confirmationBody';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['fm_related_options'] = 'fm_related_start_point';
$GLOBALS['TL_DCA']['tl_module']['subpalettes']['fm_addGeoLocator'] = 'fm_geoLocatorCountry,fm_adaptiveZoomFactor,fm_orderByDistance';

// module fields
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_taxonomy'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_taxonomy'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getTaxonomies'),
    'eval' => array('tl_class' => 'w50', 'mandatory' => true, 'chosen' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
    'sql' => "varchar(255) NOT NULL default ''"
);

// fm_taxonomy_page
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_taxonomy_page'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_taxonomy_page'],
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => array('fieldType' => 'radio'),
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => array('type' => 'belongsTo', 'load' => 'lazy')
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_select_module'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_select_module'],
    'default' => '',
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getModules'),
    'eval' => array('tl_class' => 'w50', 'submitOnChange' => true, 'mandatory' => true, 'chosen' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_select_wrapper'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_select_wrapper'],
    'inputType' => 'select',
    'exclude' => true,
    'options' => array(),
    'eval' => array('tl_class' => 'w50', 'submitOnChange' => true, 'mandatory' => true, 'chosen' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_orderby'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_orderby'],
    'inputType' => 'radio',
    'exclude' => true,
    'default' => 'desc',
    'eval' => array('tl_class' => 'clr m12'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'options' => array('desc', 'asc', 'rand'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_sorting_fields'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_sorting_fields'],
    'inputType' => 'checkboxWizard',
    'exclude' => true,
    'default' => 'id',
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'options' => array('id' => 'ID', 'title' => 'Titel', 'date' => 'Datum'),
    'eval' => array('multiple' => true),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_perPage'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_perPage'],
    'default' => '0',
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(10) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_limit_page'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_limit_page'],
    'default' => '0',
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(10) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_doNotSet_404'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_doNotSet_404'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr m12'),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_display_mode'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_display_mode'],
    'exclude' => true,
    'inputType' => 'modeSettings',
    'eval' => array('submitOnChange' => true),
    'sql' => "blob NULL"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_list_template'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_list_template'],
    'default' => 'fmodule_teaser',
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getListTemplates'),
    'eval' => array('tl_class' => 'w50', 'chosen' => true),
    'sql' => "varchar(32) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_detail_template'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_detail_template'],
    'default' => 'fmodule_full',
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getDetailTemplates'),
    'eval' => array('tl_class' => 'w50', 'chosen' => true),
    'sql' => "varchar(32) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_form_template'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_form_template'],
    'default' => 'fm_form_filter',
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getFormTemplates'),
    'eval' => array('tl_class' => 'w50', 'chosen' => true),
    'sql' => "varchar(32) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_form_fields'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_form_fields'],
    'exclude' => true,
    'inputType' => 'filterFields',
    'eval' => array('tl_class' => 'clr'),
    'sql' => "blob NULL"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_list_field'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_list_field'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getListModules'),
    'eval' => array('tl_class' => 'w50', 'submitOnChange' => true, 'mandatory' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_reset_button'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_reset_button'],
    'inputType' => 'checkbox',
    'exclude' => true,
    'eval' => array('tl_class' => 'w50'),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_related_options'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_related_options'],
    'inputType' => 'checkbox',
    'exclude' => true,
    'eval' => array('tl_class' => 'clr', 'submitOnChange' => true),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_related_start_point'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_related_start_point'],
    'inputType' => 'checkbox',
    'exclude' => true,
    'eval' => array('tl_class' => 'clr'),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_disable_submit'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_disable_submit'],
    'inputType' => 'checkbox',
    'exclude' => true,
    'eval' => array('tl_class' => 'w50'),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['f_active_options'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['f_active_options'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'options' => array(),
    'eval' => array('multiple' => true, 'tl_class' => 'clr'),
    'sql' => "blob NULL"
);
// maps
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_addMap'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_addMap'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_center_address'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_center_address'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'long'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_center_lat'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_center_lat'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_center_lng'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_center_lng'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_map_template'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_map_template'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getMapTemplates'),
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_mapZoom'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_mapZoom'],
    'exclude' => true,
    'default' => '6',
    'inputType' => 'select',
    'options' => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20),
    'eval' => array('tl_class' => 'w50'),
    'sql' => "int(10) unsigned NOT NULL default '6'"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_mapScrollWheel'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_mapScrollWheel'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr m12'),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_mapMarker'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_mapMarker'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr m12'),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_mapInfoBox'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_mapInfoBox'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr m12'),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_mapType'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_mapType'],
    'exclude' => true,
    'inputType' => 'select',
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'options' => array('ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN'),
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_mapStyle'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_mapStyle'],
    'exclude' => true,
    'inputType' => 'textarea',
    'eval' => array('allowHtml' => true, 'tl_class' => 'clr', 'rte' => 'ace|html'),
    'sql' => "text NULL"
);

// geo settings
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_addGeoLocator'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_addGeoLocator'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true, 'tl_class' => 'm12'),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_geoLocatorCountry'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_geoLocatorCountry'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getCountryNames'),
    'eval' => array('tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
    'sql' => "varchar(255) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_adaptiveZoomFactor'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_adaptiveZoomFactor'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'w50 m12'),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_orderByDistance'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_orderByDistance'],
    'exclude' => true,
    'inputType' => 'select',
    'eval' => array('tl_class' => 'w50', 'chosen' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
    'options' => array('desc', 'asc'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'sql' => "varchar(8) NOT NULL default ''"
);

// seo settings
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_overwrite_seoSettings'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_overwrite_seoSettings'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true),
    'sql' => "char(1) NOT NULL default ''"
);
// fm_seoDescription
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_seoDescription'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_seoDescription'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getModuleCols'),
    'eval' => array('tl_class' => 'w50', 'includeBlankOption' => true, 'blankOptionLabel' => '-', 'chosen' => true),
    'sql' => "varchar(255) NOT NULL default ''"
);
// fm_seoPageTitle
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_seoPageTitle'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_seoPageTitle'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getModuleCols'),
    'eval' => array('tl_class' => 'w50', 'includeBlankOption' => true, 'blankOptionLabel' => '-', 'chosen' => true),
    'sql' => "varchar(255) NOT NULL default ''"
);
// fm_seoHrefLang
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_seoHrefLang'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_seoHrefLang'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'w50 m12'),
    'sql' => "char(1) NOT NULL default ''"
);


// redirect
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_redirect_source'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_redirect_source'],
    'default' => '',
    'exclude' => true,
    'inputType' => 'select',
    'options' => array('siteID', 'siteURL'),
    'reference' => &$GLOBALS['TL_LANG']['tl_module'],
    'eval' => array('submitOnChange' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-'),
    'sql' => "varchar(64) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_redirect'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_form_redirect'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true),
    'sql' => "char(1) NOT NULL default ''"
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_redirect_jumpTo'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_redirect_jumpTo'],
    'exclude' => true,
    'inputType' => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval' => array('mandatory' => true, 'fieldType' => 'radio'),
    'sql' => "int(10) unsigned NOT NULL default '0'",
    'relation' => array('type' => 'belongsTo', 'load' => 'lazy')
);
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_redirect_url'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_redirect_url'],
    'exclude' => true,
    'search' => true,
    'inputType' => 'text',
    'eval' => array('mandatory' => true, 'decodeEntities' => true, 'maxlength' => 255, 'tl_class' => 'w50'),
    'sql' => "varchar(255) NOT NULL default ''"
);

// registration
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_editable_fields'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_editable_fields'],
    'exclude' => true,
    'inputType' => 'checkboxWizard',
    'options_callback' => array('tl_module_fmodule', 'getEditableFModuleProperties'),
    'eval' => array('multiple' => true),
    'sql' => "blob NULL"
);
// fm_sign_template
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_sign_template'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_sign_template'],
    'exclude' => true,
    'default' => 'sign_default',
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getSignTemplate'),
    'eval' => array('tl_class' => 'w50', 'chosen' => true),
    'sql' => "varchar(32) NOT NULL default ''"
);
// fm_storeFile
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_storeFile'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_storeFile'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('submitOnChange' => true),
    'sql' => "char(1) NOT NULL default ''"
);
// fm_uploadFolder
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_uploadFolder'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_uploadFolder'],
    'exclude' => true,
    'inputType' => 'fileTree',
    'eval' => array('fieldType' => 'radio', 'tl_class' => 'clr', 'mandatory' => true),
    'sql' => "binary(16) NULL"
);
// fm_useHomeDir
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_useHomeDir'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_useHomeDir'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "char(1) NOT NULL default ''"
);
// fm_useHomeDir
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_doNotOverwrite'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_doNotOverwrite'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "char(1) NOT NULL default ''"
);
// maxlength
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_maxlength'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_maxlength'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('rgxp' => 'natural', 'tl_class' => 'w50'),
    'sql' => "int(10) unsigned NOT NULL default '0'"
);
// fm_extensions
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_extensions'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_extensions'],
    'exclude' => true,
    'default' => 'jpg,jpeg,gif,png,pdf,doc,xls,ppt',
    'inputType' => 'text',
    'eval' => array('rgxp' => 'extnd', 'maxlength' => 255, 'tl_class' => 'w50'),
    'sql' => "varchar(255) NOT NULL default ''"
);
// fm_EntityAuthor
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_EntityAuthor'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_EntityAuthor'],
    'exclude' => true,
    'inputType' => 'select',
    'foreignKey' => 'tl_user.name',
    'eval' => array('chosen' => true, 'mandatory' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-', 'tl_class' => 'w50'),
    'relation' => array('type' => 'hasOne', 'load' => 'eager'),
    'sql' => "int(10) unsigned NOT NULL default '0'",
);

// fm_addNotificationEmail
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_addNotificationEmail'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_addNotificationEmail'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'long clr', 'submitOnChange' => true),
    'sql' => "char(1) NOT NULL default ''"
);

// fm_notificationEmailSubject
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_notificationEmailSubject'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_notificationEmailSubject'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('mandatory' => true, 'tl_class' => 'long clr'),
    'sql' => "varchar(512) NOT NULL default ''"
);

// fm_notificationEmailName
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_notificationEmailName'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_notificationEmailName'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(128) NOT NULL default ''"
);

// fm_sendNotificationToAdmin
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_sendNotificationToAdmin'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_sendNotificationToAdmin'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'clr'),
    'sql' => "char(1) NOT NULL default ''"
);

// fm_notificationEmailList
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_notificationEmailList'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_notificationEmailList'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50', 'rgxp'=>'emails'),
    'sql' => "varchar(255) NOT NULL default ''"
);

// fm_notificationSender
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_notificationSender'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_notificationSender'],
    'exclude' => true,
    'default' => \Config::get('adminEmail'),
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50', 'rgxp'=>'email'),
    'sql' => "varchar(255) NOT NULL default ''"
);

// fm_addConfirmationEmail
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_addConfirmationEmail'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_addConfirmationEmail'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'long clr', 'submitOnChange' => true),
    'sql' => "char(1) NOT NULL default ''"
);

// fm_confirmationEmailSubject
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_confirmationEmailSubject'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_confirmationEmailSubject'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('mandatory' => true, 'tl_class' => 'long clr'),
    'sql' => "varchar(512) NOT NULL default ''"
);

// fm_confirmationEmailName
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_confirmationEmailName'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_confirmationEmailName'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50'),
    'sql' => "varchar(128) NOT NULL default ''"
);

// fm_sendConfirmationToAdmin
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_sendConfirmationToAdmin'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_sendConfirmationToAdmin'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'long clr'),
    'sql' => "char(1) NOT NULL default ''"
);

// fm_confirmationEmailList
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_confirmationEmailList'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_confirmationEmailList'],
    'exclude' => true,
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50', 'rgxp'=>'emails'),
    'sql' => "varchar(255) NOT NULL default ''"
);

// fm_confirmationSender
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_confirmationSender'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_confirmationSender'],
    'exclude' => true,
    'default' => \Config::get('adminEmail'),
    'inputType' => 'text',
    'eval' => array('tl_class' => 'w50', 'rgxp'=>'email'),
    'sql' => "varchar(255) NOT NULL default ''"
);

// fm_confirmationRecipientEmail
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_confirmationRecipientEmail'] = array(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_confirmationRecipientEmail'],
    'exclude' => true,
    'inputType' => 'select',
    'options_callback' => array('tl_module_fmodule', 'getEmailFields'),
    'eval' => array('chosen' => true, 'mandatory' => true, 'includeBlankOption' => true, 'blankOptionLabel' => '-', 'tl_class' => 'w50'),
    'sql' => "varchar(255) NOT NULL default ''"
);

// fm_confirmationBody
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_confirmationBody'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_confirmationBody'],
    'exclude' => true,
    'inputType' => 'textarea',
    'eval' => array('rte' => 'tinyMCE', 'tl_class' => 'clr'),
    'sql' => "text NULL"
);

// fm_defaultValues
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_defaultValues'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_defaultValues'],
    'exclude' => true,
    'inputType' => 'keyValueWizardCustom',
    'options_callback' => array('tl_module_fmodule', 'getEditableFModuleProperties'),
    'sql' => "blob NULL"
);

// taxonomy url

// fm_use_specieUrl
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_use_specieUrl'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_use_specieUrl'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'w50 m12'),
    'sql' => "char(1) NOT NULL default ''"
);

// fm_use_tagsUrl
$GLOBALS['TL_DCA']['tl_module']['fields']['fm_use_tagsUrl'] = array
(
    'label' => &$GLOBALS['TL_LANG']['tl_module']['fields']['fm_use_tagsUrl'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => array('tl_class' => 'w50 m12'),
    'sql' => "char(1) NOT NULL default ''"
);

use FModule\FieldAppearance;
use FModule\GeoCoding;

/**
 * Class tl_module_fmodule
 */
class tl_module_fmodule extends tl_module
{
    /**
     * @var array
     */
    protected $moduleColsCache = array();

    /**
     * @return array
     */
    public function getSignTemplate()
    {
        return $this->getTemplateGroup('sign_');
    }

    /**
     * @return array
     */
    public function getTaxonomies()
    {
        $taxonomiesDB = $this->Database->prepare('SELECT * FROM tl_taxonomies WHERE pid = ?')->execute('0');
        $arrTaxonomies = array();

        while($taxonomiesDB->next())
        {
            $arrTaxonomies[$taxonomiesDB->id] = $taxonomiesDB->name;
        }

        return $arrTaxonomies;
    }

    /**
     * @param DataContainer $dc
     * @return array
     */
    public function getEmailFields(\DataContainer $dc)
    {
        // set variables here
        $return = array();
        $modulename = $dc->activeRecord->f_select_module;
        $tableData = $modulename . '_data';

        // return empty array
        if (!$modulename) return $return;

        // get editable fields
        System::loadLanguageFile('tl_fmodules_language_pack');
        $this->loadDataContainer($tableData);

        foreach ($GLOBALS['TL_DCA'][$tableData]['fields'] as $name => $field) {
            if (isset($field['eval']['rgxp']) && ( $field['eval']['rgxp'] == 'email' || $field['eval']['rgxp'] == 'emails' ) ) {
                $return[$name] = $field['label'][0] ? $field['label'][0] . ' (' . $name . ')' : $name;
            }
        }

        return $return;
    }

    /**
     * @param DataContainer $dc
     * @return array
     */
    public function getEditableFModuleProperties(\DataContainer $dc)
    {
        // set variables here
        $return = array();
        $modulename = $dc->activeRecord->f_select_module;
        $tableData = $modulename . '_data';
        $doNotSetByName = array('tstamp', 'pid', 'id');
        if($dc->field && $dc->field == 'fm_defaultValues')
        {
            $doNotSetByName[] = 'markerSRC';
            $doNotSetByName[] = 'singleSRC';
            $doNotSetByName[] = 'enclosure';
        }

        // return empty array
        if (!$modulename) return $return;

        // get editable fields
        System::loadLanguageFile('tl_fmodules_language_pack');
        $this->loadDataContainer($tableData);

        foreach ($GLOBALS['TL_DCA'][$tableData]['fields'] as $name => $field) {

            if ((isset($field['eval']['fmEditable']) && $field['eval']['fmEditable'] === false) || !isset($field['eval']['fmEditable'])) continue;
            if (in_array($name, $doNotSetByName)) continue;
            $return[$name] = $field['label'][0] ? $field['label'][0] . ' (' . $name . ')' : $name;
        }

        return $return;
    }

    /**
     * @param DataContainer $dc
     * @return array
     */
    public function getModuleCols(\DataContainer $dc)
    {
        // get cols from cache
        if (!empty($this->moduleColsCache)) return $this->moduleColsCache;

        // set empty cols array
        $cols = array();

        // set undefined table
        $table = '';

        // search for table
        if ($dc->activeRecord->f_list_field) {
            $feID = $dc->activeRecord->f_list_field;
            $listFeModuleDB = $this->Database->prepare('SELECT f_select_module FROM tl_module WHERE id = ?')->execute($feID);
            while ($listFeModuleDB->next()) {
                $table = $listFeModuleDB->f_select_module;
            }
        }

        if (!$table) return $cols;

        $tableData = $table . '_data';
        $doNotSetByName = array('tstamp', 'pid', 'id');

        // get editable fields
        System::loadLanguageFile('tl_fmodules_language_pack');
        $this->loadDataContainer($tableData);

        // get cols
        foreach ($GLOBALS['TL_DCA'][$tableData]['fields'] as $name => $field) {

            if (in_array($name, $doNotSetByName)) {
                continue;
            }

            $cols[$name] = $field['label'][0] ? $field['label'][0] . ' (' . $name . ')' : $name;
        }

        // set cache
        $this->moduleColsCache = $cols;
        return $cols;
    }

    /**
     * @param \DataContainer $dc
     * @return null
     */
    public function saveGeoCoding(\DataContainer $dc)
    {
        if (!$dc->activeRecord) {
            return null;
        }

        $geo_address = $dc->activeRecord->fm_center_address ? $dc->activeRecord->fm_center_address : '';
        $address_country = 'en'; // not needed here

        //
        $cords = array();

        //
        if ($geo_address) {
            $geoCoding = GeoCoding::getInstance();
            $cords = $geoCoding->getGeoCords($geo_address, $address_country);
        }

        if (!empty($cords)) {
            $tableName = $dc->table ? $dc->table : Input::get('table');
            $id = $dc->id ? $dc->id : Input::get('id');
            $lat = $cords['lat'] ? $cords['lat'] : '';
            $lng = $cords['lng'] ? $cords['lng'] : '';
            if (!$tableName || !$id) {
                return null;
            }
            $this->Database->prepare('UPDATE ' . $tableName . ' SET fm_center_lat=?,fm_center_lng=? WHERE id = ?')->execute($lat, $lng, $id);
        }
    }

    /**
     * @param DataContainer $dc
     * @return array
     */
    public function getListModules(\DataContainer $dc)
    {
        $type = 'fmodule_fe_list';
        $listID = $dc->activeRecord->f_list_field;
        $tl_moduleDB = $this->Database->prepare('SELECT name, id, f_select_module FROM tl_module WHERE type = ?')->execute($type);
        $options = array();
        $filters = array();

        while ($tl_moduleDB->next()) {
            $options[$tl_moduleDB->id] = $tl_moduleDB->name;
            $filters[$tl_moduleDB->id] = $tl_moduleDB->f_select_module;
        }

        $activeOptions = array();

        if ($listID) {

            $selectedList = $filters[$listID];
            $filterFieldsDB = $this->Database->prepare('SELECT tl_fmodules_filters.* FROM tl_fmodules JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ? ORDER BY tl_fmodules_filters.sorting')->execute($selectedList);
            $filterFields = array();
            $doNotSetByType = array('legend_start', 'legend_end', 'widget', 'map_field');
            $doNotSetByID = array('auto_page', 'auto_item');
            $allowedOptionTypes = array('search_field', 'multi_choice', 'simple_choice', 'fulltext_search', 'date_field');
            $allowedOptionID = array('pagination', 'orderBy', 'sorting_fields');

            while ($filterFieldsDB->next()) {

                if ($filterFieldsDB->fieldID && in_array($filterFieldsDB->type, $allowedOptionTypes) && !in_array($filterFieldsDB->fieldID, $allowedOptionID)) {
                    $activeOptions[$filterFieldsDB->fieldID] = $filterFieldsDB->title;
                }

                if (in_array($filterFieldsDB->type, $doNotSetByType)) {
                    continue;
                }

                if (in_array($filterFieldsDB->fieldID, $doNotSetByID)) {
                    continue;
                }

                //active options
                $filterFields[$filterFieldsDB->id] = array(
                    'id' => $filterFieldsDB->id,
                    'label' => $filterFieldsDB->title,
                    'fieldID' => $filterFieldsDB->fieldID,
                    'title' => $filterFieldsDB->title,
                    'description' => $filterFieldsDB->description,
                    'type' => $filterFieldsDB->type,
                    'isInteger' => $filterFieldsDB->isInteger,
                    'addTime' => $filterFieldsDB->addTime,
                    'from_field' => $filterFieldsDB->from_field,
                    'to_field' => $filterFieldsDB->to_field,
                    'dataFromTaxonomy' => $filterFieldsDB->dataFromTaxonomy,
                    'reactToTaxonomy' => $filterFieldsDB->reactToTaxonomy,
                    'reactToField' => $filterFieldsDB->reactToField,
                    'active' => '',
                    'cssClass' => '',
                    'templates' => array(),
                    'appearance' => FieldAppearance::getAppearance()[$filterFieldsDB->type],
                    'used_templates' => '',
                    'used_appearance' => '',
                    'changeOnSubmit' => '',
                    'dependsOn' => ''
                );
            }
            $GLOBALS['TL_DCA']['tl_module']['fields']['f_form_fields']['eval']['filterFields'] = $filterFields;
            $GLOBALS['TL_DCA']['tl_module']['fields']['f_form_fields']['eval']['currentListID'] = $listID;
            $GLOBALS['TL_DCA']['tl_module']['fields']['f_active_options']['options'] = $activeOptions;

        }
        return $options;
    }

    /**
     * @return array
     */
    public function getDetailTemplates()
    {
        return $this->getTemplateGroup('fmodule_');
    }

    /**
     * @param DataContainer $dc
     * @return array
     */
    public function getListTemplates(\DataContainer $dc)
    {
        return $this->getTemplateGroup('fmodule_');
    }

    /**
     * @param DataContainer $dc
     * @return array
     */
    public function getMapTemplates(\DataContainer $dc)
    {
        return $this->getTemplateGroup('fm_map_location');
    }

    /**
     * @return array
     */
    public function getFormTemplates()
    {
        return $this->getTemplateGroup('fm_form_');
    }

    /**
     * @return array
     */
    function getCountryNames() {

        return array_values($this->getcountries());
    }

    /**
     * @return array
     */
    public function getModules()
    {
        $return = array();
        $modulesDB = $this->Database->prepare('SELECT tablename, name FROM tl_fmodules')->execute();
        while ($modulesDB->next()) {
            $return[$modulesDB->tablename] = $modulesDB->name;
        }
        return $return;
    }

    /**
     * @param DataContainer $dc
     * @return null
     */
    public function setFEModule(\DataContainer $dc)
    {
        $id = $dc->id;
        $moduleDB = $this->Database->prepare('SELECT * FROM tl_module WHERE id = ? LIMIT 1')->execute($id);
        $modulename = '';
        $doNotSetByType = array('fulltext_search', 'legend_start', 'legend_end', 'widget', 'wrapper_field', 'toggle_field', 'map_field');
        $doNotSetByID = array('auto_item', 'auto_page', 'pagination', 'orderBy', 'sorting_fields');
        $type = '';

        while ($moduleDB->next()) {
            $modulename = $moduleDB->f_select_module;
            $type = $moduleDB->type;
        }

        if (!$modulename || is_null($modulename)) return null;
        if (!$this->Database->tableExists($modulename)) return null;

        $modulesDB = $this->Database->prepare('SELECT id, title, info FROM ' . $modulename)->execute();
        $wrapper = array();

        while ($modulesDB->next()) {
            $wrapper[$modulesDB->id] = $modulesDB->title . ' (' . $modulesDB->info . ')';
        }
        $GLOBALS['TL_DCA']['tl_module']['fields']['f_select_wrapper']['options'] = $wrapper;

        if($type == 'fmodule_fe_taxonomy')
        {
            $GLOBALS['TL_DCA']['tl_module']['fields']['f_select_module']['eval']['mandatory'] = false;
            $GLOBALS['TL_DCA']['tl_module']['fields']['f_select_wrapper']['eval']['mandatory'] = false;
        }

        // break up
        if ($type != 'fmodule_fe_list') {
            return null;
        }

        // set sorting fields
        $filterDB = $this->Database->prepare('SELECT * FROM tl_fmodules JOIN tl_fmodules_filters ON tl_fmodules.id = tl_fmodules_filters.pid WHERE tablename = ?')->execute($modulename);
        $sorting = array('id' => 'ID', 'title' => 'Titel', 'date' => 'Datum');

        while ($filterDB->next()) {

            if (in_array($filterDB->fieldID, $doNotSetByID)) {
                continue;
            }

            if (in_array($filterDB->type, $doNotSetByType)) {
                continue;
            }
            $sorting[$filterDB->fieldID] = $filterDB->title;

        }

        $GLOBALS['TL_DCA']['tl_module']['fields']['f_sorting_fields']['options'] = $sorting;
    }
}