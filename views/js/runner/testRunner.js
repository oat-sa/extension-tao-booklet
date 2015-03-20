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
    'taoQtiItemPrint/runner/qtiItemPrintRunner'
], function($, _, itemRunner){
    'use strict';

    var runner = function runner($elt, items){


        //FIXME order isn't garantee, use async or an array
        _.forEach(items, function(itemData, i){
            var $itemContainer = $('<div></div');
             itemRunner('qtiprint', itemData)
                .on('render', function(){
                    console.log("done " + i);
                    $elt.append($itemContainer);
                })
                .init()
                .render($itemContainer);
                console.log('render' + i);
        });
    };

    return runner;
});
