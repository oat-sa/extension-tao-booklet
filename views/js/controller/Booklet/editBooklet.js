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
define(['jquery', 'lodash', 'module', 'util/url'], function ($, _, module, urlHelper) {
    'use strict';

    return {
        start: function () {

            var $downloader = $('<iframe/>').hide();
            $('form').append($downloader);

            $('.btn-download').on('click', function (e) {
                e.preventDefault();
                var $form = $(e.target).closest('form');

                $downloader.attr('src', urlHelper.route('download', 'Booklet', 'taoBooklet', {
                    uri: $form.find('input[name="uri"]').val(),
                    classUri: $form.find('input[name="classUri"]').val(),
                    id: $form.find('input[name="id"]').val()
                }));
            });
        }
    };
});
