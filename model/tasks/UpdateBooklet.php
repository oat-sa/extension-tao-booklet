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

use core_kernel_classes_Resource;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\task\AbstractTaskAction;
use oat\oatbox\task\Queue;
use oat\oatbox\task\Task;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoBooklet\model\BookletGenerator;
use tao_helpers_File;

/**
 * Class UpdateBooklet
 * @package oat\taoBooklet\model\tasks
 */
class UpdateBooklet extends AbstractTaskAction implements \JsonSerializable
{
    /**
     *
     * @param array $params
     * @return \common_report_Report
     * @throws \common_exception_MissingParameter
     */
    public function __invoke($params)
    {
        if (!isset($params['uri'])) {
            throw new \common_exception_MissingParameter('uri', self::class);
        }

        $classService = BookletClassService::singleton();
        $instance = new core_kernel_classes_Resource($params['uri']);
        $test = $classService->getTest($instance);

        $configService = $this->getServiceManager()->get(BookletConfigService::SERVICE_ID);
        $config = $configService->getConfig($instance);

        $tmpFolder = tao_helpers_File::createTempDir();
        $tmpFile = BookletGenerator::generatePdf($test, $tmpFolder, $config);
        $report = $classService->updateInstanceAttachment($instance, $tmpFile);

        tao_helpers_File::delTree($tmpFolder);

        return $report;
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
        $task = $queue->createTask($action, [
            'uri' => $resource->getUri()
        ]);

        return $task;
    }
}
