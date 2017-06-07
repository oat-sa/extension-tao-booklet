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
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\task\Task;
use oat\taoBooklet\model\BookletClassService;
use tao_actions_SaSModule;

abstract class AbstractBookletController extends tao_actions_SaSModule
{
    use OntologyAwareTrait;

    /**
     * Results constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = BookletClassService::singleton();

        $this->defaultData();
    }

    /**
     * @param Task $task
     * @return common_report_Report
     */
    protected function getTaskReport($task)
    {
        $status = $task->getStatus();
        if ($status === Task::STATUS_FINISHED || $status === Task::STATUS_ARCHIVED) {
            $report = $task->getReport();
        } else {
            $report = common_report_Report::createInfo(__('Booklet task created'));
        }
        return $report;
    }
}