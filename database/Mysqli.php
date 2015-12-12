<?php

namespace core\database;

/**
 * Miniature-happiness is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Miniature-happiness is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Miniature-happiness. If not, see <http://www.gnu.org/licenses/>.
 *
 * Database connection layer for MySQL
 *
 * This file is part of Miniature-happiness
 *
 * @copyright Youconix
 * @author Rachelle Scheijen
 * @since 1.0
 */
class Mysqli implements \DAL {
	private $service_Settings;
	private $obj_connection;
	private $obj_query;
	private $bo_connection;
	private $s_lastDatabase;
	private $i_id;
	private $i_affected_rows;
	private $bo_transaction = false;
	
	/**
	 * Loads the binded parameters class
	 *
	 * @param \Settings $service_Settings
	 *        	The settings service
	 */
	public function __construct(\Settings $service_Settings) {
		$this->service_Settings = $service_Settings;
	}
	
	/**
	 * Destructor
	 */
	public function __destruct() {
		if ($this->bo_connection) {
			$this->connectionEnd ();
		}
		
		$this->service_Settings = null;
		$this->obj_connection = null;
		$this->obj_query = null;
		$this->bo_connection = null;
		$this->s_lastDatabase = null;
		$this->i_id = null;
		$this->i_affected_rows = null;
		$this->bo_transaction = null;
	}
	
