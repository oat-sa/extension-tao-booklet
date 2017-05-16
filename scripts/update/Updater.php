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
use oat\tao\scripts\update\OntologyUpdater;
use oat\taoBooklet\scripts\install\RegisterTestResultsPlugins;
use oat\taoBooklet\scripts\install\SetupBookletConfigService;

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

        if ($this->isVersion('1.1.0')) {

            $this->runExtensionScript(RegisterTestResultsPlugins::class);

            $this->setVersion('1.2.0');
        }
    }
}
