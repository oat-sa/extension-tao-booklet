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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 */

/**
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'taoQtiPrint/runner/testRunner'
], function($, testRunner){
    'use strict';

    /**
     * The renderer controller
     */
    var renderController = {

        /**
         * Controller entry point
         * @param {Object} testData - the packed test data required by the testRunner
         */
        start : function start(testData){

            //where to append the test content
            var $mainContainer = $('<main>');

            testRunner(testData)
              .on('error', function(e){
                    console.error(e);
              })
              .on('ready', function(){

                    $('body').append($mainContainer);

                    //this is made for the printer engine to known when the runner is ready
                    window.status = 'runner-ready';
              })
              .render($mainContainer);
        }
    };

    /**
     * @exports taoBooklet/controller/printtest/render
     */
    return renderController;
});
