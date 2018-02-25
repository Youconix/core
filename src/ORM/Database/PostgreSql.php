<?php

namespace youconix\Core\Database;
use Exception;

/**
 * Database connection layer for PostgreSQL
 *
 * @author Rachelle Scheijen
 * @since 1.0
 *       
 *       
 */
class PostgreSql extends \youconix\Core\ORM\Database\AbstractGeneralDAL
{

  protected $bo_upsert = false;
  protected $connectionData;

  /**
   * Connects to the database with the preset login data
   */
  public function defaultConnect()
  {
    $this->bo_connection = false;

    $this->connection($this->settings->get('settings/SQL/PostgreSql/username'),
					   $this->settings->get('settings/SQL/PostgreSql/password'),
			     $this->settings->get('settings/SQL/PostgreSql/database'),
			     $this->settings->get('settings/SQL/PostgreSql/host'),
			     $this->settings->get('settings/SQL/PostgreSql/port'));

    $this->reset();
  }

  /**
   * Checks if the given connection-data is correct
   *
   * @static
   *
   * @param String $s_username
   *            The username
   * @param String $s_password
   *            The password
   * @param String $s_database
   *            The database
   * @param String $s_host
   *            The host name, default 127.0.0.1 (localhost)
   * @param int $i_port
   *            The port
   * @return Boolean True if the data is correct, otherwise false
   */
  public static function checkLogin($s_username, $s_password, $s_database,
				    $s_host = '127.0.0.1', $i_port = -1)
  {
    if ($i_port == - 1)
      $i_port = '';

    $reporting = error_reporting();
    error_reporting(0);

    try {
      /* connect to the database */
      $s_res = pg_connect("host=" . $s_host . " port=" . $s_port . " dbname=" . $s_database . " user=" . $s_username . " password=" . $s_password);

      error_reporting($reporting);

      if (!$s_res) {
	return false;
      }

      pg_close($s_res);

      return true;
    } catch (\Exception $exception) {
      error_reporting($reporting);
      /* Error connecting */
      return false;
    }
  }

  /**
   * Connects to the set database
   *
   * @param String $s_username
   *            The username
   * @param String $s_password
   *            The password
   * @param String $s_database
   *            The database
   * @param String $s_host
   *            The host name, default 127.0.0.1 (localhost)
   * @param int $i_port
   *            The port
   * @throws DBException If connection to the database failed
   */
  public function connection($s_username, $s_password, $s_database,
			     $s_host = '127.0.0.1', $i_port = -1)
  {
    if ($this->bo_connection)
      return;

    if ($i_port == - 1)
      $i_port = '';

    $this->connectionData = new \stdClass();
    $this->connectionData->host = $s_host;
    $this->connectionData->port = $i_port;
    $this->connectionData->username = $s_username;
    $this->connectionData->password = $s_password;

    $s_res = @pg_connect("host=" . $s_host . " port=" . $i_port . " dbname=" . $s_database . " user=" . $s_username . " password=" . $s_password);
    if ($s_res == false) {
      /* Error connecting */
      throw new \DBException("Error connection to database " . $s_database . '. Check the connection-settings');
      $this->bo_conntection = false;
    }

    $this->s_lastDatabase = $s_database;
    $this->bo_connection = true;
  }

  /**
   * Closes the connection to the database
   */
  public function connectionEnd()
  {
    if ($this->bo_connection) {
      pg_close($this->obj_connection);
      $this->obj_connection = null;
      $this->bo_connection = false;
    }
  }

  /**
   * Dumps the database to a file
   * This function requires exec()!
   * @param string $target
   */
  public function dump($target)
  {
    $s_type = $this->settings->get('settings/SQL/type');
    $username = $this->settings->get('settings/SQL/' . $s_type . '/username');
    $database = $this->settings->get('settings/SQL/' . $s_type . '/database');
    $host = $this->settings->get('settings/SQL/' . $s_type . '/host');
    $port = $this->settings->get('settings/SQL/' . $s_type . '/port');

    $command = 'pg_dump --username '.$username.' --port '.$port.' --host='.$host.' -Fc --file='.$target.' '.$database;
    $result = exec($command, $output);

    if ($result !== '') {
      throw new \Exception('Dumping the database failed: ' . $result);
    }
  }

