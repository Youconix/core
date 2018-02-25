<?php

interface Display
{

    /**
     * Generates the HTML code
     *
     * @param \OutputInterface $view
     */
    public function generate(\OutputInterface $view);
}