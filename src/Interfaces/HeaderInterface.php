<?php

interface HeaderInterface {

  /**
   * Generates the header
   * 
   * @param \OutputInterface $template
   */
  public function createHeader(\OutputInterface $template);
}
