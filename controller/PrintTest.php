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
 * Copyright (c) 2015-2018 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoBooklet\controller;

use common_ext_ExtensionsManager as ExtensionsManager;
use oat\generis\model\OntologyAwareTrait;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoBooklet\model\BookletDataService;
use oat\taoQtiPrint\model\QtiTestPacker;
use tao_actions_CommonModule;

/**
 * Controller to generate html(print-ready) version of tests
 *
 * @author  Mikhail Kamarouski, <Komarouski@1pt.com>
 * @package taoBooklet
 */
class PrintTest extends tao_actions_CommonModule
{
    use OntologyAwareTrait;

    /**
     * Generate html(print-ready) version of tests
     */
    public function render()
    {
        $this->defaultData();

        if ($this->hasRequestParameter('uri') && !$this->hasRequestParameter('token')) {
            return $this->forward('preview');
        }

        session_write_close();

        $storageKey = $this->getRequestParameter('token');
        $storageService = $this->getServiceLocator()->get(BookletDataService::SERVICE_ID);
        $bookletData = $storageService->getData($storageKey);

        if (!$bookletData) {
            $bookletData = [
                'testData' => null,
            ];
        }

        $this->renderTest($bookletData);
    }

    /**
     * @throws \Exception
     */
    public function preview()
    {
        $this->defaultData();

        session_write_close();
        try {

            $uri = \tao_helpers_Uri::decode($this->getRequestParameter('uri'));
            $instance = $this->getResource($uri);
            $test = BookletClassService::singleton()->getTest($instance);
            if (!$test || !$test->exists()) {
                throw new \common_exception_NotFound('Unknown resource '.$uri);
            }
            $testService = \taoTests_models_classes_TestsService::singleton();
            $model = $testService->getTestModel($test);
            if ($model->getUri() != \taoQtiTest_models_classes_QtiTestService::INSTANCE_TEST_MODEL_QTI) {
                throw new \common_exception_NotFound('Not a QTI test');
            }

            $packer = $this->propagate(new QtiTestPacker());

            $configService = $this->getServiceLocator()->get(BookletConfigService::SERVICE_ID);
            $bookletData = [
                'testData' => $packer->packTest($test),
                'config'   => $configService->getConfig($instance),
            ];

            $this->renderTest($bookletData);
        } catch (\common_exception_NotFound $e) {
            header("HTTP/1.0 404 Not Found");
            $this->setView('error/error404.tpl', 'tao');
        }
    }

    /**
     * @param array $bookletData
     */
    protected function renderTest($bookletData)
    {
        /** @var ExtensionsManager $extManager */
        $extManager = $this->getServiceLocator()->get(ExtensionsManager::SERVICE_ID);
        $config = $extManager->getExtensionById('taoBooklet')->getConfig('rendering');
        if (isset($bookletData['config'])) {
            $config = array_merge($config, $bookletData['config']);
        }

        $this->setData('client_config_url', $this->getClientConfigUrl());
        $this->setData('client_params', [
            'testData' => $bookletData['testData'],
            'options' => $config
        ]);
        $this->setView('PrintTest/render.tpl');
    }
}
