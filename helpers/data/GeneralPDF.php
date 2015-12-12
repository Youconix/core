<?php

/**
 * General PDF generation class
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
abstract class GeneralPDF
{

    protected $service_Language;

    protected $s_encoding;

    protected $s_template;

    protected $obj_renderer;

    /**
     * PHP 5 constructor
     */
    public function __construct()
    {
        $this->service_Language = Memory::services('Language');
        $this->s_encoding = $this->service_Language->get('language/encoding');
        
        $this->obj_renderer = new DOMPDF();
    }

    /**
     * Loads the PDF template
     *
     * @param String $s_name
     *            template name
     * @throws TemplateException the template does not exists
     */
    protected function loadTemplate($s_name)
    {
        $s_styleDir = Memory::services('Template')->getStylesDir();
        
        $service_File = Memory::services('File');
        if (! $service_File->exists($s_styleDir . 'pdf/' . $s_name)) {
            throw new TemplateException("Could not load PDF template " . $s_name . ' in ' . $s_styleDir . 'pdf/.');
        }
        
        $this->s_template = $service_File->readFile($s_styleDir . 'pdf/' . $s_name);
    }

    /**
     * Formats the given value
     *
     * @param int $i_value
     *            unformatted value
     * @param int $i_decimals
     *            number of decimals, default 0
     * @return string formatted value
     */
    protected function format($i_value, $i_decimals = 0)
    {
        if ($i_value < 10000)
            return $i_value;
        
        return number_format($i_value, $i_decimals, ',', '.');
    }

    /**
     * Creates the PDF and returns the content
     *
     * @param String $s_name
     *            of the PDF
     * @return String pdf content
     */
    protected function returnString($s_name)
    {
        $this->obj_renderer->load_html($this->s_template);
        $this->obj_renderer->render();
        return $this->obj_renderer->output($s_name);
    }

    /**
     * Creates the PDF and force downloads it
     *
     * @param tring $s_name
     *            of the PDF
     */
    protected function download($s_name)
    {
        $this->obj_renderer->load_html($this->s_template);
        $this->obj_renderer->render();
        $this->obj_renderer->stream($s_name);
    }

    /**
     * Inserts the given values on the place from the given keys in the template
     *
     * @param array $a_keys
     *            keys
     * @param array $a_values
     *            values
     */
    protected function insert($a_keys, $a_values)
    {
        if (! is_array($a_keys)) {
            $a_keys = array(
                $a_keys
            );
            $a_values = array(
                $a_values
            );
        }
        
        $i_num = count($a_keys);
        for ($i = 0; $i < $i_num; $i ++) {
            $a_keys[$i] = '[' . $a_keys[$i] . ']';
        }
        
        $this->s_template = str_replace($a_keys, $a_values, $this->s_template);
    }
}