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
    const CACHE_PREFIX = 'printed-test-pack_';

    /**
     */
    public function index()
    {
        $uri = $this->getRequestParameter('uri');
        if($uri == null || empty($uri)){
            //throw
        }

        $this->setData('uri', $uri);
        $this->setView('PrintTest/index.tpl');
    }

    public function render()
    {
        $uri  = $this->getRequestParameter( 'uri' );
        $test = new core_kernel_classes_Resource( $uri );
        if ($test->hasType( new core_kernel_classes_Class( TAO_TEST_CLASS ) )) {
            $this->setData( 'label', $test->getLabel() );
            $this->setView( 'Print/render.tpl' );
        } else {
            echo 'Invalid uri provided';
        }

        ///
         $force = $this->hasRequestParameter('force');
        $cache = common_cache_FileCache::singleton();



        //$uri = $this->getRequestParameter('uri');
        //if($uri == null || empty($uri)){
            ////throw
        //}

        //$booklet = new core_kernel_classes_Resource($uri);


        //get test from booklet
        $testUri = "http://bertao/tao.rdf#i142729250359635";
        $test    = new core_kernel_classes_Resource($testUri);
        $entry   = self::$CACHE_PREFIX . $test->getUri();

        if($force || !$cache->has($entry)){

            $packer  = new QtiTestPacker();

            $testData = $packer->packTest($test);

            $cache->put($entry, $testData);
        } else {
            $testData = $cache->get($entry);
        }

        $test = new core_kernel_classes_Resource($testUri);

        $testData = $this->getTestData($test);

        $this->setData('client_config_url', $this->getClientConfigUrl());
        $this->setData('testData', $testData);
        $this->setView('PrintTest/render.tpl');
    }
}