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
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoQtiPrint\model\QtiTestPacker;
use taoQtiTest_models_classes_QtiTestService;
use taoTests_models_classes_TestsService;

/**
 * Class UpdateBooklet
 * @package oat\taoBooklet\model\tasks
 */
class UpdateBooklet extends AbstractBookletTask
{
    protected $bookletClassService;

    /**
     * AbstractBookletTask constructor.
     */
    public function __construct()
    {
        $this->bookletClassService = BookletClassService::singleton();
    }

    /**
     * Gets the config for a booklet instance using either the instance itself or an array of properties
     * @param core_kernel_classes_Resource $instance
     * @param array $params
     * @return mixed
     */
    protected function getBookletConfig($instance, $params)
    {
        $configService = $this->getServiceManager()->get(BookletConfigService::SERVICE_ID);
        return $configService->getConfig($instance);
    }

    /**
     * @param core_kernel_classes_Resource $instance
     * @return JsonSerializable
     * @throws \Exception
     */
    protected function getTestData($instance)
    {
        $testService = taoTests_models_classes_TestsService::singleton();
        $test = $this->bookletClassService->getTest($instance);

        $model = $testService->getTestModel($test);
        if ($model->getUri() != taoQtiTest_models_classes_QtiTestService::INSTANCE_TEST_MODEL_QTI) {
            throw new \Exception('Not a QTI test');
        }

        $packer = new QtiTestPacker();
        $this->getServiceManager()->propagate($packer);
        return $packer->packTest($test);
    }

    /**
     * @param string $filePath
     * @param core_kernel_classes_Resource $instance
     * @return \common_report_Report
     */
    protected function storePdf($filePath, $instance)
    {
        return $this->bookletClassService->updateInstanceAttachment($instance, $filePath);
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return __CLASS__;
    }
}
