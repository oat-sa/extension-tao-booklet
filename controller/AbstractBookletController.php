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
use oat\oatbox\filesystem\File;
use oat\oatbox\task\Task;
use oat\tao\model\TaskQueueActionTrait;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\StorageService;
use tao_actions_SaSModule;

abstract class AbstractBookletController extends tao_actions_SaSModule
{
    use TaskQueueActionTrait;
    use OntologyAwareTrait;

    /**
     * @param Task $task
     * @return common_report_Report
     */
    protected function getTaskReport(Task $task)
    {
        $status = $task->getStatus();
        if ($status === Task::STATUS_FINISHED || $status === Task::STATUS_ARCHIVED) {
            $report = $task->getReport();
        } else {
            $report = common_report_Report::createInfo(__('Booklet task created'));
        }
        return $report;
    }

    /**
     * Sets the headers to download a file
     * @param string $fileName
     * @param string $mimeType
     */
    protected function prepareDownload($fileName, $mimeType)
    {
        //used by jquery file download to find out the download has been triggered ...
        header('Set-Cookie: fileDownload=true');
        setcookie('fileDownload', 'true', 0, '/');

        //file meta
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Content-Type: ' . $mimeType);
    }

    /**
     * Extracts the path of the file attached to a report
     * @param common_report_Report $report
     * @return mixed|null
     */
    protected function getReportAttachment(common_report_Report $report)
    {
        $filename = null;
        /** @var common_report_Report $success */
        foreach ($report->getSuccesses() as $success) {
            if (!is_null($filename = $success->getData())) {
                if (is_array($filename)) {
                    $filename = $filename['uriResource'];
                }
                break;
            }
        }
        return $filename;
    }

    /**
     * Gets file from URI
     * @param string $fileUri
     * @return File
     */
    protected function getFile($fileUri)
    {
        /* @var StorageService $storageService */
        $storageService = $this->getServiceLocator()->get(StorageService::SERVICE_ID);
        $fileResource = $this->getResource($fileUri);
        return $storageService->getFile($fileResource);
    }

    /**
     * @return BookletClassService
     */
    protected function getClassService()
    {
        if (is_null($this->service)) {
            $this->service = BookletClassService::singleton();
        }
        return $this->service;
    }
}