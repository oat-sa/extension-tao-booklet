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
 * Copyright (c) 2020 (original work) Open Assessment Technologies SA;
 */

namespace oat\taoBooklet\test\integration\model;

use core_kernel_classes_Property as Property;
use core_kernel_classes_Resource as Resource;
use oat\generis\model\data\Ontology;
use oat\generis\test\GenerisTestCase;
use oat\taoBooklet\model\BookletClassService;

class BookletClassServiceTest extends GenerisTestCase
{
//    const URI = '#attachmentUri';

    /** @var Ontology */
    private $ontologyMock;

    /** @var BookletClassService */
    private $subject;

    protected function setUp()
    {
        parent::setUp();

        $this->ontologyMock = $this->getOntologyMock();
        $this->subject = BookletClassService::singleton();
        $this->subject->setModel($this->ontologyMock);
    }

    /**
     * @dataProvider provideAttachments
     */
    public function testItCanReturnAttachment($attachment, string $expectedResult): void
    {
        $testBooklet = new Resource('testBookletUri');
        $testBooklet->setModel($this->ontologyMock);

        $fileContentProperty = new Property(BookletClassService::PROPERTY_FILE_CONTENT);
        $fileContentProperty->setModel($this->ontologyMock);

        if ($attachment instanceof Resource) {
            $attachment->setModel($this->ontologyMock);
        }

        $testBooklet->setPropertyValue($fileContentProperty, $attachment);

        $this->assertSame($expectedResult, $this->subject->getAttachment($testBooklet));
    }

    public function provideAttachments(): array
    {
        return [
            'attachmentIsResource' => [
                'attachment' => new Resource('testAttachmentUri'),
                'expectedResult' => 'testAttachmentUri',
            ],
            'attachmentIsLiteral' => [
                'attachment' => new \core_kernel_classes_Literal('expectedLiteral'),
                'expectedResult' => 'expectedLiteral',
            ],
            'attachmentIsString' => [
                'attachment' => 'expectedString',
                'expectedResult' => 'expectedString',
            ],
            'attachmentIsUnexpected' => [
                'attachment' => false,
                'expectedResult' => '',
            ]
        ];
    }
}
