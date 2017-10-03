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
 * @author Dieter Raber <dieter@taotesting.com>
 */
function subst(type) {
    var vars = extractVars();
    var config = getConfig();
    var layoutConfig = config.layout || {};
    var lineConfig = config[type] || {};
    var cells = {};

    function extractVars() {
        var parameters = document.location.search.substring(1).split('&');
        var values = {};
        var name, value;
        for (name in parameters) {
            value = parameters[name].split('=', 2);
            values[value[0]] = decodeURIComponent(value[1]);
        }
        return values;
    }

    function addScanMarks(type) {
        // extend list if required
        var marks = {
            square: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAAAAAA6fptVAAAACklEQVR42mNgAAAAAgAB5Sfe/AAAAABJRU5ErkJggg==',
            cross: 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACQAAAAkAQMAAADbzgrbAAAABlBMVEUAAAAAAAClZ7nPAAAAAXRSTlMAQObYZgAAABdJREFUCNdjYGBIAGJak/+B4AOEpI+NAIm7FpmtX/1fAAAAAElFTkSuQmCC'
        };

        var mark = marks[type] ? marks[type] : marks['square'];

        var cells = document.querySelectorAll('.line .scan-mark');

        [].forEach.call(cells, function(cell) {
            if(cell.innerHTML) {
                return;
            }
            cell.innerHTML = wrap(mark, 'img');
        });
    }


    function addStylesheet(theme) {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.type = 'text/css';
        link.href = theme;
        document.querySelector('head').appendChild(link);
    }


    function getConfig() {
        try {
            return JSON.parse(window.atob(vars.config));
        } catch (e) {
            return {};
        }
    }

    function addCellContent(position, value) {
        cells[position] = (cells[position] || '') + value;
    }

    function getBarCode(dataStr) {

        PDF417.init(dataStr);

        var barcode = PDF417.getBarcodeArray();

        // block sizes (width and height) in pixels
        var bw = 2;
        var bh = 2;

        var canvas = document.createElement('canvas');
        canvas.id = 'pdf417Code';
        canvas.width = bw * barcode['num_cols'];
        canvas.height = bh * barcode['num_rows'];

        var ctx = canvas.getContext('2d');

        // graph barcode elements
        var y = 0;
        // for each row
        for (var r = 0; r < barcode['num_rows']; ++r) {
            var x = 0;
            // for each column
            for (var c = 0; c < barcode['num_cols']; ++c) {
                if (barcode['bcode'][r][c] == 1) {
                    ctx.fillRect(x, y, bw, bh);
                }
                x += bw;
            }
            y += bh;
        }
        return canvas.toDataURL('image/png');
    }

    function writeCells() {
        var name, cell;
        for (name in cells) {
            cell = document.getElementById('cell-' + name);
            if (cell) {
                cell.innerHTML = cells[name];
            }
        }
    }

    function wrap(content, nodeName, id) {
        var element = '<' + (nodeName || 'span');
        if(id) {
            element += ' id="' + id + '"';
        }
        if(nodeName === 'img') {
            element += ' src="' + content + '" alt="" />';
        }
        else {
            element += '>' + content + '</' + nodeName + '>';
        }
        return element;
    }


    if (layoutConfig.cover_page && vars.page === vars.frompage) {
        document.getElementById('line').style.display = "none";
    } else {


        // css theme
        if (config.table_theme) {
            addStylesheet(config.table_theme);
        }

        /*
            cell overview
            =============
            usually top:

            logo            b1
            doctitle        b2
            date            b3 (creation date)


            usually bottom:

            unique_id       a1
            custom_id       a2
            expiration_date a3
            pdf417          a4

            mention         b1
            link            b2
            page_number     b3

            small_print     c

            rows that are completely empty will be removed
         */

        // logo
        if (lineConfig.logo && config.logo) {
            addCellContent('b1', wrap(config.logo, 'img', 'company_logo'));
        }

        // title
        if (lineConfig.title && vars.doctitle) {
            var position = (lineConfig.scan_marks &&
            !lineConfig.date &&
            !lineConfig.logo &&
            !lineConfig.unique_id &&
            !lineConfig.custom_id &&
            !lineConfig.expiration_date &&
            !lineConfig.matrix_barcode) ? 'a1' : 'b2';

            addCellContent(position, wrap(vars.doctitle));
        }

        // date
        if (lineConfig.date && vars.date) {
            addCellContent('b3', wrap(vars.date));
        }

        // mention
        if (lineConfig.mention && config.mention) {
            addCellContent('b1', wrap(config.mention));
        }

        // link
        if (lineConfig.link && config.link) {
            addCellContent('b2', wrap(config.link));
        }

        // page number
        if (lineConfig.page_number) {
            position = lineConfig.matrix_barcode ? 'b2' : 'b3';
            addCellContent(position, wrap(config.page_format.replace(/%s/, vars.page).replace(/%s/, vars.topage)));
        }

        // small print
        if (lineConfig.small_print && config.small_print) {
            addCellContent('c', wrap(config.small_print, 'small'));
        }

        // unique id
        if (lineConfig.unique_id && config.unique_id) {
            addCellContent('a1', wrap(config.unique_id));
        }


        if (lineConfig.custom_id && config.custom_id) {
            addCellContent('a2', wrap(config.custom_id));
        }

        // expiration date
        if (lineConfig.expiration_date && config.expiration_date) {
            addCellContent('a3', wrap(config.expiration_date));
        }

        // matrix barcode
        if (lineConfig.matrix_barcode && config.matrix_barcode) {
            addCellContent('a4', wrap(getBarCode(config.matrix_barcode), 'img', 'pdf417_code'));
            document.querySelector('#cell-a4').style.padding = 0;
        }

        writeCells();

        // add scan marks
        if (lineConfig.scan_marks) {
            addScanMarks(config.scan_mark_symbol || 'square');
        }

    }
}
