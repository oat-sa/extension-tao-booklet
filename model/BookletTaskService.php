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

namespace oat\taoBooklet\model;

use common_session_SessionManager;
use core_kernel_classes_Resource;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\task\Queue;
use oat\oatbox\task\Task;
use oat\taoBooklet\model\tasks\UpdateBooklet;

class BookletTaskService extends ConfigurableService
{
    const SERVICE_ID = 'taoBooklet/bookletTaskService';

    /**
     * Create task in queue
     * @param core_kernel_classes_Resource $resource
     * @return Task created task id
     */
    public function createBookletTask(core_kernel_classes_Resource $resource)
    {
        $action = new UpdateBooklet();
        $this->getServiceManager()->propagate($action);
        $queue = $this->getServiceLocator()->get(Queue::SERVICE_ID);
        $queueParameters = [
            'uri' => $resource->getUri(),
            'user' => common_session_SessionManager::getSession()->getUserUri(),
        ];
        $task = $queue->createTask($action, $queueParameters, false, $resource->getLabel(), $resource->getUri());

        return $task;
    }
}