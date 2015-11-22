<?php
namespace core\database;

class Builder_Mysqli implements \Builder
{

    private $service_Database;

    private $s_query;

    private $s_limit;

    private $s_group;

    private $s_order;

    private $a_joins;

    private $a_fieldsPre;

    private $a_fields;

    private $a_values;

    private $a_types;

    private $bo_create;

    private $s_resultQuery;

    private $obj_where;

    private $obj_create;

    private $obj_having;

    /**
     * PHP 5 constructor
     *
     * @param DAL $service_Database
     *            The DAL
     */
    public function __construct(\DAL $service_Database)
    {
        \Profiler::profileSystem('core/database/Builder_Mysql.inc.php','Loading query builder');
        
        $this->service_Database = $service_Database;
        if( !$this->service_Database->isConnected() ){
        	$this->service_Database->defaultConnect();
        }
        
        $this->obj_where = new Where_Mysqli();
        $this->obj_create = new Create_Mysqli();
        $this->obj_having = new Having_Mysqli();
        $this->reset();
        
        \Profiler::profileSystem('core/database/Builder_Mysql.inc.php','Loaded query builder');
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->service_Database = null;
        $this->obj_where = null;
        $this->obj_create = null;
        $this->obj_having = null;
    }

    /**
     * Resets the builder
     */
    private function reset()
    {
        $this->s_query = '';
        $this->s_limit = '';
        $this->s_group = '';
        $this->s_order = '';
        $this->a_joins = array();
        $this->a_fieldsPre = array();
        $this->a_fields = array();
        $this->a_values = array();
        $this->a_types = array();
        $this->bo_create = false;
        $this->s_resultQuery = '';
        $this->obj_where->reset();
        $this->obj_create->reset();
        $this->obj_having->reset();
    }
    
    public function __clone(){
        $this->obj_where = new Where_Mysqli();
        $this->obj_create = new Create_Mysqli();
        $this->obj_having = new Having_Mysqli();
        $this->reset();
    }
    
    /**
	 * Returns if the object schould be treated as singleton
	 *
	 * @return boolean True if the object is a singleton
	 */
	public static function isSingleton(){
        return true;
    }

    /**
     * Shows the tables in the current database
     */
    public function showTables()
    {
        $this->bo_create = false;
        
        $this->s_query = 'SHOW TABLES';
    }

    /**
     * Shows the databases that the user has access to
     */
    public function showDatabases()
    {
        $this->bo_create = false;
        
        $this->s_query = 'SHOW DATABASES';
    }

    /**
     * Creates a select statement
     *
     * @param String $s_table
     *            name
     * @param String $s_fields
     *            names sepperated with a ,
     */
    public function select($s_table, $s_fields)
    {
        $this->bo_create = false;
        
        $this->s_query = "SELECT " . $s_fields . " FROM " . DB_PREFIX . $s_table . " ";
        
        return $this;
    }

    /**
     * Creates a insert statement
     *
     * @param String $s_table
     *            name
     * @param array $a_fields
     *            names, also accepts a single value
     * @param array $a_types
     *            types : l (SQL, no parse), i (int) ,d (double) ,s (string) or b (blob), also accepts a single value
     * @param array $a_values
     *            also accepts a single value
     */
    public function insert($s_table, $a_fields, $a_types, $a_values)
    {
        $this->bo_create = false;
        
        if (! is_array($a_fields)) {
            $a_fields = array(
                $a_fields
            );
            $a_types = array(
                $a_types
            );
            $a_values = array(
                $a_values
            );
        }
        
        $this->s_query = "INSERT INTO " . DB_PREFIX . $s_table . " ";
        
        $this->a_fields = $a_fields;
        $this->a_values = $a_values;
        $this->a_types = $a_types;
        
        return $this;
    }

    /**
     * Creates a update statement
     *
     * @param String $s_table
     *            name
     * @param array $a_fields
     *            names, also accepts a single value
     * @param array $a_types
     *            types : l (SQL, no parse), i (int) ,d (double) ,s (string) or b (blob), also accepts a single value
     * @param array $a_values
     *            also accepts a single value
     */
    public function update($s_table, $a_fields, $a_types, $a_values)
    {
        $this->bo_create = false;
        
        if (! is_array($a_fields)) {
            $a_fields = array(
                $a_fields
            );
            $a_types = array(
                $a_types
            );
            $a_values = array(
                $a_values
            );
        }
        
        $this->s_query = "UPDATE " . DB_PREFIX . $s_table . " ";
        $this->a_fields = $a_fields;
        $this->a_values = $a_values;
        $this->a_types = $a_types;
        
        return $this;
    }

