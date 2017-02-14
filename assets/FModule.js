var FModule = {};

(function () {

    'use strict';

    if ( typeof window.addEventListener != "undefined" ) {

        window.addEventListener( 'DOMContentLoaded', initialize, false );
    }

    function initialize() {

        FModule.keyValueWizardCustom = function ( el, command, id ) {

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

                        if (input = childs[i].getFirst('select')) {

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
        };

        FModule.toggleFMField = function (el) {

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
        };

        FModule.FModuleOrderByWizard = function ( objElement, strCommand, strID ) {

            var table = $(strID),
                tbody = table.getElement('tbody'),
                parent = $(objElement).getParent('tr'),
                rows = tbody.getChildren(),
                tabindex = tbody.get('data-tabindex'),
                input, childs, i, j;

            Backend.getScrollOffset();

            switch ( strCommand ) {

                case 'copy':

                    var tr = new Element('tr');
                    childs = parent.getChildren();

                    for (i=0; i<childs.length; i++) {

                        var next = childs[i].clone(true).inject(tr, 'bottom');

                        if (input = childs[i].getFirst('select')) {

                            next.getFirst().value = input.value;
                        }
                    }

                    tr.inject(parent, 'after');

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

                    $$( childs[j].getElement('.chzn-container') ).destroy();
                    $$( childs[j].getElement('.tl_select_column') ).destroy();

                    if (input = childs[j].getFirst('select')) {

                        input.set('tabindex', tabindex++);
                        input.name = input.name.replace(/\[[0-9]+]/g, '[' + i + ']');

                        new Chosen( childs[j].getFirst('select.tl_chosen') );
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
    
})();