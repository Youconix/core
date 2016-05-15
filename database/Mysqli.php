<?php
namespace youconix\core\database;

/**
 * Database connection layer for MySQL
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Mysqli extends \youconix\core\database\GeneralDAL
{

    /**
     * Connects to the database with the preset login data
     */
    public function defaultConnect()
    {
        \Profiler::profileSystem('core/database/Mysqli.inc.php', 'Connecting to database');
        $this->bo_connection = false;
        
        $s_type = $this->settings->get('settings/SQL/type');
        $this->connection($this->settings->get('settings/SQL/' . $s_type . '/username'), $this->settings->get('settings/SQL/' . $s_type . '/password'), $this->settings->get('settings/SQL/' . $s_type . '/database'), $this->settings->get('settings/SQL/' . $s_type . '/host'), $this->settings->get('settings/SQL/' . $s_type . '/port'));
        
        $this->reset();
        
        \Profiler::profileSystem('core/database/Mysqli.inc.php', 'Connected to database');
    }

    /**
     * Checks if the given connection-data is correct
     *
     * @static
     *
     * @param string $s_username
     *            The username
     * @param string $s_password
     *            The password
     * @param string $s_database
     *            The database
     * @param string $s_host
     *            The host name, default 127.0.0.1 (localhost)
     * @param int $i_port
     *            The port
     * @return boolean True if the data is correct, otherwise false
     */
    public static function checkLogin($s_username, $s_password, $s_database, $s_host = '127.0.0.1', $i_port = -1)
    {
        if ($i_port == - 1)
            $i_port = '';
        
        if (empty($s_username) || empty($s_host) || empty($s_database))
            return false;
            
            /* connect to the database */
        if ($i_port == - 1 || $i_port == '') {
            $obj_connection = new \mysqli($s_host, $s_username, $s_password, $s_database);
        } else {
            $obj_connection = new \mysqli($s_host, $s_username, $s_password, $s_database, $i_port);
        }
        if ($obj_connection->connect_errno) {
            return false;
        }
        
        $obj_connection->close();
        unset($obj_connection);
        
        return true;
    }

    /**
     * Connects to the set database
     *
     * @param string $s_username
     *            The username
     * @param string $s_password
     *            The password
     * @param string $s_database
     *            The database
     * @param string $s_host
     *            The host name, default 127.0.0.1 (localhost)
     * @param int $i_port
     *            The port
     * @throws DBException If connection to the database failed
     */
    public function connection($s_username, $s_password, $s_database, $s_host = '127.0.0.1', $i_port = -1)
    {
        if ($this->bo_connection)
            return;
        
        $this->bo_connection = false;
        
        /* connect to the database */
        if ($i_port == - 1 || $i_port == '') {
            $this->obj_connection = new \mysqli($s_host, $s_username, $s_password, $s_database);
        } else {
            $this->obj_connection = new \mysqli($s_host, $s_username, $s_password, $s_database, $i_port);
        }
        if ($this->obj_connection->connect_errno) {
            /* Error connecting */
            throw new \DBException("Error connection to database " . $s_database . '. Check the connection-settings');
        }
        
        $this->s_lastDatabase = $s_database;
        $this->bo_connection = true;
    }

    /**
     * Closes the connection to the mysql database
     */
    public function connectionEnd()
    {
        if ($this->bo_connection) {
            @$this->obj_connection->close();
            $this->bo_connection = false;
        }
    }

    /**
     * Escapes the given data for save use in queries
     *
     * @param string $s_data
     *            The data that need to be escaped
     * @return string The escaped data
     */
    public function escape_string($s_data)
    {
        $s_data = htmlentities($s_data, ENT_QUOTES);
        
        return $this->obj_connection->real_escape_string($s_data);
    }

    /**
     * Excequetes the given query on the selected database
     *
     * @param string $s_query            
     * @return \DAL
     */
    public function prepare($s_query)
    {
        if (is_null($s_query) || empty($s_query)) {
            throw new \Exception("Illegal query call " . $s_query);
        }
        
        $this->reset();
        
        if (is_null($this->obj_connection)) {
            throw new \DBException("No connection to the database");
        }
        
        $this->obj_query = null;
        
        if (preg_match_all('/:[a-zA-Z-_0-9]+/s', $s_query, $a_matches)) {
            $i = 0;
            foreach ($a_matches[0] as $field) {
                $s_query = str_replace($field, '?', $s_query);
                $this->a_bindedKeys[$field] = $i;
                $i ++;
            }
        }
        $this->s_query = $s_query;
        
        $this->query = $this->obj_connection->stmt_init();
        
        if (! $this->query->prepare($s_query)) {
            throw new \DBException("Query failed : " . $this->obj_connection->error . '.\n' . $s_query);
        }
        
        return $this;
    }

    /**
     * Runs the query
     *
     * @throws \DBException If the query fails
     */
    public function exequte()
    {
        $this->bindParams($this->a_bindedTypes, $this->a_bindedValues);
        $res = $this->query->execute();
        
        if ($res === false) {
            throw new \DBException("Query failed : " . $this->obj_connection->error . '.\n' . $this->s_query);
        }
        
        preg_match('/^([a-zA-Z]+)\s/', $this->s_query, $a_matches);
        $s_command = strtoupper($a_matches[1]);
        if ($s_command == 'SELECT' || $s_command == 'SHOW' || $s_command == 'ANALYZE' || $s_command == 'OPTIMIZE' || $s_command == 'REPAIR') {
            $this->a_result = null; // force cleaning
            
            $this->query->store_result();
            
            $obj_meta = $this->query->result_metadata();
            $a_params = [];
            while ($field = $obj_meta->fetch_field()) {
                $a_params[] = &$this->a_result[$field->name];
            }
            
            call_user_func_array([
                $this->query,
                'bind_result'
            ], $a_params);
            
            $this->obj_query = $this->query;
        } else 
            if ($s_command == 'INSERT') {
                $this->i_id = $this->query->insert_id;
            } else 
                if ($s_command == 'UPDATE' || $s_command == 'DELETE') {
                    $this->i_affected_rows = $this->query->affected_rows;
                }
    }

    /**
     * Bind de waardes aan de query
     *
     * @param array $a_types
     *            The parameter types
     * @param array $a_values
     *            The paramenter values
     * @throws \DBException
     */
    protected function bindParams($a_types, $a_values)
    {
        $params = [
            0 => ''
        ];
        $num = count($a_types);
        
        if ($num == 0) {
            return;
        }
        
        for ($i = 0; $i < $num; $i ++) {
            $type = $a_types[$i];
            
            $params[0] .= $type;
            $params[] = $a_values[$i];
        }
        
        $callable = [
            $this->query,
            'bind_param'
        ];
        call_user_func_array($callable, $this->refValues($params));
    }

    /**
     * Callback to bind the parameters to the query
     *
     * @param array $a_arguments
     *            arguments
     * @return array arguments
     */
    protected function refValues($a_arguments)
    {
        if (strnatcmp(phpversion(), '5.3') >= 0) { // Reference is required for PHP 5.3+
            $a_refs = [];
            foreach ($a_arguments as $s_key => $value)
                $a_refs[$s_key] = &$a_arguments[$s_key];
            return $a_refs;
        }
        return $a_arguments;
    }

    /**
     * Returns the number of results from the last excequeted query
     *
     * @return int The number of results
     * @throws DBException when no SELECT-query was excequeted
     */
    public function num_rows()
    {
        if (is_null($this->obj_query)) {
            throw new \DBException("Trying to count the numbers of results on a non-SELECT-query");
        }
        
        return $this->obj_query->num_rows;
    }

    /**
     * Returns the result from the query with the given row and field
     *
     * @param
     *            int The row
     * @param
     *            string The field
     * @return string The content of the requested result-field
     * @throws DBException if no SELECT-query was excequeted
     */
    public function result($i_row, $s_field)
    {
        $this->checkSelect();
        
        if ($i_row > $this->num_rows() || $i_row < 0)
            throw new \DBException("Trying to get data from a not existing field");
        
        $this->resetPointer();
        
        $i_rows = $this->num_rows();
        if ($i_row >= $i_rows) {
            throw new \DBException("Unable to fetch row " . $i_row . " Only " . $i_rows . " are present");
        }
        
        $this->obj_query->data_seek($i_row);
        $a_data = $this->fetch_assoc();
        
        if (! array_key_exists($s_field, $a_data[0])) {
            throw new \DBException("Unable to fetch the unknown field " . $s_field);
        }
        
        return $a_data[0][$s_field];
    }

    /**
     * Returns the results of the query in a numeric array
     *
     * @return array data-set
     * @throws DBException when no SELECT-query was excequeted
     */
    public function fetch_row()
    {
        $this->checkSelect();
        
        $this->resetPointer();
        
        $a_result = [];
        while ($this->obj_query->fetch()) {
            $i_field = 0;
            $a_temp = [];
            foreach ($this->a_result as $s_key => $value) {
                $a_temp[$i_field] = $value;
                $i_field ++;
            }
            $a_result[] = $a_temp;
        }
        
        return $a_result;
    }

    /**
     * Returns the results of the query in a associate and numeric array
     *
     * @return array data-set
     * @throws DBException when no SELECT-query was excequeted
     */
    public function fetch_array()
    {
        $this->checkSelect();
        
        $this->resetPointer();
        
        $a_result = [];
        while ($this->obj_query->fetch()) {
            $i_field = 0;
            $a_temp = [];
            foreach ($this->a_result as $s_key => $value) {
                $a_temp[$i_field] = $value;
                $a_temp[$s_key] = $value;
                $i_field ++;
            }
            $a_result[] = $a_temp;
        }
        
        return $a_result;
    }

    /**
     * Returns the results of the query in a associate array
     *
     * @return array data-set
     * @throws DBException when no SELECT-query was excequeted
     */
    public function fetch_assoc()
    {
        $this->checkSelect();
        
        $this->resetPointer();
        
        $a_result = [];
        
        while ($this->obj_query->fetch()) {
            $a_temp = [];
            foreach ($this->a_result as $s_key => $value) {
                $a_temp[$s_key] = $value;
            }
            $a_result[] = $a_temp;
        }
        
        return $a_result;
    }

    /**
     * Returns the results of the query in a associate array with the given field as counter-key
     *
     * @param
     *            string The field that is the counter-key
     * @return array data-set sorted on the given key
     * @throws DBException when no SELECT-query was excequeted
     */
    public function fetch_assoc_key($s_key)
    {
        $this->checkSelect();
        
        $this->resetPointer();
        
        $a_result = [];
        while ($this->obj_query->fetch()) {
            $a_temp = [];
            foreach ($this->a_result as $s_fieldkey => $value) {
                $a_temp[$s_fieldkey] = $value;
                
                if ($s_fieldkey == $s_key)
                    $s_rowKey = $value;
            }
            $a_result[$s_rowKey] = $a_temp;
        }
        
        return $a_result;
    }

    /**
     * Returns the results of the query as a object-array
     *
     * @return object data-set
     * @throws Exception when no SELECT-query was excequeted
     */
    public function fetch_object()
    {
        $this->checkSelect();
        
        $this->resetPointer();
        
        $a_temp = [];
        while ($obj_res = $this->obj_query->fetch_object()) {
            $a_temp[] = $a_obj;
        }
        
        return $a_temp;
    }

    /**
     * Starts a new transaction
     *
     * @throws DBException a transaction is allready active
     */
    public function transaction()
    {
        if ($this->bo_transaction) {
            throw new \DBException("Can not start new transaction. Call commit() or rollback() first.");
        }
        
        $this->obj_connection->query("START TRANSACTION");
        $this->bo_transaction = true;
    }

    /**
     * Commits the current transaction
     *
     * @throws DBException no transaction is active
     */
    public function commit()
    {
        if (! $this->bo_transaction) {
            throw new \DBException("Can not commit transaction. Call transaction() first.");
        }
        
        $this->obj_connection->query("COMMIT");
        $this->bo_transaction = false;
    }

    /**
     * Rolls the current transaction back
     *
     * @throws DBException no transaction is active
     */
    public function rollback()
    {
        if (! $this->bo_transaction) {
            throw new \DBException("Can not rollback transaction. Call transaction() first.");
        }
        
        $this->obj_connection->query("ROLLBACK");
        $this->bo_transaction = false;
    }

    /**
     * Changes the active database to the given one
     *
     * @param string $s_database
     *            database
     * @throws DBException if the new databases does not exist or no access
     */
    public function useDB($s_database)
    {
        try {
            $this->query("USE " . $s_database);
            
            $this->s_lastDatabase = $s_database;
        } catch (\Exception $ex) {
            throw new \Exception("Database-change failed .\n" . $s_database);
        }
    }

    /**
     * Checks if a database exists and if the user has access to it
     *
     * @param string $s_database            
     * @return boolean if the database exists, otherwise false
     */
    public function databaseExists($s_database)
    {
        $this->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $s_database . "'");
        if ($this->num_rows() > 0)
            return true;
        
        return false;
    }

    /**
     * Checks if the last query was a SELECT-query
     *
     * @throws DBException when no SELECT-query was excequeted
     */
    protected function checkSelect()
    {
        if ($this->obj_query == null) {
            throw new \DBException("Database-error : trying to get data on a non-SELECT-query");
        }
    }

    /**
     * Describes the table structure
     *
     * @param string $s_table
     *            The table name
     * @param
     *            string The structure
     * @param
     *            boolean Set to true to add "IF NOT EXISTS"
     * @param
     *            boolean Set to true to add dropping the table first
     * @throws \DBException If the table does not exists
     */
    public function describe($s_table, $bo_addNotExists = false, $bo_dropTable = false)
    {
        $this->query('SHOW CREATE TABLE ' . $s_table);
        if ($this->num_rows() == 0) {
            throw new \DBException('Table ' . $s_table . ' does not exist.');
        }
        
        $a_table = $this->fetch_row();
        $s_description = $a_table[0][1];
        if ($bo_dropTable) {
            $s_description = 'DROP TABLE IF EXISTS ' . $s_table . ";\n" . $s_description;
        }
        if ($bo_addNotExists) {
            $s_description = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $s_description);
        }
        return $s_description;
    }

    /**
     * Resets the data result pointer
     */
    protected function resetPointer()
    {
        if (! is_null($this->obj_query))
            $this->obj_query->data_seek(0);
    }

    /**
     * Clears the previous result set
     */
    protected function clearResult()
    {
        if (! is_null($this->obj_query)) {
            $this->obj_query->free_result();
            $this->obj_query->close();
            
            $this->obj_query = null;
        }
    }
}