    /**
     * Creates a delete statement
     *
     * @param String $s_table
     *            name
     */
    public function delete($s_table)
    {
        $this->bo_create = false;
        
        $this->s_query = "DELETE FROM " . DB_PREFIX . $s_table . " ";
        
        return $this;
    }

    /**
     * Returns the create table generation class
     *
     * @param String $s_table
     *            table name
     * @param Boolean $bo_dropTable
     *            to true to drop the given table before creating it
     * @return Create The create table generation class
     */
    public function getCreate($s_table, $bo_dropTable = false)
    {
        $this->bo_create = true;
        $this->obj_create->setTable($s_table, $bo_dropTable);
        return $this->obj_create;
    }

    /**
     * Adds a inner join between 2 tables
     *
     * @param String $s_table
     *            name
     * @param String $s_field1
     *            from the first table
     * @param String $s_field2
     *            from the second table
     */
    public function innerJoin($s_table, $s_field1, $s_field2)
    {
        $this->a_joins[] = "INNER JOIN " . DB_PREFIX . $s_table . " ON " . $s_field1 . " = " . $s_field2 . " ";
        
        return $this;
    }

    /**
     * Adds a outer join between 2 tables
     *
     * @param String $s_table
     *            name
     * @param String $s_field1
     *            from the first table
     * @param String $s_field2
     *            from the second table
     */
    public function outerJoin($s_table, $s_field1, $s_field2)
    {
        $this->a_joins[] = "OUTER JOIN " . DB_PREFIX . $s_table . " ON " . $s_field1 . " = " . $s_field2 . " ";
        
        return $this;
    }

    /**
     * Adds a left join between 2 tables
     *
     * @param String $s_table
     *            name
     * @param String $s_field1
     *            from the first table
     * @param String $s_field2
     *            from the second table
     */
    public function leftJoin($s_table, $s_field1, $s_field2)
    {
        $this->a_joins[] = "LEFT JOIN " . DB_PREFIX . $s_table . " ON " . $s_field1 . " = " . $s_field2 . " ";
        
        return $this;
    }

    /**
     * Adds a right join between 2 tables
     *
     * @param String $s_table
     *            name
     * @param String $s_field1
     *            from the first table
     * @param String $s_field2
     *            from the second table
     */
    public function rightJoin($s_table, $s_field1, $s_field2)
    {
        $this->a_joins[] = "RIGHT JOIN " . DB_PREFIX . $s_table . " ON " . $s_field1 . " = " . $s_field2 . " ";
        
        return $this;
    }

    /**
     * Returns the where generation class
     *
     * @return Where where generation class
     */
    public function getWhere()
    {
        return $this->obj_where;
    }

    /**
     * Adds a limitation to the query statement
     * Only works on select, update and delete statements
     *
     * @param int $i_limit
     *            of records
     * @param int $i_offset
     *            to start from, default 0 (first record)
     */
    public function limit($i_limit, $i_offset = 0)
    {
        $this->s_limit = "LIMIT " . $i_offset . "," . $i_limit . " ";
        
        return $this;
    }

    /**
     * Groups the results by the given field
     *
     * @param String $s_field            
     */
    public function group($s_field)
    {
        $this->s_group = 'GROUP BY ' . $s_field;
        
        return $this;
    }

    /**
     * Returns the having generation class
     *
     * @return Having having generation class
     */
    public function getHaving()
    {
        return $this->obj_having;
    }

    /**
     * Orders the records in the given order
     *
     * @param String $s_field1
     *            field to order on
     * @param String $s_ordering1
     *            method (ASC|DESC)
     * @param String $s_field2
     *            field to order on, optional
     * @param String $s_ordering2
     *            method (ASC|DESC), optional
     */
    public function order($s_field1, $s_ordering1 = 'ASC', $s_field2 = '', $s_ordering2 = 'ASC')
    {
        $this->s_order = "ORDER BY " . $s_field1 . " " . $s_ordering1;
        if (empty($s_field2))
            $this->s_order .= " ";
        else
            $this->s_order .= "," . $s_field2 . " " . $s_ordering2 . " ";
        
        return $this;
    }

    /**
     * Return the total amount statement for the given field
     *
     * @param String $s_field
     *            field name
     * @param String $s_alias
     *            alias
     * @return String statement
     */
    public function getSum($s_field, $s_alias = '')
    {
        return $this->getSpecialField($s_field, $s_alias, 'SUM');
    }

