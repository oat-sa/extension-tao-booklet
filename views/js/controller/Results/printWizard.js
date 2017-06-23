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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */
define([
    'jquery',
    'lodash',
    'ui/hider',
    'util/url',
    'report',
    'taoBooklet/component/taskQueue',
    'jquery.fileDownload'
], function ($, _, hider, urlHelper, report, taskQueueTableFactory) {
    'use strict';

    return {
        start: function () {
            var $header = $('header.section-header');
            var $formContainer = $('.print-form');
            var $reportContainer = $('.print-report');
            var $form = $('form', $formContainer);
            var $submitter = $('.form-submitter', $form);
            var $sent = $(":input[name='" + $form.attr('name') + "_sent']", $form);
            var asyncQueue = $header.data('async-queue');
            var $containers = $('.main-container');

            function switchContainer(purpose) {
                hider.hide($containers);
                hider.show($containers.filter('[data-purpose="' + purpose + '"]'));
            }

            function refreshTree() {
                $('.tree').trigger('refresh.taotree', [{
                    uri : $header.data('select-node')
                }]);
            }

            function displayReport(response) {
                switchContainer('report');
                $reportContainer.append(response);

                // Fold action (show detailed report)
                hider.toggle($('#fold', $reportContainer), response.nested);
                $('#fold > input[type="checkbox"]', $reportContainer).on('click', function() {
                    report.fold();
                });

                // Continue button
                $('#import-continue', $reportContainer).on('click', refreshTree);
            }

            switchContainer('form');

            //overwrite the submit behaviour
            $submitter.off('click').on('click', function (e) {
                var params = {};
                var instances = [];
                var classes = [];

                e.preventDefault();
                if (parseInt($sent.val(), 10)) {
                    // prepare download params
                    _.forEach($form.serializeArray(), function (param) {
                        if (param.name.indexOf('instances_') === 0) {
                            instances.push(param.value);
                        } else if (param.name.indexOf('classes_') === 0) {
                            classes.push(param.value);
                        } else {
                            params[param.name] = param.value;
                        }
                    });
                    params.instances = encodeURIComponent(JSON.stringify(instances));
                    params.classes = encodeURIComponent(JSON.stringify(classes));

                    if (asyncQueue) {
                        // display report after form submit
                        $.ajax({
                            url: $form.attr('action'),
                            data: params,
                            type: 'POST',
                            dataType: "text"
                        }).done(displayReport);
                    } else {
                        // download file after form submit
                        $.fileDownload($form.attr('action'), {
                            httpMethod: 'POST',
                            data: params,
                            failCallback: displayReport,
                            successCallback: refreshTree
                        });
                    }
                }
            });

            if (asyncQueue) {
                taskQueueTableFactory($header.data('queue'), $containers.filter('.print-tasks'), {
                    downloadUrl: urlHelper.route('downloadTask', 'TaskQueueData', 'taoBooklet')
                });
            }
        }
    };
});
