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
namespace oat\taoBooklet\controller;

use common_report_Report;
use core_kernel_classes_Class;
use core_kernel_classes_Property;
use core_kernel_classes_Resource;
use core_kernel_versioning_File;
use oat\oatbox\task\Task;
use oat\taoBooklet\form\EditForm;
use oat\taoBooklet\form\GenerateForm;
use oat\taoBooklet\form\WizardForm;
use oat\taoBooklet\form\WizardTestForm;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoBooklet\model\StorageService;
use oat\taoBooklet\model\tasks\CreateBooklet;
use oat\taoBooklet\model\tasks\UpdateBooklet;
use oat\taoDeliveryRdf\model\NoTestsException;
use tao_actions_SaSModule;
use tao_helpers_Uri;

/**
 * Controller to managed assembled deliveries
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @package taoDelivery
 */
class Booklet extends tao_actions_SaSModule
{
    public function __construct()
    {
        $this->service = BookletClassService::singleton();
    }

    /**
     * (non-PHPdoc)
     * @see tao_actions_SaSModule::getClassService()
     */
    protected function getClassService()
    {
        return BookletClassService::singleton();
    }

    /**
     * Main action
     *
     * @access public
     * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
     * @return void
     */
    public function index()
    {
        $this->setView( 'index.tpl' );
    }

    /**
     * Edit action
     * @throws \tao_models_classes_MissingRequestParameterException
     * @throws \tao_models_classes_dataBinding_GenerisFormDataBindingException
     */
    public function editBooklet()
    {
        $clazz           = $this->getCurrentClass();
        $instance        = $this->getCurrentInstance();
        $myFormContainer = new EditForm( $clazz, $instance );

        $myForm = $myFormContainer->getForm();

        $fileResource = $instance->getOnePropertyValue(
            new core_kernel_classes_Property( BookletClassService::PROPERTY_FILE_CONTENT )
        );

        $myFormContainer->setAllowDownload( $fileResource instanceof core_kernel_classes_Resource );

        if ($myForm->isSubmited() && $myForm->isValid()) {
            $values = $myForm->getValues();
            // save properties
            $binder = new \tao_models_classes_dataBinding_GenerisFormDataBinder( $instance );
            $binder->bind( $values );

            $this->setData( 'message', __( 'Booklet saved' ) );
            $this->setData( 'reload', true );
        }

        $this->setData( 'formTitle', __( 'Edit Booklet' ) );
        $this->setData( 'myForm', $myForm->render() );
        $this->setData( 'queueId', $instance->getUri() );
        $this->setView( 'Booklet/edit.tpl' );
    }

    public function preview(){
        $configService = $this->getServiceManager()->get(BookletConfigService::SERVICE_ID);
        $instance = $this->getCurrentInstance();
        $test     = $this->getClassService()->getTest( $instance );
        $config   = $configService->getConfig($instance);

        if(is_null($test)){
            throw new \common_Exception('No test linked to the booklet');
        }
        $url = tao_helpers_Uri::url( 'render', 'PrintTest', 'taoBooklet', array(
            'uri' => $test->getUri(),
            'config' => base64_encode(json_encode($config)),
        ) );

        $this->setData( 'renderUrl', $url);
        $this->setView( 'Booklet/preview.tpl');
    }

    /**
     * Used for regeneration of attached pdf
     * @throws \core_kernel_persistence_Exception
     * @throws \tao_models_classes_MissingRequestParameterException
     */
    public function regenerate()
    {
        $instance  = $this->getCurrentInstance();

        $task = UpdateBooklet::createTask($instance);

        $report = $this->getTaskReport($task);

        $this->returnReport( $report );
    }