    /**
     * Return the maximun value statement for the given field
     *
     * @param String $s_field
     *            field name
     * @param String $s_alias
     *            alias, default the field name
     * @return String statement
     */
    public function getMaximun($s_field, $s_alias = '')
    {
        return $this->getSpecialField($s_field, $s_alias, 'MAX');
    }

    /**
     * Return the minimun value statement for the given field
     *
     * @param String $s_field
     *            field name
     * @param String $s_alias
     *            alias
     * @return String statement
     */
    public function getMinimun($s_field, $s_alias = '')
    {
        return $this->getSpecialField($s_field, $s_alias, 'MIN');
    }

    /**
     * Return the average value statement for the given field
     *
     * @param String $s_field
     *            field name
     * @param String $s_alias
     *            alias
     * @return String statement
     */
    public function getAverage($s_field, $s_alias = '')
    {
        return $this->getSpecialField($s_field, $s_alias, 'AVG');
    }

    /**
     * Return statement for counting the number of records on the given field
     *
     * @param String $s_field
     *            field name
     * @param String $s_alias
     *            alias
     * @return String statement
     */
    public function getCount($s_field, $s_alias = '')
    {
        return $this->getSpecialField($s_field, $s_alias, 'COUNT');
    }

    /**
     * Generates the field statements
     *
     * @param String $s_field
     *            field name
     * @param String $s_alias
     *            alias
     * @param String $s_key
     *            statement code
     * @return String statement
     */
    private function getSpecialField($s_field, $s_alias, $s_key)
    {
        if (! empty($s_alias)) {
            $s_alias = 'AS ' . $s_alias . ' ';
        }
        
        return $s_key . '(' . $s_field . ') ' . $s_alias;
    }

    /**
     * Returns the query result
     *
     * @return Service_Database query result as a database object
     */
    public function getResult()
    {
        $a_query = $this->render();
        
        if (count($a_query['values']) == 0) {
            $this->service_Database->query($a_query['query']);
        } else {
            $this->service_Database->queryBinded($a_query['query'], $a_query['types'], $a_query['values']);
        }
        
        return $this->service_Database;
    }

    /**
     * Builds the query
     */
    public function render()
    {
        $this->s_resultQuery = $this->s_query;
        if (! is_array($this->a_fields))
            $this->a_fields = array(
                $this->a_fields
            );
        
        $s_command = strtoupper(substr($this->s_query, 0, strpos($this->s_query, ' ')));
        
        if ($s_command == 'SELECT') {
            $this->addJoins();
            
            $this->addHaving();
            
            $this->addWhere();
            
            $this->addGroup();
            
            $this->addOrder();
            
            $this->addLimit();
        } else 
            if ($s_command == 'UPDATE') {
                $this->addJoins();
                
                $a_data = array();
                $i_num = count($this->a_fields);
                for ($i = 0; $i < $i_num; $i ++) {
                    if ($this->a_types[$i] != 'l') {
                        $a_data[] = $this->a_fields[$i] . ' = ?';
                    } else {
                        $a_data[] = $this->a_fields[$i] . ' = ' . $this->a_values[$i];
                        unset($this->a_values[$i]);
                        unset($this->a_types[$i]);
                    }
                }
                
                $this->s_resultQuery .= ' SET ' . implode(',', $a_data) . ' ';
                
                $this->addGroup();
                
                $this->addHaving();
                
                $this->addWhere();
                
                $this->addLimit();
                
                $this->addLimit();
            } else 
                if ($s_command == 'INSERT') {
                    $a_values = array();
                    $i_num = count($this->a_values);
                    for ($i = 0; $i < $i_num; $i ++) {
                        if ($this->a_types[$i] != 'l') {
                            $a_values[] = '?';
                        } else {
                            $this->a_fields[$i] .= ' = ' . $this->a_values[$i];
                            unset($this->a_values[$i]);
                            unset($this->a_types[$i]);
                        }
                    }
                    $this->s_resultQuery .= '(' . implode(',', $this->a_fields) . ') VALUES (' . implode(',', $a_values) . ') ';
                } else 
                    if ($s_command == 'DELETE') {
                        $this->addWhere();
                        
                        $this->addLimit();
                    } else 
                        if ($s_command == 'SHOW') {
                            $this->addWhere();
                        } else 
                            if ($this->bo_create) {
                                $s_dropTable = $this->obj_create->getDropTable();
                                
                                if ($s_dropTable != '') {
                                    $this->service_Database->query($s_dropTable);
                                }
                                
                                $this->s_resultQuery = $this->obj_create->render();
                            }
        
        $a_data = array(
            'query' => $this->s_resultQuery,
            'values' => $this->a_values,
            'types' => $this->a_types
        );
        $this->reset();
        return $a_data;
    }

