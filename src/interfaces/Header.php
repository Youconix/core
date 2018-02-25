<?php

interface Header {

  /**
   * Generates the header
   * 
   * @param \Output $template
   */
  public function createHeader(\Output $template);
}
