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
    'lodash',
    'jquery',
    'i18n',
    'ui/filter',
    'ui/feedback',
    'util/url',
    'layout/actions',
    'core/promise',
    'ui/taskQueue/taskQueue',
    'ui/taskQueueButton/standardButton'
], function (_, $, __, filterFactory, feedback, urlUtils, actionManager, Promise, taskQueue, taskCreationButtonFactory) {
    'use strict';

    /**
     * wrapped the old jstree API used to refresh the tree and optionally select a resource
     * @param {String} [uriResource] - the uri resource node to be selected
     */
    var refreshTree = function refreshTree(uriResource){
        actionManager.trigger('refresh', {
            uri : uriResource
        });
    };

    return {
        start: function () {
            var $form = $('#form_1');
            var $container = $form.closest('.content-block');
            var taskCreationButton, $oldSubmitter;
            var $regenerateBtn = $('#booklet-regenerate');

            // hide regenerate on the create page
            $regenerateBtn.hide();

            //find the old submitter and replace it with the new component
            $oldSubmitter = $form.find('.form-submitter');
            taskCreationButton = taskCreationButtonFactory({
                type : 'info',
                icon : 'play',
                title : __('Generate booklet'),
                label : __('Generate'),
                taskQueue : taskQueue,
                taskCreationUrl : $form.prop('action'),
                taskCreationData : function getTaskCreationData(){
                    return $form.serializeArray();
                },
                taskReportContainer : $container
            })
            .on('enqueued', function () {
                refreshTree();
            })
            .on('finished', function(result){
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
                            refreshTree();
                        }
                } else {
                    // move from the wizard on generated
                    refreshTree();
                }
            }).on('continue', function(){
                refreshTree();
            }).on('error', function(err){
                //format and display error message to user
                feedback().error(err);
                // refreshTree();
                $('#booklet-new a').click();
            }).render($oldSubmitter.closest('.form-toolbar'));

            //replace the old submitter with the new one and apply its style
            $oldSubmitter.replaceWith(taskCreationButton.getElement().css({float: 'right'}));
        }
    };
});
