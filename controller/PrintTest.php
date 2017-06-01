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

use common_ext_ExtensionsManager;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoBooklet\model\BookletDataService;
use oat\taoQtiPrint\model\QtiTestPacker;
use tao_actions_CommonModule;

/**
 * Class PrintTest
 * @package oat\taoBooklet\controller
 */
class PrintTest extends tao_actions_CommonModule
{
    /**
     * Generate html(print-ready) version of tests
     */
    public function render()
    {
        session_write_close();

        $storageKey = $this->getRequestParameter('token');
        $storageService = $this->getServiceManager()->get(BookletDataService::SERVICE_ID);
        $bookletData = $storageService->getData($storageKey);

        if (!$bookletData) {
            $bookletData = [
                'testData' => null
            ];
        }

        $this->renderTest($bookletData);
    }

    /**
     * @throws \Exception
     */
    public function preview()
    {
        session_write_close();

        $instance = new \core_kernel_classes_Resource(\tao_helpers_Uri::decode($this->getRequestParameter('uri')));
        $test = BookletClassService::singleton()->getTest($instance);
        $testService = \taoTests_models_classes_TestsService::singleton();
        $model = $testService->getTestModel($test);
        if ($model->getUri() != \taoQtiTest_models_classes_QtiTestService::INSTANCE_TEST_MODEL_QTI) {
            throw new \Exception('Not a QTI test');
        }

        $packer = new QtiTestPacker();
        $this->getServiceManager()->propagate($packer);

        $configService = $this->getServiceManager()->get(BookletConfigService::SERVICE_ID);
        $bookletData = [
            'testData' => $packer->packTest($test),
            'config' => $configService->getConfig($instance),
        ];

        $this->renderTest($bookletData);
    }

    /**
     * @param array $bookletData
     */
    protected function renderTest($bookletData)
    {
        $config = common_ext_ExtensionsManager::singleton()->getExtensionById('taoBooklet')->getConfig('rendering');
        if (isset($bookletData['config'])) {
            $config = array_merge($config, $bookletData['config']);
        }

        $this->setData('client_config_url', $this->getClientConfigUrl());
        $this->setData('testData', json_encode($bookletData['testData']));
        $this->setData('options', json_encode($config));
        $this->setView('PrintTest/render.tpl');
    }
}