    /**
     * Invokes download of pregenerated delivery
     * @throws \common_exception_Error
     * @throws \tao_models_classes_MissingRequestParameterException
     */
    public function download()
    {
        $instance = $this->getCurrentInstance();

        $fileResource = $instance->getOnePropertyValue(
            new core_kernel_classes_Property( BookletClassService::PROPERTY_FILE_CONTENT )
        );

        if ($fileResource instanceof core_kernel_classes_Resource) {
            $file = new core_kernel_versioning_File( $fileResource );

            header( 'Content-Disposition: attachment; filename="' . basename( $file->getAbsolutePath() ) . '"' );
            \tao_helpers_Http::returnFile( $file->getAbsolutePath() );
        }

    }

    /**
     * Overloaded delete, also takes care about attached files
     * @throws \Exception
     * @throws \tao_models_classes_MissingRequestParameterException
     */
    public function delete()
    {
        if ($this->hasRequestParameter( 'uri' )) {
            $instance = $this->getCurrentInstance();
            StorageService::removeAttachedFile( $instance );
        }
        parent::delete();
    }

    /**
     * Creates new instance of booklet
     * @throws \tao_models_classes_dataBinding_GenerisFormDataBindingException
     */
    public function wizard()
    {
        $this->defaultData();
        try {
            $bookletClass = $this->getCurrentClass();
            $formContainer = new WizardForm( $bookletClass );
            $myForm        = $formContainer->getForm();

            if ($myForm->isValid() && $myForm->isSubmited()) {

                $test     = new core_kernel_classes_Resource( $myForm->getValue( tao_helpers_Uri::encode(BookletClassService::PROPERTY_TEST) ) );
                $report = $this->generateFromForm($myForm, $test, $bookletClass);

                $this->setData( 'reload', true );

                $this->returnReport( $report );
            } else {
                $this->renderForm($myForm);
            }

        } catch ( NoTestsException $e ) {
            $this->setView( 'Booklet/wizard.tpl' );
        }
    }

    /**
     * Creates new instance of booklet from a test instance
     * @throws \tao_models_classes_dataBinding_GenerisFormDataBindingException
     */
    public function testBooklet()
    {
        $this->defaultData();

        try {
            $test = $this->getCurrentInstance();
            $bookletClass = $this->getRootClass();
            $formContainer = new WizardTestForm($bookletClass, $test);
            $myForm = $formContainer->getForm();

            if ($myForm->isValid() && $myForm->isSubmited()) {

                $report = $this->generateFromForm($myForm, $test, $bookletClass);

                $this->setData('reload', false);
                $this->setData('selectNode', $test->getUri());

                $this->returnReport($report, false);
            } else {
                $myForm->getElement(tao_helpers_Uri::encode(RDFS_LABEL))->setValue($test->getLabel());

                $this->renderForm($myForm);
            }

        } catch (NoTestsException $e) {
            $this->setView('Booklet/wizard.tpl');
        }
    }

    /**
     * @param GenerateForm $form
     * @param core_kernel_classes_Resource $test
     * @param core_kernel_classes_Class $bookletClass
     * @return \common_report_Report
     */
    protected function generateFromForm($form, $test, $bookletClass)
    {
        $values = $form->getValues();
        $clazz  = new core_kernel_classes_Class( $bookletClass );

        $task = CreateBooklet::createTask($test, $clazz, $values);

        $report = $this->getTaskReport($task);

        return $report;
    }

    /**
     * @param GenerateForm $form
     */
    protected function renderForm($form)
    {
        $this->getServiceManager()->get(BookletConfigService::SERVICE_ID)->setDefaultFormValues($form);

        $this->setData('myForm', $form->render());
        $this->setData('formTitle', __('Create a new booklet'));
        $this->setView('form.tpl', 'tao');
    }

    /**
     * @param $task
     * @return common_report_Report
     */
    protected function getTaskReport($task)
    {
        $status = $task->getStatus();
        if ($status === Task::STATUS_FINISHED || $status === Task::STATUS_ARCHIVED) {
            $report = $task->getReport();
        } else {
            $report = common_report_Report::createInfo(__('Booklet task created'));
        }
        return $report;
    }
}
