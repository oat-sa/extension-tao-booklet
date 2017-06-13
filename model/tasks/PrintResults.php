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

use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoBooklet\model\StorageService;
use oat\taoDelivery\model\execution\DeliveryExecution;
use oat\taoOutcomeUi\helper\ResponseVariableFormatter;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoQtiItem\model\QtiJsonItemCompiler;
use oat\taoQtiTest\models\runner\config\QtiRunnerConfig;
use oat\taoQtiTest\models\runner\session\TestSession;
use oat\taoQtiTest\models\TestSessionService;
use oat\taoResultServer\models\classes\ResultServerService;
use qtism\data\View;
use qtism\runtime\tests\RouteItem;
use tao_helpers_Date;

/**
 * Class UpdateBooklet
 * @package oat\taoBooklet\model\tasks
 */
class PrintResults extends AbstractBookletTask
{
    /**
     * @var ResultsService
     */
    protected $resultsService;

    /**
     * @var BookletClassService
     */
    protected $bookletClassService;

    /**
     * @var DeliveryExecution
     */
    protected $deliveryExecution;

    /**
     * AbstractBookletTask constructor.
     */
    public function __construct()
    {
        $this->bookletClassService = BookletClassService::singleton();
        $this->resultsService = ResultsService::singleton();
    }

    /**
     *
     * @param array $params
     * @return \common_report_Report
     * @throws \common_exception_MissingParameter
     */
    public function __invoke($params)
    {
        // make sure the context is loaded
        $extensionManager = $this->getServiceLocator()->get('generis/extensionManager');
        $extensionManager->getExtensionById('taoDeliveryRdf');

        return parent::__invoke($params);
    }


    /**
     * Gets the list of mandatory parameters
     * @return array
     */
    protected function getMandatoryParams()
    {
        return ['id', 'uri', 'user', 'config'];
    }

    /**
     * @return ResultsService
     */
    protected function getResultsService()
    {
        return $this->resultsService;
    }

    /**
     * Gets the config for a booklet instance using either the instance itself or an array of properties
     * @return mixed
     */
    protected function getBookletConfig()
    {
        $configService = $this->getServiceLocator()->get(BookletConfigService::SERVICE_ID);
        $config = $configService->getConfig($this->getParam('config'));
        $config[BookletConfigService::CONFIG_REGULAR] = true;
        $config[BookletConfigService::CONFIG_DATE] = tao_helpers_Date::displayeDate($this->getDeliveryExecution()->getStartTime());
        return $config;
    }

    /**
     * @return array
     * @throws \Exception
     */
    protected function getTestData()
    {
        $resultServerService = $this->getServiceLocator()->get(ResultServerService::SERVICE_ID);
        $resultStorage = $resultServerService->getResultStorage($this->getParam('uri'));
        $this->getResultsService()->setImplementation($resultStorage);

        $resultId = $this->getParam('id');
        $deliveryExecution = $this->getDeliveryExecution();

        $testSessionService = $this->getServiceLocator()->get(TestSessionService::SERVICE_ID);
        /* @var \qtism\runtime\tests\AssessmentTestSession $testSession */
        $testSession = $testSessionService->getTestSession($deliveryExecution);
        $inputParameters = $testSessionService->getRuntimeInputParameters($deliveryExecution);
        $fileStorage = \tao_models_classes_service_FileStorage::singleton();
        $directoryIds = explode('|', $inputParameters['QtiTestCompilation']);
        $compilationDirs = array(
            'private' => $fileStorage->getDirectoryById($directoryIds[0]),
            'public' => $fileStorage->getDirectoryById($directoryIds[1])
        );
        $assessmentTest = $testSession->getAssessmentTest();
        $route = $testSession->getRoute();
        $routeItems = $route->getAllRouteItems();
        $lastPart = null;
        $lastSection = null;
        $testData = [
            'type' => 'qtiprint',
            'data' => [
                'id' => $assessmentTest->getIdentifier(),
                'uri' => $resultId,
                'title' => $assessmentTest->getTitle(),
                'testParts' => [],
            ],
            'items' => [],
            'variables' => $this->getResultVariables($resultId),
        ];

        $config = $this->getServiceLocator()->get(QtiRunnerConfig::SERVICE_ID);
        $reviewConfig = $config->getConfigValue('review');
        $displaySubsectionTitle = isset($reviewConfig['displaySubsectionTitle']) ? (bool)$reviewConfig['displaySubsectionTitle'] : true;

        foreach ($routeItems as $routeItem) {
            $itemRef = $routeItem->getAssessmentItemRef();
            $testPart = $routeItem->getTestPart();
            $partId = $testPart->getIdentifier();

            if ($displaySubsectionTitle) {
                $section = $routeItem->getAssessmentSection();
            } else {
                $sections = $routeItem->getAssessmentSections()->getArrayCopy();
                $section = $sections[0];
            }
            $sectionId = $section->getIdentifier();
            $itemId = $itemRef->getIdentifier();
            $itemUri = strstr($itemRef->getHref(), '|', true);

            if ($lastPart != $partId) {
                $lastPart = $partId;
            }
            if ($lastSection != $sectionId) {
                $lastSection = $sectionId;
            }

            if (!isset($testData['data']['testParts'][$partId])) {
                $testData['data']['testParts'][$partId] = [
                    'id' => $partId,
                    'sections' => [],
                ];
            }
            if (!isset($testData['data']['testParts'][$partId]['sections'][$sectionId])) {
                $testData['data']['testParts'][$partId]['sections'][$sectionId] = [
                    'id' => $sectionId,
                    'title' => $section->getTitle(),
                    'rubricBlock' => $this->getRubricBlock($routeItem, $testSession, $compilationDirs),
                    'items' => [],
                ];
            }

            $testData['data']['testParts'][$partId]['sections'][$sectionId]['items'][] = [
                'id' => $itemId,
                'href' => $itemUri,
            ];

            $testData['items'][$itemUri] = $this->getItemData($itemRef->getHref());
        }

        return $testData;
    }

