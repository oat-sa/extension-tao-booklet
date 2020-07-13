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
 * Copyright (c) 2015-2017 (original work) Open Assessment Technologies SA;
 */

/**
 *
 * This controller renders a printable test in the current page.
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
define([
    'jquery',
    'lodash',
    'core/logger',
    'taoQtiPrint/runner/testRunner',
    'tpl!taoBooklet/tpl/print'
], function ($, _, loggerFactory, testRunner, printTpl) {
    'use strict';

    var logger = loggerFactory('printTest/render');

    /**
     * Old school way to debug document, especially when generated in PDF.
     * It appends the message to the body.
     * @param {String} msg - the content to display
     * @param {String} type - the type of message
     */
    function showMessage(msg, type) {
        type = type || 'info';
        $('body').append(`<div class="feedback-${type}">${msg}</div>`);
    }

    /**
     * Hack the current layout to match arbitrary rules
     * @param {JQuery} $container
     */
    function printLayoutHacking($container) {

        var threshold = 6000;   //this height seems to work - value is empirical and linked to the A4 page layout with 300 DPI
        var pages = 2;          //start on page 2
        var previous;           //keep a ref to the previous section

        //add a blank page
        var blankPage = function blankPage($section) {
            $section.before('<div class="breaker"></div>');
            pages++;
        };

        //add pages based on the given height
        var computePageFromHeight = function computePageFromHeight(height) {
            if (height > threshold) {
                pages += Math.ceil(height / threshold) - 1;
            }
        };

        //browse direct section add apply the odd/even rules for section and items
        $container.children('section').each(function () {
            var $section = $(this);
            var height;

            if ($section.hasClass('section')) {

                if (pages % 2 === 0) {
                    blankPage($section);
                }

                computePageFromHeight($section.outerHeight());

                previous = 'section';
                pages++;
            }
            if ($section.hasClass('item')) {
                if (previous !== 'item') {

                    if (pages % 2 !== 0) {
                        blankPage($section);
                    }

                    height = $section.outerHeight();
                    $section.nextUntil('.section', '.item').each(function () {
                        height += $(this).outerHeight();
                    });
                    computePageFromHeight(height);

                    pages++;
                }
                previous = 'item';
            }
        });
    }

    /**
     * Extract shared stimulus from a item, put it in section before a item
     * Remove repeated shared stimulus
     * @param {JQuery} $container
     */
    function extractSharedStimulus($container) {
        const hrefs = {};
        // find shared stimulus
        $container.find('.qti-include').each(function () {
            const $include = $(this);
            const href = $include.attr('data-href');
            if (!hrefs[href]) {
                hrefs[href] = true;
                const $section = $include.closest('section.item');
                // move shared stimulus in section before item
                const $newSection = $('<section class="grid-row include"></section>');
                $newSection.append($include);
                $section.before($newSection);
            } else {
                // each shared stimulus is included only once
                $include.remove();
            }
        });
    }

    /**
     * The renderer controller
     * @exports taoBooklet/controller/printTest/render
     */
    return {

        /**
         * Controller entry point
         * @param {Object} config
         */
        start: function start(config) {
            var testData = config.testData;
            var options = config.options;
            var layoutOptions = options && options.layout || {};
            var layoutClasses = {
                'one_page_item': 'one-item-per-page',
                'one_page_section': 'one-section-per-page'
            };

            //this is just in case something went wrong, but we weren't able to catch it.
            var timeout = setTimeout(function () {
                showMessage("Something went wrong...", 'error');
                ready();
            }, 45 * 1000);

            //the content will be inserted in a detached element (to save time)
            var $mainContainer = $(printTpl({
                cls: _.reduce(layoutOptions, function (list, value, name) {
                    if (value && layoutClasses[name]) {
                        list.push(layoutClasses[name]);
                    }
                    return list;
                }, []).join(' ')
            }));

            /**
             * As the page can be called by an external tool and
             * as we build the page in JS asynchronously, we need this hack
             * to tell the 3rd part tool that the page is ready.
             */
            function ready() {
                window.status = 'DONE';
            }

            //we attach the container to the DOM
            $('body').append($mainContainer);

            //instantiate the TestRunner
            testRunner(testData, options)
                .on('error', function (e) {
                    logger.error(e);
                    showMessage(e, 'error');
                    ready();
                })
                .on('ready', function () {

                    //hack layout calculation
                    if (layoutOptions['add_blank_pages']) {
                        printLayoutHacking($mainContainer);
                    }

                    extractSharedStimulus($mainContainer);

                    clearTimeout(timeout);

                    //we are done
                    ready();
                })
                .render($mainContainer);
        }
    };
});
