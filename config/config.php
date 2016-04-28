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

// Path
$GLOBALS['FM_AUTO_PATH'] = 'system/modules/fmodule/assets/';

if ((version_compare(VERSION, '4.0', '>=') && !$GLOBALS['FM_NO_COMPOSER'] && $GLOBALS['FM_NO_COMPOSER'] != true)) {
    $GLOBALS['FM_AUTO_PATH'] = 'bundles/fmodule/';
}

// Back End Modules
$GLOBALS['BE_MOD']['system']['fmodule'] = array(
    'icon' => $GLOBALS['FM_AUTO_PATH'] . 'icon.png',
    'tables' => array(
        'tl_fmodules',
        'tl_fmodules_filters',
        'tl_fmodules_feed',
        'tl_fmodules_license'
    )
);

// Front End Modules
array_insert($GLOBALS['FE_MOD'], 5, array(
    'fmodule' => array(
        'fmodule_fe_list' => 'ModuleListView',
        'fmodule_fe_detail' => 'ModuleDetailView',
        'fmodule_fe_formfilter' => 'ModuleFormFilter',
        'fmodule_fe_registration' => 'ModuleFModuleRegistration'
    )
));

// Widgets
$GLOBALS['BE_FFL']['optionWizardExtended'] = 'OptionWizardExtended';
$GLOBALS['BE_FFL']['modeSettings'] = 'ModeSettings';
$GLOBALS['BE_FFL']['filterFields'] = 'FilterFields';
$GLOBALS['BE_FFL']['keyValueWizardCustom'] = 'KeyValueWizardCustom';

// Files
if (TL_MODE == 'BE') {
    $GLOBALS['TL_CSS'][] = $GLOBALS['FM_AUTO_PATH'] . 'stylesheet.css';
}

// Google Maps
$GLOBALS['loadGoogleMapLibraries'] = false;

// Hooks
$GLOBALS['TL_HOOKS']['initializeSystem'][] = array('DCACreator', 'index');
$GLOBALS['TL_HOOKS']['postLogin'][] = array('FModule', 'setLanguage');
$GLOBALS['TL_HOOKS']['getSearchablePages'][] = array('FModule', 'getSearchablePages');
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = array('FModule', 'createUserGroupDCA');
$GLOBALS['TL_HOOKS']['autoComplete'][] = array('FModule', 'getAutoCompleteAjax');
$GLOBALS['TL_HOOKS']['removeOldFeeds'][] = array('FModule', 'purgeOldFeeds');
$GLOBALS['TL_HOOKS']['generateXmlFiles'][] = array('FModule', 'generateFeeds');
$GLOBALS['TL_HOOKS']['translateUrlParameters'][] = array('FModuleTranslation', 'translateUrlParameters');

// InsertTags
$GLOBALS['TL_HOOKS']['replaceInsertTags'][] = array('FModuleInsertTags', 'setHooks');


// Ajax
$GLOBALS['TL_MOOTOOLS'][] =
    "<script>
        if(AjaxRequest)
        {
            AjaxRequest.toggleFMField = function(el)
            {
                el.blur();
                var image = $(el).getFirst('img');
                var href = $(el).get('href');
                var tempSrc = image.get('src');
                var src = image.get('data-src');

                var featured = (image.get('data-state') == 1);

		        if (!featured) {
                    image.src = src;
                    image.set('data-src', tempSrc);
                    image.set('data-state', 1);
                    new Request({'url': href}).get({'rt': Contao.request_token});
                } else {
                    image.src = src;
                    image.set('data-src', tempSrc);
                    image.set('data-state', 0);
                    new Request({'url': href}).get({'rt':Contao.request_token});
                }

                return false;
            }
        }
    </script>
    <script>
    	if(Backend)
    	{
			Backend.keyValueWizardCustom = function(el, command, id) {
			var table = $(id),
				tbody = table.getElement('tbody'),
				parent = $(el).getParent('tr'),
				rows = tbody.getChildren(),
				tabindex = tbody.get('data-tabindex'),
				input, childs, i, j;

			Backend.getScrollOffset();

			switch (command) {
					case 'copy':
					var tr = new Element('tr');
					childs = parent.getChildren();
					for (i=0; i<childs.length; i++) {
						var next = childs[i].clone(true).inject(tr, 'bottom');
						if (input = childs[i].getFirst('input')) {
							next.getFirst().value = input.value;
						}
						if (select = childs[i].getFirst('select')) {
							next.getFirst('select').value = select.value;
						}
					}
					tr.inject(parent, 'after');
					$$(tr.getElement('.chzn-container')).destroy();
					$$(tr.getElement('.tl_select_column')).destroy();
					new Chosen(tr.getElement('select.tl_chosen'));
					Stylect.convertSelects();

					break;
				case 'up':
					if (tr = parent.getPrevious('tr')) {
						parent.inject(tr, 'before');
					} else {
						parent.inject(tbody, 'bottom');
					}
					break;
				case 'down':
					if (tr = parent.getNext('tr')) {
						parent.inject(tr, 'after');
					} else {
						parent.inject(tbody, 'top');
					}
					break;
				case 'delete':
					if (rows.length > 1) {
						parent.destroy();
					}
					break;
			}

			rows = tbody.getChildren();

			for (i=0; i<rows.length; i++) {
				childs = rows[i].getChildren();
				for (j=0; j<childs.length; j++) {
					if (input = childs[j].getFirst('input')) {
						input.set('tabindex', tabindex++);
						input.name = input.name.replace(/\[[0-9]+]/g, '[' + i + ']')
					}
					if (input = childs[j].getFirst('select')) {
						input.set('tabindex', tabindex++);
						input.name = input.name.replace(/\[[0-9]+]/g, '[' + i + ']')
					}
				}
			}

			new Sortables(tbody, {
				constrain: true,
				opacity: 0.6,
				handle: '.drag-handle'
			});
		}
    }
	</script>";

// Permissions
$GLOBALS['TL_PERMISSIONS'][] = 'fmodules';
$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesp';

$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesfeed';
$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesfeedp';

$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesfilters';
$GLOBALS['TL_PERMISSIONS'][] = 'fmodulesfiltersp';

// ProSearch
$GLOBALS['PS_SEARCHABLE_MODULES']['fmodule'] = array(
    'tables' => array('tl_fmodules', 'tl_fmodules_filters'),
    'searchIn' => array('name', 'tablename', 'info', 'title', 'type', 'fieldID'),
    'title' => array('name', 'title'),
    'setCustomIcon' => array(array('ProSearchApi', 'setCustomIcon')),
    'setCustomShortcut' => array(array('ProSearchApi', 'setCustomShortcut'))
);

// Wrapper
$GLOBALS['TL_WRAPPERS']['start'][] = 'legend_start';
$GLOBALS['TL_WRAPPERS']['stop'][] = 'legend_end';
