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
namespace oat\taoBooklet\form;

use tao_helpers_form_FormFactory;
use core_kernel_classes_Class;

/**
 * Create a form from a booklet
 * Each property will be a field, regarding it's widget.
 *
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package taoBooklet
 */
class WizardForm extends \tao_actions_form_Instance
{

    /*
    * Short description of method initElements
    *
    * @access public
    * @author Joel Bout, <joel.bout@tudor.lu>
    * @return mixed
    */
    public function initElements()
    {
        parent::initElements();
        //create the element to select the import format

        $formatElt = tao_helpers_form_FormFactory::getElement( 'test', 'Combobox' );
        $formatElt->setDescription( __( 'Select the test you want to prepare for printing' ) );
        $testClass = new core_kernel_classes_Class( TAO_TEST_CLASS );

        $options = array();

//        $user = \common_session_SessionManager::getSession()->getUser();
        foreach ($testClass->getInstances( true ) as $test) {
            $options[$test->getUri()] = $test->getLabel();
        }

        if (empty( $options )) {
            throw new \taoSimpleDelivery_actions_form_NoTestsException();
        }

        $formatElt->setOptions( $options );
        $formatElt->addValidator( tao_helpers_form_FormFactory::getValidator( 'NotEmpty' ) );
        $this->form->addElement( $formatElt );

        $createElt = \tao_helpers_form_FormFactory::getElement( 'create', 'Button' );
        $createElt->setValue( __( 'Generate' ) );
        $createElt->setIcon( "icon-publish" );
        $createElt->addClass( "form-submitter btn-success small" );

        $this->form->setActions( array( $createElt ), 'bottom' );
    }
}