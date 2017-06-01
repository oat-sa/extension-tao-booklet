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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */


/**
 * Controller to generate html(print-ready) version of tests
 *
 * @author Mikhail Kamarouski, <Komarouski@1pt.com>
 * @package taoBooklet
 */

namespace oat\taoBooklet\controller;

use common_ext_ExtensionsManager;
use oat\taoBooklet\model\BookletDataService;
use tao_actions_CommonModule;

/**
 * Class PrintTest
 * @package oat\taoBooklet\controller
 */
class PrintTest extends tao_actions_CommonModule
{
    /**
     * Generate html(print-ready) version of tests
     */
    public function render()
    {
        session_write_close();

        $storageKey = $this->getRequestParameter('token');
        $storageService = $this->getServiceManager()->get(BookletDataService::SERVICE_ID);
        $bookletData = $storageService->getData($storageKey);

        if (!$bookletData) {
            $bookletData = [
                'testData' => null
            ];
        }

        $config = common_ext_ExtensionsManager::singleton()->getExtensionById('taoBooklet')->getConfig('rendering');
        if (isset($bookletData['config'])) {
            $config = array_merge($config, $bookletData['config']);
        }

        $this->setData('client_config_url', $this->getClientConfigUrl());
        $this->setData('testData', json_encode($bookletData['testData']));
        $this->setData('options', json_encode($config));
        $this->setView('PrintTest/render.tpl');
    }
}
