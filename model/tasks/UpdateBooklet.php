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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA ;
 *
 */
/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */

namespace oat\taoBooklet\model\tasks;

use common_exception_MissingParameter;
use common_session_SessionManager;
use core_kernel_classes_Resource;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\task\Queue;
use oat\oatbox\task\Task;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoBooklet\model\BookletDataService;
use oat\taoBooklet\model\export\PdfBookletExporter;
use oat\taoQtiPrint\model\QtiTestPacker;
use tao_helpers_File;
use tao_helpers_Uri;
use taoQtiTest_models_classes_QtiTestService;
use taoTests_models_classes_TestsService;

/**
 * Class UpdateBooklet
 * @package oat\taoBooklet\model\tasks
 */
class UpdateBooklet extends AbstractBookletTask
{
    use OntologyAwareTrait;

    /**
     *
     * @param array $params
     * @return \common_report_Report
     * @throws \common_exception_MissingParameter
     */
    public function __invoke($params)
    {
        foreach (['uri', 'user'] as $name) {
            if (!isset($params[$name])) {
                throw new common_exception_MissingParameter($name, self::class);
            }
        }

        $this->startCliSession($params['user']);

        $bookletUri = $params['uri'];
        $classService = BookletClassService::singleton();
        $instance = $this->getResource($bookletUri);
        $test = $classService->getTest($instance);
        $config = $this->getBookletConfig($instance);

        $storageKey = uniqid(hash('crc32', $bookletUri));
        $storageService = $this->getServiceLocator()->get(BookletDataService::SERVICE_ID);
        $storageService->setData($storageKey, [
            'test' => $test->getUri(),
            'testData' => $this->compileTest($test),
            'config' => $config,
        ]);

        $tmpFolder = tao_helpers_File::createTempDir();
        $tmpFile = $tmpFolder . 'test.pdf';
        $url = tao_helpers_Uri::url('render', 'PrintTest', 'taoBooklet', ['token' => $storageKey]);

        $exporter = new PdfBookletExporter($test->getLabel(), $config);
        $exporter->setContent($url);
        $exporter->saveAs($tmpFile);

        $report = $classService->updateInstanceAttachment($instance, $tmpFile);

        tao_helpers_File::delTree($tmpFolder);
        $storageService->cleanData($storageKey);

        return $report;
    }

    /**
     * @param $instance
     * @return mixed
     */
    protected function getBookletConfig($instance)
    {
        $configService = $this->getServiceManager()->get(BookletConfigService::SERVICE_ID);
        return $configService->getConfig($instance);
    }

    /**
     * @param string $testUri
     * @return null|\oat\taoTests\models\pack\TestPack
     * @throws \Exception
     */
    protected function compileTest($testUri)
    {
        $testService = taoTests_models_classes_TestsService::singleton();
        $test = $this->getResource($testUri);

        $model = $testService->getTestModel($test);
        if ($model->getUri() != taoQtiTest_models_classes_QtiTestService::INSTANCE_TEST_MODEL_QTI) {
            throw new \Exception('Not a QTI test');
        }

        $packer = new QtiTestPacker();
        $this->getServiceManager()->propagate($packer);
        return $packer->packTest($test);
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return __CLASS__;
    }

    /**
     * Create task in queue
     * @param core_kernel_classes_Resource $resource
     * @return Task created task id
     */
    public static function createTask(core_kernel_classes_Resource $resource)
    {
        $action = new static();
        $action->setServiceLocator(ServiceManager::getServiceManager());
        $queue = ServiceManager::getServiceManager()->get(Queue::SERVICE_ID);
        $queueParameters = [
            'uri' => $resource->getUri(),
            'user' => common_session_SessionManager::getSession()->getUserUri(),
        ];
        $task = $queue->createTask($action, $queueParameters, false, $resource->getLabel(), $resource->getUri());

        return $task;
    }
}
