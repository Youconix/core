<?php

/**
 * Helper for generating PDFs
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
class Helper_PDF extends Helper
{

    /**
     * PHP 5 constructor
     */
    public function __construct()
    {
        if (! class_exists('DOMPDF')) {
            require (NIV . 'vendor/dompdf/dompdf/dompdf_config.inc.php');
            require (NIV . 'Core/helpers/data/GeneralPDF.inc.php');
        }
    }
}
?>
