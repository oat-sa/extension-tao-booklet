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
use JsonSerializable;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoBooklet\model\StorageService;
use oat\taoDelivery\model\execution\ServiceProxy;
use oat\taoQtiPrint\model\DeliveryExecutionPacker;
use tao_helpers_Date;

/**
 * Class PrintResults
 * @package oat\taoBooklet\model\tasks
 */
class PrintResults extends AbstractBookletTask
{
    /**
     *
     * @param array $params
     * @return \common_report_Report
     * @throws \common_exception_MissingParameter
     */
    public function __invoke($params)
    {
        // make sure the context is loaded
        $extensionManager = $this->getServiceLocator()->get('generis/extensionManager');
        $extensionManager->getExtensionById('taoDeliveryRdf');

        return parent::__invoke($params);
    }

    /**
     * Gets the list of mandatory parameters
     * @return array
     */
    protected function getMandatoryParams()
    {
        return array_merge(parent::getMandatoryParams(), ['config']);
    }

    /**
     * Gets the config for a booklet instance using either the instance itself or an array of properties
     * @param core_kernel_classes_Resource $instance
     * @return mixed
     */
    protected function getBookletConfig($instance)
    {
        $deliveryExecution = ServiceProxy::singleton()->getDeliveryExecution($instance);
        $configService = $this->getServiceLocator()->get(BookletConfigService::SERVICE_ID);
        $config = $configService->getConfig($this->getParam('config'));
        $config[BookletConfigService::CONFIG_REGULAR] = true;
        $config[BookletConfigService::CONFIG_DATE] = tao_helpers_Date::displayeDate($deliveryExecution->getStartTime());
        return $config;
    }

    /**
     * Gets the test definition data in order to print it
     * @param core_kernel_classes_Resource $instance
     * @return JsonSerializable|array
     * @throws \Exception
     */
    protected function getTestData($instance)
    {
        $deliveryExecutionPacker = $this->getServiceLocator()->get(DeliveryExecutionPacker::SERVICE_ID);

        $testData = $deliveryExecutionPacker->getTestData($instance);
        $testData['states'] = $deliveryExecutionPacker->getResultVariables($instance->getUri());
        return $testData;
    }

    /**
     * Stores the generated PDF file
     * @param core_kernel_classes_Resource $instance
     * @param string $filePath
     * @return \common_report_Report
     */
    protected function storePdf($instance, $filePath)
    {
        $report = new \common_report_Report(\common_report_Report::TYPE_SUCCESS);

        $storageService = $this->getServiceLocator()->get(StorageService::SERVICE_ID);
        $fileResource = $storageService->storeFile($filePath);

        $report->setMessage(__('%s rendered', $instance->getLabel()));
        $report->setData($fileResource);
        return $report;
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return __CLASS__;
    }
}
