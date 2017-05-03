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

use common_cache_FileCache;
use common_ext_ExtensionsManager;
use core_kernel_classes_Resource;
use Exception;
use oat\taoQtiPrint\model\QtiTestPacker;
use tao_actions_CommonModule;
use tao_helpers_Uri;
use taoTests_models_classes_TestsService;

class PrintTest extends tao_actions_CommonModule
{
    const CACHE_PREFIX = 'printed-test-pack_';

    public function render()
    {
        session_write_close();

        //load the rendering config
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('taoBooklet');
        $config = $ext->getConfig('rendering');

        $testService    = taoTests_models_classes_TestsService::singleton();
        $cache          = common_cache_FileCache::singleton();

        $force          = $this->hasRequestParameter('force');
        $test           = new core_kernel_classes_Resource(tao_helpers_Uri::decode($this->getRequestParameter('uri')));

        if ($this->hasRequestParameter('config')) {
            $config = array_merge($config, json_decode(base64_decode($this->getRequestParameter('config')), true));
        }

        $model          = $testService->getTestModel($test);
        if ($model->getUri() != \taoQtiTest_models_classes_QtiTestService::INSTANCE_TEST_MODEL_QTI) {
            throw new Exception('Not a QTI test');
        }

        //we use the cache as the pack generation is heavy
        $entry = self::CACHE_PREFIX . $test->getUri();
        if($force == true || !$cache->has($entry)){

            //generate the pack
            $packer   = new QtiTestPacker();
            $this->getServiceManager()->propagate($packer);
            $testData = json_encode($packer->packTest($test));

            //put the pack in cache
            $cache->put($testData, $entry);

        } else {
            $testData = $cache->get($entry);
        }

        $this->setData('client_config_url', $this->getClientConfigUrl());
        $this->setData('testData', $testData);
        $this->setData('options', json_encode($config));
        $this->setView('PrintTest/render.tpl');
    }
}
