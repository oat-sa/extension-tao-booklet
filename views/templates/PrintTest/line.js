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

    function deleteEmptyRows() {

        var tr = document.querySelectorAll('tr');
        var th;
        var iTr = tr.length;
        var iTh;
        var hasContent;

        while(iTr--) {
            hasContent = false;
            th = tr[iTr].querySelectorAll('th');
            iTh = th.length;
            while(iTh--) {
                if(th[iTh].innerHTML.trim()){
                    hasContent = true;
                }
            }
            if(!hasContent){
                tr[iTr].parentNode.removeChild(tr[iTr]);
            }
        }
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

    function wrap(content, element) {
        element = element || 'span';
        if(element === 'img') {
           return '<img src="' + content + '" alt="" />';
        }
        return '<' + element + '>' + content + '</' + element + '>';
    }


    if (layoutConfig.cover_page && vars.page === vars.frompage) {
        document.getElementById('line').style.display = "none";
    } else {
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
            addCellContent('b1', wrap(config.logo, 'img'));
        }

        // title
        if (lineConfig.title && vars.doctitle) {
            addCellContent('b2', wrap(vars.doctitle));
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
            addCellContent('b3', wrap(vars.page + '/' + vars.topage));
        }

        // small print
        if (lineConfig.small_print && config.small_print) {
            addCellContent('c', wrap(config.small_print, 'small'));
        }

        // unique id
        if (lineConfig.unique_id && config.unique_id) {
            addCellContent('a1', wrap(config.unique_id));
        }

        // custom id
        if (lineConfig.custom_id && config.custom_id) {
            addCellContent('a2', wrap(config.custom_id));
        }

        // expiration date
        if (lineConfig.expiration_date && config.expiration_date) {
            addCellContent('a3', wrap(config.expiration_date));
        }

        // // matrix barcode
        // needs to be commented until the qr generation is fixed
        // if (lineConfig.matrix_barcode && config.matrix_barcode) {
        //     addCellContent('a4', wrap(config.matrix_barcode, 'img'));
        // }


        writeCells();
        deleteEmptyRows();
    }
}
