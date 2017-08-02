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
use \Jurosh\PDFMerge\PDFMerger;

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
     * Gets the config for a booklet instance using either the instance itself or an array of properties
     * @param core_kernel_classes_Resource $instance
     * @return mixed
     */
    abstract protected function getBookletConfig($instance);

    /**
     * Gets the test definition data in order to print it
     * @param core_kernel_classes_Resource $instance
     * @return JsonSerializable|array
     * @throws \Exception
     */
    abstract protected function getTestData($instance);

    /**
     * Stores the generated PDF file
     * @param core_kernel_classes_Resource $instance
     * @param string $filePath
     * @return \common_report_Report
     */
    abstract protected function storePdf($instance, $filePath);

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

        return $this->generatePdf();
    }

    /**
     * @return \common_report_Report
     */
    protected function generatePdf()
    {
        $uri = $this->getParam('uri');
        $root = $this->getParam('root');

        if (is_array($uri)) {
            $list = array_values($uri);
        } else {
            $list = [$uri];
        }

        if (!$root) {
            $root = $list[0];
        }

        $rootInstance = $this->getResource($root);
        $tmpFolder = tao_helpers_File::createTempDir();
        $pdfFiles = [];

        foreach ($list as $idx => $uri) {
            $instance = $this->getResource($uri);

            $config = $this->getBookletConfig($instance);
            $storageKey = $this->cacheBookletData($uri, [
                'testData' => $this->getTestData($instance),
                'config' => $config,
            ]);

            $tmpFile = "${tmpFolder}${idx}-booklet.pdf";
            $pdfFiles[] = $tmpFile;

            $exporter = new PdfBookletExporter($config[BookletConfigService::CONFIG_TITLE], $config);
            $exporter->setContent($this->getRendererUrl($storageKey));
            $exporter->saveAs($tmpFile);

            $this->cleanBookletData($storageKey);
        }

        if (count($pdfFiles) == 1) {
            $report = $this->storePdf($rootInstance, $pdfFiles[0]);
        } else {
            $tmpFile = "${tmpFolder}booklet.pdf";
            $pdf = new PDFMerger();

            foreach($pdfFiles as $pdfFile) {
                $pdf->addPDF($pdfFile, 'all');
            }
            $pdf->merge('file', $tmpFile);

            $report = $this->storePdf($rootInstance, $tmpFile);
        }

        tao_helpers_File::delTree($tmpFolder);

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
