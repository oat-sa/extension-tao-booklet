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
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
define(['module', 'helpers', 'jquery'], function (module, helpers, $) {
    'use strict';

    return {
        start: function () {

            var $downloader = $('<iframe/>').hide();
            $('form').append($downloader);

            $('.btn-download').on('click', function (e) {
                e.preventDefault();
                var $form = $(e.target).closest('form');

                $downloader.attr('src', helpers._url('download', 'Booklet', 'taoBooklet', {
                    uri: $('input[name="uri"]').val(),
                    classUri: $('input[name="classUri"]').val(),
                    id: $('input[name="id"]').val()
                }));
            });

            //$('.btn-regenerate').on('click', function (e) {
            //    e.preventDefault();
            //    var $form = $(e.target).closest('form')
            //    $form.attr('action', helpers._url('regenerate', 'Booklet', 'taoBooklet'));
            //    $form.submit();
            //});
        }
    };
});
