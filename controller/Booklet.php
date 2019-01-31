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
use core_kernel_classes_Resource;
use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\filesystem\File;
use oat\taoBooklet\form\EditForm;
use oat\taoBooklet\form\GenerateForm;
use oat\taoBooklet\form\WizardBookletForm;
use oat\taoBooklet\form\WizardPrintForm;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoBooklet\model\BookletTaskService;
use oat\taoBooklet\model\StorageService;
use oat\taoDeliveryRdf\model\NoTestsException;
use tao_helpers_Uri;
use tao_models_classes_dataBinding_GenerisFormDataBinder;

/**
 * Controller to managed assembled deliveries
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @package taoDelivery
 */
class Booklet extends AbstractBookletController
{
    /**
     * Main action
     *
     * @access public
     * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
     * @return void
     */
    public function index()
    {
        $this->defaultData();
        $this->setView( 'index.tpl' );
    }

    /**
     * Edit action
     * @throws \tao_models_classes_MissingRequestParameterException
     * @throws \tao_models_classes_dataBinding_GenerisFormDataBindingException
     */
    public function editBooklet()
    {
        $this->defaultData();

        $clazz           = $this->getCurrentClass();
        $instance        = $this->getCurrentInstance();
        $myFormContainer = new EditForm( $clazz, $instance );

        $myForm = $myFormContainer->getForm();

        $fileResource = $this->getClassService()->getAttachment($instance);
        try {
            $file = $this->getServiceLocator()->get(FileReferenceSerializer::SERVICE_ID)->unserialize($fileResource);
            $allowDownload = ($file instanceof File);
        } catch (\common_Exception $e) {
            $allowDownload = false;

        }
        $myFormContainer->setAllowDownload($allowDownload);

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
        $this->setData( 'queueId', $instance->getUri() ); //TODO: can it be removed?
        $this->setView( 'Booklet/edit.tpl' );
    }

    /**
     * @throws \common_Exception
     */
    public function preview()
    {
        $this->defaultData();

        $instance = $this->getCurrentInstance();
        $test     = $this->getClassService()->getTest( $instance );
        if(is_null($test)){
            throw new \common_Exception('No test linked to the booklet');
        }
        $url = tao_helpers_Uri::url( 'preview', 'PrintTest', 'taoBooklet', ['uri' => tao_helpers_Uri::encode($instance->getUri())]);

        $this->setData( 'renderUrl', $url);
        $this->setView( 'Booklet/preview.tpl');
    }

    /**
     * Used for regeneration of attached pdf
     *
     * @throws \tao_models_classes_MissingRequestParameterException
     */
    public function regenerate()
    {
        $this->defaultData();

        $instance  = $this->getCurrentInstance();

        $task = $this->getServiceLocator()->get(BookletTaskService::SERVICE_ID)->createPrintBookletTask($instance);

        //TODO: use the new queue js component for action `booklet-regenerate` in the tree
        return $this->returnTaskJson($task);
    }

    /**
     * Invokes download of pregenerated delivery
     * @throws \common_exception_Error
     * @throws \tao_models_classes_MissingRequestParameterException
     */
    public function download()
    {
        $this->defaultData();

        $instance = $this->getCurrentInstance();

        try {
            $fileResource = $this->getClassService()->getAttachment($instance);
            if ($fileResource) {
                /** @var File $file */
                $file = $this->getServiceLocator()->get(StorageService::SERVICE_ID)->getFile($fileResource);
                if ($file->exists()) {
                    $this->prepareDownload($instance->getLabel() . '_' . $file->getBasename(), $file->getMimeType());
                    \tao_helpers_Http::returnStream($file->readPsrStream());
                } else {
                    throw new \common_exception_NotFound('File does not exists: ' . $file->getPrefix());
                }
            } else {
                throw new \common_exception_NotFound('Unknown resource ' . $fileResource);
            }
        } catch (\common_exception_NotFound $e) {
            header("HTTP/1.0 404 Not Found");
        }
    }

    /**
     * Overloaded delete, also takes care about attached files
     * @throws \Exception
     * @throws \tao_models_classes_MissingRequestParameterException
     */
    public function delete()
    {
        $this->defaultData();

        if ($this->hasRequestParameter('uri')) {
            $instance = $this->getCurrentInstance();
            $this->getClassService()->removeInstanceAttachment($instance);
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
            $bookletClass  = $this->getCurrentClass();
            $formContainer = new WizardBookletForm( $bookletClass );
            $myForm        = $formContainer->getForm();

            if ($myForm->isValid() && $myForm->isSubmited()) {
                $test   = $this->getResource($myForm->getValue(tao_helpers_Uri::encode(BookletClassService::PROPERTY_TEST)));
                $report = $this->generateFromForm($myForm, $test, $bookletClass);

                $this->setData( 'reload', true );

                //TODO: instead of report a special json string should be returned: $this->returnTaskJson()
                $this->returnReport( $report );
            } else {
                $this->renderForm($myForm);
            }

        } catch (NoTestsException $e) {
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
            $formContainer = new WizardPrintForm($bookletClass, $test);
            $myForm = $formContainer->getForm();

            if ($myForm->isValid() && $myForm->isSubmited()) {

                $report = $this->generateFromForm($myForm, $test, $bookletClass);

                $this->setData('reload', false);
                $this->setData('selectNode', $test->getUri());

                //TODO: instead of report a special json string should be returned: $this->returnTaskJson()
                $this->returnReport($report, false);
            } else {
                $myForm->getElement(tao_helpers_Uri::encode(OntologyRdfs::RDFS_LABEL))->setValue($test->getLabel());

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
        $report = new common_report_Report(common_report_Report::TYPE_SUCCESS);

        // TODO: this method should return the created task but how should we handle/display the following error????
        $model = \taoTests_models_classes_TestsService::singleton()->getTestModel($test);
        if ($model->getUri() != \taoQtiTest_models_classes_QtiTestService::INSTANCE_TEST_MODEL_QTI) {
            $report->setType(common_report_Report::TYPE_ERROR);
            $report->setMessage(__('%s is not a QTI test', $test->getLabel()));
            return $report;
        }

        // generate tao instance
        $class  = $this->getClass($bookletClass);
        $instance = BookletClassService::singleton()->createBookletInstance($class, __('%s Booklet', $test->getLabel()), $test);
        $binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($instance);
        $binder->bind($form->getValues());

        $this->getServiceLocator()->get(BookletTaskService::SERVICE_ID)->createPrintBookletTask($instance);

        // return report with instance
        $report->setMessage(__('Booklet %s created', $instance->getLabel()));
        $report->setData($instance);
        return $report;
    }

    /**
     * @param GenerateForm $form
     */
    protected function renderForm($form)
    {
        $this->getServiceLocator()->get(BookletConfigService::SERVICE_ID)->setDefaultFormValues($form);

        $this->setData('myForm', $form->render());
        $this->setData('formTitle', __('Create a new booklet'));
        $this->setView('form.tpl', 'tao');
    }
}
