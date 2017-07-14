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
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\taoBooklet\model\export;

use common_ext_ExtensionsManager;
use mikehaertl\wkhtmlto\Pdf;
use oat\taoBooklet\model\BookletConfigService;

/**
 * PdfBookletExporter provides functionality to export booklet to
 * PDF format (directly send to end user or save into pdf file).
 *
 * This implementation uses wkhtmltopdf to work (see {@link http://wkhtmltopdf.org/})
 * and it's PHP wrapper ({@link https://github.com/mikehaertl/phpwkhtmltopdf}).
 * Wkhtmltopdf needs to be installed on the server.
 *
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class PdfBookletExporter extends BookletExporter
{
    /**
     * @var Pdf instance of the wrapper around wkhtmltopdf
     */
    protected $pdf;

    /**
     * Creates an new exporter
     * @param string $title the document title
     * @param array $bookletConfig some additional config
     * @throws BookletExporterException when the binary isn't installed
     */
    public function __construct($title = '', $bookletConfig = [])
    {

        //load the config
        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('taoBooklet');
        $config = $ext->getConfig('wkhtmltopdf');

        //the default options
        $options = array(

            //as the page is built in JS, the engine is waiting for
            //window.status to equal 'runner-ready' to capture the content
            //*** Disabled as the current version of wkhtmltopdf is suffering an issue     ***
            //*** that sometimes prevents the process to be aware of window-status change. ***
            //'window-status' => 'DONE',


            //set javascript behavior
            'enable-javascript',
            'debug-javascript',
            'no-stop-slow-scripts',
            'javascript-delay' => 15000, // will wait for 15s before considering the content fully rendered

            //enable browser media print
            'print-media-type',

            //set errors handling
            'load-error-handling' => 'ignore',
            'load-media-error-handling' => 'ignore',

            //print resolution
            'dpi' => 300,

            //document title
            'title' => $title,

        );

        //load the options defined in the config
        if(is_array($config['options'])){
            $options = array_merge($options, $config['options']);
        }

        if (isset($bookletConfig[BookletConfigService::CONFIG_LAYOUT])) {
            $layoutConfig = $bookletConfig[BookletConfigService::CONFIG_LAYOUT];
            if (empty($layoutConfig[BookletConfigService::CONFIG_PAGE_HEADER])) {
                unset($options['header-html']);
            }
            if (empty($layoutConfig[BookletConfigService::CONFIG_PAGE_FOOTER])) {
                unset($options['footer-html']);
            }
        }

        if (isset($options['header-html']) || isset($options['footer-html'])) {
            $options['replace']['config'] = base64_encode(json_encode($bookletConfig));
        }

        //instantiate the PDF wrapper
        $this->pdf = new Pdf($options);

        //ignore warnings, mainly SSL/TLS ones
        $this->pdf->ignoreWarnings = true;

        //set the path of the wkhtml binary
        if(is_array($config)){
            $this->pdf->binary = trim($config['binary']);
        }

        if (!$this->isWkhtmltopdfInstalled()) {
            throw new BookletExporterException('wkhtmltopdf binary is not installed on the server. Please contact your administrator.');
        }
    }

    /**
     * Set Pdf option(s)
     *
     * @param array $options list of global PDF options to set as name/value pairs
     * @return PdfBookletExporter instance for method chaining
     */
    public function setOptions($options = array())
    {
        $this->pdf->setOptions($options);
        return $this;
    }

    /**
     * Set booklet content
     *
     * @param string $content either a URL, a HTML string or a PDF/HTML filename
     * @return PdfBookletExporter instance for method chaining
     * @throws BookletExporterException if wrong $content parameter passed
     */
    public function setContent($content)
    {
        if (filter_var($content, FILTER_VALIDATE_URL)) { //url

            //if we call an external service with the same session, we need to close it before
            session_write_close();

            $result = $content;
        } elseif (file_exists($content) && is_file($content)) {  //file path
            $result = file_get_contents($content);
        } else { //HTML string
            $result = $content;
        }
        if (is_string($result)) {
            $this->_content = $result;
            return $this;
        }
        throw new BookletExporterException('Wrong content type');
    }



    /**
     * Save booklet into file
     *
     * @param string $path the file path
     * @return bool whether file was created successfully
     * @throws BookletExporterException if something goes wrong during the generation
     */
    public function saveAs($path)
    {
        $this->pdf->addPage($this->_content);
        $result = $this->pdf->saveAs($path);
        if(!$result){
            throw new BookletExporterException('Unable to generate the PDF : '  . $this->pdf->getError());
        }
        return $result;
    }


    /**
     * Send PDF to client, either inline display or as download a file
     *
     * @param string|null $filename the filename to send. If empty, the PDF is streamed inline.
     * @return bool whether PDF was created successfully
     * @throws BookletExporterException if something goes wrong during the generation
     */
    public function export($filename = null)
    {
        $this->pdf->addPage($this->_content);

        $result = $this->pdf->send($filename);
        if(!$result){
            throw new BookletExporterException('Unable to generate the PDF : '  . $this->pdf->getError());
        }
        return $result;
    }


    /**
     * @return string the detailed error message. Empty string if none.
     */
    public function getError()
    {
        return $this->pdf->getError();
    }

    /**
     * @return boolean whether wkhtmltopdf tool is installed
     */
    private function isWkhtmltopdfInstalled()
    {
        return file_exists( $this->pdf->binary ) && is_executable( $this->pdf->binary );
    }

    /**
     * @return string guessed path to bin wkhtmltopdf
     */
    static public function guessWhereWkhtmltopdfInstalled()
    {
        $whereIsCommand = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? 'where' : 'which';

        $process = proc_open(
            "$whereIsCommand wkhtmltopdf",
            array(
                0 => array("pipe", "r"), //STDIN
                1 => array("pipe", "w"), //STDOUT
                2 => array("pipe", "w"), //STDERR
            ),
            $pipes
        );
        if ($process !== false) {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
            if($stderr){
               \common_Logger::w('Got an error while trying to locate wkhtmltopdf : ' . $stderr);
            }
            return $stdout;
        }
        return '';
    }
}
