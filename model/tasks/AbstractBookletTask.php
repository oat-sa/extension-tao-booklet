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

namespace oat\taoBooklet\model\tasks;

use common_exception_MissingParameter;
use common_session_DefaultSession;
use common_session_SessionManager;
use core_kernel_classes_Resource;
use core_kernel_users_GenerisUser;
use JsonSerializable;
use oat\generis\model\OntologyAwareTrait;
use oat\oatbox\task\AbstractTaskAction;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoBooklet\model\BookletDataService;
use oat\taoBooklet\model\export\PdfBookletExporter;
use PHPSession;
use tao_helpers_File;
use tao_helpers_Uri;

/**
 * Class AbstractBookletTask
 * @package oat\taoBooklet\model\tasks
 */
abstract class AbstractBookletTask extends AbstractTaskAction implements JsonSerializable
{
    use OntologyAwareTrait;

    /**
     * The list of task parameters
     * @var array
     */
    protected $taskParams;

    /**
     * The related instance
     * @var core_kernel_classes_Resource
     */
    protected $instance;

    /**
     * Gets the config for a booklet instance using either the instance itself or an array of properties
     * @return mixed
     */
    abstract protected function getBookletConfig();

    /**
     * Gets the test definition data in order to print it
     * @return JsonSerializable|array
     * @throws \Exception
     */
    abstract protected function getTestData();

    /**
     * @param string $filePath
     * @return \common_report_Report
     */
    abstract protected function storePdf($filePath);

    /**
     *
     * @param array $params
     * @return \common_report_Report
     * @throws \common_exception_MissingParameter
     */
    public function __invoke($params)
    {
        $this->validateParams($params);
        $this->startCliSession($this->getParam('user'));

        $this->instance = $this->getResource($this->getParam('uri'));

        return $this->generatePdf();
    }

    /**
     * @return \common_report_Report
     */
    protected function generatePdf()
    {
        $config = $this->getBookletConfig();
        $storageKey = $this->cacheBookletData($this->getParam('uri'), [
            'testData' => $this->getTestData(),
            'config' => $config,
        ]);

        $tmpFolder = tao_helpers_File::createTempDir();
        $tmpFile = $tmpFolder . 'test.pdf';

        $exporter = new PdfBookletExporter($config[BookletConfigService::CONFIG_TITLE], $config);
        $exporter->setContent($this->getRendererUrl($storageKey));
        $exporter->saveAs($tmpFile);

        $report = $this->storePdf($tmpFile);

        tao_helpers_File::delTree($tmpFolder);
        $this->cleanBookletData($storageKey);

        return $report;
    }

    /**
     * Validates the parameters provided to the task
     * @param array $params
     * @throws common_exception_MissingParameter
     */
    protected function validateParams($params)
    {
        foreach ($this->getMandatoryParams() as $name) {
            if (!isset($params[$name])) {
                throw new common_exception_MissingParameter($name, self::class);
            }
        }

        $this->taskParams = $params;
    }

    /**
     * Gets a parameter of the task
     * @param string $name
     * @return mixed|null
     */
    protected function getParam($name)
    {
        if (isset($this->taskParams[$name])) {
            return $this->taskParams[$name];
        }
        return null;
    }

    /**
     * Gets the list of mandatory parameters
     * @return array
     */
    protected function getMandatoryParams()
    {
        return ['uri', 'user'];
    }

    /**
     * Gets the related instance
     * @return core_kernel_classes_Resource
     */
    protected function getInstance()
    {
        return $this->instance;
    }

    /**
     * Create a session for a particular user in CLI
     * @param string $userUri
     */
    protected function startCliSession($userUri)
    {
        if (PHP_SAPI == 'cli') {
            $user = new core_kernel_users_GenerisUser(new core_kernel_classes_Resource($userUri));
            $session = new common_session_DefaultSession($user);

            // force a session, cannot use the SessionManager as it does not allow session in CLI
            // the session is required by the PrintTest controller called to render the PDF
            session_name(GENERIS_SESSION_NAME);
            session_start();
            PHPSession::singleton()->setAttribute(common_session_SessionManager::PHPSESSION_SESSION_KEY, $session);

            common_session_SessionManager::startSession($session);
        }
    }

    /**
     * @param string $uri
     * @param array $data
     * @return string
     */
    protected function cacheBookletData($uri, $data)
    {
        $storageKey = uniqid(hash('crc32', $uri), true);
        $this->getServiceLocator()->get(BookletDataService::SERVICE_ID)->setData($storageKey, $data);
        return $storageKey;
    }

    /**
     * @param string $storageKey
     */
    protected function cleanBookletData($storageKey)
    {
        $this->getServiceLocator()->get(BookletDataService::SERVICE_ID)->cleanData($storageKey);
    }

    /**
     * Gets the URL to the renderer service
     * @param string $storageKey
     * @return string
     */
    protected function getRendererUrl($storageKey)
    {
        return tao_helpers_Uri::url('render', 'PrintTest', 'taoBooklet', ['token' => $storageKey]);
    }
}