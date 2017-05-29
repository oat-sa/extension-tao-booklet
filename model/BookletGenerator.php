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
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA;
 *
 *
 */

namespace oat\taoBooklet\model;

use common_report_Report;
use core_kernel_classes_Resource;
use core_kernel_classes_Class;
use oat\taoBooklet\model\export\PdfBookletExporter;
use oat\taoBooklet\model\tasks\UpdateBooklet;
use tao_helpers_Uri;

class BookletGenerator

{
    /**
     * Generate a new Booklet from a specific test
     * in a specific class and return a report
     *
     * @param core_kernel_classes_Resource $test
     * @param core_kernel_classes_Class $class
     * @param array $config
     * @return common_report_Report
     */
    public static function generate(core_kernel_classes_Resource $test, core_kernel_classes_Class $class, $config = [])
    {
        $report = new common_report_Report(common_report_Report::TYPE_SUCCESS);

        $model = \taoTests_models_classes_TestsService::singleton()->getTestModel($test);
        if ($model->getUri() != \taoQtiTest_models_classes_QtiTestService::INSTANCE_TEST_MODEL_QTI) {
            $report->setType(common_report_Report::TYPE_ERROR);
            $report->setMessage(__('%s is not a QTI test', $test->getLabel()));
            return $report;
        }

        // generate tao instance
        $instance = BookletClassService::singleton()->createBookletInstance($class, __('%s Booklet', $test->getLabel()), $test);

        UpdateBooklet::createTask($instance);

        // return report with instance
        $report->setMessage(__('%s created', $instance->getLabel()));
        $report->setData($instance);
        return $report;
    }


    /**
     * Creates pdf in target directory
     *
     * @param core_kernel_classes_Resource $test
     * @param string $targetFolder path
     * @param array $config
     *
     * @return string path to file
     */
    public static function generatePdf(core_kernel_classes_Resource $test, $targetFolder, $config = [])
    {
        $tmpFile = $targetFolder . 'test.pdf';
        $url = tao_helpers_Uri::url('render', 'PrintTest', 'taoBooklet', array(
            'uri' => tao_helpers_Uri::encode($test->getUri()),
            'config' => base64_encode(json_encode($config)),
            'force' => true
        ));

        $exporter = new PdfBookletExporter($test->getLabel(), $config);
        $exporter->setContent($url);
        $exporter->saveAs($tmpFile);

        return $tmpFile;
    }
}
