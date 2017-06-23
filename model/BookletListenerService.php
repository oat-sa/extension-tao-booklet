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
namespace oat\taoBooklet\model;

use oat\oatbox\service\ConfigurableService;
use oat\tao\model\plugins\PluginModule;
use oat\taoOutcomeUi\model\event\ResultsListPluginEvent;

/**
 * Class BookletListenerService
 * @package oat\taoBooklet\model
 */
class BookletListenerService extends ConfigurableService
{
    const SERVICE_ID = 'taoBooklet/bookletListenerService';

    /**
     * @param ResultsListPluginEvent $event
     */
    public function resultsListPlugins(ResultsListPluginEvent $event)
    {
        /* @var PluginModule $plugin */
        foreach ($event->getPlugins() as $plugin) {
            if ($plugin->getId() == 'taskQueue') {
                $plugin->setActive($this->getServiceLocator()->get(BookletTaskService::SERVICE_ID)->isAsyncQueue());
            }
        }
    }
}
