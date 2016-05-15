<?php
interface DatabaseParser {
    public function createAddTables($document);
    
    public function updateTables($document);
}