    /**
     * Adds the joins
     */
    private function addJoins()
    {
        foreach ($this->a_joins as $s_join) {
            $this->s_resultQuery .= $s_join;
        }
    }

    /**
     * Adds the group by
     */
    private function addGroup()
    {
        $this->s_resultQuery .= $this->s_group . " ";
    }

    /**
     * Adds the having part
     */
    private function addHaving()
    {
        $a_having = $this->obj_having->render();
        if (is_null($a_having))
            return;
        
        $this->a_values = array_merge($this->a_values, $a_having['values']);
        $this->a_types = array_merge($this->a_types, $a_having['types']);
        
        $this->s_resultQuery .= $a_having['having'] . " ";
    }

    /**
     * Adds the where part
     */
    private function addWhere()
    {
        $a_where = $this->obj_where->render();
        if (is_null($a_where))
            return;
        
        $this->a_values = array_merge($this->a_values, $a_where['values']);
        $this->a_types = array_merge($this->a_types, $a_where['types']);
        
        $this->s_resultQuery .= $a_where['where'] . " ";
    }

    /**
     * Adds the limit part
     */
    private function addLimit()
    {
        $this->s_resultQuery .= $this->s_limit;
    }

    /**
     * Adds the order part
     */
    private function addOrder()
    {
        $this->s_resultQuery .= $this->s_order;
    }

    /**
     * Starts a new transaction
     *
     * @throws DBException a transaction is allready active
     */
    public function transaction()
    {
        $this->service_Database->transaction();
        
        return $this;
    }

    /**
     * Commits the current transaction
     *
     * @throws DBException no transaction is active
     */
    public function commit()
    {
        $this->service_Database->commit();
        
        return $this;
    }

    /**
     * Rolls the current transaction back
     *
     * @throws DBException no transaction is active
     */
    public function rollback()
    {
        $this->service_Database->rollback();
        
        return $this;
    }

    /**
     * Returns the DAL
     *
     * @return DAL The DAL
     */
    public function getDatabase()
    {
        return $this->service_Database;
    }

    /**
     * Dumps the current active database to a file
     *
     * @return string The database dump
     */
    public function dumpDatabase()
    {
        $sql = '';
        
        /* Remove constrains */
        $this->service_Database->query("SELECT table_name,column_name,referenced_table_name,referenced_column_name,constraint_name 
            FROM  information_schema.key_column_usage WHERE
            referenced_table_name is not null
            and table_schema = '" . $this->service_Database->getDatabase() . "'");
        
        $a_contrains = array();
        if ($this->service_Database->num_rows() > 0) {
            $a_contrains = $this->service_Database->fetch_assoc();
            
            foreach ($a_contrains as $a_constrain) {
                $sql .= 'ALTER TABLE ' . $a_constrain['table_name'] . ' IF EXISTS DROP FOREIGN KEY ' . $a_constrain['constraint_name'] . ';';
            }
            $sql .= "\n-- --------------------------\n";
        }
        
        $this->service_Database->query('SHOW TABLES');
        $a_tables = $this->service_Database->fetch_row();
        foreach ($a_tables as $s_table) {
        	$s_table = str_replace(DB_PREFIX, '', $s_table);
        	
            $service_Database = $this->getDatabase();
            
            $sql .= $this->dumpTable($s_table[0]);
            $sql .= "\n";
            $sql .= "-- ---------------------------\n\n";
        }
        
        /* Restore constrains */
        if (count($a_contrains) > 0) {
            foreach ($a_contrains as $a_contrain) {
                $sql .= 'ALTER TABLE ' . $a_constrain['table_name'] . ' ADD CONSTRAINT ' . $a_constrain['constraint_name'] . ' FOREIGN KEY ( ' . $a_contrain['column_name'] . ') 
                    REFERENCES ' . $a_contrain['referenced_table_name'] . ' ( ' . $a_contrain['referenced_column_name'] . ' ) ON DELETE RESTRICT ON UPDATE RESTRICT ;' . "\n";
            }
            
            $sql .= "\n-- --------------------------\n";
        }
        
        return $sql;
    }

    protected function dumpTable($s_table)
    {
        /* Table structure */
        $s_sql = "--\n" . '-- Table structure for table ' . DB_PREFIX.$s_table . ".\n--\n";
        $s_structure = $this->service_Database->describe(DB_PREFIX.$s_table, false, true);
        
        /* Table content */
        $this->select($s_table, '*');
        $database = $this->getResult();
        
        /* Get colums */
        $s_sql .= "--\n" . '-- Dumping data for table ' . DB_PREFIX.$s_table . ".\n--\n";
        if ($database->num_rows() == 0) {
            return $s_sql;
        }
        $a_data = $database->fetch_assoc();
        $a_keys = array_keys($a_data[0]);
        
        $a_columns = array();
        foreach ($a_keys as $s_column) {
            $a_columns[] = $s_column;
        }
        $s_insert = 'INSERT INTO ' . $s_table . ' (' . implode(',', $a_columns) . ') VALUES (';
        
        foreach ($a_data as $a_item) {
            $a_values = array();
            foreach ($a_item as $s_key => $s_value) {
                if (is_numeric($s_value)) {
                    $a_values[] = $s_value;
                } else {
                    $a_values[] = "'" . str_replace("'", "\'", $s_value) . "'";
                }
            }
            
            $s_sql .= $s_insert . implode(',', $a_values) . ");\n";
        }
        
        return $s_sql;
    }
}

abstract class QueryConditions_Mysqli
{

