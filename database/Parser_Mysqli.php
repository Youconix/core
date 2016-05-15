<?php

namespace youconix\core\database;

class Parser_Mysqli implements \DatabaseParser {

  /**
   * 
   * @var \Builder
   */
  protected $builder;
  protected $a_keys;

  public function __construct(\Builder $builder) {
    $this->builder = $builder;
  }

  public function createAddTables($document) {
    $a_tables = [];
    $this->a_keys = [];

    foreach ($document->children() as $table) {
      $a_tables[] = $this->parseTable($table);
    }

    $s_dump = implode(";\n\n", $a_tables);
    $s_dump .= ";\n\n\n";

    foreach ($this->a_keys as $key) {
      $s_dump .= "ALTER TABLE " . $key->table . " ADD CONSTRAINT " . $key->name . " FOREIGN KEY (" . $key->col . ") REFERENCES " . str_replace('.', '(', $key->ref) . ");\n";
    }

    return $s_dump;
  }

  public function updateTables($document, $bo_remove = true) {
    $a_tables = [];

    foreach ($document->children() as $table) {
      $s_tableName = (string) $table['name'];
      $current = $this->builder->describe($s_tableName);
      $declaration = $this->parseTable($table);

      if (count($current) == 0) {
	$a_tables[] = $declaration;
      } else {
	$s_table = $this->parseUpdateTable($table['name'], $current, $declaration, $bo_remove);
	if( !empty($s_table) ){
	  $a_tables[] = $s_table;
	}
      }
    }

    print_r($a_tables);
  }

  protected function parseUpdateTable($s_table, $current, $declaration, $bo_remove) {
    $s_fields = substr($declaration, (strpos($declaration, '(') + 1));
    $s_fields = substr($s_fields, 0, strrpos($s_fields, ')'));
    $a_rules = explode(",\n", trim($s_fields));

    $s_primary;
    $a_keys = [];

    $a_declarationRules = [];
    print_r($a_rules);
    print_r($current);
    foreach ($a_rules AS $rule) {
      $rule = trim($rule);

      if ((substr(strtolower($rule), 0, 5) == 'index') || (substr(strtolower($rule), 0, 6) == 'unique')) {
	preg_match('/^([a-zA-Z]+)\(([a-zA-Z_][a-zA-Z0-9_]*)/',trim($rule),$matches);
	$s_type = $matches[1];
	$s_field = $matches[2];
	
	$a_keys[$s_field] = $s_type;
      
	continue;
      }

      $field = substr($rule, 0, strpos($rule, ' '));

      if (strtolower($field) == 'primary') {
	$s_primary = substr($rule, (strpos($rule, '(') + 1));
	$s_primary = substr($s_primary, 0, strrpos($s_primary, ')'));
      } else {
	$a_declarationRules[trim($field)] = trim($rule);
      }
    }

    $s_outputPre = 'ALTER TABLE ' . $s_table;
    $s_output = '';

    if ($bo_remove) {
      foreach ($current AS $field => $value) {
	if (!array_key_exists($field, $a_declarationRules)) {
	  $s_output .= $s_outputPre . ' DROP COLUMN ' . $field . ";\n";
	}
      }
    }
    foreach ($a_declarationRules AS $field => $rule) {
      $s_currentRule = trim($this->parseField($current[$field]));
      if (substr($s_currentRule, -1) == ',') {
	$s_currentRule = substr($s_currentRule, 0, -1);
      }

      if ($s_currentRule != $rule) {
	$s_output .= $s_outputPre . ' CHANGE ' . $field . ' ' . $rule . ";\n";
      }
    }
    
    $s_output .= $this->checkIndexes($a_keys, $current, $s_table,$s_primary);
    
    if( !empty($s_output) ){
      $s_output = 'LOCK TABLES '.$s_table.' WRITE'.";\n".
      $s_output.
      "UNLOCK TABLES;\n";
    }
    return $s_output;
  }
  
  protected function checkIndexes($declaration,$current,$s_table,$s_primary){
    if( count($declaration) == 0 ){
      return '';
    }
    
    $bo_primaryFound = false;
    foreach($current AS $field => $value){
      if( !array_key_exists($field,$declaration) ){
	$s_output = 'ALTER TABLE '.$s_table.' DROP INDEX '.$field.";\n";
	continue;
      }
      
      $s_decKey = '';
      switch(strtolower($value['COLUMN_KEY'])){
	case 'pri' :
	  $bo_primaryFound = true;
	  if( $field != $s_primary ){
	    $s_output .= 'ALTER TABLE '.$s_table.' DROP PRIMARY KEY, ADD PRIMARY KEY('.$s_primary.");\n";
	  }
	  continue;
	case 'uni' :
	  $s_decKey = 'UNIQUE';
	  break;
	case 'index' :
	  $s_decKey = 'INDEX';
	  break;
      }
    }
    
    /*
     * if( strtolower($declaration[$field]) == 'uni' ){
	    continue;
	  }
	  $s_output .= 'DROP INDEX ON '.$field.' ON '.$s_table.";\n";
	  if( $declaration[$field] == '')  
	    $s_output .= 'ALTER TABLE '.$s_table.' ADD INDEX '.$field.";\n";
	  }
	  break;
     */
    
    echo($s_primary);
      //print_r($declaration);
      //print_r($current);
    
    $s_output = '';
    
    // Check primary
      if( strtolower($current[$s_primary]['COLUMN_KEY']) != 'pri' ){
	echo('primary changed');
      }
    
    return $s_output;
  }

