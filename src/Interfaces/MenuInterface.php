<?php

interface MenuInterface
{

    /**
     * Generates the menu
     * 
     * @param \OutputInterface $template
     */
    public function generateMenu(\OutputInterface $template);
}