    /**
     * @param string $filePath
     * @return \common_report_Report
     */
    protected function storePdf($filePath)
    {
        $report = new \common_report_Report(\common_report_Report::TYPE_SUCCESS);

        $storageService = $this->getServiceLocator()->get(StorageService::SERVICE_ID);
        $fileResource = $storageService->storeFile($filePath);

        $report->setMessage(__('%s rendered', $this->getInstance()->getLabel()));
        $report->setData($fileResource);
        return $report;
    }

    /**
     * @return DeliveryExecution
     */
    protected function getDeliveryExecution()
    {
        if (!$this->deliveryExecution) {
            $this->deliveryExecution = \taoDelivery_models_classes_execution_ServiceProxy::singleton()->getDeliveryExecution($this->getParam('id'));
        }
        return $this->deliveryExecution;
    }

    /**
     * Extracts the result variables, with respect to the user's filter, and inject item states to allow preview with results
     * @return array
     */
    protected function getResultVariables()
    {
        $resultId = $this->getParam('id');
        $displayedVariables = $this->getResultsService()->getStructuredVariables($resultId, 'lastSubmitted', [\taoResultServer_models_classes_ResponseVariable::class]);
        $responses = ResponseVariableFormatter::formatStructuredVariablesToItemState($displayedVariables);
        $excludedVariables = array_flip(['numAttempts', 'duration']);

        foreach ($displayedVariables as &$item) {
            if (!isset($item['uri'])) {
                continue;
            }
            $itemUri = $item['uri'];
            if (isset($responses[$itemUri])) {
                $item['state'] = json_encode(array_diff_key($responses[$itemUri], $excludedVariables));
            } else {
                $item['state'] = null;
            }
        }

        return $displayedVariables;
    }

    /**
     * Gets the definition of an item
     * @param string $itemRef
     * @return array
     * @throws \common_Exception
     * @throws \common_exception_InconsistentData
     * @throws \tao_models_classes_FileNotFoundException
     */
    protected function getItemData($itemRef)
    {
        $path = QtiJsonItemCompiler::ITEM_FILE_NAME;
        $directoryIds = explode('|', $itemRef);
        if (count($directoryIds) < 3) {
            throw new \common_exception_InconsistentData('The itemRef is not formatted correctly');
        }

        $itemUri = $directoryIds[0];
        $userDataLang = \common_session_SessionManager::getSession()->getDataLanguage();
        $directory = \tao_models_classes_service_FileStorage::singleton()->getDirectoryById($directoryIds[2]);

        if ($directory->has($userDataLang)) {
            $lang = $userDataLang;
        } elseif ($directory->has(DEFAULT_LANG)) {
            \common_Logger::i(
                $userDataLang . ' is not part of compilation directory for item : ' . $itemUri . ' use ' . DEFAULT_LANG
            );
            $lang = DEFAULT_LANG;
        } else {
            throw new \common_Exception(
                'item : ' . $itemUri . 'is neither compiled in ' . $userDataLang . ' nor in ' . DEFAULT_LANG
            );
        }
        try {
            return json_decode($directory->read($lang . DIRECTORY_SEPARATOR . $path), true);
        } catch (\FileNotFoundException $e) {
            throw new \tao_models_classes_FileNotFoundException(
                $path . ' for item reference ' . $itemRef
            );
        }
    }

    /**
     * @param RouteItem $routeItem
     * @param TestSession $session
     * @param array $compilationDirs
     * @return array
     */
    protected function getRubricBlock($routeItem, $session, $compilationDirs)
    {
        $rubrics = [];

        if ($routeItem) {

            $rubricRefs = $routeItem->getRubricBlockRefs();

            if (count($rubricRefs) > 0) {

                // -- variables used in the included rubric block templates.
                // base path (base URI to be used for resource inclusion).
                $basePathVarName = TAOQTITEST_BASE_PATH_NAME;
                $$basePathVarName = $compilationDirs['public']->getPublicAccessUrl();

                // state name (the variable to access to get the state of the assessmentTestSession).
                $stateName = TAOQTITEST_RENDERING_STATE_NAME;
                $$stateName = $session;

                // views name (the variable to be accessed for the visibility of rubric blocks).
                $viewsName = TAOQTITEST_VIEWS_NAME;
                $$viewsName = array(View::CANDIDATE);

                $tmpDir = \tao_helpers_File::createTempDir();
                foreach ($rubricRefs as $rubric) {
                    $data = $compilationDirs['private']->read($rubric->getHref());
                    $tmpFile = $tmpDir . basename($rubric->getHref());
                    file_put_contents($tmpFile, $data);
                    ob_start();
                    include($tmpFile);
                    $rubrics[] = ob_get_clean();
                    unlink($tmpFile);
                }
                rmdir($tmpDir);
            }
        }
        return $rubrics;
    }

    /**
     * @return string
     */
    public function jsonSerialize()
    {
        return __CLASS__;
    }
}