  protected function parseTable($table) {
    $s_tableName = (string) $table['name'];
    $s_table = 'CREATE TABLE IF NOT EXISTS ' . $s_tableName . " (\n";
    $s_primary = '';
    $a_indexes = [];

    foreach ($table->children() as $field) {
      if ($field->getName() == 'foreign_key') {
	$key = new \stdClass();
	$key->table = $s_tableName;
	$key->col = $field->col;
	$key->ref = $field->ref;
	$key->name = $s_tableName . '_' . $field->col;
	$this->a_keys[] = $key;
	continue;
      }
      if ($field->getName() == 'index') {
	$a_indexes[] = $field;
	continue;
      }
      if ($field->getName() != 'field') {
	continue;
      }

      $a_field = $this->analyseField($field);
      if ($a_field['COLUMN_KEY'] == 'PRI') {
	$s_primary = $a_field['COLUMN_NAME'];
      }
      $s_table .= $this->parseField($a_field);
    }

    $s_table .= $this->parseIndexes($a_indexes);
    $s_table .= "\t PRIMARY KEY (" . $s_primary . ")\n ) ENGINE=InnoDB DEFAULT CHARSET=latin1";

    return $s_table;
  }

  protected function parseField($field_data) {    
    $s_field = $field_data['COLUMN_NAME'] . ' ' . strtoupper($field_data['COLUMN_TYPE']);
    ( $field_data['IS_NULLABLE'] == 'NO' ) ? $s_field .= ' NOT NULL' : $s_field .= ' NULL';

    return "\t" . $s_field . ",\n";
  }

  protected function analyseField($field) {
    $a_data = [
	'COLUMN_NAME' => (string)$field['name'],
	'COLUMN_DEFAULT' => '',
	'IS_NULLABLE' => 'YES',
	'DATA_TYPE' => '',
	'CHARACTER_MAXIMUM_LENGTH' => '',
	'COLUMN_KEY' => '',
	'EXTRA' => '',
	'COLUMN_TYPE' => ''
    ];

    $a_values = [];
    $bo_unsigned = false;

    $s_fieldType = (string) $field['type'];
    ($s_fieldType == 'enum') ? $s_fieldSize = - 1 : $s_fieldSize = (int) $field['size'];

    foreach ($field->children() as $item) {
      switch ($item->getName()) {
	case 'notnull':
	  $a_data['IS_NULLABLE'] = 'NO';
	  break;
	case 'unsigned':
	  $bo_unsigned = true;
	  break;
	case 'key':
	  $a_data['COLUMN_KEY'] = 'PRI';
	  $a_data['IS_NULLABLE'] = 'NO';
	  break;
	case 'default':
	  $a_data['COLUMN_DEFAULT'] = $item['value'];
	  break;
	case 'value':
	  $a_values[] = "'" . $item . "'";
	  break;
      }
    }

    $s_fieldType = str_replace([
	'integer',
	'float',
	'string'
	    ], [
	'int',
	'double',
	'varchar'
	    ], $s_fieldType);

    if (in_array($s_fieldType, [
		'smallint',
		'int',
		'bigint',
		'decimal',
		'double',
		'varchar'
	    ])) {
      $a_data['COLUMN_TYPE'] = $s_fieldType .= '(' . $s_fieldSize . ')';
    } else
    if ($s_fieldType == 'enum') {
      $a_data['COLUMN_TYPE'] = '(' . implode(',', $a_values) . ')';
    }
    if ($bo_unsigned) {
      $a_data['COLUMN_TYPE'] .= ' unsigned';
    }

    return $a_data;
  }

  protected function parseIndexes($a_indexes) {
    $s_indexes = '';

    foreach ($a_indexes as $index) {
      $s_key = 'INDEX';
      foreach ($index->children() as $item) {
	switch ($item->getName()) {
	  case 'unique':
	    $s_key = 'UNIQUE';
	    break;
	  case 'fulltext':
	    $s_key = 'FULLTEXT';
	    break;
	}
      }
      $s_indexes .= "\t" . $s_key . "(" . $index['name'] . "),\n";
    }

    return $s_indexes;
  }

}
