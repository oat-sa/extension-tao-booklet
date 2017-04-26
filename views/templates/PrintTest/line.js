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

    function writeCells() {
        var name, cell;
        for (name in cells) {
            cell = document.getElementById('cell-' + name);
            if (cell) {
                cell.innerHTML = cells[name];
            }
        }
    }

    function wrap(content) {
        return '<span>' + content + '</span>';
    }

    if (vars.page === vars.frompage) {
        document.getElementById('line').style.display = "none";
    } else {
        if (lineConfig.logo && config.logo) {
            addCellContent('left', '<img src="' + config.logo + '" alt="logo" />');
        }

        if (lineConfig.mention && config.mention) {
            addCellContent('left', wrap(config.mention));
        }

        if (lineConfig.link && config.link) {
            addCellContent('middle', wrap(config.link));
        }

        if (lineConfig.title && vars.doctitle) {
            addCellContent('middle', wrap(vars.doctitle));
        }

        if (lineConfig.date && vars.date) {
            addCellContent('right', wrap(vars.date));
        }

        if (lineConfig.page_number) {
            addCellContent('right', wrap(vars.page + '/' + vars.topage));
        }

        writeCells();
    }
}