    protected $s_query;

    protected $a_types;

    protected $a_values;

    protected $a_keys = array(
        '=' => '=',
        '==' => '=',
        '<>' => '<>',
        '!=' => '<>',
        '<' => '<',
        '>' => '>',
        'LIKE' => 'LIKE',
        'IN' => 'IN',
        'BETWEEN' => 'BETWEEN',
    	'<=' => '<=',
    	'>=' => '>='
    );

    /**
     * Resets the class
     */
    public function reset()
    {
        $this->s_query = '';
        $this->a_types = array();
        $this->a_values = array();
    }

    /**
     * Adds fields with an and relation
     *
     * @param array $a_fields
     *            fields,also accepts a single value
     * @param array $a_types
     *            types : l (SQL, no parse), i (int) ,d (double) ,s (string) or b (blob), also accepts a single value
     * @param array $a_values
     *            also accepts a single value
     * @param array $a_keys
     *            (=|<>|<|>|LIKE|IN|BETWEEN|>=|<=), also accepts a single value. leave empty for =
     * @throws DBException the key is invalid
     */
    public function addAnd($a_fields, $a_types, $a_values, $a_keys = array())
    {
        if (! is_array($a_fields))
            $a_fields = array(
                $a_fields
            );
        if (! is_array($a_types))
            $a_types = array(
                $a_types
            );
        if (! is_array($a_values))
            $a_values = array(
                $a_values
            );
        if (! is_array($a_keys))
            $a_keys = array(
                $a_keys
            );
        
        $i_num = count($a_fields);
        $j = 0;
        for ($i = 0; $i < $i_num; $i ++) {
            (array_key_exists($i, $a_keys) && ! empty($a_keys[$i])) ? $s_key = $a_keys[$i] : $s_key = '=';
            
            $this->addField($a_fields[$i], $a_types[$j], $a_values[$j], $s_key, 'AND');
            if ($s_key == 'BETWEEN') {
                $j ++;
                $this->a_types[] = $a_types[$j];
                $this->a_values[] = $a_values[$j];
            }
            $j ++;
        }
        
        return $this;
    }

    /**
     * Adds fields with an or relation
     *
     * @param array $a_fields
     *            fields,also accepts a single value
     * @param array $a_types
     *            types : l (SQL, no parse), i (int) ,d (double) ,s (string) or b (blob), also accepts a single value
     * @param array $a_values
     *            also accepts a single value
     * @param array $a_keys
     *            (=|<>|<|>|LIKE|IN|BETWEEN), also accepts a single value. leave empty for =
     * @throws DBException the key is invalid
     */
    public function addOr($a_fields, $a_types, $a_values, $a_keys = array())
    {
        if (! is_array($a_fields)) {
            $a_fields = array(
                $a_fields
            );
            $a_types = array(
                $a_types
            );
            $a_values = array(
                $a_values
            );
            $a_keys = array(
                $a_keys
            );
        }
        
        $i_num = count($a_fields);
        $j = 0;
        for ($i = 0; $i < $i_num; $i ++) {
            (array_key_exists($i, $a_keys) && ! empty($a_keys[$i])) ? $s_key = $a_keys[$i] : $s_key = '=';
            
            $this->addField($a_fields[$i], $a_types[$j], $a_values[$j], $s_key, 'OR');
            if ($s_key == 'BETWEEN') {
                $j ++;
                $this->a_types[] = $a_types[$j];
                $this->a_values[] = $a_values[$j];
            }
            $j ++;
        }
        
        return $this;
    }

