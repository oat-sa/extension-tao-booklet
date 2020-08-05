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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoBooklet\model;

use common_exception_Error;
use common_exception_NotFound;
use common_session_SessionManager;
use core_kernel_classes_Resource as Resource;
use core_kernel_persistence_Exception;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\service\ConfigurableService;
use oat\oatbox\service\ServiceManagerAwareTrait;
use oat\tao\model\taskQueue\QueueDispatcherInterface;
use oat\tao\model\taskQueue\Task\TaskInterface;
use oat\taoBooklet\model\tasks\PrintDelivery;
use oat\taoBooklet\model\tasks\PrintResults;
use oat\taoBooklet\model\tasks\PrintBooklet;
use oat\taoDelivery\model\execution\ServiceProxy;

/**
 * @author Jean-Sébastien Conan <jean-sebastien@taotesting.com>
 */
class BookletTaskService extends ConfigurableService
{
    use ServiceManagerAwareTrait;

    public const SERVICE_ID = 'taoBooklet/bookletTaskService';

    /** @var QueueDispatcherInterface */
    protected $queueService;

    /**
     * @return QueueDispatcherInterface
     */
    protected function getQueueService()
    {
        if (!$this->queueService) {
            $this->queueService = $this->getServiceLocator()->get(QueueDispatcherInterface::SERVICE_ID);
        }

        return $this->queueService;
    }

    /**
     * Creates a task that will generate a Booklet PDF from an AssessmentTest
     *
     * @param string $booklet Uri of booklet instance
     * @param string $label Label of booklet
     *
     * @return TaskInterface
     * @throws common_exception_Error
     */
    public function createPrintBookletTask(string $booklet, string $label): TaskInterface
    {
        return $this->getQueueService()->createTask(
            $this->propagate(new PrintBooklet()),
            [
                'uri'  => $booklet,
                'user' => common_session_SessionManager::getSession()->getUserUri(),
                'label' => $label,
            ],
            __('Generating PDF file for booklet "%s"', $label)
        );
    }

    /**
     * Creates a task that will generate a Booklet PDF from a DeliveryExecution
     *
     * @param Resource $resource
     * @param array    $printConfig
     *
     * @return TaskInterface
     * @throws common_exception_NotFound
     * @throws common_exception_Error
     * @throws core_kernel_persistence_Exception
     */
    public function createPrintResultsTask(Resource $resource, $printConfig)
    {
        $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($resource);
        $delivery = $deliveryExecution->getDelivery();

        $queueParameters = [
            'uri'    => $resource->getUri(),
            'user'   => common_session_SessionManager::getSession()->getUserUri(),
            'config' => $printConfig,
        ];

        $label = $delivery->getLabel();
        if (isset($printConfig[OntologyRdfs::RDFS_LABEL])) {
            $label = $printConfig[OntologyRdfs::RDFS_LABEL];
        }
        if (isset($printConfig[BookletClassService::PROPERTY_DESCRIPTION])) {
            $label .= ' - ' . $printConfig[BookletClassService::PROPERTY_DESCRIPTION];
        }

        return $this->getQueueService()->createTask(
            $this->propagate(new PrintResults()),
            $queueParameters,
            __('Generate booklet for results of "%s"', $label)
        );
    }

    /**
     * Creates a task that will generate a Booklet PDF from a Delivery
     *
     * @param Resource $resource
     * @param array                        $printConfig
     * @return TaskInterface
     */
    public function createPrintDeliveryTask(Resource $resource, $printConfig)
    {
        $queueParameters = [
            'uri'    => $resource->getUri(),
            'user'   => common_session_SessionManager::getSession()->getUserUri(),
            'config' => $printConfig,
        ];

        $label = $resource->getLabel();
        if (isset($printConfig[OntologyRdfs::RDFS_LABEL])) {
            $label = $printConfig[OntologyRdfs::RDFS_LABEL];
        }

        return $this->getQueueService()->createTask(
            $this->propagate(new PrintDelivery()),
            $queueParameters,
            __('Generate booklet for delivery "%s"', $label)
        );
    }
}
