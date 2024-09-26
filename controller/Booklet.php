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
 * Copyright (c) 2014-2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoBooklet\controller;

use common_Exception;
use common_exception_NotFound;
use common_ext_ExtensionException;
use core_kernel_persistence_Exception;
use Exception;
use oat\generis\model\fileReference\FileReferenceSerializer;
use oat\generis\model\OntologyRdfs;
use oat\oatbox\filesystem\File;
use oat\tao\model\TaoOntology;
use oat\taoBooklet\form\EditForm;
use oat\taoBooklet\form\WizardBookletForm;
use oat\taoBooklet\form\WizardPrintForm;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletConfigService;
use oat\taoBooklet\model\BookletTaskService;
use oat\taoBooklet\model\StorageService;
use oat\taoBooklet\model\tasks\CompileBooklet;
use oat\taoQtiItem\model\qti\Resource;
use RuntimeException;
use tao_helpers_form_Form as Form;
use tao_helpers_Http;
use tao_helpers_Uri;
use tao_models_classes_dataBinding_GenerisFormDataBinder as GenerisFormDataBinder;
use tao_models_classes_dataBinding_GenerisFormDataBindingException;
use tao_models_classes_MissingRequestParameterException;

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
     * @throws common_ext_ExtensionException
     */
    public function index(): void
    {
        $this->defaultData();
        $this->setView('index.tpl');
    }

    /**
     * Edit action
     * @throws core_kernel_persistence_Exception
     * @throws common_ext_ExtensionException
     * @throws tao_models_classes_MissingRequestParameterException
     * @throws tao_models_classes_dataBinding_GenerisFormDataBindingException
     */
    public function editBooklet(): void
    {
        $this->defaultData();

        $currentInstance = $this->getCurrentInstance();

        $this->setData('module-config', json_encode([
            'isPreviewEnabled' => $currentInstance->getLabel() !== 'in progress'
        ]));

        /** @var FileReferenceSerializer $fileReferenceSerializer */
        $fileReferenceSerializer = $this->getServiceLocator()->get(FileReferenceSerializer::SERVICE_ID);

        try {
            $attachmentFile = $fileReferenceSerializer->unserialize(
                $this->getClassService()->getAttachment($currentInstance)
            );
        } catch (common_Exception $e) {
            $attachmentFile = null;
        }

        $form = (new EditForm($this->getCurrentClass(), $currentInstance))
            ->setAllowDownload($attachmentFile instanceof File)
            ->getForm();

        if ($form !== null && $form->isSubmited() && $form->isValid()) {
            (new GenerisFormDataBinder($currentInstance))->bind($form->getValues());
            $this->setData('message', __('Booklet saved'));
            $this->setData('reload', true);
        }

        $this->setData('form-title', __('Edit Booklet'));
        $this->setData('form-fields', $form->render());
        $this->setView('Booklet/edit.tpl');
    }

    /**
     * @throws \common_Exception
     */
    public function preview()
    {
        $this->defaultData();

        $instance = $this->getCurrentInstance();
        $test     = $this->getClassService()->getTest($instance);
        if (is_null($test)) {
            throw new \common_Exception('No test linked to the booklet');
        }
        $url = tao_helpers_Uri::url('preview', 'PrintTest', 'taoBooklet', ['uri' => tao_helpers_Uri::encode($instance->getUri())]);

        $this->setData('renderUrl', $url);
        $this->setView('Booklet/preview.tpl');
    }

    /**
     * Used for regeneration of attached pdf
     *
     * @return mixed
     * @throws common_ext_ExtensionException
     * @throws tao_models_classes_MissingRequestParameterException
     * @throws core_kernel_persistence_Exception
     */
    public function regenerate()
    {
        $this->defaultData();

        $instance = $this->getCurrentInstance();

        $task = $this->getServiceLocator()
            ->get(BookletTaskService::SERVICE_ID)
            ->createPrintBookletTask($instance->getUri(), $instance->getLabel());

        return $this->returnTaskJson($task);
    }

    /**
     * Invokes download of pregenerated delivery
     * @throws common_ext_ExtensionException
     * @throws core_kernel_persistence_Exception
     * @throws tao_models_classes_MissingRequestParameterException
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
                    tao_helpers_Http::returnStream($file->readPsrStream());
                } else {
                    throw new common_exception_NotFound('File does not exists: ' . $file->getPrefix());
                }
            } else {
                throw new common_exception_NotFound('Unknown resource ' . $fileResource);
            }
        } catch (common_exception_NotFound $e) {
            header("HTTP/1.0 404 Not Found");
        }
    }

    /**
     * Overloaded delete, also takes care about attached files
     * @throws Exception
     * @throws tao_models_classes_MissingRequestParameterException
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
     * @return mixed
     * @throws common_ext_ExtensionException
     */
    public function wizard()
    {
        $fromTest = false;
        if ($this->isRequestComingFromTests()) {
            $fromTest = true;
        }
        $this->defaultData();

        try {
            $test = null;
            $currentClass = $this->getCurrentClass();

            if ($fromTest) {
                $currentClass = $this->getRootClass();
                try {
                    $test = $this->getCurrentInstance();
                } catch (Exception $e) {
                    $test = $this->getCurrentInstance(tao_helpers_Uri::encode(BookletClassService::PROPERTY_TEST));
                }
            }

            $this->prepareWizardForm($currentClass, $test);
        } catch (Exception $e) {
            $this->setView('Booklet/wizard.tpl');
        }
    }

    private function isRequestComingFromTests()
    {
        $referer = $this->getPsrRequest()->getHeader('Referer')[0] ?? '';

        return false !== str_contains($referer, 'ext=taoTests');
    }

    private function prepareWizardForm(\core_kernel_classes_Class $targetClass, \core_kernel_classes_Resource $attachedTest = null)
    {
        try {
            $form = (new WizardBookletForm($targetClass))->getForm();
            if ($attachedTest && !$form->isSubmited()) {
                $label = $form->getElement(tao_helpers_Uri::encode(OntologyRdfs::RDFS_LABEL));

                if ($label) {
                    $label->setValue($attachedTest->getLabel());
                }

                $formElement = new \tao_helpers_form_elements_xhtml_Hidden(tao_helpers_Uri::encode(BookletClassService::PROPERTY_TEST));
                $formElement->setValue($attachedTest->getUri());
                $form->removeElement(tao_helpers_Uri::encode(BookletClassService::PROPERTY_TEST));
                $form->addElement($formElement);
            }

            if ($form === null || !$form->isSubmited()) {
                $this->renderForm($form);

                return;
            }

            if (!$form->isValid()) {
                return $this->returnJsonError(__('Fill in all required fields'));
            }

            $test = $this->getResource(
                $form->getValue(tao_helpers_Uri::encode(BookletClassService::PROPERTY_TEST))
            );

            return $this->returnTaskJson(
                CompileBooklet::createTask($this->getCurrentClass(), $test, $form->getValues())
            );
        } catch (Exception $e) {
            $this->setView('Booklet/wizard.tpl');
        }
    }

    protected function renderForm(Form $form): void
    {
        $this->getServiceLocator()
            ->get(BookletConfigService::SERVICE_ID)
            ->setDefaultFormValues($form);

        $this->setData('myForm', $form->render());
        $this->setData('formTitle', __('Create a new booklet'));

        $this->setView('form.tpl', 'tao');
    }
}
