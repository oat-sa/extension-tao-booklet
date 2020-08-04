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
use common_report_Report;
use core_kernel_classes_Resource;
use Exception;
use JsonSerializable;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoBooklet\model\export\BookletExporterException;
use oat\taoQtiPrint\model\QtiTestPacker;
use taoQtiTest_models_classes_QtiTestService;
use taoTests_models_classes_TestsService;

/**
 * Class PrintBooklet
 * @package oat\taoBooklet\model\tasks
 */
class PrintBooklet extends AbstractBookletTask
{
    /** @var BookletClassService */
    protected $bookletClassService;

    /**
     * AbstractBookletTask constructor.
     */
    public function __construct()
    {
        $this->bookletClassService = BookletClassService::singleton();
    }

    /**
     * @param array $params
     *
     * @return common_report_Report
     * @throws common_exception_MissingParameter
     * @throws BookletExporterException
     */
    public function __invoke($params)
    {
        $report = parent::__invoke($params);

        if ($report === null || $report->containsError()) {
            $this->getResource($params['uri'])->delete(true);
        } else {
            $this->getResource($params['uri'])->setLabel($params['label']);
        }

        return $report;
    }

    /**
     * Gets the config for a booklet instance using either the instance itself or an array of properties
     * @param core_kernel_classes_Resource $instance
     * @return mixed
     */
    protected function getBookletConfig($instance)
    {
        $configService = $this->getServiceLocator()->get(BookletConfigService::SERVICE_ID);

        return $configService->getConfig($instance);
    }

    /**
     * Gets the test definition data in order to print it
     * @param core_kernel_classes_Resource $instance
     * @return JsonSerializable|array
     * @throws Exception
     */
    protected function getTestData($instance)
    {
        $testService = taoTests_models_classes_TestsService::singleton();
        $test = $this->bookletClassService->getTest($instance);

        $model = $testService->getTestModel($test);
        if ($model->getUri() !== taoQtiTest_models_classes_QtiTestService::INSTANCE_TEST_MODEL_QTI) {
            throw new Exception('Not a QTI test');
        }

        return $this->getTestPacker()->packTest($test);
    }

    /**
     * Stores the generated PDF file
     * @param core_kernel_classes_Resource $instance
     * @param string $filePath
     * @return common_report_Report
     */
    protected function storePdf($instance, $filePath)
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

    /**
     * @return QtiTestPacker
     */
    private function getTestPacker(): QtiTestPacker
    {
        return $this->propagate(new QtiTestPacker());
    }

    /**
     * @return array|string[]
     */
    protected function getMandatoryParams(): array
    {
        return array_merge(parent::getMandatoryParams(), ['label']);
    }
}
