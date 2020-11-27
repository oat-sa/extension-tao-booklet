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
namespace oat\taoBooklet\scripts\update;

use oat\tao\helpers\Template;
use oat\tao\model\accessControl\func\AccessRule;
use oat\tao\model\accessControl\func\AclProxy;
use oat\tao\model\user\TaoRoles;
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoBooklet\model\BookletDataService;
use oat\taoBooklet\model\BookletListenerService;
use oat\taoBooklet\model\StorageService;
use oat\taoBooklet\model\BookletTaskService;
use oat\taoBooklet\scripts\install\RegisterTestResultsPlugins;
use oat\taoBooklet\scripts\install\SetupBookletConfigService;
use oat\taoBooklet\scripts\install\SetupEventListeners;
use oat\taoBooklet\scripts\install\SetupStorage;

/**
 *
 * @author Joel Bout <joel@taotesting.com>
 */
class Updater extends \common_ext_ExtensionUpdater {

    /**
     *
     * @param string $initialVersion
     * @return string $versionUpdatedTo
     */
    public function update($initialVersion) {

        $this->skip('0.1','0.3.0');

        if ($this->isVersion('0.3.0')) {

            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoBooklet');
            $config = $extension->getConfig('wkhtmltopdf');
            $config['options'] = array_merge($config['options'], [
                'page-size' => 'A4',
                'orientation' => 'Portrait',
            ]);

            $extension->setConfig('wkhtmltopdf', $config);

            $this->setVersion('0.4.0');
        }

        $this->skip('0.4.0', '1.0.0');

        if ($this->isVersion('1.0.0')) {

            OntologyUpdater::syncModels();

            $this->runExtensionScript(SetupBookletConfigService::class);

            $extension = \common_ext_ExtensionsManager::singleton()->getExtensionById('taoBooklet');
            $config = $extension->getConfig('wkhtmltopdf');
            $config['options'] = array_merge($config['options'], [
                'header-html' => Template::getTemplate('PrintTest' . DIRECTORY_SEPARATOR . 'header.html', 'taoBooklet'),
            ]);

            $extension->setConfig('wkhtmltopdf', $config);

            $this->setVersion('1.1.0');
        }

        $this->skip('1.1.0', '1.2.1');

        if ($this->isVersion('1.2.1')) {

            $storageService = new StorageService();
            $this->getServiceManager()->propagate($storageService);
            $this->getServiceManager()->register(StorageService::SERVICE_ID, $storageService);

            $this->runExtensionScript(SetupStorage::class);
            $this->setVersion('1.3.0');
        }

        if ($this->isVersion('1.3.0')) {

            $bookletDataService = new BookletDataService();
            $this->getServiceManager()->propagate($bookletDataService);
            $this->getServiceManager()->register(BookletDataService::SERVICE_ID, $bookletDataService);

            AclProxy::applyRule(new AccessRule(
                AccessRule::GRANT,
                TaoRoles::ANONYMOUS,
                ['ext'=>'taoBooklet', 'mod' => 'PrintTest', 'act' => 'render']
            ));

            $this->setVersion('1.4.0');
        }

        if ($this->isVersion('1.4.0')) {

            $bookletDataService = new BookletDataService();
            $this->getServiceManager()->propagate($bookletDataService);
            $this->getServiceManager()->register(BookletDataService::SERVICE_ID, $bookletDataService);

            $this->setVersion('1.4.1');
        }

        $this->skip('1.4.1', '1.4.3');

        if ($this->isVersion('1.4.3')) {

            $bookletListenerService = new BookletListenerService();
            $this->getServiceManager()->propagate($bookletListenerService);
            $this->getServiceManager()->register(BookletListenerService::SERVICE_ID, $bookletListenerService);

            $this->runExtensionScript(RegisterTestResultsPlugins::class);
            $this->runExtensionScript(SetupEventListeners::class);

            $bookletTaskService = new BookletTaskService();
            $this->getServiceManager()->propagate($bookletTaskService);
            $this->getServiceManager()->register(BookletTaskService::SERVICE_ID, $bookletTaskService);

            $this->setVersion('1.5.0');
        }

        $this->skip('1.5.0', '1.5.2');

        if ($this->isVersion('1.5.2')) {

            OntologyUpdater::syncModels();

            $this->setVersion('1.6.0');
        }

        if ($this->isVersion('1.6.0')) {

            OntologyUpdater::syncModels();

            $this->setVersion('1.7.0');
        }


        if ($this->isVersion('1.7.0')) {

            OntologyUpdater::syncModels();

            $this->setVersion('1.8.0');
        }


        $this->skip('1.8.0', '1.9.0');

        if ($this->isVersion('1.9.0')) {

            OntologyUpdater::syncModels();

            $this->setVersion('1.9.1');
        }

        $this->skip('1.9.1', '1.11.0');

        if ($this->isVersion('1.11.0')) {

            OntologyUpdater::syncModels();

            $this->setVersion('1.12.0');
        }

        $this->skip('1.12.0', '2.1.2');
    }
}
