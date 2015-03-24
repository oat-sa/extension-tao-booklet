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
 */

namespace oat\taoBooklet\test;

use oat\tao\test\TaoPhpUnitTestRunner;
use oat\taoBooklet\model\export\PdfBookletExporter;
use oat\taoBooklet\model\export\BookletExporterException;

/**
 * 
 * @author Aleh Hutnikau, <hutnikau@1pt.com>
 * @package taoBooklet
 */
class PdfBookletExporterTest extends TaoPhpUnitTestRunner
{
    /**
     * @var PdfBookletExporter exporter instance
     */
    private $pdfBookletExporter;
    
    /**
     * @var string sample html file content
     */
    private $HTMLString = '<html>
                <head></head>
                <body>Body content</body>
            </html>';
    
    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        ob_start();
    }
    
    /**
     * tests initialization
     */
    public function setUp()
    {
        TaoPhpUnitTestRunner::initTest();
        try {
            $this->pdfBookletExporter = new PdfBookletExporter();
        } catch (BookletExporterException $e) {
            $this->markTestSkipped(
                $e->getMessage()
            );
        }
    }
    
    /**
     * Check pdf file export
     */
    public function testExport()
    {
        $name = 'filename';
        $this->pdfBookletExporter->setContent($this->HTMLString);
        ob_start();
        $this->assertTrue($this->pdfBookletExporter->export($name));
        $pdfFileContent = ob_get_clean();
        
        $headers = xdebug_get_headers();
        
        $this->assertStringStartsWith('%PDF', $pdfFileContent);
        $this->assertTrue(in_array('Content-Type: application/pdf', $headers));
    }
    
    /**
     * Check different types of input (url, Html string, filepath)
     */
    public function testSetContent() 
    {
        $HTMLFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . 'bookletSample.html';
        //set document content as a string
        $this->assertTrue($this->pdfBookletExporter->setContent($this->HTMLString) instanceof PdfBookletExporter);
        $this->assertTrue($this->pdfBookletExporter->getContent() == $this->HTMLString);
        
        //set document content as a filepath
        $this->pdfBookletExporter->setContent($HTMLFilePath);
        $this->assertTrue($this->pdfBookletExporter->getContent() == file_get_contents($HTMLFilePath));
        
        $this->pdfBookletExporter->setContent('');
        $this->assertTrue($this->pdfBookletExporter->getContent() == '');
        
        //set document content as a url (internet connection required)
        $this->pdfBookletExporter->setContent('http://google.com');
        $this->assertTrue($this->pdfBookletExporter->getContent() != '');
    }
    
    /**
     * @expectedException oat\taoBooklet\model\export\BookletExporterException
     * @expectedExceptionMessage Wrong content type
     */
    public function testSetContentException() 
    {
        $this->pdfBookletExporter->setContent(null);
    }
    
    
    /**
     * Check pdf file saving
     */
    public function testSaveAs()
    {
        $pdfFilePath = __DIR__ . DIRECTORY_SEPARATOR . 'samples' . DIRECTORY_SEPARATOR . 'bookletSample.pdf';
        $this->pdfBookletExporter->setContent($this->HTMLString);
        $this->assertTrue($this->pdfBookletExporter->saveAs($pdfFilePath));
        $this->assertFileExists($pdfFilePath);
        unlink($pdfFilePath);
    }
    
}
