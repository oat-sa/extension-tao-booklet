/*  
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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
define(['jquery', 'lodash', 'module', 'helpers', 'generis.tree.select'], function ($, _, module, helpers, GenerisTreeSelectClass) {
    'use strict';


    function buildGroupsTree(container) {

        var treeOptions = container.data();

        new GenerisTreeSelectClass('#' + treeOptions.actionId + '-tree', helpers._url('getData', 'GenerisTree', 'tao'), {
            actionId: treeOptions.actionId,
            saveUrl: treeOptions.saveUrl,
            saveData: {
                resourceUri: treeOptions.resourceUri,
                propertyUri: treeOptions.propertyUri
            },
            checkedNodes: treeOptions.checkedNodes,
            serverParameters: {
                openNodes: treeOptions.openNodes,
                rootNode: treeOptions.rootNode
            },
            saveCallback: function () {
                helpers._load(
                    helpers.getMainContainerSelector(),
                    helpers._url('editBooklet', 'Booklet', 'taoBooklet'),
                    {uri: treeOptions.resourceUri}
                );
            },
            paginate: 10
        });
    }

    return {
        start: function () {

            var $downloader = $('<iframe/>').hide();
            $('form').append($downloader);

            $('.btn-download').on('click', function (e) {
                e.preventDefault();
                var $form = $(e.target).closest('form');

                $downloader.attr('src', helpers._url('download', 'Booklet', 'taoBooklet', {
                    uri: $form.find('input[name="uri"]').val(),
                    classUri: $form.find('input[name="classUri"]').val(),
                    id: $form.find('input[name="id"]').val()
                }));
            });

            buildGroupsTree($('.group-container'));
        }
    };
});
