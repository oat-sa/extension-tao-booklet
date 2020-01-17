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

namespace oat\taoBooklet\scripts\install;

use oat\taoBooklet\model\StorageService;
use oat\oatbox\extension\InstallAction;
use oat\oatbox\filesystem\FileSystemService;

/**
 * Class setupStorage
 *
 * @author Gyula Szucs, <gyula@taotesting.com>
 */
class SetupStorage extends InstallAction
{
    /**
     * @param array $params
     * @return \common_report_Report
     */
    public function __invoke($params)
    {
        $fsService = $this->getServiceLocator()->get(FileSystemService::SERVICE_ID);
        if (!$fsService->hasDirectory(StorageService::FILE_SYSTEM_ID)) {
            $fsService->createFileSystem(StorageService::FILE_SYSTEM_ID, 'taoBooklet');
            $this->registerService(FileSystemService::SERVICE_ID, $fsService);
        }

        return new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Booklet storage registered.');
    }
}
