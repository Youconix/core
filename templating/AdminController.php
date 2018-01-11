<?php

namespace youconix\core\templating;

abstract class AdminController extends BaseController
{

  /**
   * Loads the given view into the parser
   *
   * @param string $s_view
   *            The view relative to the template-directory
   * @param array $a_data
   * 		  Data as key-value pair
   * @param string $s_templateDir
   * 		  Override the default template directory
   * @return \Output
   * @throws \TemplateException if the view does not exist
   * @throws \IOException if the view is not readable
   */
  protected function createView($s_view, $a_data = [], $s_templateDir = 'admin')
  {
    return parent::createView($s_view, $a_data, $s_templateDir);
  }

  /**
   * 
   * @param \Output $template
   */
  protected function setDefaultValues(\Output $template)
  {
    $template->append('currentLanguage', $this->getLanguage()->getLanguage());
  }
}
