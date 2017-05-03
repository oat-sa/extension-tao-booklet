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

class BookletRenderer
{
    /**
     * Generate a new Booklet from a specific test
     * in a specific class and return a report
     *
     * @param core_kernel_classes_Resource $test
     * @param core_kernel_classes_Class $class
     * @return common_report_Report
     */
    static public function generate(core_kernel_classes_Resource $test, core_kernel_classes_Class $class)
    {
        $report = new common_report_Report(common_report_Report::TYPE_SUCCESS);

        $model = \taoTests_models_classes_TestsService::singleton()->getTestModel($test);
        if ($model->getUri() != \taoQtiTest_models_classes_QtiTestService::INSTANCE_TEST_MODEL_QTI) {
            $report->setType(common_report_Report::TYPE_ERROR);
            $report->setMessage(__('%s is not a QTI test', $test->getLabel()));
            return $report;
        }

        // generate file content
        $tmpFolder = \tao_helpers_File::createTempDir();
        $tmpFile = $tmpFolder.'test.txt';
        $content = '';
        foreach (self::getItems($test) as $item) {
            $content .= self::renderItem($item);
        }
        file_put_contents($tmpFile, $content);

        // generate tao instance
        $instance = BookletClassService::singleton()->createBookletInstance($class, __('%s Booklet', $test->getLabel()), $tmpFile);

        \tao_helpers_File::delTree($tmpFolder);

        // return report with instance
        $report->setMessage(__('%s created', $instance->getLabel()));
        $report->setData($instance);
        return $report;
    }

    /**
     * Get items of test
     *
     * @param core_kernel_classes_Resource $test
     * @return array
     * @todo Analyse test content and determin items to use
     */
    static protected function getItems(core_kernel_classes_Resource $test) {

        $items = \taoTests_models_classes_TestsService::singleton()->getTestItems($test);
        return $items;
    }

    /**
     * Render a single item
     *
     * @param core_kernel_classes_Resource $item
     * @return string
     * @todo Real item rendering
     */
    static protected function renderItem(core_kernel_classes_Resource $item) {
        return 'Item '.$item->getLabel().PHP_EOL;
    }
}
