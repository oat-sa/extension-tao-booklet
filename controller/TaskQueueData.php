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

namespace oat\taoBooklet\controller;

use common_report_Report;
use common_session_SessionManager;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use core_kernel_versioning_File;
use oat\oatbox\task\Queue;
use oat\oatbox\task\Task;
use oat\tao\model\TaskQueueActionTrait;
use oat\taoBooklet\model\BookletClassService;
use tao_actions_CommonModule;
use tao_helpers_Http;

/**
 * Rest API controller for task queue
 *
 * Class tao_actions_TaskQueue
 * @package oat\tao\controller\api
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 */
class TaskQueueData extends tao_actions_CommonModule
{
    use TaskQueueActionTrait;

    /**
     * Lists all tasks related to booklet create/regenerate
     */
    public function getTasks()
    {
        $user = common_session_SessionManager::getSession()->getUser();

        $taskQueue = $this->getServiceManager()->get(Queue::SERVICE_ID);

        $dataPayLoad = $taskQueue->getPayload($user->getIdentifier());

        $this->returnJson($dataPayLoad);
    }

    /**
     * Gets the status of a particular task
     */
    public function getStatus()
    {
        if ($this->hasRequestParameter('taskId')) {
            /**
             * @var $task \oat\Taskqueue\JsonTask
             */
            $task = $this->getTask($this->getRequestParameter('taskId'));
            $report = $task->getReport();
            $data = [
                'status' => $task->getStatus(),
                'label' => $task->getLabel(),
                'creationDate' => $task->getCreationDate(),
                'report' => $report
            ];
            $this->returnJson([
                'success' => true,
                'data' => $data,
            ]);
            return;
        }
        $this->returnJson([
            'success' => false,
        ]);
        return;
    }

    /**
     * Puts the task into archives
     */
    public function archiveTask()
    {
        $taskId = $this->getRequestParameter('taskId');
        /**
         * @var $taskService Queue
         */
        $taskService = $this->getServiceManager()->get(Queue::SERVICE_ID);
        try {
            $task = $this->getTask($taskId);
        } catch (\Exception $e) {
            $this->returnError(__('unknown task id %s', $taskId));
            return;
        }
        if (empty($task)) {
            $this->returnError(__('unknown task id %s', $taskId));
            return;
        }
        try {
            $taskService->updateTaskStatus($taskId, Task::STATUS_ARCHIVED);
            $task = $taskService->getTask($taskId);;
            $this->returnJson([
                'success' => true,
                'data' => [
                    'id' => $taskId,
                    'status' => $task->getStatus()
                ]
            ]);
            return;
        } catch (\Exception $e) {
            $this->returnError(__('impossible to update task status'));
            return;
        }
    }
}
