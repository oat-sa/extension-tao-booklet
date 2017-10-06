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
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\task\Queue;
use oat\oatbox\task\Task;
use oat\taoBooklet\model\tasks\PrintDelivery;
use oat\taoBooklet\model\tasks\PrintResults;
use oat\taoBooklet\model\tasks\PrintBooklet;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\Taskqueue\Persistence\RdsQueue;

class BookletTaskService extends ConfigurableService
{
    const SERVICE_ID = 'taoBooklet/bookletTaskService';

    /**
     * @var Queue
     */
    protected $queueService;

    /**
     * @return Queue
     */
    protected function getQueueService()
    {
        if (!$this->queueService) {
            $this->queueService = $this->getServiceLocator()->get(Queue::SERVICE_ID);
        }
        return $this->queueService;
    }

    /**
     * Checks if the queue manager is asynchronous
     * @return bool
     */
    public function isAsyncQueue()
    {
        $queue = $this->getQueueService();
        return $queue instanceof RdsQueue;
    }

    /**
     * Creates a task that will generate a Booklet PDF from an AssessmentTest
     * @param core_kernel_classes_Resource $resource
     * @return Task created task id
     */
    public function createPrintBookletTask(core_kernel_classes_Resource $resource)
    {
        $action = new PrintBooklet();
        $this->getServiceManager()->propagate($action);
        $queueParameters = [
            'uri' => $resource->getUri(),
            'user' => common_session_SessionManager::getSession()->getUserUri(),
        ];
        $task = $this->getQueueService()->createTask($action, $queueParameters, false, $resource->getLabel(), $resource->getUri());

        return $task;
    }

    /**
     * Creates a task that will generate a Booklet PDF from a DeliveryExecution
     * @param core_kernel_classes_Resource $resource
     * @param array $printConfig
     * @return Task created task id
     */
    public function createPrintResultsTask(core_kernel_classes_Resource $resource, $printConfig)
    {
        $action = new PrintResults();
        $this->getServiceManager()->propagate($action);
        
        $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($resource);
        $delivery = $deliveryExecution->getDelivery();
        
        $queueParameters = [
            'uri' => $resource->getUri(),
            'user' => common_session_SessionManager::getSession()->getUserUri(),
            'config' => $printConfig,
        ];

        $label = $delivery->getLabel();
        if (isset($printConfig[OntologyRdfs::RDFS_LABEL])) {
            $label = $printConfig[OntologyRdfs::RDFS_LABEL];
        }
        if (isset($printConfig[BookletClassService::PROPERTY_DESCRIPTION])) {
            $label .= ' - ' . $printConfig[BookletClassService::PROPERTY_DESCRIPTION];
        }
        $task = $this->getQueueService()->createTask($action, $queueParameters, false, $label, $delivery->getUri());

        return $task;
    }

    /**
     * Creates a task that will generate a Booklet PDF from a Delivery
     * @param core_kernel_classes_Resource $resource
     * @param array $printConfig
     * @return Task created task id
     */
    public function createPrintDeliveryTask(core_kernel_classes_Resource $resource, $printConfig)
    {
        $action = new PrintDelivery();
        $this->getServiceManager()->propagate($action);
        
        $queueParameters = [
            'uri' => $resource->getUri(),
            'user' => common_session_SessionManager::getSession()->getUserUri(),
            'config' => $printConfig,
        ];

        $label = $resource->getLabel();
        if (isset($printConfig[OntologyRdfs::RDFS_LABEL])) {
            $label = $printConfig[OntologyRdfs::RDFS_LABEL];
        }
        
        $task = $this->getQueueService()->createTask($action, $queueParameters, false, $label, $resource->getUri());

        return $task;
    }
}
