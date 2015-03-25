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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 */

/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'async',
    'taoQtiItemPrint/runner/qtiItemPrintRunner'
], function($, _, async, itemRunner){
    'use strict';

    var runner = function runner($elt, testData){
        var itemRunners = [];

        _.forEach(testData.items, function(itemData){

            var runItem = function runItem(done){
                var $itemContainer = $('<div style="page-break-after: always;"></div');

                itemRunner('qtiprint', itemData)
                    .on('error', function(err){
                        done(err);
                    })
                    .on('render', function(){
                        done(null, $itemContainer);
                    })
                    .init()
                    .render($itemContainer);
            };
            itemRunners.push(runItem);
        });

        async.parallel(itemRunners, function itemDone(err, results){
            if(err){
                throw new Error(err);
            }
            $elt.empty().append(results);
        });
    };

    return runner;
});