  /**
   * Escapes the given data for save use in queries
   *
   * @param String $s_data
   *            The data that need to be escaped
   * @return String The escaped data
   */
  public function escape_string($s_data)
  {
    $s_data = htmlentities($s_data, ENT_QUOTES);

    return pg_escape_string($s_data);
  }

  /**
   * Excequetes the given query on the selected database
   *
   * @param string $s_query            
   * @throws Exception if the arguments are illegal
   * @return \DALInterface
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
    $this->s_query = $s_query;

    $this->obj_query = pg_prepare($this->obj_connection, '', $s_query);
    if ($this->obj_query === false) {
      throw new \DBException("Query failed : " . pg_last_error($this->obj_connection) . '.\n' . $s_query);
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
    $result = pg_execute($this->obj_connection, '', $this->a_bindedValues);

    if ($result === false) {
      throw new \DBException("Query failed : " . pg_last_error($this->obj_connection) . '.\n' . $this->s_query);
    }

    preg_match('/^([a-zA-Z]+)\s/', $this->s_query, $a_matches);
    $s_command = strtoupper($a_matches[1]);
    switch ($s_command) {
      case 'SELECT':
      case 'SHOW':
      case 'ANALYZE':
      case 'OPTIMIZE':
      case 'REPAIR':
	$this->obj_query = null;
	$this->obj_query = $result;

	if ($this->s_query == 'SELECT lastval()') {
	  $data = $this->fetch_row();
	  $this->i_id = $data[0];
	  $this->obj_query = null;
	}
	break;
      case 'INSERT':
	$this->prepare('SELECT lastval()');
	$this->exequte();
	break;
      case 'UPDATE':
      case 'DELETE':
	$this->i_affected_rows = pg_affected_rows($result);
	break;
    }
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

    return pg_num_rows($this->obj_query);
  }

  /**
   * Returns the result from the query with the given row and field
   *
   * @param
   *            int The row
   * @param
   *            String The field
   * @return String The content of the requested result-field
   * @throws DBException if no SELECT-query was excequeted
   */
  public function result($i_row, $s_field)
  {
    if (is_null($this->obj_query))
      throw new DBException("Trying to get data on a non-SELECT-query");

    return pg_fetch_result($this->obj_query, $i_row, $s_field);
  }

  /**
   * Returns the results of the query in a numeric array
   *
   * @return array data-set
   * @throws DBException when no SELECT-query was excequeted
   */
  public function fetch_row()
  {
    if (is_null($this->obj_query))
      throw new DBException("Trying to get data on a non-SELECT-query");

    $a_temp = [];
    while ($a_res = pg_fetch_row($this->obj_query)) {
      $a_temp[] = $a_res;
    }

    return $a_temp;
  }

  /**
   * Returns the results of the query in a associate and numeric array
   *
   * @return array data-set
   * @throws DBException when no SELECT-query was excequeted
   */
  public function fetch_array()
  {
    if (is_null($this->obj_query))
      throw new DBException("Trying to get data on a non-SELECT-query");

    $a_ret = [];
    for ($i = 0; $a_arr = pg_fetch_array($s_result, $i, PGSQL_ASSOC); $i ++) {
      $a_ret = $a_arr[$i];
    }

    return $a_ret;
  }

  /**
   * Returns the results of the query in a associate array
   *
   * @return array data-set
   * @throws DBException when no SELECT-query was excequeted
   */
  public function fetch_assoc()
  {
    if (is_null($this->obj_query))
      throw new DBException("Trying to get data on a non-SELECT-query");

    $a_temp = [];
    while ($a_res = pg_fetch_assoc($this->obj_query)) {
      $a_temp[] = $a_res;
    }

    return $a_temp;
  }

  /**
   * Returns the results of the query in a associate array with the given field as counter-key
   *
   * @param
   *            String The field that is the counter-key
   * @return array data-set sorted on the given key
   * @throws DBException when no SELECT-query was excequeted
   */
  public function fetch_assoc_key($s_key)
  {
    if (is_null($this->obj_query))
      throw new DBException("Trying to get data on a non-SELECT-query");

    $a_temp = [];
    while ($a_res = pg_fetch_assoc($this->obj_query)) {
      $a_temp[$a_res[$s_key]] = $a_res;
    }

    return $a_temp;
  }

