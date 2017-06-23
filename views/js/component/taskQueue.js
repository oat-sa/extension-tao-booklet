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
    'lodash',
    'util/url',
    'ui/taskQueue/table'
], function ($, _, urlHelper, taskQueueTableFactory) {
    'use strict';

    var defaults = {
        rows: 10,
        dataUrl: urlHelper.route('getTasks', 'TaskQueueData', 'taoBooklet'),
        statusUrl: urlHelper.route('getStatus', 'TaskQueueData', 'taoBooklet'),
        removeUrl: urlHelper.route('archiveTask', 'TaskQueueData', 'taoBooklet')
    };

    /**
     * Renders a booklet task queue table component
     * @param {String} queueId
     * @param {jQuery} $container
     * @param {Object} [config]
     * @returns {taskQueueTable}
     */
    function bookletTaskQueue(queueId, $container, config) {
        var taskQueue = taskQueueTableFactory(_.merge({context: queueId}, config || {}, defaults));

        taskQueue.init()
            .render($container);

        return taskQueue;
    }

    return bookletTaskQueue;
});
