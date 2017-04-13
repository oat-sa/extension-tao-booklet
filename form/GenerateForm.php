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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 */
namespace oat\taoBooklet\form;

use oat\taoBooklet\model\BookletClassService;
use tao_helpers_form_FormFactory;
use tao_helpers_Uri;

/**
 * Create a form from a booklet
 * Each property will be a field, regarding it's widget.
 *
 * @access public
 * @package taoBooklet
 */
class GenerateForm extends \tao_actions_form_Instance
{

    public function initElements()
    {
        parent::initElements();

        $formatElt = tao_helpers_form_FormFactory::getElement( 'anonymousClass', 'Hidden' );
        $formatElt->setValue(tao_helpers_Uri::encode( BookletClassService::PROPERTY_ANONYMOUS ));
        $this->getForm()->addElement($formatElt);

        $anonymousElm = $this->getForm()->getElement( tao_helpers_Uri::encode( BookletClassService::PROPERTY_ANONYMOUS ) );
        $anonymousElm->addValidator( tao_helpers_form_FormFactory::getValidator( 'NotEmpty' ) );

        $createElt = \tao_helpers_form_FormFactory::getElement( 'create', 'Button' );
        $createElt->setValue( __( 'Generate' ) );
        $createElt->setIcon( "icon-play" );
        $createElt->addClass( "form-submitter btn-success small" );

        $this->form->setActions( array( $createElt ), 'bottom' );
    }
}
