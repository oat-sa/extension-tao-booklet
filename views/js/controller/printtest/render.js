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
 *
 * This controller renders a printable test in the current page.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'taoQtiPrint/runner/testRunner'
], function($, testRunner){
    'use strict';

    /**
     * As the page can be called by an external tool and
     * as we build the page in JS asynchronously, we need this hack
     * to tell the 3rd part tool that the page is ready.
     */
    var ready = function ready(){
        window.status = 'runner-ready';
    };

    /**
     * Old school way to debug document, especially when generated in PDF.
     * It appends the message to the body.
     * @param {String} msg - the content to display
     */
    var showMessage = function showMessage(msg, type){
        type = type || 'info';
        $('body').append('<div class="feedback-' + type + '">'  + msg + '</div>');
    };

    /**
     * Hack the current layout to match arbitrary rules
     */
    var printLayoutHacking = function printLayoutHacking($container){

        var threshold = 6000;   //this height seems to work - value is empirical and linked to the A4 page layout with 300 DPI
        var pages = 2;          //start on page 2
        var previous;           //keep a ref to the previous section

        //add a blank page
        var blankPage = function blankPage( $section ){
            $section.before('<div class="breaker"></div>');
            pages++;
        };

        //add pages based on the given height
        var computePageFromHeight = function computePageFromHeight(height){
            if(height > threshold){
                pages += Math.ceil(height / threshold) - 1;
            }
        };

        //browse direct section add apply the odd/even rules for section and items
        $container.children('section').each(function(){
            var $section = $(this);
            var height;
            var msg = '';

            if($section.hasClass('section')){

                if(pages % 2 === 0){
                    blankPage($section);
                }

                computePageFromHeight($section.outerHeight());

                previous = 'section';
                pages++;
            }
            if($section.hasClass('item')){
                if(previous !== 'item'){

                    if(pages % 2 !== 0){
                        blankPage($section);
                    }

                    height = $section.outerHeight();
                    $section.nextUntil('.section', '.item').each(function(){
                        height += $(this).outerHeight();
                    });
                    computePageFromHeight(height);

                    pages++;
                }
                previous = 'item';
            }
        });
    };

    /**
     * The renderer controller
     */
    var renderController = {

        /**
         * Controller entry point
         * @param {Object} testData - the packed test data required by the testRunner
         */
        start : function start(testData){

            //this is just in case something went wrong, but we weren't able to catch it.
            setTimeout(function(){
                showMessage("Something went wrong...", 'error');
                ready();
            }, 45*1000);


            //the content will be inserted in a detached element (to save time)
            var $mainContainer = $('<main>');

            //instantiate the TestRunner
            testRunner(testData)
              .on('error', function(e){
                console.error(e);
                showMessage(e, 'error');
                ready();
              })
              .on('ready', function(){

                //we attach the container to the DOM
                $('body').append($mainContainer);

                //hacky layout calculation
                printLayoutHacking($mainContainer);

                //we are done
                ready();
              })
              .render($mainContainer);
        }
    };

    /**
     * @exports taoBooklet/controller/printtest/render
     */
    return renderController;
});
