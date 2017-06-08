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
use oat\taoBooklet\model\BookletTaskService;
use oat\taoOutcomeUi\model\ResultsService;
use oat\taoResultServer\models\classes\ResultServerService;
use tao_helpers_Uri;

/**
 * Class Results
 * @package oat\taoBooklet\controller
 */
class Results extends AbstractBookletController
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
        $this->resultsService = ResultsService::singleton();
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

            $task = $this->getServiceManager()->get(BookletTaskService::SERVICE_ID)->createPrintResultsTask($delivery, $resultId, $values);

            $report = $this->getTaskReport($task);

            $this->returnReport($report);
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
}