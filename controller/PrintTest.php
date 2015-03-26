<?php
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
 *
 */


/**
 * Controller to generate html(print-ready) version of tests
 *
 * @author Mikhail Kamarouski, <Komarouski@1pt.com>
 * @package taoBooklet
 */
namespace oat\taoBooklet\controller;

use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use tao_actions_CommonModule;

class PrintTest extends tao_actions_CommonModule
{

    public function render()
    {
        $uri  = $this->getRequestParameter( 'uri' );
        $test = new core_kernel_classes_Resource( $uri );
        if ($test->hasType( new core_kernel_classes_Class( TAO_TEST_CLASS ) )) {
            echo '<h1>TEST</h1>';
            echo "<i>{$test->getLabel()}</i>";
        } else {
            echo 'Invalid uri provided';
        }
    }
}