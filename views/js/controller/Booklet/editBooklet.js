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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
define([
    'jquery',
    'lodash',
    'i18n',
    'module',
    'util/url',
    'ui/taskQueue/taskQueue',
    'ui/taskQueueButton/treeButton',
    'layout/actions/binder'
], function ($, _, __, module, urlHelper, taskQueue, treeTaskButtonFactory, binder) {
    'use strict';

    return {
        start: function () {

            var $downloader = $('<iframe/>').hide();
            var $regenerateBtn = $('#booklet-regenerate');
            var taskRegenerateButton;

            $regenerateBtn.show();

            taskRegenerateButton = treeTaskButtonFactory({
                replace : true,
                icon : 'reset',
                label : __('Regenerate'),
                taskQueue : taskQueue
            }).render($regenerateBtn);

            binder.register('booklet_regenerate', function register(actionContext) {
                var data = _.pick(actionContext, ['uri', 'classUri', 'id']);
                var uniqueValue = data.uri || data.classUri || '';
                taskRegenerateButton.setTaskConfig({
                    taskCreationUrl : this.url,
                    taskCreationData : {uri : uniqueValue}
                }).start();
            });

            $('form').append($downloader);

            $('.btn-download').on('click', function (e) {
                e.preventDefault();
                var $form = $(e.target).closest('form');

                $downloader.attr('src', urlHelper.route('download', 'Booklet', 'taoBooklet', {
                    uri: $form.find('input[name="uri"]').val(),
                    classUri: $form.find('input[name="classUri"]').val(),
                    id: $form.find('input[name="id"]').val()
                }));
            });
        }
    };
});
