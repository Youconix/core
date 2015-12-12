<?php

interface Builder
{

    /**
     * Creates the builder
     *
     * @param DAL $service_Database
     *            The DAL
     */
    public function __construct(\DAL $service_Database);

    /**
     * Returns if the object schould be treated as singleton
     *
     * @return boolean True if the object is a singleton
     */
    public static function isSingleton();

    /**
     * Shows the tables in the current database
     */
    public function showTables();

    /**
     * Shows the databases that the user has access to
     */
    public function showDatabases();

    /**
     * Creates a select statement
     *
     * @param string $s_table
     *            name
     * @param string $s_fields
     *            names sepperated with a ,
     */
    public function select($s_table, $s_fields);

    /**
     * Creates a insert statement
     *
     * @param string $s_table
     *            name
     * @param array $a_fields
     *            names, also accepts a single value
     * @param array $a_types
     *            types : l (SQL, no parse), i (int) ,d (double) ,s (string) or b (blob), also accepts a single value
     * @param array $a_values
     *            also accepts a single value
     */
    public function insert($s_table, $a_fields, $a_types, $a_values);

    /**
     * Creates a update statement
     *
     * @param string $s_table
     *            name
     * @param array $a_fields
     *            names, also accepts a single value
     * @param array $a_types
     *            types : l (SQL, no parse), i (int) ,d (double) ,s (string) or b (blob), also accepts a single value
     * @param array $a_values
     *            also accepts a single value
     */
    public function update($s_table, $a_fields, $a_types, $a_values);

    /**
     * Creates a delete statement
     *
     * @param string $s_table
     *            name
     */
    public function delete($s_table);

    /**
     * Returns the create table generation class
     *
     * @param string $s_table
     *            table name
     * @param boolean $bo_dropTable
     *            to true to drop the given table before creating it
     * @return \Create The create table generation class
     */
    public function getCreate($s_table, $bo_dropTable);

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
    public function innerJoin($s_table, $s_field1, $s_field2);

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
    public function outerJoin($s_table, $s_field1, $s_field2);

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
    public function leftJoin($s_table, $s_field1, $s_field2);

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
    public function rightJoin($s_table, $s_field1, $s_field2);

    /**
     * Returns the where generation class
     *
     * @return \Where where generation class
     */
    public function getWhere();

    /**
     * Adds a limitation to the query statement
     * Only works on select, update and delete statements
     *
     * @param int $i_limit
     *            of records
     * @param int $i_offset
     *            to start from, default 0 (first record)
     */
    public function limit($i_limit, $i_offset = 0);

    /**
     * Groups the results by the given field
     *
     * @param string $s_field            
     */
    public function group($s_field);

    /**
     * Returns the having generation class
     *
     * @return \Having having generation class
     */
    public function getHaving();

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
    public function order($s_field1, $s_ordering1 = 'ASC', $s_field2 = '', $s_ordering2 = 'ASC');

    /**
     * Return the total amount statement for the given field
     *
     * @param string $s_field
     *            field name
     * @param string $s_alias
     *            alias, default the field name
     * @return string statement
     */
    public function getSum($s_field, $s_alias = '');

    /**
     * Return the maximun value statement for the given field
     *
     * @param string $s_field
     *            field name
     * @param string $s_alias
     *            alias, default the field name
     * @return string statement
     */
    public function getMaximun($s_field, $s_alias = '');

    /**
     * Return the minimun value statement for the given field
     *
     * @param string $s_field
     *            field name
     * @param string $s_alias
     *            alias, default the field name
     * @return string statement
     */
    public function getMinimun($s_field, $s_alias = '');

    /**
     * Return the average value statement for the given field
     *
     * @param string $s_field
     *            field name
     * @param string $s_alias
     *            alias, default the field name
     * @return string statement
     */
    public function getAverage($s_field, $s_alias = '');

    /**
     * Return statement for counting the number of records on the given field
     *
     * @param string $s_field
     *            field name
     * @param string $s_alias
     *            alias, default the field name
     * @return string statement
     */
    public function getCount($s_field, $s_alias = '');

    /**
     * Returns the query result
     *
     * @return DAL query result as a database object
     */
    public function getResult();

