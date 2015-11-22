<?php

/** 
 * Base GUI PDF class for the framework.  Use this file as parent for all GUI controllers with PDF download-functionality                           
 *                                                                              
 * This file is part of Miniature-happiness                                    
 *                                                                              
 * @copyright Youconix                                
 * @author    Rachelle Scheijen                                                
 * @since     1.0
 *                                                                              
 * Miniature-happiness is free software: you can redistribute it and/or modify 
 * it under the terms of the GNU Lesser General Public License as published by  
 * the Free Software Foundation, either version 3 of the License, or            
 * (at your option) any later version.                                          
 *                                                                              
 * Miniature-happiness is distributed in the hope that it will be useful,      
 * but WITHOUT ANY WARRANTY; without even the implied warranty of               
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the                
 * GNU General Public License for more details.                                 
 *                                                                              
 * You should have received a copy of the GNU Lesser General Public License     
 * along with Miniature-happiness.  If not, see <http://www.gnu.org/licenses/>.
 */
if (! class_exists('\includes\BaseLogicClass')) {
    require (NIV . 'includes/BaseLogicClass.php');
}

abstract class PdfLogicClass extends \includes\BaseLogicClass
{

    /**
     * Displays the PDF in the browser
     *
     * @param String $s_name
     *            name
     * @param String $s_content
     *            content
     * @param int $i_size
     *            size
     */
    protected function inlinePDF($s_name, $s_content, $i_size)
    {
        $this->prepareFramework();
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $s_name . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length:' . $i_size);
        
        echo ($s_content);
        
        exit();
    }

    /**
     * Force downloads the PDF
     *
     * @param String $s_name
     *            name
     * @param String $s_content
     *            content
     * @param int $i_size
     *            size
     */
    protected function downloadPDF($s_name, $s_content, $i_size)
    {
        $this->downloadGeneral($s_name, $s_content, $i_size, 'application/pdf');
    }

    /**
     * Force downloads the document
     *
     * @param String $s_name
     *            name
     * @param String $s_content
     *            content
     * @param int $i_size
     *            size
     *            @parm String $s_mimetype The mimetype, leave empty for auto detection
     */
    protected function downloadGeneral($s_name, $s_content, $i_size, $s_mimetype = '')
    {
        $this->prepareFramework();
        
        if (empty($s_mimetype)) {
            $s_mimetype = Memory::services('FileData')->getMimeType($s_name);
        }
        
        header('Content-Type: ' . $s_mimetype);
        header('Content-Disposition: attachment; filename="' . $s_name . '"');
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length:' . $i_size);
        
        echo ($s_content);
        exit();
    }

    /**
     * Prepares the framework for PDF download
     */
    protected function prepareFramework()
    {
        if (is_null($this->service_Template))
            return;
    }
}