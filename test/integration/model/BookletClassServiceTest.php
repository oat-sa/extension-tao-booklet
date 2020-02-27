<?php
/**
 * ***********************************************************************
 * INVALSI CONFIDENTIAL
 * __________________
 *
 *  All Rights Reserved.
 *
 *  NOTICE:  All information contained herein is, and remains
 *  the property of INVALSI and its suppliers, if any.  The
 *  intellectual and technical concepts contained herein are
 *  proprietary to INVALSI and its suppliers, and are protected
 *  by trade secret or copyright law.
 *  Dissemination of this information or reproduction of this material
 *  is strictly forbidden unless prior written permission is obtained
 *  from INVALSI.
 *  ************************************************************************
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
