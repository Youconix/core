<?php

interface Display
{

    /**
     * Generates the HTML code
     *
     * @param \Output $view
     */
    public function generate(\Output $view);
}