/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
define([
    'i18n',
    'layout/actions/binder',
    'core/plugin',
    'util/url'
], function (__, binder, pluginFactory, urlHelper) {
    'use strict';

    /**
     * Will add a "View" button on each line of the list of results
     */
    return pluginFactory({
        name: 'print',

        init: function init() {
            var resultsList = this.getHost();
            var action = {
                binding: 'load',
                url: urlHelper.route('printWizard', 'Results', 'taoBooklet')
            };

            // this action will be available for each displayed line in the list
            resultsList.addAction({
                id: 'print',
                label: __('Print'),
                icon: 'print',
                action: function viewResults(id) {
                    var context = {
                        id: id,
                        uri: resultsList.getClassUri()
                    };

                    binder.exec(action, context);
                }
            });
        }
    });
});
