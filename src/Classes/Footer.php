<?php

namespace youconix\Core\Classes;

/**
 * Site footer
 *
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Footer implements \FooterInterface
{

  /**
   *
   * @var \OutputInterface
   */
  protected $template;

  /**
   *
   * @var \SettingsInterface
   */
  protected $settings;

  /**
   * Starts the class footer
   *
   * @param \OutputInterface $template
   * @param \SettingsInterface $settings
   */
  public function __construct(\OutputInterface $template, \SettingsInterface $settings)
  {
    $this->template = $template;
    $this->settings = $settings;
  }

  /**
   * Generates the footer
   */
  public function createFooter()
  {
    $this->template->set('version', $this->settings->get('version'));
  }
}