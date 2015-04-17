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

/**
 * BookletExporter is the base class for exporting booklet
 *
 * @author Aleh Hutnikau <hutnikau@1pt.com>
 */
abstract class BookletExporter
{
    /**
     * @var mixed booklet content
     */
    protected $_content;
    
    /**
     * Export booklet content
     */
    abstract function export();

    /**
     * Set booklet content
     *
     * @param mixed $content booklet content
     */
    public function setContent($content) 
    {
        $this->_content = $content;
    }
    
    /**
     * Get booklet content
     * @return string booklet content
     */
    public function getContent() 
    {
        return $this->_content;
    }
    
    /**
     * Save booklet into file
     * 
     * @return mixed export error message
     * @throws BookletExporterException if not overrided
     */
    public function getError() 
    {
        throw new BookletExporterException(__METHOD__ . ' is not supported.');
    }
    
    /**
     * Save booklet into file
     * 
     * @param string $path the file path
     * @return bool whether file was created successfully
     * @throws BookletExporterException if not overrided
     */
    public function saveAs($path)
    {
        throw new BookletExporterException(__METHOD__ . ' is not supported.');
    }
}