    /**
     * Builds the query
     */
    public function render();

    /**
     * Starts a new transaction
     *
     * @throws \DBException a transaction is allready active
     */
    public function transaction();

    /**
     * Commits the current transaction
     *
     * @throws \DBException no transaction is active
     */
    public function commit();

    /**
     * Rolls the current transaction back
     *
     * @throws \DBException no transaction is active
     */
    public function rollback();

    /**
     * Returns the DAL
     *
     * @return \DAL The DAL
     */
    public function getDatabase();

    /**
     * Dumps the current active database to a file
     *
     * @return string The database dump
     */
    public function dumpDatabase();
}

interface Where
{

    /**
     * Resets the class Where
     */
    public function reset();

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
     *            (=|<>|<|>|LIKE|IN|BETWEEN), also accepts a single value. leave empty for =
     * @throws \DBException the key is invalid
     */
    public function addAnd($a_fields, $a_types, $a_values, $a_keys = array());

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
     * @throws \DBException the key is invalid
     */
    public function addOr($a_fields, $a_types, $a_values, $a_keys = array());

    /**
     * Starts a sub where part
     */
    public function startSubWhere();

    /**
     * Ends a sub where part
     */
    public function endSubWhere();

    /**
     * Adds a sub query
     *
     * @param \Builder $obj_builder
     *            object
     * @param string $s_field            
     * @param string $s_key
     *            (=|<>|LIKE|IN|BETWEEN)
     * @param string $s_command
     *            command (AND|OR)
     * @throws \DBException the key is invalid
     * @throws \DBException the command is invalid
     */
    public function addSubQuery($obj_builder, $s_field, $s_key, $s_command);

    /**
     * Renders the where
     *
     * @return array where
     */
    public function render();
}

interface Having
{

    /**
     * Resets the class Having
     */
    public function reset();

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
     *            (=|<>|<|>|LIKE|IN|BETWEEN), also accepts a single value. leave empty for =
     * @throws \DBException the key is invalid
     */
    public function addAnd($a_fields, $a_types, $a_values, $a_keys);

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
     * @throws \DBException the key is invalid
     */
    public function addOr($a_fields, $a_types, $a_values, $a_keys);

    /**
     * Starts a sub having part
     */
    public function startSubHaving();

    /**
     * Ends a sub having part
     */
    public function endSubHaving();

    /**
     * Renders the having
     *
     * @return array having
     */
    public function render();
}

interface Create
{

    public function reset();

    /**
     * Creates a table
     *
     * @param string $s_table
     *            name
     * @param boolean $bo_dropTable
     *            to true to drop the given table before creating it
     */
    public function setTable($s_table, $bo_dropTable = false);

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
     *            to true for signed value, default unsigned
     * @param string $bo_null
     *            to true for NULL allowed
     * @param string $bo_autoIncrement
     *            to true for auto increment
     */
    public function addRow($s_field, $s_type, $i_length, $s_default = '', $bo_signed = false, $bo_null = false, $bo_autoIncrement = false);

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
    public function addEnum($s_field, $a_values, $s_default, $bo_null = false);

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
    public function addSet($s_field, $s_values, $s_default, $bo_null = false);

    /**
     * Adds a primary key to the given field
     *
     * @param string $s_field
     *            field name
     * @throws \DBException If the field is unknown or if the primary key is allready set
     */
    public function addPrimary($s_field);

    /**
     * Adds a index to the given field
     *
     * @param string $s_field
     *            field name
     * @throws \DBException If the field is unknown
     */
    public function addIndex($s_field);

    /**
     * Sets the given fields as unique
     *
     * @param string $s_field
     *            field name
     * @throws \DBException If the field is unknown
     */
    public function addUnique($s_field);

    /**
     * Sets full text search on the given field
     *
     * @param string $s_field
     *            field name
     * @throws \DBException If the field is unknown
     * @throws \DBException If the field type is not VARCHAR and not TEXT.
     */
    public function addFullTextSearch($s_field);

    /**
     * Returns the drop table setting
     *
     * @return string drop table command. Empty string for not dropping
     */
    public function getDropTable();

    /**
     * Creates the query
     *
     * @return string query
     */
    public function render();
}