  /**
   * Returns the results of the query as a object-array
   *
   * @return Object data-set
   * @throws Exception when no SELECT-query was excequeted
   */
  public function fetch_object()
  {
    if (is_null($this->obj_query))
      throw new DBException("Trying to get data on a non-SELECT-query");

    $a_temp = [];
    while ($obj_res = pg_fetch_object($this->obj_query)) {
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
      throw new DBException("Can not start new transaction. Call commit() or rollback() first.");
    }

    $this->query("BEGIN");
    $this->bo_transaction = true;
  }

  /**
   * Commits the current transaction
   *
   * @throws DBException no transaction is active
   */
  public function commit()
  {
    if (!$this->bo_transaction) {
      throw new DBException("Can not commit transaction. Call transaction() first.");
    }

    $this->query("COMMIT");
    $this->bo_transaction = false;
  }

  /**
   * Rolls the current transaction back
   *
   * @throws DBException no transaction is active
   */
  public function rollback()
  {
    if (!$this->bo_transaction) {
      throw new DBException("Can not rollback transaction. Call transaction() first.");
    }

    $this->query("ROLLBACK");
    $this->bo_transaction = false;
  }

  public function useDB($s_database)
  {
    $this->connectionEnd();
    $this->connection($this->connectionData->username,
		      $this->connectionData->password, $s_database,
		      $this->connectionData->host, $this->connectionData->port);
  }

  /**
   * Checks if a database exists and if the user has access to it
   *
   * @param string $s_database            
   * @return boolean if the database exists, otherwise false
   */
  public function databaseExists($s_database)
  {
    $this->prepare('SELECT 1 FROM pg_database WHERE datname = ":database"');
    $this->bindString('database', $s_database);
    $this->exequte();

    return ($this->num_rows() > 0);
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
  public function describe($s_table, $bo_addNotExists = false,
			   $bo_dropTable = false)
  {
    $this->prepare("SELECT f.attnum AS number, f.attname AS name, f.attnum, f.attnotnull AS notnull,  
            pg_catalog.format_type(f.atttypid,f.atttypmod) AS type,  
        CASE  
            WHEN p.contype = 'p' THEN 't'  
            ELSE 'f'  
            END AS primarykey,  
        CASE  
            WHEN p.contype = 'u' THEN 't'  
            ELSE 'f'
            END AS uniquekey,
        CASE
            WHEN p.contype = 'f' THEN g.relname
            END AS foreignkey,
        CASE
            WHEN p.contype = 'f' THEN p.confkey
            END AS foreignkey_fieldnum,
        CASE
            WHEN p.contype = 'f' THEN g.relname
            END AS foreignkey,
        CASE
            WHEN p.contype = 'f' THEN p.conkey
            END AS foreignkey_connnum,
        CASE
            WHEN f.atthasdef = 't' THEN d.adsrc
            END AS default
        FROM pg_attribute f  
            JOIN pg_class c ON c.oid = f.attrelid  
            JOIN pg_type t ON t.oid = f.atttypid  
            LEFT JOIN pg_attrdef d ON d.adrelid = c.oid AND d.adnum = f.attnum  
            LEFT JOIN pg_namespace n ON n.oid = c.relnamespace  
            LEFT JOIN pg_constraint p ON p.conrelid = c.oid AND f.attnum = ANY (p.conkey)  
            LEFT JOIN pg_class AS g ON p.confrelid = g.oid  
        WHERE c.relkind = 'r'::char  
        AND n.nspname = ':schema'  -- Replace with Schema name  
        AND c.relname = ':relname'  -- Replace with table name  
        AND f.attnum > 0 ORDER BY number");
    $this->bindString('schema', $this->getDatabase());
    $this->bindstring('relname', $s_table);
    $this->exequte();

    $a_table = $this->fetch_row();
    $s_description = $a_table[0][1];
    if ($bo_dropTable) {
      $s_description = 'DROP TABLE IF EXISTS ' . $s_table . ";\n" . $s_description;
    }
    if ($bo_addNotExists) {
      $s_description = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS',
				   $s_description);
    }
    return $s_description;
  }

  public function getVersion()
  {
    $this->prepare('SELECT version()');
    $this->exequte();

    $a_data = $this->fetch_row();
    $a_data = explode(' ', $a_data[0]);
    $a_data = explode('.', $a_data[1]);

    return $a_data[0] . '.' . $a_data[1];
  }

  /**
   *
   * @param int $i_column
   * @param int $i_row
   * @return string
   * @throws Exception
   */
  public function fetch_column($i_column, $i_row = 0)
  {
    // TODO: Implement fetch_column() method.
  }
}