    /**
     * Adds a field
     *
     * @param String $s_field
     *            field
     * @param array $s_type
     *            type : l (SQL, no parse), i (int) ,d (double) ,s (string) or b (blob), also accepts a single value
     * @param String $s_value            
     * @param String $s_key
     *            key (=|<>|<|>|LIKE|IN|BETWEEN)
     * @param String $s_command
     *            command (AND|OR)
     * @throws DBException the key is invalid
     */
    protected function addField($s_field, $s_type, $s_value, $s_key, $s_command)
    {
        if (! array_key_exists($s_key, $this->a_keys))
            throw new \DBException('Unknown where key ' . $s_key . '.');
        
        if (! empty($this->s_query))
            $this->s_query .= ' ' . $s_command . ' ';
        
        if ($s_type == 'l') {
            if ($s_key != 'IN')
                $this->s_query .= $s_field . ' ' . $this->a_keys[$s_key] . ' ' . $s_value . ' ';
            else
                $this->s_query .= $s_field . ' IN (' . $s_value . ') ';
            
            return;
        } else 
            if ($s_key == 'IN') {
                $a_data = array();
                
                foreach ($s_value as $item) {
                    switch ($s_type) {
                        case 'i':
                            if (! is_int($item))
                                throw new \DBException('Invalid IN value. expected int but got ' . gettype($item) . '.');
                            $a_data[] = $item;
                            break;
                        
                        case 'i':
                            if (! is_float($item))
                                throw new \DBException('Invalid IN value. expected float but got ' . gettype($item) . '.');
                            $a_data[] = $item;
                            break;
                        
                        case 'i':
                            if (! is_string($item))
                                throw new \DBException('Invalid IN value. expected string but got ' . gettype($item) . '.');
                            $a_data[] = "'" . $item . "'";
                            break;
                        
                        default:
                            throw new \DBException('Invalid IN type. Only ints, floats and strings are supported.');
                    }
                }
                
                $this->s_query .= $s_field . ' IN (' . implode(',', $a_data) . ') ';
                return;
            }
        
        $this->a_types[] = $s_type;
        $this->a_values[] = $s_value;
        
        if ($s_key == 'LIKE')
            $this->s_query .= $s_field . ' LIKE CONCAT("%",?,"%") ';
        else 
            if ($s_key == 'BETWEEN')
                $this->s_query .= $s_field . ' BETWEEN ? AND ? ';
            else
                $this->s_query .= $s_field . ' ' . $this->a_keys[$s_key] . ' ? ';
    }
}

class Where_Mysqli extends QueryConditions_Mysqli implements \Where
{

    protected $a_builder;

    /**
     * Resets the class Where_Mysqli
     */
    public function reset()
    {
        parent::reset();
        $this->a_builder = null;
    }

    /**
     * Starts a sub where part
     */
    public function startSubWhere()
    {
        $this->s_query .= '(';
        
        return $this;
    }

    /**
     * Ends a sub where part
     */
    public function endSubWhere()
    {
        $this->s_query .= ')';
        
        return $this;
    }

    /**
     * Adds a sub query
     *
     * @param Builder $obj_builder
     *            object
     * @param String $s_field            
     * @param String $s_key
     *            (=|<>|LIKE|IN|BETWEEN)
     * @param String $s_command
     *            command (AND|OR)
     * @throws DBException the key is invalid
     * @throws DBException the command is invalid
     */
    public function addSubQuery($obj_builder, $s_field, $s_key, $s_command)
    {
        if (! ($obj_builder instanceof Builder))
            throw new \DBException("Can only add object of the type Builder.");
        
        if (! array_key_exists($s_key, $this->a_keys))
            throw new \DBException('Unknown where key ' . $s_key . '.');
        
        $s_command = strtoupper($s_command);
        if (! in_array($s_command, array(
            'OR',
            'AND'
        )))
            throw new \DBException('Unknown where command ' . $s_command . '.  Only AND & OR are supported.');
        
        $this->a_builder = array(
            'object' => $obj_builder,
            'field' => $s_field,
            'key' => $s_key,
            'command' => $s_command
        );
        
        return $this;
    }

