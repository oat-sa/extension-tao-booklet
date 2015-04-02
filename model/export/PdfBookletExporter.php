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

namespace oat\taoBooklet\model\export;

use common_ext_ExtensionsManager;
use mikehaertl\wkhtmlto\Pdf;

/**
 * PdfBookletExporter provides functionality to export booklet to
 * PDF format (directly send to end user or save into pdf file).
 *
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
class PdfBookletExporter extends BookletExporter
{
    /**
     * @var Pdf instance of the wrapper around wkhtmltopdf
     */
    protected $pdf;

    public function __construct()
    {

        $this->pdf = new Pdf(array(
            'window-status' => 'runner-ready',
            'load-error-handling' => 'ignore',
            'no-stop-slow-scripts',
            'print-media-type'
        ));
        $this->pdf->ignoreWarnings = true;

        $ext = common_ext_ExtensionsManager::singleton()->getExtensionById('taoBooklet');
        $creatorConfig = $ext->getConfig('wkhtmltopdf');

        if(is_array($creatorConfig)){
            $this->pdf->binary = trim($creatorConfig['binary']);
        }

        if (!$this->isWkhtmltopdfInstalled()) {
            throw new BookletExporterException('wkhtmltopdf tool is not installed');
        }

    }

    /**
     * Send PDF to client, either inline display or as download a file
     *
     * @param string|null $filename the filename to send. If empty, the PDF is streamed inline.
     * @return bool whether PDF was created successfully
     */
    public function export($filename = null)
    {
        $this->pdf->addPage($this->_content);
        return $this->pdf->send($filename);
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
        $result = '';
        if (filter_var($content, FILTER_VALIDATE_URL)) { //url

            //if we call an external service with the same session, we need to close it before
            session_write_close();

            $this->setOptions(array(
                'cookie' => array(session_name() => session_id())
            ));
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
     * Set Pdf option(s)
     *
     * @param array $options list of global PDF options to set as name/value pairs
     * @return PdfBookletExporter instance for method chaining
     */
    public function setOptions($options = array()) {
        $this->pdf->setOptions($options);
        return $this;
    }

    /**
     * Save booklet into file
     *
     * @param string $path the file path
     * @return bool whether file was created successfully
     */
    public function saveAs($path)
    {
        $this->pdf->addPage($this->_content);
        $result = $this->pdf->saveAs($path);
        if(!$result){
            \common_Logger::e('PDF ERROR : '  . $this->pdf->getError());
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
            return $stdout;
        }
        return '';
    }
}
