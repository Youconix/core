<?php
interface DatabaseParserInterface {
    public function createAddTables($document);
    
    public function updateTables($document);
}