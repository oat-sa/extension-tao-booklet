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
use core_kernel_classes_Class;
use core_kernel_classes_Resource;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\task\Queue;
use oat\oatbox\task\Task;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoBooklet\model\BookletGenerator;
use tao_models_classes_dataBinding_GenerisFormDataBinder;

/**
 * Class CreateBooklet
 * @package oat\taoBooklet\model\tasks
 */
class CreateBooklet extends AbstractBookletTask
{
    /**
     *
     * @param array $params
     * @return \common_report_Report
     * @throws \common_exception_MissingParameter
     */
    public function __invoke($params)
    {
        foreach (['class', 'test', 'values', 'user'] as $name) {
            if (!isset($params[$name])) {
                throw new common_exception_MissingParameter($name, self::class);
            }
        }

        $this->startCliSession($params['user']);

        $configService = $this->getServiceManager()->get(BookletConfigService::SERVICE_ID);
        $config = $configService->getConfig($params['values']);
        $test = new core_kernel_classes_Resource($params['test']);
        $clazz = new core_kernel_classes_Class($params['class']);

        $report = BookletGenerator::generate($test, $clazz, $config);
        $instance = $report->getData();

        // save properties from form
        $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($instance);
        $binder->bind($params['values']);

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
     * @param core_kernel_classes_Resource $test
     * @param core_kernel_classes_Class $class
     * @param array $values
     * @return Task created task id
     */
    public static function createTask(core_kernel_classes_Resource $test, core_kernel_classes_Class $class, $values = [])
    {
        $action = new static();
        $action->setServiceLocator(ServiceManager::getServiceManager());
        $queue = ServiceManager::getServiceManager()->get(Queue::SERVICE_ID);
        $task = $queue->createTask($action, [
            'test' => $test->getUri(),
            'class' => $class->getUri(),
            'values' => $values,
            'user' => common_session_SessionManager::getSession()->getUserUri(),
        ]);

        return $task;
    }
}