	/**
	 * Connects to the database with the preset login data
	 */
	public function defaultConnect() {
		\Profiler::profileSystem('core/database/Mysqli.inc.php','Connecting to database');
		$this->bo_connection = false;
		
		$s_type = $this->service_Settings->get ( 'settings/SQL/type' );
		$this->connection ( $this->service_Settings->get ( 'settings/SQL/' . $s_type . '/username' ), $this->service_Settings->get ( 'settings/SQL/' . $s_type . '/password' ), $this->service_Settings->get ( 'settings/SQL/' . $s_type . '/database' ), $this->service_Settings->get ( 'settings/SQL/' . $s_type . '/host' ), $this->service_Settings->get ( 'settings/SQL/' . $s_type . '/port' ) );
		
		$this->reset ();

		\Profiler::profileSystem('core/database/Mysqli.inc.php','Connected to database');
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
	 * Resets the internal query cache.
	 */
	private function reset() {
		if (is_object ( $this->obj_query ) && ! $this->bo_transaction) {
			$this->clearResult ();
		}
		
		$this->obj_query = null;
		$this->i_id = - 1;
		$this->i_affected_rows = - 1;
	}
	
	/**
	 * Checks if the given connection-data is correct
	 *
	 * @static
	 *
	 * @param string $s_username
	 *        	The username
	 * @param string $s_password
	 *        	The password
	 * @param string $s_database
	 *        	The database
	 * @param string $s_host
	 *        	The host name, default 127.0.0.1 (localhost)
	 * @param int $i_port
	 *        	The port
	 * @return boolean True if the data is correct, otherwise false
	 */
	public static function checkLogin($s_username, $s_password, $s_database, $s_host = '127.0.0.1', $i_port = -1) {
		if ($i_port == - 1)
			$i_port = '';
		
		if (empty ( $s_username ) || empty ( $s_host ) || empty ( $s_database ))
			return false;
			
			/* connect to the database */
		if ($i_port == - 1 || $i_port == '') {
			$obj_connection = new \mysqli ( $s_host, $s_username, $s_password, $s_database );
		} else {
			$obj_connection = new \mysqli ( $s_host, $s_username, $s_password, $s_database, $i_port );
		}
		if ($obj_connection->connect_errno) {
			return false;
		}
		
		$obj_connection->close ();
		unset ( $obj_connection );
		
		return true;
	}
	
	/**
	 * Connects to the set database
	 *
	 * @param string $s_username
	 *        	The username
	 * @param string $s_password
	 *        	The password
	 * @param string $s_database
	 *        	The database
	 * @param string $s_host
	 *        	The host name, default 127.0.0.1 (localhost)
	 * @param int $i_port
	 *        	The port
	 * @throws DBException If connection to the database failed
	 */
	public function connection($s_username, $s_password, $s_database, $s_host = '127.0.0.1', $i_port = -1) {
		if ($this->bo_connection)
			return;
		
		$this->bo_connection = false;
		
		/* connect to the database */
		if ($i_port == - 1 || $i_port == '') {
			$this->obj_connection = new \mysqli ( $s_host, $s_username, $s_password, $s_database );
		} else {
			$this->obj_connection = new \mysqli ( $s_host, $s_username, $s_password, $s_database, $i_port );
		}
		if ($this->obj_connection->connect_errno) {
			/* Error connecting */
			throw new \DBException ( "Error connection to database " . $s_database . '. Check the connection-settings' );
		}
		
		$this->s_lastDatabase = $s_database;
		$this->bo_connection = true;
	}
	
	/**
	 * Closes the connection to the mysql database
	 */
	public function connectionEnd() {
		if ($this->bo_connection) {
			@$this->obj_connection->close ();
			$this->bo_connection = false;
		}
	}
	
	/**
	 * Returns the ID generated by a INSERT-command
	 *
	 * @return int The generated id
	 */
	public function getId() {
		return $this->i_id;
	}
	
	/**
	 * Returns numbers of rows affected generated by a UPDATE or DELETE command
	 *
	 * @return int The requested id
	 */
	public function affected_rows() {
		return $this->i_affected_rows;
	}
	
	/**
	 * Returns or there is a connection to the database
	 *
	 * @return boolean True if there is a connection with the DB, false if is not
	 */
	public function isConnected() {
		return $this->bo_connection;
	}
	
	/**
	 * Escapes the given data for save use in queries
	 *
	 * @param string $s_data
	 *        	The data that need to be escaped
	 * @return string The escaped data
	 */
	public function escape_string($s_data) {
		$s_data = htmlentities ( $s_data, ENT_QUOTES );
		
		return $this->obj_connection->real_escape_string ( $s_data );
	}
	
	/**
	 * Excequetes the given query on the selected database
	 *
	 * @para string $s_query The query to excequte
	 *
	 * @throws DBException if no connection to the database exists
	 * @throws DBException in case of a SQL error
	 */
	public function query($s_query) {
		$this->queryBinded ( $s_query, array (), array () );
	}
	
	/**
	 * Excequetes the given query on the selected database with binded parameters
	 *
	 * @param string $s_query
	 *        	to excequte
	 * @param array $a_types
	 *        	types : i (int) ,d (double) ,s (string) or b (blob)
	 * @param array $a_values
	 *        	values
	 * @throws Exception if the arguments are illegal
	 * @throws DBException when the query failes
	 */
	public function queryBinded($s_query, $a_types, $a_values) {
		if (is_null ( $s_query ) || empty ( $s_query )) {
			throw new \Exception ( "Illegal query call " . $s_query );
		}
		
		$this->reset ();
		
		if (is_null ( $this->obj_connection ))
			throw new \DBException ( "No connection to the database" );
		
		$this->obj_query = null;
		
		$query = $this->obj_connection->stmt_init ();
		
		if (! $query->prepare ( $s_query )) {
			throw new \DBException ( "Query failed : " . $this->obj_connection->error . '.\n' . $s_query );
		}
		
		if (! is_array ( $a_types ))
			$a_types = array (
					$a_types 
			);
		if (! is_array ( $a_values ))
			$a_values = array (
					$a_values 
			);
		
		$this->bindParams ( $query, $a_types, $a_values );
		
		$res = $query->execute ();
		
		if ($res === false) {
			throw new \DBException ( "Query failed : " . $this->obj_connection->error . '.\n' . $s_query );
		}
		
		preg_match('/^([a-zA-Z]+)\s/',$s_query,$a_matches);
		$s_command = strtoupper($a_matches[1]);
		if ($s_command == 'SELECT' || $s_command == 'SHOW' || $s_command == 'ANALYZE' || $s_command == 'OPTIMIZE' || $s_command == 'REPAIR') {
			$this->a_result = null; // force cleaning
			
			$query->store_result ();
			
			$obj_meta = $query->result_metadata ();
			$a_params = array ();
			while ( $field = $obj_meta->fetch_field () ) {
				$a_params [] = &$this->a_result [$field->name];
			}
			
			call_user_func_array ( array (
					$query,
					'bind_result' 
			), $a_params );
			
			$this->obj_query = $query;
		} else if ($s_command == 'INSERT') {
			$this->i_id = $query->insert_id;
		} else if ($s_command == 'UPDATE' || $s_command == 'DELETE') {
			$this->i_affected_rows = $query->affected_rows;
		}
	}
	
	/**
	 * Bind de waardes aan de query
	 *
	 * @param resource	$query	The query object
	 * @param array		$a_types		The parameter types
	 * @param array		$a_values		The paramenter values
	 * @throws \DBException
	 */
	private function bindParams($query, $a_types, $a_values) {
		$params = array (
				0 => '' 
		);
		$num = count ( $a_types );
		
		if ($num == 0) {
			return;
		}
		
		for($i = 0; $i < $num; $i ++) {
			$type = $a_types [$i];
			
			if (! is_string ( $type ) || ! in_array ( $type, array (
					'i',
					'd',
					's',
					'b' 
			) ))
				throw new \DBException ( 'Illegal binding type ' . $type . '  Only i (int), d (double), s (string) and b (blob) is allowed.' );
			
			$params [0] .= $type;
			$params [] = $a_values [$i];
		}
		
		$callable = array (
				$query,
				'bind_param' 
		);
		call_user_func_array ( $callable, $this->refValues ( $params ) );
	}
	
	/**
	 * Callback to bind the parameters to the query
	 *
	 * @param array $a_arguments
	 *        	arguments
	 * @return array arguments
	 */
	private function refValues($a_arguments) {
		if (strnatcmp ( phpversion (), '5.3' ) >= 0) { // Reference is required for PHP 5.3+
			$a_refs = array ();
			foreach ( $a_arguments as $s_key => $value )
				$a_refs [$s_key] = &$a_arguments [$s_key];
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
	public function num_rows() {
		if (is_null ( $this->obj_query )) {
			throw new \DBException ( "Trying to count the numbers of results on a non-SELECT-query" );
		}
	
		return $this->obj_query->num_rows;
	}
	
	/**
	 * Returns the result from the query with the given row and field
	 *
	 * @param
	 *        	int The row
	 * @param
	 *        	string The field
	 * @return string The content of the requested result-field
	 * @throws DBException if no SELECT-query was excequeted
	 */
	public function result($i_row, $s_field) {
		$this->checkSelect ();
		
		if ($i_row > $this->num_rows () || $i_row < 0)
			throw new \DBException ( "Trying to get data from a not existing field" );
		
		$this->resetPointer ();
		
		$i_rows = $this->num_rows ();
		if ($i_row >= $i_rows) {
			throw new \DBException ( "Unable to fetch row " . $i_row . " Only " . $i_rows . " are present" );
		}
		
		$this->obj_query->data_seek ( $i_row );
		$a_data = $this->fetch_assoc ();
		
		if (! array_key_exists ( $s_field, $a_data [0] )) {
			throw new \DBException ( "Unable to fetch the unknown field " . $s_field );
		}
		
		return $a_data [0] [$s_field];
	}
	
	/**
	 * Returns the results of the query in a numeric array
	 *
	 * @return array data-set
	 * @throws DBException when no SELECT-query was excequeted
	 */
	public function fetch_row() {
		$this->checkSelect ();
		
		$this->resetPointer ();
		
		$a_result = array ();
		while ( $this->obj_query->fetch () ) {
			$i_field = 0;
			$a_temp = array ();
			foreach ( $this->a_result as $s_key => $value ) {
				$a_temp [$i_field] = $value;
				$i_field ++;
			}
			$a_result [] = $a_temp;
		}
		
		return $a_result;
	}
	
	/**
	 * Returns the results of the query in a associate and numeric array
	 *
	 * @return array data-set
	 * @throws DBException when no SELECT-query was excequeted
	 */
	public function fetch_array() {
		$this->checkSelect ();
		
		$this->resetPointer ();
		
		$a_result = array ();
		while ( $this->obj_query->fetch () ) {
			$i_field = 0;
			$a_temp = array ();
			foreach ( $this->a_result as $s_key => $value ) {
				$a_temp [$i_field] = $value;
				$a_temp [$s_key] = $value;
				$i_field ++;
			}
			$a_result [] = $a_temp;
		}
		
		return $a_result;
	}
	
	/**
	 * Returns the results of the query in a associate array
	 *
	 * @return array data-set
	 * @throws DBException when no SELECT-query was excequeted
	 */
	public function fetch_assoc() {
		$this->checkSelect ();
		
		$this->resetPointer ();
		
		$a_result = array ();
		
		while ( $this->obj_query->fetch () ) {
			$a_temp = array ();
			foreach ( $this->a_result as $s_key => $value ) {
				$a_temp [$s_key] = $value;
			}
			$a_result [] = $a_temp;
		}
		
		return $a_result;
	}
	
	/**
	 * Returns the results of the query in a associate array with the given field as counter-key
	 *
	 * @param
	 *        	string The field that is the counter-key
	 * @return array data-set sorted on the given key
	 * @throws DBException when no SELECT-query was excequeted
	 */
	public function fetch_assoc_key($s_key) {
		$this->checkSelect ();
		
		$this->resetPointer ();
		
		$a_result = array ();
		while ( $this->obj_query->fetch () ) {
			$a_temp = array ();
			foreach ( $this->a_result as $s_fieldkey => $value ) {
				$a_temp [$s_fieldkey] = $value;
				
				if ($s_fieldkey == $s_key)
					$s_rowKey = $value;
			}
			$a_result [$s_rowKey] = $a_temp;
		}
		
		return $a_result;
	}
	
	/**
	 * Returns the results of the query as a object-array
	 *
	 * @return object data-set
	 * @throws Exception when no SELECT-query was excequeted
	 */
	public function fetch_object() {
		$this->checkSelect ();
		
		$this->resetPointer ();
		
		$a_temp = array ();
		while ( $obj_res = $this->obj_query->fetch_object () ) {
			$a_temp [] = $a_obj;
		}
		
		return $a_temp;
	}
	
	/**
	 * Starts a new transaction
	 *
	 * @throws DBException a transaction is allready active
	 */
	public function transaction() {
		if ($this->bo_transaction) {
			throw new \DBException ( "Can not start new transaction. Call commit() or rollback() first." );
		}
		
		$this->obj_connection->query("START TRANSACTION" );
		$this->bo_transaction = true;
	}
	
	/**
	 * Commits the current transaction
	 *
	 * @throws DBException no transaction is active
	 */
	public function commit() {
		if (! $this->bo_transaction) {
			throw new \DBException ( "Can not commit transaction. Call transaction() first." );
		}
		
		$this->obj_connection->query ( "COMMIT" );
		$this->bo_transaction = false;
	}
	
	/**
	 * Rolls the current transaction back
	 *
	 * @throws DBException no transaction is active
	 */
	public function rollback() {
		if (! $this->bo_transaction) {
			throw new \DBException ( "Can not rollback transaction. Call transaction() first." );
		}
		
		$this->obj_connection->query ( "ROLLBACK" );
		$this->bo_transaction = false;
	}
	
	/**
	 * Analyses the given table
	 *
	 * @param string $s_table
	 *        	table name
	 * @return boolean if the table is OK, otherwise false
	 */
	public function analyse($s_table) {
		$this->query ( "ANALYZE TABLE " . $s_table );
		
		$a_result = $this->fetch_assoc ();
		if ($a_result [0] ['Msg_text'] != 'OK' && $a_result [0] ['Msg_text'] != 'Table is already up to date') {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Repairs the given table
	 *
	 * @param string $s_table
	 *        	table name
	 * @return boolean if the table repair succeeded, otherwise false
	 */
	public function repair($s_table) {
		$this->query ( "REPAIR TABLE " . $s_table );
		
		$a_result = $this->fetch_assoc ();
		if ($a_result [0] ['Msg_text'] != 'OK') {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Optimizes the given table
	 *
	 * @param string $s_table
	 *        	table name
	 */
	public function optimize($s_table) {
		$this->query ( "OPTIMIZE TABLE " . $s_table );
		
		$a_result = $this->fetch_assoc ();
		if ($a_result [0] ['Msg_text'] != 'OK' && $a_result [0] ['Msg_text'] != 'Table is already up to date') {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Changes the active database to the given one
	 *
	 * @param string $s_database
	 *        	database
	 * @throws DBException if the new databases does not exist or no access
	 */
	public function useDB($s_database) {
		try {
			$this->query ( "USE " . $s_database );
			
			$this->s_lastDatabase = $s_database;
		} catch ( \Exception $ex ) {
			throw new \Exception ( "Database-change failed .\n" . $s_database );
		}
	}
	
	/**
	 * Checks if a database exists and if the user has access to it
	 *
	 * @param string $s_database        	
	 * @return boolean if the database exists, otherwise false
	 */
	public function databaseExists($s_database) {
		$this->query ( "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $s_database . "'" );
		if ($this->num_rows () > 0)
			return true;
		
		return false;
	}
	
	/**
	 * Checks if the last query was a SELECT-query
	 *
	 * @throws DBException when no SELECT-query was excequeted
	 */
	private function checkSelect() {
		if ($this->obj_query == null) {
			throw new \DBException ( "Database-error : trying to get data on a non-SELECT-query" );
		}
	}
	
	/**
	 * Describes the table structure
	 *
	 * @param string $s_table
	 *        	The table name
	 * @param
	 *        	string The structure
	 * @param
	 *        	boolean Set to true to add "IF NOT EXISTS"
	 * @param
	 *        	boolean Set to true to add dropping the table first
	 * @throws \DBException If the table does not exists
	 */
	public function describe($s_table, $bo_addNotExists = false, $bo_dropTable = false) {
		$this->query ( 'SHOW CREATE TABLE ' . $s_table );
		if ($this->num_rows () == 0) {
			throw new \DBException ( 'Table ' . $s_table . ' does not exist.' );
		}
		
		$a_table = $this->fetch_row ();
		$s_description = $a_table [0] [1];
		if ($bo_dropTable) {
			$s_description = 'DROP TABLE IF EXISTS ' . $s_table . ";\n" . $s_description;
		}
		if ($bo_addNotExists) {
			$s_description = str_replace ( 'CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $s_description );
		}
		return $s_description;
	}
	
	/**
	 * Resets the data result pointer
	 */
	private function resetPointer() {
		if (! is_null ( $this->obj_query ))
			$this->obj_query->data_seek ( 0 );
	}
	
	/**
	 * Clears the previous result set
	 */
	private function clearResult() {
		if (! is_null ( $this->obj_query )) {
			$this->obj_query->free_result ();
			$this->obj_query->close ();
			
			$this->obj_query = null;
		}
	}

	
	/**
	 * Returns the current loaded database
	 * 
	 * @return string
	 */
	public function getDatabase(){
		return $this->s_lastDatabase;
	}
}
