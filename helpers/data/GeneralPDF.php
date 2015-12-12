<?php
namespace youconix\core\helpers\data;

/**
 * General PDF generation class
 *
* This file is part of Miniature-happiness                                    
 *                                                                              
 * @copyright Youconix                                
 * @author    Rachelle Scheijen                                                
 * @since     1.0 
 */
abstract class GeneralPDF
{

	/**
	 * 
	 * @var \Language
	 */
    protected $language;
    
    /**
     * 
     * @var \Output
     */
    protected $template;
    
    /**
     * 
     * @var \youconix\services\FileHandler
     */
    protected $fileHandler;

    protected $s_encoding;

    protected $s_template;

    protected $obj_renderer;

    /**
     * PHP 5 constructor
     * 
     * @param \Language	$language
     */
    public function __construct(\Language $language,\Output $template,\youconix\services\FileHandler $file)
    {
        $this->language = $language;
        $this->template = $template;
        $this->fileHandler = $file;
        $this->s_encoding = $this->language->get('language/encoding');
        
        $this->obj_renderer = new \dompdf\dompdf\DOMPDF();
    }

    /**
     * Loads the PDF template
     *
     * @param string $s_name
     *            template name
     * @throws TemplateException the template does not exists
     */
    protected function loadTemplate($s_name)
    {
        $s_styleDir = $this->template->getStylesDir();
        
        if (! $this->fileHandler->exists($s_styleDir . 'pdf/' . $s_name)) {
            throw new TemplateException("Could not load PDF template " . $s_name . ' in ' . $s_styleDir . 'pdf/.');
        }
        
        $this->s_template = $this->fileHandler->readFile($s_styleDir . 'pdf/' . $s_name);
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
     * @param string $s_name
     *            of the PDF
     * @return string pdf content
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