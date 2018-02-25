<?php

namespace youconix\core\Templating;

abstract class AdminController extends BaseController
{
  /**
   * Loads the given view into the parser
   *
   * @param string $s_view
   *            The view relative to the template-directory
   * @param array $a_data
   *      Data as key-value pair
   * @param string $s_templateDir
   *      Override the default template directory
   * @return \OutputInterface
   * @throws \TemplateException if the view does not exist
   * @throws \IOException if the view is not readable
   */
  protected function createView($s_view, $a_data = [], $s_templateDir = 'admin')
  {
    $output = $this->wrapper->getOutput();

    $output->load($s_view, $s_templateDir);
    $output->setArray($a_data);
    $this->wrapper->getLayout()->parse($output);

    return $output;
  }

  /**
   *
   * @param \OutputInterface $template
   */
  protected function setDefaultValues(\OutputInterface $template)
  {
    $template->append('currentLanguage', $this->getLanguage()->getLanguage());
  }

  /**
   *
   * @param string $parent
   * @param string $field
   * @return string
   */
  protected function getText($parent, $field)
  {
    return t('system/admin/' . $parent . '/' . $field);
  }

  /**
   *
   * @param string $name
   * @param boolan $value
   * @return \youconix\core\helpers\OnOff
   */
  protected function createSlider($name, $value)
  {
    $slider = clone $this->onOff;
    $slider->setName($name);
    if ($value) {
      $slider->setSelected(true);
    }

    return $slider;
  }
}
