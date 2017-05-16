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

use core_kernel_classes_Resource;
use oat\taoBooklet\form\WizardPrintForm;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoBooklet\model\export\PdfBookletExporter;
use oat\taoOutcomeUi\helper\ResponseVariableFormatter;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoQtiItem\model\QtiJsonItemCompiler;
use oat\taoQtiTest\models\runner\config\QtiRunnerConfig;
use oat\taoQtiTest\models\TestSessionService;
use oat\taoResultServer\models\classes\ResultServerService;
use tao_actions_SaSModule;
use tao_helpers_Date;
use tao_helpers_File;
use tao_helpers_Uri;

/**
 * Class Results
 * @package oat\taoBooklet\controller
 */
class Results extends tao_actions_SaSModule
{
    /**
     * @var ResultsService
     */
    private $resultsService;

    /**
     * Results constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->service = BookletClassService::singleton();
        $this->resultsService = ResultsService::singleton();

        $this->defaultData();
    }

    /**
     * @return \tao_models_classes_ClassService
     */
    protected function getClassService()
    {
        return $this->service;
    }

    /**
     * @return ResultsService
     */
    protected function getResultsService()
    {
        return $this->resultsService;
    }

    /**
     * Setup the print of the results
     */
    public function printWizard()
    {
        $resultId = tao_helpers_Uri::decode($this->getRequestParameter('id'));
        $deliveryUri = tao_helpers_Uri::decode($this->getRequestParameter('uri'));
        $bookletClass = $this->getRootClass();

        $delivery = new core_kernel_classes_Resource($deliveryUri);
        $formContainer = new WizardPrintForm($bookletClass, $delivery);
        $form = $formContainer->getForm();

        $this->getResultStorage($delivery);
        $testTaker = $this->getTestTakerData($resultId);

        if ($form->isValid() && $form->isSubmited()) {
            $values = $form->getValues();
            $this->generatePdf($resultId, $deliveryUri, $values);
        } else {
            $form->getElement(tao_helpers_Uri::encode(RDFS_LABEL))->setValue($delivery->getLabel());
            $form->getElement('id')->setValue(tao_helpers_Uri::encode($resultId));
            $form->getElement(tao_helpers_Uri::encode(BookletClassService::PROPERTY_DESCRIPTION))->setValue($testTaker['userLabel']);

            $this->getServiceManager()->get(BookletConfigService::SERVICE_ID)->setDefaultFormValues($form);

            $this->setData('myForm', $form->render());
            $this->setData('formTitle', __('Print the results'));
            $this->setView('form.tpl', 'tao');
        }
    }

    /**
     * Prints the results of the test
     */
    public function printResults()
    {
        $resultId = tao_helpers_Uri::decode($this->getRequestParameter('id'));
        $deliveryUri = tao_helpers_Uri::decode($this->getRequestParameter('uri'));

        try {
            $delivery = new core_kernel_classes_Resource($deliveryUri);
            $this->getResultStorage($delivery);

            $testData = $this->getTestData($resultId);
            $testData['variables'] = $this->getResultVariables($resultId);

            $config = json_decode(base64_decode($this->getRequestParameter('config')), true);
            $config[BookletConfigService::CONFIG_REGULAR] = true;
            $config[BookletConfigService::CONFIG_DATE] = $testData['date'];

            $this->setData('client_config_url', $this->getClientConfigUrl());
            $this->setData('testData', json_encode($testData));
            $this->setData('options', json_encode($config));
            $this->setView('PrintTest/render.tpl');
        } catch (\common_exception_Error $e) {
            $this->setData('type', 'error');
            $this->setData('error', $e->getMessage());
            $this->setView('index.tpl');
        }
    }

    /**
     * Gets the data of a particular test taker
     * @param string $resultId
     * @return array
     */
    protected function getTestTakerData($resultId)
    {
        $testTaker = $this->getResultsService()->getTestTakerData($resultId);

        if ((is_object($testTaker) && (get_class($testTaker) == 'core_kernel_classes_Literal')) || (is_null($testTaker))) {
            //the test taker is unknown
            $login = $testTaker;
            $label = $testTaker;
            $firstName = $testTaker;
            $userLastName = $testTaker;
            $userEmail = $testTaker;
        } else {
            $login = (count($testTaker[PROPERTY_USER_LOGIN]) > 0) ? current(
                $testTaker[PROPERTY_USER_LOGIN]
            )->literal : "";
            $label = (count($testTaker[RDFS_LABEL]) > 0) ? current($testTaker[RDFS_LABEL])->literal : "";
            $firstName = (count($testTaker[PROPERTY_USER_FIRSTNAME]) > 0) ? current(
                $testTaker[PROPERTY_USER_FIRSTNAME]
            )->literal : "";
            $userLastName = (count($testTaker[PROPERTY_USER_LASTNAME]) > 0) ? current(
                $testTaker[PROPERTY_USER_LASTNAME]
            )->literal : "";
            $userEmail = (count($testTaker[PROPERTY_USER_MAIL]) > 0) ? current(
                $testTaker[PROPERTY_USER_MAIL]
            )->literal : "";
        }

        return [
            'userLogin' => $login,
            'userLabel' => $label,
            'userFirstName' => $firstName,
            'userLastName' => $userLastName,
            'userEmail' => $userEmail,
        ];
    }

