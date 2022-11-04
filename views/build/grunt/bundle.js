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
 * Copyright (c) 2014-2018 (original work) Open Assessment Technologies SA;
 */

/**
 * configure the extension bundles
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
module.exports = function (grunt) {
    'use strict';

    grunt.config.merge({
        bundle: {
            taobooklet: {
                options: {
                    extension: 'taoBooklet',
                    outputDir: 'loader',
                    dependencies: ['taoQtiPrint', 'taoItems', 'taoQtiItem'],
                    allowExternal: ['qtiCustomInteractionContext', 'qtiInfoControlContext'],
                    bundles: [
                        {
                            name: 'taoBooklet',
                            default: true,
                            bootstrap: true,
                            babel: true
                        },
                        {
                            name: 'taoBookletRunner',
                            entryPoint: 'taoBooklet/controller/PrintTest/render',
                            standalone: true,
                            babel: true,
                            targets: {
                                ie: '11'
                            },
                            include: [
                                'taoItems/assets/**/*',
                                'taoItems/preview/**/*',
                                'taoItems/previewer/**/*',
                                'taoItems/runner/**/*',
                                'taoItems/runtime/**/*',
                                'taoQtiItem/mathRenderer/mathRenderer',
                                'taoQtiItem/portableElementRegistry/**/*',
                                'taoQtiItem/qtiCommonRenderer/**/*',
                                'taoQtiItem/reviewRenderer/**/*',
                                'taoQtiItem/qtiCreator/**/*',
                                'taoQtiItem/qtiItem/**/*',
                                'taoQtiItem/qtiRunner/**/*',
                                'taoQtiItem/qtiXmlRenderer/**/*',
                                'qtiCustomInteractionContext',
                                'qtiInfoControlContext',
                                'taoQtiPrint/lib/**/*',
                                'taoQtiPrint/qtiCommonRenderer/**/*',
                                'taoQtiPrint/qtiPrintRenderer/**/*',
                                'taoQtiPrint/runner/**/*'
                            ]
                        }
                    ]
                }
            }
        }
    });

    // bundle task
    grunt.registerTask('taobookletbundle', ['bundle:taobooklet']);
};