    /**
     * Renders the where
     *
     * @return array where
     */
    public function render()
    {
        if (empty($this->s_query))
            return null;
        
        if (! is_null($this->a_builder)) {
            $obj_builder = $this->a_builder['object']->render();
            $this->s_query .= $this->a_builder['command'] . ' ' . $this->a_builder['field'] . ' ' . $this->a_builder['key'] . ' (' . $obj_builder['query'] . ')';
            $this->a_values[] = $obj_builder['values'];
            $this->a_types[] = $obj_builder['types'];
        }
        
        return array(
            'where' => ' WHERE ' . $this->s_query,
            'values' => $this->a_values,
            'types' => $this->a_types
        );
    }
}

class Having_Mysqli extends QueryConditions_Mysqli implements \Having
{

    /**
     * Starts a sub having part
     */
    public function startSubHaving()
    {
        $this->s_query .= '(';
        
        return $this;
    }

    /**
     * Ends a sub having part
     */
    public function endSubHaving()
    {
        $this->s_query .= ')';
        
        return $this;
    }

    /**
     * Renders the having
     *
     * @return array having
     */
    public function render()
    {
        if (empty($this->s_query))
            return null;
        
        return array(
            'having' => ' HAVING ' . $this->s_query,
            'values' => $this->a_values,
            'types' => $this->a_types
        );
    }
}

class Create_Mysqli implements \Create
{

    private $s_query;

    private $a_createRows;

    private $a_createTypes;

    private $s_engine;

    private $s_dropTable;

    /**
     * Resets the class Create_Mysql
     */
    public function reset()
    {
        $this->s_query = '';
        $this->a_createRows = array();
        $this->a_createTypes = array();
        $this->s_engine = '';
        $this->s_dropTable = '';
    }

    /**
     * Creates a table
     *
     * @param String $s_table
     *            name
     * @param Boolean $bo_dropTable
     *            to true to drop the given table before creating it
     */
    public function setTable($s_table, $bo_dropTable = false)
    {
        if ($bo_dropTable) {
            $this->s_dropTable = 'DROP TABLE IF EXISTS ' . DB_PREFIX . $s_table;
        }
        
        $this->s_query = "CREATE TABLE " . DB_PREFIX . $s_table . " (";
        
        return $this;
    }

    /**
     * Adds a field to the create stament
     *
     * @param String $s_field
     *            field name
     * @param String $s_type
     *            field type (database type!)
     * @param int $i_length
     *            length of the field, only for length fields
     * @param String $s_default
     *            default value
     * @param String $bo_signed
     *            to true for unsigned value, default signed
     * @param String $bo_null
     *            to true for NULL allowed
     * @param String $bo_autoIncrement
     *            to true for auto increment
     */
    public function addRow($s_field, $s_type, $i_length = -1, $s_default = '', $bo_signed = true, $bo_null = false, $bo_autoIncrement = false)
    {
        ($bo_signed) ? $s_signed = ' SIGNED ' : $s_signed = ' UNSIGNED ';
        
        $s_null = $this->checkNull($bo_null);
        if ($bo_null && $s_default == "") {
            $s_default = ' DEFAULT NULL ';
        } else 
            if ($s_default != "") {
                $s_default = " DEFAULT '" . $s_default . "' ";
            }
        
        ($bo_autoIncrement) ? $s_autoIncrement = ' AUTO_INCREMENT' : $s_autoIncrement = '';
        $s_type = strtoupper($s_type);
        
        if (in_array($s_type, array(
            'VARCHAR',
            'SMALLINT',
            'MEDIUMINT',
            'INT',
            'BIGINT'
        ))) {
            $this->a_createRows[$s_field] = $s_field . ' ' . strtoupper($s_type) . '(' . $i_length . ') ' . $s_default . $s_null . $s_autoIncrement;
        } else 
            if ($s_type == 'DECIMAL') {
                $this->a_createRows[$s_field] = $s_field . ' DECIMAL(10,0) ' . $s_default . $s_null . $s_autoIncrement;
            } else {
                $this->a_createRows[$s_field] = $s_field . ' ' . strtoupper($s_type) . ' ' . $s_default . $s_null . $s_autoIncrement;
            }
        
        return $this;
    }

    /**
     * Adds an enum field to the create stament
     *
     * @param String $s_field
     *            field name
     * @param array $a_values
     *            values
     * @param String $s_default
     *            default value
     * @param String $bo_null
     *            to true for NULL allowed
     */
    public function addEnum($s_field, $a_values, $s_default, $bo_null = false)
    {
        $a_valuesPre = array();
        foreach ($a_values as $s_value) {
            $a_valuesPre[] = "'" . $s_value . "'";
        }
        
        $s_null = $this->checkNull($bo_null);
        if ($bo_null && empty($s_default)) {
            $s_default = ' DEFAULT NULL ';
        } else {
            $s_default = " DEFAULT '" . $s_default . "' ";
        }
        
        $this->a_createRows[$s_field] = $s_field . ' ENUM(' . implode(',', $a_valuesPre) . ') ' . $s_default . $s_null;
        
        return $this;
    }

