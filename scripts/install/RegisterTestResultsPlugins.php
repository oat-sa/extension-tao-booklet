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

namespace oat\taoBooklet\scripts\install;

use common_report_Report as Report;
use oat\oatbox\extension\InstallAction;
use oat\taoOutcomeUi\model\plugins\ResultsPluginService;

/**
 * Installation action that registers the test runner plugins
 *
 * @author Bertrand Chevrier <bertrand@taotesting.com>
 */
class RegisterTestResultsPlugins extends InstallAction
{

    public static $plugins = [
        'action' => [
            [
                'id' => 'print',
                'name' => 'Print results',
                'module' => 'taoBooklet/plugins/printResults',
                'description' => 'Print the test results',
                'category' => 'action',
                'active' => true,
                'tags' => [ 'print', 'action' ]
            ]
        ],
        'tool' => [
            [
                'id' => 'taskQueue',
                'name' => 'Results tasks queue',
                'module' => 'taoBooklet/plugins/taskQueue/taskQueue',
                'description' => 'List the print tasks',
                'category' => 'tool',
                'active' => true,
                'tags' => [ 'print', 'tool' ]
            ]
        ]
    ];

    public function __invoke($params)
    {
        $pluginService = $this->getServiceLocator()->get(ResultsPluginService::SERVICE_ID);
        $count = $pluginService->registerPluginsByCategories(self::$plugins);
        return new Report(Report::TYPE_SUCCESS, $count .  ' plugins registered.');
    }
}
