<?php

interface Menu
{

    /**
     * Generates the menu
     * 
     * @param \Output $template
     */
    public function generateMenu(\Output $template);
}