    /**
     * Adds a set field to the create stament
     *
     * @param String $s_field
     *            field name
     * @param array $a_values
     *            values
     * @param String $s_default
     *            default value
     * @param String $bo_null
     *            to true for NULL allowed
     */
    public function addSet($s_field, $s_values, $s_default, $bo_null = false)
    {
        $a_valuesPre = array();
        foreach ($a_values as $s_value) {
            $a_valuesPre[] = "'" . $s_value . "'";
        }
        
        $s_null = $this->checkNull($bo_null);
        if ($bo_null && empty($s_default)) {
            $s_default = ' DEFAULT NULL ';
        } else {
            $s_default = " DEFAULT '" . $s_default . "' ";
        }
        
        $this->a_createRows[$s_field] = $s_field . ' SET(' . implode(',', $a_valuesPre) . ') ' . $s_default . $s_null;
        
        return $this;
    }

    /**
     * Adds a primary key to the given field
     *
     * @param String $s_field
     *            field name
     * @throws DBException If the field is unknown or if the primary key is allready set
     */
    public function addPrimary($s_field)
    {
        if (! array_key_exists($s_field, $this->a_createRows)) {
            throw new \DBException("Can not add primary key on unknown field $s_field.");
        }
        if (array_key_exists('primary', $this->a_createTypes)) {
            throw new \DBException("Only one primary key pro table is allowed.");
        }
        
        $this->a_createTypes['primary'] = 'PRIMARY KEY (' . $s_field . ')';
        
        return $this;
    }

    /**
     * Adds a index to the given field
     *
     * @param String $s_field
     *            field name
     * @throws DBException If the field is unknown
     */
    public function addIndex($s_field)
    {
        if (! array_key_exists($s_field, $this->a_createRows)) {
            throw new \DBException("Can not add index key on unknown field $s_field.");
        }
        
        $this->a_createTypes[] = 'KEY ' . $s_field . ' (' . $s_field . ')';
        
        return $this;
    }

    /**
     * Sets the given fields as unique
     *
     * @param String $s_field
     *            field name
     * @throws DBException If the field is unknown
     */
    public function addUnique($s_field)
    {
        if (! array_key_exists($s_field, $this->a_createRows)) {
            throw new \DBException("Can not add unique key on unknown field $s_field.");
        }
        
        $this->a_createTypes[] = 'UNIQUE KEY ' . $s_field . ' (' . $s_field . ')';
        
        return $this;
    }

    /**
     * Sets full text search on the given field
     *
     * @param String $s_field
     *            field name
     * @throws DBException If the field is unknown
     * @throws DBException If the field type is not VARCHAR and not TEXT.
     */
    public function addFullTextSearch($s_field)
    {
        if (! array_key_exists($s_field, $this->a_createRows)) {
            throw new \DBException("Can not add full text search on unknown field $s_field.");
        }
        if (stripos($this->a_createRows[$s_field], 'VARCHAR') === false && stripos($this->a_createRows[$s_field], 'TEXT') === false) {
            throw new \DBException("Full text search can only be added on VARCHAR or TEXT fields.");
        }
        
        $this->a_createTypes[] = 'FULLTEXT KEY ' . $s_field . ' (' . $s_field . ')';
        
        $this->s_engine = 'ENGINE=MyISAM';
        
        return $this;
    }

    /**
     * Parses the null setting
     *
     * @param boolean $bo_null
     *            null setting
     * @return String null text
     */
    private function checkNull($bo_null)
    {
        $s_null = ' NOT NULL ';
        if ($bo_null) {
            $s_null = ' NULL ';
        }
        
        return $s_null;
    }

    /**
     * Returns the drop table setting
     *
     * @return String drop table command. Empty string for not dropping
     */
    public function getDropTable()
    {
        return $this->s_dropTable;
    }

    /**
     * Creates the query
     *
     * @return String query
     */
    public function render()
    {
        $this->s_query .= "\n" . implode(",\n", $this->a_createRows);
        if (count($this->a_createTypes) > 0) {
            $this->s_query .= ",\n";
            $this->s_query .= implode(",\n", $this->a_createTypes);
        }
        $this->s_query .= "\n)" . $this->s_engine;
        
        return $this->s_query;
    }
}