<?php
namespace youconix\core\database;

/**
 * PostgreSql query builder
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 2.0
 */
class Builder_PostgreSql implements \Builder
{

    /**
     *
     * @var \DAL
     */
    protected $service_Database;

    protected $s_query;

    protected $s_limit;

    protected $s_group;

    protected $s_order;

    protected $a_joins;

    protected $a_fieldsPre;

    protected $a_fields;

    protected $a_values;

    protected $a_types;

    protected $bo_create;

    protected $bo_upsert = false;

    protected $bo_supportUpsert = false;

    protected $s_resultQuery;

    protected $s_upsert;

    protected $obj_where;

    protected $obj_create;

    protected $obj_having;

    /**
     * PHP 5 constructor
     *
     * @param \DAL $service_Database            
     */
    public function __construct(\DAL $service_Database)
    {
        \Profiler::profileSystem('core/database/Builder_PostgreSql.inc.php', 'Loading query builder');
        
        $this->service_Database = $service_Database;
        if (! $this->service_Database->isConnected()) {
            $this->service_Database->defaultConnect();
        }
        
        $this->obj_where = new Where_PostgreSql($this);
        $this->obj_create = new Create_PostgreSql($this);
        $this->obj_having = new Having_PostgreSql($this);
        $this->reset();
        
        \Profiler::profileSystem('core/database/Builder_PostgreSql.inc.php', 'Loaded query builder');
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
    protected function reset()
    {
        $this->s_query = '';
        $this->s_limit = '';
        $this->s_group = '';
        $this->s_order = '';
        $this->a_joins = [];
        $this->a_fieldsPre = [];
        $this->a_fields = [];
        $this->a_values = [];
        $this->a_types = [];
        $this->bo_create = false;
        $this->s_resultQuery = '';
        $this->s_upsert = '';
        $this->obj_where->reset();
        $this->obj_create->reset();
        $this->obj_having->reset();
    }

    public function __clone()
    {
        $this->obj_where = new Where_PostgreSql($this);
        $this->obj_create = new Create_PostgreSql($this);
        $this->obj_having = new Having_PostgreSql($this);
        $this->reset();
    }

    /**
     * Returns if the object schould be treated as singleton
     *
     * @return bool True if the object is a singleton
     */
    public static function isSingleton()
    {
        return true;
    }

    /**
     * Shows the tables in the current database
     */
    public function showTables()
    {
        $this->bo_create = false;
        
        $this->s_query = "SELECT * FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'";
    }

    /**
     * Shows the databases that the user has access to
     */
    public function showDatabases()
    {
        $this->bo_create = false;
        
        $this->s_query = 'SELECT table_schema,table_name FROM information_schema.tables ORDER BY table_schema,table_name';
    }

    /**
     * Creates a select statement
     *
     * @param string $s_table
     *            name
     * @param string $s_fields
     *            names sepperated with a ,
     * @return \Builder
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
     * @param string $s_table            
     * @return \Builder
     */
    public function insert($s_table)
    {
        $this->bo_create = false;
        
        $this->s_query = "INSERT INTO " . DB_PREFIX . $s_table . " ";
        
        $this->a_fields = [];
        $this->a_values = [];
        $this->a_types = [];
        
        return $this;
    }

    /**
     * Creates a update statement
     *
     * @param string $s_table            
     * @return \Builder
     */
    public function update($s_table)
    {
        $this->bo_create = false;
        
        $this->s_query = "UPDATE " . DB_PREFIX . $s_table . " ";
        $this->a_fields = [];
        $this->a_values = [];
        $this->a_types = [];
        
        return $this;
    }

    /**
     * Creates a delete statement
     *
     * @param string $s_table
     *            name
     * @return \Builder
     */
    public function delete($s_table)
    {
        $this->bo_create = false;
        
        $this->s_query = "DELETE FROM " . DB_PREFIX . $s_table . " ";
        
        return $this;
    }

    /**
     * Updates the given record with the unique field or inserts a new one if it does not exist
     *
     * @param string $s_table            
     * @param string $s_unique            
     * @return \Builder
     */
    public function upsert($s_table, $s_unique)
    {
        $this->bo_create = false;
        
        if ($this->service_Database->getVersion() < 9.5) {
            throw new \DBException('You need at least PostgreSql version 9.5 to use upsert.');
        }
        
        $this->s_upsert = $s_unique;
        
        return $this->insert($s_table);
    }

    /**
     * Binds a string value
     *
     * @param string $s_key
     *            The field name
     * @param string $s_value
     *            The value
     * @return \Builder
     */
    public function bindString($s_key, $s_value)
    {
        $this->a_fields[] = $s_key;
        $this->a_values[$s_key] = $s_value;
        $this->a_types[$s_key] = 's';
        
        return $this;
    }

    /**
     * Binds an integer value
     *
     * @param string $s_key
     *            The field name
     * @param float $i_value
     *            The value
     * @return \Builder
     */
    public function bindInt($s_key, $i_value)
    {
        $this->a_fields[] = $s_key;
        $this->a_values[$s_key] = $i_value;
        $this->a_types[$s_key] = 'i';
        
        return $this;
    }

    /**
     * Binds a float value
     *
     * @param string $s_key
     *            The field name
     * @param float $fl_value
     *            The value
     * @return \Builder
     */
    public function bindFloat($s_key, $fl_value)
    {
        $this->a_fields[] = $s_key;
        $this->a_values[$s_key] = $fl_value;
        $this->a_types[$s_key] = 'f';
        
        return $this;
    }

    /**
     * Binds a binary value
     *
     * @param string $s_key
     *            The field name
     * @param binary $value
     *            The value
     * @return \Builder
     */
    public function bindBlob($s_key, $value)
    {
        $this->a_fields[] = $s_key;
        $this->a_values[$s_key] = $value;
        $this->a_types[$s_key] = 'b';
        
        return $this;
    }

    /**
     * Adds a literal statement
     *
     * @param string $s_key
     *            The field name
     * @param string $statement            
     * @return \Builder
     */
    public function bindLiteral($s_key, $statement)
    {
        $this->a_fields[] = $s_key;
        $this->a_values[$s_key] = $statement;
        $this->a_types[$s_key] = 'l';
        
        return $this;
    }

    /**
     * Returns the create table generation class
     *
     * @param string $s_table
     *            table name
     * @param bool $bo_dropTable
     *            to true to drop the given table before creating it
     * @return \Create The create table generation class
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
     * @param string $s_table
     *            name
     * @param string $s_field1
     *            from the first table
     * @param string $s_field2
     *            from the second table
     */
    public function innerJoin($s_table, $s_field1, $s_field2)
    {
        $this->a_joins[] = "INNER JOIN " . DB_PREFIX . $s_table . " ON (" . $s_field1 . " = " . $s_field2 . ") ";
        
        return $this;
    }

    /**
     * Adds a outer join between 2 tables
     *
     * @param string $s_table
     *            name
     * @param string $s_field1
     *            from the first table
     * @param string $s_field2
     *            from the second table
     */
    public function outerJoin($s_table, $s_field1, $s_field2)
    {
        $this->a_joins[] = "OUTER JOIN " . DB_PREFIX . $s_table . " ON (" . $s_field1 . " = " . $s_field2 . ") ";
        
        return $this;
    }

    /**
     * Adds a left join between 2 tables
     *
     * @param string $s_table
     *            name
     * @param string $s_field1
     *            from the first table
     * @param string $s_field2
     *            from the second table
     */
    public function leftJoin($s_table, $s_field1, $s_field2)
    {
        $this->a_joins[] = "LEFT JOIN " . DB_PREFIX . $s_table . " ON (" . $s_field1 . " = " . $s_field2 . ") ";
        
        return $this;
    }

    /**
     * Adds a right join between 2 tables
     *
     * @param string $s_table
     *            name
     * @param string $s_field1
     *            from the first table
     * @param string $s_field2
     *            from the second table
     */
    public function rightJoin($s_table, $s_field1, $s_field2)
    {
        $this->a_joins[] = "RIGHT JOIN " . DB_PREFIX . $s_table . " ON (" . $s_field1 . " = " . $s_field2 . ") ";
        
        return $this;
    }

    /**
     * Returns the where generation class
     *
     * @return \Where where generation class
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
     * @param string $s_field            
     */
    public function group($s_field)
    {
        $this->s_group = 'GROUP BY ' . $s_field;
        
        return $this;
    }

    /**
     * Returns the having generation class
     *
     * @return \Having having generation class
     */
    public function getHaving()
    {
        return $this->obj_having;
    }

    /**
     * Orders the records in the given order
     *
     * @param string $s_field1
     *            field to order on
     * @param string $s_ordering1
     *            method (ASC|DESC)
     * @param string $s_field2
     *            field to order on, optional
     * @param string $s_ordering2
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
     * @param string $s_field
     *            field name
     * @param string $s_alias
     *            alias
     * @return string statement
     */
    public function getSum($s_field, $s_alias = '')
    {
        return $this->getSpecialField($s_field, $s_alias, 'SUM');
    }

    /**
     * Return the maximun value statement for the given field
     *
     * @param string $s_field
     *            field name
     * @param string $s_alias
     *            alias, default the field name
     * @return string statement
     */
    public function getMaximun($s_field, $s_alias = '')
    {
        return $this->getSpecialField($s_field, $s_alias, 'MAX');
    }

    /**
     * Return the minimun value statement for the given field
     *
     * @param string $s_field
     *            field name
     * @param string $s_alias
     *            alias
     * @return string statement
     */
    public function getMinimun($s_field, $s_alias = '')
    {
        return $this->getSpecialField($s_field, $s_alias, 'MIN');
    }

    /**
     * Return the average value statement for the given field
     *
     * @param string $s_field
     *            field name
     * @param string $s_alias
     *            alias
     * @return string statement
     */
    public function getAverage($s_field, $s_alias = '')
    {
        return $this->getSpecialField($s_field, $s_alias, 'AVG');
    }

    /**
     * Return statement for counting the number of records on the given field
     *
     * @param string $s_field
     *            field name
     * @param string $s_alias
     *            alias
     * @return string statement
     */
    public function getCount($s_field, $s_alias = '')
    {
        return $this->getSpecialField($s_field, $s_alias, 'COUNT');
    }

    /**
     * Generates the field statements
     *
     * @param string $s_field
     *            field name
     * @param string $s_alias
     *            alias
     * @param string $s_key
     *            statement code
     * @return string statement
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
        
        $this->service_Database->prepare($a_query['query']);
        
        foreach ($a_query['types'] as $s_field => $type) {
            switch ($type) {
                case 's':
                    $this->service_Database->bindString($s_field, $a_query['values'][$s_field]);
                    break;
                
                case 'i':
                    $this->service_Database->bindInt($s_field, $a_query['values'][$s_field]);
                    break;
                
                case 'f':
                    $this->service_Database->bindFloat($s_field, $a_query['values'][$s_field]);
                    break;
                
                case 'b':
                    $this->service_Database->bindBlob($s_field, $a_query['values'][$s_field]);
                    break;
            }
        }
        
        $this->service_Database->exequte();
        
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
        
        switch ($s_command) {
            case 'SELECT':
                $this->addJoins();
                
                $this->addHaving();
                
                $this->addWhere();
                
                $this->addGroup();
                
                $this->addOrder();
                
                $this->addLimit();
                break;
            case 'UPDATE':
                $this->addJoins();
                
                $a_data = [];
                foreach ($this->a_fields as $field) {
                    if ($this->a_types[$field] != 'l') {
                        $a_data[] = $field . ' = :' . $field;
                    } else {
                        $a_data[] = $field . ' = ' . $this->a_values[$field];
                        unset($this->a_values[$field]);
                        unset($this->a_types[$field]);
                    }
                }
                
                $this->s_resultQuery .= ' SET ' . implode(',', $a_data) . ' ';
                
                $this->addGroup();
                
                $this->addHaving();
                
                $this->addWhere();
                
                $this->addLimit();
                
                $this->addLimit();
                break;
            case 'INSERT':
                $a_values = [];
                foreach ($this->a_fields as $field) {
                    if ($this->a_types[$field] != 'l') {
                        $a_values[] = ':' . $field;
                    } else {
                        $field .= ' = ' . $this->a_values[$field];
                        unset($this->a_values[$field]);
                        unset($this->a_types[$field]);
                    }
                }
                $this->s_resultQuery .= '(' . implode(',', $this->a_fields) . ') VALUES (' . implode(',', $a_values) . ') ';
                
                if ($this->bo_upsert) {
                    $this->s_resultQuery .= 'ON CONFLICT DO UPDATE SET ' . $this->s_upsert . '=excluded.' . $this->s_upsert;
                }
                break;
            case 'DELETE':
                $this->addWhere();
                
                $this->addLimit();
                break;
            case 'SHOW':
                $this->addWhere();
                break;
            default:
                if ($this->bo_create) {
                    $s_dropTable = $this->obj_create->getDropTable();
                    
                    if ($s_dropTable != '') {
                        $this->service_Database->query($s_dropTable);
                    }
                    
                    $this->s_resultQuery = $this->obj_create->render();
                }
                break;
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
     * @throws \DBException a transaction is allready active
     */
    public function transaction()
    {
        $this->service_Database->transaction();
        
        return $this;
    }

    /**
     * Commits the current transaction
     *
     * @throws \DBException no transaction is active
     */
    public function commit()
    {
        $this->service_Database->commit();
        
        return $this;
    }

    /**
     * Rolls the current transaction back
     *
     * @throws \DBException no transaction is active
     */
    public function rollback()
    {
        $this->service_Database->rollback();
        
        return $this;
    }

    /**
     * Returns the DAL
     *
     * @return \DAL The DAL
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
        
        $a_contrains = [];
        if ($this->service_Database->num_rows() > 0) {
            $a_contrains = $this->service_Database->fetch_assoc();
            
            foreach ($a_contrains as $a_constrain) {
                $sql .= 'ALTER TABLE ' . $a_constrain['table_name'] . ' IF EXISTS DROP FOREIGN KEY ' . $a_constrain['constraint_name'] . ';';
            }
            $sql .= "\n-- --------------------------\n";
        }
        
        $this->service_Database->query("SELECT * FROM pg_catalog.pg_tables WHERE schemaname != 'pg_catalog' AND schemaname != 'information_schema'");
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
        $s_sql = "--\n" . '-- Table structure for table ' . DB_PREFIX . $s_table . ".\n--\n";
        $s_structure = $this->service_Database->describe(DB_PREFIX . $s_table, false, true);
        
        /* Table content */
        $this->select($s_table, '*');
        $database = $this->getResult();
        
        /* Get colums */
        $s_sql .= "--\n" . '-- Dumping data for table ' . DB_PREFIX . $s_table . ".\n--\n";
        if ($database->num_rows() == 0) {
            return $s_sql;
        }
        $a_data = $database->fetch_assoc();
        $a_keys = array_keys($a_data[0]);
        
        $a_columns = [];
        foreach ($a_keys as $s_column) {
            $a_columns[] = $s_column;
        }
        $s_insert = 'INSERT INTO ' . $s_table . ' (' . implode(',', $a_columns) . ') VALUES (';
        
        foreach ($a_data as $a_item) {
            $a_values = [];
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

    /**
     * Returns the table description
     *
     * @param string $s_table            
     * @return array
     */
    public function describe($s_table)
    {
        $this->service_Database->prepare('SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = :database AND TABLE_NAME = :table');
        $this->service_Database->bindString('database', $this->service_Database->getDatabase());
        $this->service_Database->bindString('table', DB_PREFIX . $s_table);
        $this->service_Database->exequte();
        
        $a_data = $this->service_Database->fetch_assoc();
        return $a_data;
    }

    /**
     * Returns the fields description
     *
     * @param string $s_table
     * @return array
     */
    public function decribeFields($s_table)
    {
        $a_descriptionRaw = $this->describe($s_table);
        
        $a_description = [];
        foreach ($a_descriptionRaw as $row) {
            $a_description[$row['COLUMN_NAME']] = array(
                'type' => $row['DATA_TYPE'],
                'null' => ($row['IS_NULLABLE'] != 'NO'),
                'primary' => ($row['COLUMN_KEY'] == 'PRI'),
                'max' => $row['CHARACTER_MAXIMUM_LENGTH'],
                'default' => $row['COLUMN_DEFAULT'],
                'set' => []
            );
            
            if ($row['DATA_TYPE'] == 'enum') {
                $field = str_replace(array(
                    'enum(',
                    ')'
                ), array(
                    '',
                    ''
                ), $row['COLUMN_TYPE']);
                $field = explode(',', $field);
                foreach ($field as $item) {
                    $a_description[$row['COLUMN_NAME']]['set'][] = str_replace(array(
                        "'",
                        '"'
                    ), array(
                        '',
                        ''
                    ), $item);
                }
            }
        }
        
        return $a_description;
    }
}

abstract class QueryConditions_PostgreSql
{
    /**
     *
     * @var \Builder_PostgreSql
     */
    protected $parent;

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
    
    public function __construct(Builder_PostgreSql $parent){
        $this->parent = $parent;
    }

    /**
     * Resets the class
     */
    public function reset()
    {
        $this->s_query = '';
        $this->a_types = [];
        $this->a_values = [];
    }

    /**
     * Binds a string value
     *
     * @param string $s_field
     *            The field name
     * @param string $s_value
     *            The value
     * @param string $s_type
     *            The type (AND|OR), leave empty for AND
     * @param string $s_key
     *            (=|<>|<|>|LIKE|IN|BETWEEN). leave empty for =
     * @return \Builder
     */
    public function bindString($s_field, $s_value, $s_type = 'AND', $s_key = '=')
    {
        $this->bind($s_field, $s_value, $s_type, $s_key, 's');
        
        return $this;
    }

    /**
     * Binds an integer value
     *
     * @param string $s_field
     *            The field name
     * @param float $i_value
     *            The value
     * @param string $s_type
     *            The type (AND|OR), leave empty for AND
     * @param string $s_key
     *            (=|<>|<|>|LIKE|IN|BETWEEN). leave empty for =
     * @return \Builder
     */
    public function bindInt($s_field, $i_value, $s_type = 'AND', $s_key = '=')
    {
        $this->bind($s_field, $i_value, $s_type, $s_key, 'i');
        
        return $this;
    }

    /**
     * Binds a float value
     *
     * @param string $s_field
     *            The field name
     * @param float $fl_value
     *            The value
     * @param string $s_type
     *            The type (AND|OR), leave empty for AND
     * @param string $s_key
     *            (=|<>|<|>|LIKE|IN|BETWEEN). leave empty for =
     * @return \Builder
     */
    public function bindFloat($s_field, $fl_value, $s_type = 'AND', $s_key = '=')
    {
        $this->bind($s_field, $fl_value, $s_type, $s_key, 'f');
        
        return $this;
    }

    /**
     * Binds a binary value
     *
     * @param string $s_field
     *            The field name
     * @param binary $value
     *            The value
     * @param string $s_type
     *            The type (AND|OR), leave empty for AND
     * @param string $s_key
     *            (=|<>|<|>|LIKE|IN|BETWEEN). leave empty for =
     * @return \Builder
     */
    public function bindBlob($s_field, $value, $s_type = 'AND', $s_key = '=')
    {
        $this->bind($s_field, $value, $s_type, $s_key, 'b');
        
        return $this;
    }

    /**
     * Adds a literal statement
     *
     * @param string $s_field
     *            The field name
     * @param string $statement            
     * @param string $s_type
     *            The type (AND|OR), leave empty for AND
     * @param string $s_key
     *            (=|<>|<|>|LIKE|IN|BETWEEN). leave empty for =
     * @return \Builder
     */
    public function bindLiteral($s_field, $statement, $s_type = 'AND', $s_key = '=')
    {
        $this->bind($s_field, $s_value, $s_type, $s_key, 'l');
        
        return $this;
    }

    /**
     * Binds a field
     *
     * @param string $s_field
     *            The field name
     * @param mixed $value            
     * @param string $s_glue
     *            The glue type (AND|OR)
     * @param string $s_key
     *            (=|<>|<|>|LIKE|IN|BETWEEN)
     * @param string $s_type
     *            The bind type (s|i|f|b|l)
     */
    private function bind($s_field, $value, $s_glue, $s_key, $s_type)
    {
        $s_glue = strtoupper($s_glue);
        if (! in_array($s_glue, [
            'AND',
            'OR'
        ] )) {
            $s_glue = 'AND';
        }
        
        $s_key = strtoupper($s_key);
        if (! array_key_exists($s_key, $this->a_keys)) {
            $s_key = '=';
        }
        $s_key = $this->a_keys[$s_key];
        
        if ($this->s_query != '') {
            $this->s_query .= ' ' . $s_glue . ' ';
        }
        
        if ($s_type == 'l') {
            $this->s_query .= $s_field . ' = ' . $value;
            return;
        }
        
        if ($s_key == 'BETWEEN') {
            $this->s_query .= $s_field . ' BETWEEN :' . $s_field . '_1 AND :' . $s_field . '_2';
            $this->a_values[$s_field . '_1'] = $value[0];
            $this->a_types[$s_field . '_1'] = $s_type[0];
            
            $this->a_values[$s_field . '_2'] = $value[1];
            $this->a_types[$s_field . '_2'] = $s_type[0];
        } else 
            if ($s_key == 'IN') {
                $a_fields = [];
                for ($i = 0; $i < count($values); $i ++) {
                    $a_fields[] = ':' . $s_field . '_' . $i;
                    $this->a_types[$s_field . '_' . $i] = $s_type;
                    $this->a_values[$s_field . '_' . $i] = $value[$i];
                }
                
                $this->s_query .= $s_field . ' IN (' . implode(',', $a_fields) . ')';
            } else {
                $this->s_query .= $s_field . ' = :' . $s_field;
                $this->a_values[$s_field] = $value;
                $this->a_types[$s_field] = $s_type;
            }
    }
    
    /**
     * Returns the query result (parent)
     *
     * @return DAL query result as a database object
     */
    public function getResult(){
        return $this->parent->getResult();
    }
}

class Where_PostgreSql extends QueryConditions_PostgreSql implements \Where
{

    protected $a_builder;

    /**
     * Resets the class Where_PostgreSql
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
     * @param string $s_field            
     * @param string $s_key
     *            (=|<>|LIKE|IN|BETWEEN)
     * @param string $s_command
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

class Having_PostgreSql extends QueryConditions_PostgreSql implements \Having
{
    /**
     *
     * @var \Builder_PostgreSql
     */
    protected $parent;
    
    public function __construct(Builder_PostgreSql $parent){
        $this->parent = $parent;
    }

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
    
    /**
     * Returns the query result (parent)
     *
     * @return DAL query result as a database object
     */
    public function getResult(){
        return $this->parent->getResult();
    }
}

class Create_PostgreSql implements \Create
{

    private $s_query;

    private $a_createRows;

    private $a_createTypes;

    private $s_engine;

    private $s_dropTable;
    
    /**
     *
     * @var \Builder_PostgreSql
     */
    protected $parent;
    
    public function __construct(Builder_PostgreSql $parent){
        $this->parent = $parent;
    }

    /**
     * Resets the class Create_PostgreSql
     */
    public function reset()
    {
        $this->s_query = '';
        $this->a_createRows = [];
        $this->a_createTypes = [];
        $this->s_engine = '';
        $this->s_dropTable = '';
    }

    /**
     * Creates a table
     *
     * @param string $s_table
     *            name
     * @param bool $bo_dropTable
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
     * @param string $s_field
     *            field name
     * @param string $s_type
     *            field type (database type!)
     * @param int $i_length
     *            length of the field, only for length fields
     * @param string $s_default
     *            default value
     * @param string $bo_signed
     *            to true for unsigned value, default signed
     * @param string $bo_null
     *            to true for NULL allowed
     * @param string $bo_autoIncrement
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
     * @param string $s_field
     *            field name
     * @param array $a_values
     *            values
     * @param string $s_default
     *            default value
     * @param string $bo_null
     *            to true for NULL allowed
     */
    public function addEnum($s_field, $a_values, $s_default, $bo_null = false)
    {
        $a_valuesPre = [];
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
     * @param string $s_field
     *            field name
     * @param array $a_values
     *            values
     * @param string $s_default
     *            default value
     * @param string $bo_null
     *            to true for NULL allowed
     */
    public function addSet($s_field, $s_values, $s_default, $bo_null = false)
    {
        $a_valuesPre = [];
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
     * @param string $s_field
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
     * @param string $s_field
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
     * @param string $s_field
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
     * @param string $s_field
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
     * @param bool $bo_null
     *            null setting
     * @return string null text
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
     * @return string drop table command. Empty string for not dropping
     */
    public function getDropTable()
    {
        return $this->s_dropTable;
    }

    /**
     * Creates the query
     *
     * @return string query
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
    
    /**
     * Returns the query result (parent)
     *
     * @return DAL query result as a database object
     */
    public function getResult(){
        return $this->parent->getResult();
    }
}