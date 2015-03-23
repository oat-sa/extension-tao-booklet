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

use oat\taoBooklet\form\EditForm;
use oat\taoBooklet\model\StorageService;
use tao_actions_SaSModule;
use oat\taoBooklet\model\BookletClassService;
use oat\taoBooklet\model\BookletGenerator;
use core_kernel_classes_Resource;
use core_kernel_classes_Class;
use oat\taoBooklet\form\WizardForm;

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

        if ($myForm->isSubmited() && $myForm->isValid()) {
            $values = $myForm->getValues();
            // save properties
            $binder   = new \tao_models_classes_dataBinding_GenerisFormDataBinder( $instance );
            $instance = $binder->bind( $values );

            $this->setData( 'message', __( 'Booklet saved' ) );
            $this->setData( 'reload', true );
        }

        $this->setData( 'formTitle', __( 'Edit Booklet' ) );
        $this->setData( 'myForm', $myForm->render() );
        $this->setView( 'form.tpl', 'tao' );
    }

    /**
     * Used for regeneration of attached pdf
     * @throws \core_kernel_persistence_Exception
     * @throws \tao_models_classes_MissingRequestParameterException
     */
    public function regenerate()
    {
        $instance = $this->getCurrentInstance();

        $testUri = $instance->getOnePropertyValue( new \core_kernel_classes_Property( INSTANCE_TEST_MODEL_QTI ) );
        $test    = new core_kernel_classes_Resource( $testUri );

        $tmpFolder = \tao_helpers_File::createTempDir();
        $tmpFile   = BookletGenerator::generatePdf( $test, $tmpFolder );

        $report = $this->getClassService()->updateInstanceAttachment( $instance, $tmpFile );

        \tao_helpers_File::delTree( $tmpFolder );

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

        $contentUri = $instance->getOnePropertyValue(
            new \core_kernel_classes_Property( BookletClassService::PROPERTY_FILE_CONTENT )
        );
        $file       = new \core_kernel_versioning_File( $contentUri );

        header( 'Content-Disposition: attachment; filename="' . basename( $file->getAbsolutePath() ) . '"' );
        \tao_helpers_Http::returnFile( $file->getAbsolutePath() );

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
            $formContainer = new WizardForm( $this->getCurrentClass() );
            $myForm        = $formContainer->getForm();

            if ($myForm->isValid() && $myForm->isSubmited()) {

                $clazz    = new core_kernel_classes_Class( $this->getCurrentClass() );
                $test     = new core_kernel_classes_Resource( $myForm->getValue( 'test' ) );
                $report   = BookletGenerator::generate( $test, $clazz );
                $instance = $report->getData();

                // save properties from form
                $values   = $myForm->getValues();
                $binder   = new \tao_models_classes_dataBinding_GenerisFormDataBinder( $instance );
                $instance = $binder->bind( $values );

                $this->setData( 'message', __( 'Booklet created' ) );
                $this->setData( 'reload', true );

                $this->returnReport( $report );

            } else {
                $this->setData( 'myForm', $myForm->render() );
                $this->setData( 'formTitle', __( 'Create a new booklet' ) );
                $this->setView( 'form.tpl', 'tao' );
            }

        } catch ( \taoSimpleDelivery_actions_form_NoTestsException $e ) {
            $this->setView( 'Booklet/wizard.tpl' );
        }
    }

}
