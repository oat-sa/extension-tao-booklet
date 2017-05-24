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
use core_kernel_classes_Class;
use oat\oatbox\service\ServiceManager;
use oat\oatbox\task\AbstractTaskAction;
use oat\oatbox\task\Queue;
use oat\oatbox\task\Task;
use oat\taoBooklet\model\BookletGenerator;

/**
 * Class CreateBooklet
 * @package oat\taoBooklet\model\tasks
 */
class CreateBooklet extends AbstractTaskAction implements \JsonSerializable
{
    /**
     *
     * @param array $params
     * @return \common_report_Report
     * @throws \common_exception_MissingParameter
     */
    public function __invoke($params)
    {
        foreach (['class', 'test', 'config'] as $name) {
            if (!isset($params[$name])) {
                throw new \common_exception_MissingParameter($name, self::class);
            }
        }

        $test = new core_kernel_classes_Resource($params['test']);
        $clazz = new core_kernel_classes_Class($params['class']);

        return BookletGenerator::generate($test, $clazz, $params['config']);
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
     * @param array $config
     * @return Task created task id
     */
    public static function createTask(core_kernel_classes_Resource $test, core_kernel_classes_Class $class, $config = [])
    {
        $action = new static();
        $action->setServiceLocator(ServiceManager::getServiceManager());
        $queue = ServiceManager::getServiceManager()->get(Queue::SERVICE_ID);
        $task = $queue->createTask($action, [
            'test' => $test->getUri(),
            'class' => $class->getUri(),
            'config' => $config
        ]);

        return $task;
    }
}
