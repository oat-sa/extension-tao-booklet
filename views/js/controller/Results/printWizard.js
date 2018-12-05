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
    'i18n',
    'ui/hider',
    'util/url',
    'ui/feedback',
    'layout/actions/binder',
    'core/taskQueue/taskQueue',
    'ui/taskQueueButton/standardButton'
], function ($, _, __, hider, urlHelper, feedback, binder, taskQueue, taskCreationButtonFactory) {
    'use strict';

    return {
        start: function () {
            var $header = $('header.section-header');
            var $formContainer = $('.print-form');
            var $reportContainer = $('.print-report');
            var $form = $('form', $formContainer);
            var $submitter = $('.form-submitter', $form);
            var $containers = $('.main-container');
            var taskCreationButton;

            function switchContainer(purpose) {
                hider.hide($containers);
                hider.show($containers.filter('[data-purpose="' + purpose + '"]'));
            }

            function refreshTree() {
                $('.tree').trigger('refresh.taotree', [{
                    uri : $header.data('select-node')
                }]);
            }

            switchContainer('form');
            taskCreationButton = taskCreationButtonFactory({
                type : 'info',
                icon : 'play',
                title : __('Generate Results'),
                label : __('Generate'),
                taskQueue : taskQueue,
                taskCreationUrl : $form.prop('action'),
                taskCreationData : function getTaskCreationData(){
                    return $form.serializeArray();
                },
                taskReportContainer : $reportContainer
            }).on('finished', function(result){
                if (result.task
                    && result.task.report
                    && _.isArray(result.task.report.children)
                    && result.task.report.children.length
                    && result.task.report.children[0]) {
                    if(result.task.report.children[0].data
                        && result.task.report.children[0].data.uriResource){
                        feedback().info(__('%s completed', result.task.taskLabel));
                        refreshTree(result.task.report.children[0].data.uriResource);
                    }else{
                        this.displayReport(result.task.report.children[0], __('Error'));
                        $reportContainer.removeClass('hidden');
                    }
                }
            }).on('continue', function(){
                refreshTree();
            }).on('error', function(err){
                //format and display error message to user
                feedback().error(err);
                taskCreationButton.terminate().reset();
            }).on('enqueued', function(){
                refreshTree();
            }).render($submitter.closest('.form-toolbar'));

            //replace the old submitter with the new one and apply its style
            $submitter.replaceWith(taskCreationButton.getElement().css({float: 'right'}));
        }
    };
});