    /**
     * Returns the currently configured result storage
     *
     * @param \core_kernel_classes_Resource $delivery
     * @return \taoResultServer_models_classes_ReadableResultStorage
     */
    protected function getResultStorage($delivery)
    {
        $resultServerService = $this->getServiceManager()->get(ResultServerService::SERVICE_ID);
        $resultStorage = $resultServerService->getResultStorage($delivery->getUri());
        $this->getResultsService()->setImplementation($resultStorage);
        return $resultStorage;
    }

    /**
     * Extracts the result variables, with respect to the user's filter, and inject item states to allow preview with results
     *
     * @param string $resultId
     * @return array
     */
    protected function getResultVariables($resultId)
    {
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
     * Builds the test map related to the executed delivery
     * @param string $resultId
     * @return array
     */
    protected function getTestData($resultId)
    {
        $deliveryExecution = \taoDelivery_models_classes_execution_ServiceProxy::singleton()->getDeliveryExecution($resultId);

        $testSessionService = $this->getServiceManager()->get(TestSessionService::SERVICE_ID);
        /* @var \qtism\runtime\tests\AssessmentTestSession $testSession */
        $testSession = $testSessionService->getTestSession($deliveryExecution);
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
            'date' => tao_helpers_Date::displayeDate($deliveryExecution->getStartTime()),
        ];

        $config = $this->getServiceManager()->get(QtiRunnerConfig::SERVICE_ID);
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
                    'rubricBlock' => $this->getRubricBlock($routeItem, $testSession),
                    'items' => [],
                ];
            }

            $testData['data']['testParts'][$partId]['sections'][$sectionId]['items'][] = [
                'id' => $itemId,
                'uri' => $itemUri,
            ];

            $testData['items'][$itemUri] = $this->getItemData($itemRef->getHref());
        }

        return $testData;
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
            throw new \common_exception_InconsistentData('The itemRef is not formated correctly');
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
     * @param $routeItem
     * @param $session
     * @return string
     */
    protected function getRubricBlock($routeItem, $session)
    {
        $rubrics = '';

        if ($routeItem) {

            $rubricRefs = $routeItem->getRubricBlockRefs();

            if (count($rubricRefs) > 0) {

                $compilationDirs = $context->getCompilationDirectory();

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

                ob_start();
                foreach ($routeItem->getRubricBlockRefs() as $rubric) {
                    $data = $compilationDirs['private']->read($rubric->getHref());
                    $tmpFile = \tao_helpers_File::createTempDir() . basename($rubric->getHref());
                    file_put_contents($tmpFile, $data);
                    include($tmpFile);
                    unlink($tmpFile);
                }
                $rubrics = ob_get_contents();
                ob_end_clean();
            }
        }
        return $rubrics;
    }

    /**
     * @param string $resultId
     * @param string $deliveryUri
     * @param array $values
     */
    protected function generatePdf($resultId, $deliveryUri, $values)
    {
        $configService = $this->getServiceManager()->get(BookletConfigService::SERVICE_ID);
        $config = $configService->getConfig($values);

        $tmpFolder = tao_helpers_File::createTempDir();
        $tmpFile = $tmpFolder . 'results.pdf';
        $url = tao_helpers_Uri::url('printResults', 'Results', 'taoBooklet', array(
            'id' => tao_helpers_Uri::encode($resultId),
            'uri' => tao_helpers_Uri::encode($deliveryUri),
            'config' => base64_encode(json_encode($config)),
            'force' => true
        ));

        $title = $values[RDFS_LABEL];
        $exporter = new PdfBookletExporter($title, $config);
        $exporter->setContent($url);
        $exporter->saveAs($tmpFile);

        header('Set-Cookie: fileDownload=true');
        setcookie("fileDownload", "true", 0, "/");
        header("Content-type: application/pdf");
        header('Content-Disposition: attachment; filename=' . $title . '.pdf');
        readfile($tmpFile);

        tao_helpers_File::delTree($tmpFolder);
    }
}