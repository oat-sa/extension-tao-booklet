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
    'jquery',
    'i18n',
    'layout/actions/binder',
    'core/plugin',
    'util/url',
    'taoBooklet/component/taskQueue',
    'tpl!taoBooklet/plugins/taskQueue/taskQueue'
], function ($, __, binder, pluginFactory, urlHelper, taskQueueFactory, taskQueueTpl) {
    'use strict';

    /**
     * Will add a "View" button on each line of the list of results
     */
    return pluginFactory({
        name: 'taskQueue',

        init: function init() {
            var resultsList = this.getHost();
            var areaBroker = this.getAreaBroker();
            var $resultsContainer = areaBroker.getContainer();
            var $taskQueueArea = $resultsContainer.find('.task-queue-area');

            if (!$taskQueueArea.length) {
                $taskQueueArea = $(taskQueueTpl());
                $resultsContainer.append($taskQueueArea);
            }

            taskQueueFactory(resultsList.getClassUri(), $taskQueueArea, {
                downloadUrl: urlHelper.route('downloadTask', 'TaskQueueData', 'taoBooklet')
            });
        }
    });
});
