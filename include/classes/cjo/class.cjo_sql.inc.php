<?php
/**
 * This file is part of CONTEJO - CONTENT MANAGEMENT 
 * It is an open source content management system and had 
 * been forked from Redaxo 3.2 (www.redaxo.org) in 2006.
 * 
 * PHP Version: 5.3.1+
 *
 * @package     contejo
 * @subpackage  core
 * @version     2.7.x
 *
 * @author      Stefan Lehmann <sl@contejo.com>
 * @copyright   Copyright (c) 2008-2012 CONTEJO. All rights reserved. 
 * @link        http://contejo.com
 *
 * @license     http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License, version 3 or later
 *  CONTEJO is free software. This version may have been modified pursuant to the
 *  GNU General Public License, and as distributed it includes or is derivative
 *  of works licensed under the GNU General Public License or other free or open
 *  source software licenses. See _copyright.txt for copyright notices and
 *  details.
 * @filesource
 */

/**
 * cjoSql class
 *
 * The cjoSql class is a object handler for connecting an interacting with the database.
 * @package     contejo
 * @subpackage     core
 */
class cjoSql implements Iterator{
    
    /**
     * internal data pointer
     * @var int
     */
    public $counter;

    /**
     * error text
     * @var string
     */
    public $error;

    /**
     * error number
     * @var int
     */
    public $errno;

    /**
     * last auto_increment number
     * @var int
     */
    public $last_insert_id; 

    /**
     * debugging enabled
     * @var boolean
     */
    public $debugsql;
    
    /**
     * values set by setValue
     * @var array
     */
    public $values;
    
    /**
     * ignore changes INSERT to INSERT IGNORE
     * @var bool
     */
    protected $ignore;  
    
    /**
     * Columns in the ResultSet
     * @var array
     */
    protected $fieldnames; // Spalten im ResultSet
    
    /**
     * Columns in the ResultSet
     * @var array
     */    
    protected $raw_fieldnames;    
    
    /**
     * Table in the ResultSet
     * @var array
     */
    protected $tablenames;
    
    /**
     * Value of the last line which has been fetched 
     * @var array
     */
    protected $last_row;
    
    /**
     * name of the table
     * @var string
     */
    protected $table;

    /**
     * WHERE condition
     * @var string
     */
    protected $where_var;
    
    /**
     * Parameter of where condition
     * @var array
     */
    protected $where_params;
    
    /**
     * LIMIT condition
     * @var string
     */
    protected $limit;
    
    /**
     * SQL query
     * @var string
     */
    protected $query;

    /**
     * number of rows from a result set
     * @var int
     */
    protected $rows;

    /**
     * result set
     * @var resource
     */
    protected $result;

    /**
     * SQL persistent link identifier
     * @var unknown_type
     */
    protected $identifier;

    /**
     * id of the database connection
     * @var string
     */
    public $DBID;
    
    /**
     * PDOStatement object
     * @var object|false
     */
    protected $stmt;
    
    /**
     * Contains one or more PDO objects. 
     * @var array
     */
    protected static $pdo = array(); // array von datenbankverbindungen
    
    /**
     * Constructor
     * @param int|array $dbid id of contejo database or connection to a different database as array
     * @param bool $debug enables or disables debugging
     * @return void
     * @access public
     */
    public function __construct($dbid=1, $debug=false) {
        $this->debugsql = (bool) $debug;   
        $this->flush();    
        $this->selectDB($dbid);
    }

    /**
     * Selects the database.
     * If selection fails exit with warning.
     * @return void
     * @access public
     */
    protected function selectDB($db){
        
        global $CJO;
        
        if (!is_array($db)) {
            $db = (int) $db;
            if ($db < 2) {
                $host = cjo_server('HTTP_HOST','string');
                $localhosts = explode(',',$CJO['LOCALHOST']);
                $dbid = cjoAssistance::isLocalhost() ? 'LOCAL' : 1;
            }
            $db = $CJO['DB'][$dbid];
        }
        else {
            $dbid = md5(implode('',$db));
        }

        $this->DBID = $dbid;
        
        if (isset(self::$pdo[$dbid])) return;
        
        try {
            foreach(cjoAssistance::toArray($db) as $key=>$value) {
                $key = strtolower($key);
                $$key = $value;
            }
            self::$pdo[$dbid] = self::createConnection($host, $name, $login, $psw);  
        }
        catch(PDOException $e) {
            $this->noDBConnection();
        }
    }
    
    /**
     * Builds connection to the database.
     * @param string $host
     * @param string $database
     * @param string $login
     * @param string $password
     * @param string $persistent
     * @return void
     * @access public
     */
    static protected function createConnection($host, $database, $login, $password, $persistent=true) {
        
        $dsn = 'mysql:host='. $host .';dbname='. $database;
        $options = array(PDO::ATTR_PERSISTENT => (boolean) $persistent,
                         PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8; SET CHARACTER SET utf8; SET SQL_MODE="";',
                         PDO::ATTR_FETCH_TABLE_NAMES => false);

        return new PDO($dsn, $login, $password, $options);
    }

    /**
     * Set the table name.
     * @param string $table_name
     * @return void
     * @access public
     */
    public function setTable($table_name) {
        $this->table = $table_name;
    }

    /**
     * Returns the table name.
     * @return string
     * @access public
     */
    public function getTable() {
        return $this->table;
    }
    
    /**
     * Sets the value of a column.
     * @param string $field name of the column
     * @param mixed $value value
     * @return void
     * @access public
     */
    public function setValue($field, $value) {
        $value = str_replace(array('\r','\n','\t','\v'), array("\r","\n","\t","\v"), $value);
        $this->values[trim($field)] = $value;
    }

    /**
     * Sets an array of values and columns.
     * @param array $values
     * @return boolean
     * @access public
     */
    public function setValues($values) {

        if (is_array($values)) {
            foreach($values as $field => $value) {
                $this->setValue($field, $value);
            }
            return true;
        }
        return false;
    }
    
    /**
    * Returns whether values are set inside this object
    * @return boolean True if value isset and not null, otherwise false
    */
    public function hasValues() {
        return !empty($this->values);
    }
  
    /**
     * Checks if a column is containing a specific value.
     * @param string $field name of the column
     * @param string $value
     * @return boolean
     * @access public
     */
    public function isValueOf($field, $value) {
        return ($value == "") ? true : (strpos($this->getValue($field), $value) !== false);
    }

    /**
     * Sets the WHERE condition for the current request.
     * @param string $where
     * @param array $params 
     * @access public
     *
     * example 1:
     *  	$sql->setWhere(array('id' => 3, 'field' => '')); // results in id = 3 AND field = ''
     *  	$sql->setWhere(array(array('id' => 3, 'field' => ''))); // results in id = 3 OR field = ''
     *
     * example 2:
     *  	$sql->setWhere('myid = :id OR anotherfield = :field', array('id' => 3, 'field' => ''));
     *
     * example 3 (deprecated):
     *  	$sql->setWhere('myid="35" OR abc="test"');     *
     */
    public function setWhere($where, $params = null) {
        if(is_array($where)) {
            $this->where_var = "WHERE";
            $this->where_params = $where;
        }
        else if(is_string($where) && is_array($params)) {
            $this->where_var = "WHERE ".$where;
            $this->where_params = $params;
        }
        else if(is_string($where)) {
            $this->where_var = "WHERE ".$where;
            $this->where_params = array();
        }
    }

    /**
     * Returns true if a value has been set by setValue.
     * @param string $fieldname name of the field
     * @return bool
     * @access public
     */
    public function hasSetValue($fieldname)  {
        return isset($this->values[$fieldname]);
    }    
    
    /**
     * Returns the result value of the specified field.
     * @param string $fieldname name of the field
     * @return mixed
     * @access public
     */
    public function getValue($fieldname)  {

        // fast fail,... value already set manually?
        if ($this->hasSetValue($fieldname))
            return $this->values[$fieldname];

        // check if there is an table alias defined
        // if not, try to guess the tablename
        if (strpos($fieldname, '.') === false) {
            $tables = $this->_getTablenames();
            foreach($tables as $table) {
                if (in_array($table .'.'. $fieldname, $this->raw_fieldnames)) {
                    return $this->fetchValue($table .'.'. $fieldname);
                }
            }
        }

        return $this->fetchValue($fieldname);
    }

    /**
     * Fetches the given value from the current row of the result set 
     * @param string $fieldname
     * @throws cjoException
     * @return string|bool
     * @access public
     */
    public function fetchValue($fieldname) {
        
        if (empty($fieldname)) {
            throw new cjoException('fieldname must not bee empty', E_USER_WARNING);
        }
  
        if (isset($this->values[$fieldname]))
            return $this->values[$fieldname];

        if (empty($this->last_row)) {
            // no row fetched, but also no query was executed before
            if ($this->stmt == null) {
                return null;
            }
            $this->last_row = $this->stmt->fetch(PDO::FETCH_ASSOC);
        }

        $res = false;

        // isset doesn't work here, because values may also be null
        if (is_array($this->last_row) && array_key_exists($fieldname, $this->last_row)) {
            $res = $this->last_row[$fieldname];
        }

        return $res;
    }
    /**
     * Fetches the next row from a result set.
     * @return mixed
     * @access public
     */
    public function getRow() {
        if (empty($this->last_row)) {
            $this->next();
        }
        return $this->last_row;
    }
        
    /**
     * Checks if a field name exists in the result.
     * @param $fieldname name of the column
     * @return boolean
     * @access public
     */
    public function hasValue($fieldname) {

        if (strpos($fieldname, '.') !== false) {
            $parts = explode('.', $fieldname);
            return in_array($parts[0], $this->_getTablenames()) && in_array($parts[1], $this->_getFieldnames());
        }
        return in_array($fieldname, $this->_getFieldnames());
    }
    
    public function isNull($fieldname) {
        return ($this->hasValue($fieldname))
               ? $this->getValue($fieldname) === null
               : null;
    }
    
    /**
     * Returns the number of resulting rows.
     * @return int
     * @access public
     */
    public function getRows() {
        return $this->rows;
    }

    /**
     * Returns the current score of the internal counter.
     * @return int
     * @access public
     */
    public function getCounter() {
        return $this->counter;
    }

    /**
     * Retrieves the number of fields.
     * @return int
     * @access private
     */
    protected function getFields() {
        return ($this->stmt) ? $this->stmt->columnCount() : 0;
    }

    /**
     * Resets the cursor to the first element of the result
     * @return void
     * @access public
     * @deprecated
     */
    public function resetCounter() {
        $this->reset();
    }
    
    /**
     * Sets the LIMIT for the current request.
     * @param int $start
     * @param int $end 
     * @return void
     * @access public
     */
    public function setLimit($start = 1, $end = null) {
        $this->limit = array($start, $end);
    }
    
    /**
     * Returns the LIMIT for the current request.
     * @return string
     * @access public
     */
    public function getLimit() {    
        $limit  = !empty($this->limit[0]) ? ' LIMIT '.$this->limit[0]: '';
        $limit .= ($limit!= '' && !empty($this->limit[1])) ? ', '.$this->limit[1] : '';
        return $limit;
    }
        
    /**
     * Generates the SQL query.
     * @return string
     * @access protected
     */
    protected function buildPreparedValues() {
        
        $query = array();
        $new_values = array();
        if (is_array($this->values)) {
            foreach ($this->values as $fieldname => $value) {
                $new_values['set_'. $fieldname] = $value;
                $query[] = '`'. $fieldname . '` = :set_'. $fieldname;
            }
            $this->values = $new_values;
        }

        if (empty($query)) {
            return false;
            throw new cjoException('no values given to buildPreparedValues for update(), insert() or replace()', E_USER_WARNING);
        }

        return implode(', ', $query);
    } 

    /**
     * Generates the where statematen.
     * @return string
     * @access protected
     */
    protected function buildPreparedWhere() {
        if (empty($this->where_var)) {
            return '';
        }   
        else if ($this->where_var == "WHERE" && is_array($this->where_params)) {
            return $this->where_var.' '.$this->buildWhereArg($this->where_params);
        }
        else {
            return $this->where_var;
        }    
    }
      
    /**
     * Concats the given array to a sql condition.
     * AND/OR opartors are alternated depending on $level
     *
     * @param array $fields
     * @param int $level
     */
    protected function buildWhereArg($fields, $level=0) {

        $op = ($level % 2 == 1) ? ' OR ' : ' AND ';

        $query = '';
        foreach(cjoAssistance::toArray($fields) as $fieldname => $value) {
            
            $arg = (is_array($value)) 
                 ? '('.$this->buildWhereArg($value, $level+1).')'
                 : ''.$fieldname.' = :'.$fieldname;

            if ($query != '') {
                $query .= $op;
            }
            $query .= $arg;
        }
        return $query;
    }    
    
    /**
     * Gibt den Typ der Abfrage (SQL) zurück,
     * oder false wenn die Abfrage keinen Typ enthält
     * @param string $query SQL query
     * @return string (SELECT|SHOW|UPDATE|INSERT|DELETE|REPLACE)
     * @access protected
     */
    protected function getQueryType($query = null) {

        if (!empty($query)) {
            if (isset($this)) {
                $query = $this->query;
            }
            else {
                return null;
            }
        }
        $query = trim($query);

        if (preg_match('/^(SELECT|SHOW|UPDATE|INSERT|DELETE|REPLACE)/i', $query, $matches))
            return strtoupper($matches[1]);

        return false;
    }

    /**
    * Prepares a statement for execution and returns a statement object 
    * @param string $query A query string with placeholders
    * @return PDOStatement The prepared statement
     * @access public
     */
    public function prepareQuery($query){
        $query = trim($query);
        $this->query = $query;
        return ($this->stmt = self::$pdo[$this->DBID]->prepare($query));
    }
    
    /**
     * Executes the prepared statement with the given input parameters
     * @param array $params Array of input parameters
     * @return boolean True on success, False on error
     * @access public
     */
    public function execute($params) {
        if(!is_array($params)) {
            throw new cjoException('expecting $params to be an array, "'. gettype($params) .'" given!');
        }
        if (!$this->stmt) {
            throw new cjoException('Error occured while preparing statement "'.  $this->query .'"!');
        }

        $params = $this->prepareParams($params);
        $this->stmt->execute($params);
        return;
    }
  
    /**
     * Prepares the Parameters
     * @param array $params
     * @return string
     * @access protected
     */
    protected function prepareParams($params) {
        $temp = array();
        foreach ($params as $key => $value) {
            if (substr($key, 0, 1) != ':') $key = ':'.$key;
            $temp[$key] = (is_array($value) ? self::prepareParams($params) : $value);
        }
        return $temp;
    }
    
    /**
     * Executes a request using the current database.
     * @param string $query SQL query
     * @return boolean
     * @access public
     */
    public function setQuery($query, $params = array()) {
        
        if (!is_array($params)) {
            throw new cjoException('expecting $params to be an array, "'. gettype($params) .'" given!');
        }

        $this->flush();
        $this->prepareQuery($query);
        $this->setWhere($params);
        
        if ($this->stmt) {
            $this->execute($params);
        }
        else {
            throw new cjoException('Error occured while preparing statement "'. $qry .'"!');
        }
        
        $this->rows = ($this->stmt !== false)
                    ? $this->stmt->rowCount()
                    : 0;
                    
        $hasError = $this->hasError();
        if ($this->debugsql) {
          $this->printError($query, $params);
        }
        else if ($hasError) {
          cjoMessage::addError($this->getError().' '.$this->getTable());
        }

        return !$hasError;
    }
    
    /**
     * Executes a request using the current database.
     * @param string $query SQL query
     * @return boolean
     * @access public
     */
    public function setDirectQuery($query) {

        $this->flush();
        $this->query = $query;
        
        $this->stmt = self::$pdo[$this->DBID]->query('');
        $this->stmt = self::$pdo[$this->DBID]->query($this->query);

        $this->rows = ($this->stmt !== false)
                    ? $this->stmt->rowCount()
                    : 0;
     
        $hasError = $this->hasError();
        if ($this->debugsql) {
          $this->printError($query, $params);
        }
        else if ($hasError) {
          cjoMessage::addError($this->getError().' '.$this->getTable());
        }

        return !$hasError;
    }
    
    
    /**
     * Executes a request and returns a status message.
     * @param string $query SQL query
     * @param string $message a success message
     * @return string|boolean
     * @see function setQuery()
     * @example
     * 
     * use:
     *
     * <code>
     * $sql = new cjoSql();
     * $message = $sql->statusQuery(
     *    'INSERT  INTO abc SET a="ab"',
     *    'Datensatz  erfolgreich eingefügt');
     * </code>
     *
     * instead of:
     *
     * <code>
     * $sql = new cjoSql();
     * if ($sql->setQuery('INSERT INTO abc SET a="ab"'))
     * $message = 'Datensatz erfolgreich eingefügt');
     * else
     * 		$message = $sql- >getError();
     * </code>
     *
     * @access public
     */
    public function statusQuery($query, $message = false) {

        $success = $this->setQuery($query);
        if ($message !== false) {
            if ($success) {
                cjoMessage::addSuccess($message);
            } else {
                cjoMessage::addError($this->getError().' '.$this->getTable());
            }
        }
        return $success;
    }
    
    /**
     * Executes a request and returns a status message.
     * @param string $query MYSQL query
     * @param array $params statement parameters 
     * @param string $message a success message
     * @return string|boolean
     * @see function setQuery()
     * @example
     *
     * <code>
     * $sql = new cjoSql();
     * $message = $sql->statusQuery(
     *    'INSERT  INTO abc SET a=:a',
     *    array(a=>'test'),
     *    'Datensatz  erfolgreich eingefügt');
     * </code>
     *
     * @access public
     */
    public function preparedStatusQuery($query, $params, $message = false) {
        $success = $this->setQuery($query, $params);
        if ($message !== false) {
            if ($success) {
                cjoMessage::addSuccess($message);
            } else {
                cjoMessage::addError($this->getError().' '.$this->getTable());
            }
        }
        return $success;
    }    
    
    /**
     * Executes an select request with
     * defined table and where parameters.
     * @param string $fields
     * @return boolean
     * @see function setTable()
     * @see function setWhere()
     * @example
     * <code>
     *           $select = new cjoSql();
     *           $select->setTable(TBL_USER);
     *           $select->setWhere("user_id='".$cur_user_id."'");
     *           $select->Select('user_id, clang');
     * </code>
     * @access public
     */
    public function Select($fields='*'){

        return $this->setQuery(
                    'SELECT '.$fields.' FROM `'.$this->getTable().'` '. $this->buildPreparedWhere().' '.$this->getLimit(),
                    $this->where_params);
    }

    /**
     * Executes an update request with
     * defined table, value and where parameters.
     * @param string|null $success_message success message
     * @return string|boolean
     * @see function setTable()
     * @see function setValue()
     * @see function setWhere()
     * @see function statusQuery()
     * @example
     * <code>
     *           $update = new cjoSql();
     *           $update->setTable(TBL_USER);
     *           $update->setWhere("user_id='".$cur_user_id."'");
     *           $update->setValue("description",$description);
     *           $update->setValue("updatedate",time());
     *           $update->Update('Update successful');
     * </code>
     * @access public
     */
    public function Update($success_message = false) {

        return $this->preparedStatusQuery(
        				'UPDATE `'.$this->getTable().'` SET '.$this->buildPreparedValues().' '. $this->buildPreparedWhere().' '.$this->getLimit(),
                        array_merge($this->values, $this->where_params),
                        $success_message);
    }

    /**
     * Executes an insert request with
     * defined table and value parameters.
     * @param string|null $success_message
     * @return string|boolean
     * @see function setTable()
     * @see function setValue()
     * @see function statusQuery()
     * @example
     * <code>
     *           $insert = new cjoSql();
     *           $insert->setTable(TBL_USER);
     *           $insert->setValue("description",$description);
     *           $insert->setValue("createdate",time());
     *           $insert->Insert('Data saved');
     * </code>
     * @access public
     */
    public function Insert($success_message = false) {
        
        $table  = $this->getTable();
        $ignore = (bool) $this->ignore ? 'IGNORE ' : '';
        
        $state = $this->preparedStatusQuery(
        				'INSERT '.$ignore.'INTO `'.$this->getTable().'` SET '.$this->buildPreparedValues(),
                        $this->values,
                        $success_message);
    
        $this->last_insert_id =  $this->getLastId();

        return $state;
    }

    /**
     * Executes an replace request with
     * defined table and value parameters.
     * @param string|null $success_message
     * @return string|boolean
     * @see function setTable()
     * @see function setValue()
     * @see function statusQuery()
     * @example
     * <code>
     *           $replace = new cjoSql();
     *           $replace->setTable(TBL_USER);
     *           $replace->setValue("description",$description);
     *           $replace->setValue("createdate",time());
     *           $replace->Replace('Data replaced');
     * </code>
     * @access public
     */
    public function Replace($success_message = false) {
        return $this->preparedStatusQuery(
        				'REPLACE INTO `'.$this->getTable().'` SET '.$this->buildPreparedValues().' '. $this->buildPreparedWhere(),
                        array_merge($this->values, $this->where_params),
                        $success_message);
    }

    /**
     * Executes an delete request with
     * defined table and where parameters.
     * @param string|null $success_message
     * @return string|boolean
     * @see function setTable()
     * @see function setWhere()
     * @see function statusQuery()
     * @example
     * <code>
     *           $delete = new cjoSql();
     *           $delete->setTable(TBL_USER);
     *           $delete->setWhere("user_id='".$cur_user_id."'");
     *           $delete->Delete('All deleted');
     * </code>
     * @access public
     */
    public function Delete($success_message = false){
        return $this->preparedStatusQuery(
        				'DELETE FROM `'.$this->getTable().'` '. $this->buildPreparedWhere().' '.$this->getLimit(),
                        $this->where_params,
                        $success_message);
    }
    
    /**
     * Changes the value of ignore var.
     * @param bool 
     * @access public
     */
    public function setIgnore($ignore=true){
        return $this->ignore = (bool) $ignore;
    }

    /**
     * Resets all class vars.
     * @return void
     * @access public
     */
    public function flush() {
        
        $this->flushValues();
        $this->where_params   = array();        
        $this->fieldnames     = NULL;
        $this->raw_fieldnames = NULL;
        $this->tablenames     = NULL;
        $this->last_row       = array();   
        $this->limit          = array();        
        $this->table          = '';
        $this->where_var      = '';  
        $this->query          = '';
        $this->counter        = 0;
        $this->rows           = 0;
        $this->result         = '';
        $this->last_insert_id = '';
        $this->error          = '';
        $this->errno          = '';
        
    }

    /**
     * Resets all values set by setValue().
     * @return void
     * @see function setValue()
     * @access public
     */
    public function flushValues() {
        $this->values = array();
    }
    
    /**
     * Returns true if there is a next value.
     * @return void
     * @access public
     */
    public function hasNext() {
        return $this->counter < $this->rows;
    }
    
    /**
     * Resets the cursor to the first element of the result
     * @return void
     * @access public
     */
    public function reset() {
        // re-execute the statement
        if ($this->stmt) {
            $this->execute($this->where_params);
            $this->counter = 0;
        }
    }
    
    /**
     * Moves the internal data pointer ahead.
     * @return void
     * @see function next();
     * @access public
     */
    public function nextValue() {
        $this->next();
    }


    /**
     * Returnes a SQL singleton objekt
     * @param in|string $DBID
     * @param boolean $force if true creates allways a new object
     * @return object cjoSql object
     * @access public
     */
    public function getInstance($DBID = '', $force = true) {

        static $instance = null;

        if ($instance) {
            $instance->flush();
        }
        else if($createInstance) {
            $instance = new cjoSql($DBID);
        }

        return $instance;
    }

    /**
     * Returns the last inserted id
     * @return int
     * @access public
     */
    public function getLastId() {
        return self::$pdo[$this->DBID]->lastInsertId();
    }
    
    /**
     * Returns an array with the complete result.
     * @param string $query optional if setQuery has been executed before
     * @param string $fetch_type default: PDO::FETCH_ASSOC
     * @return array
     * @access public
     */
    public function getArray($query = NULL, $fetch_type = PDO::FETCH_ASSOC){
        return $this->_getArray($query ? $query :  $this->query, $fetch_type);
    }
    
    
    /**
     * Returns an array with the complete result.
     * @param string $query optional if setQuery has been executed before
     * @param string $fetch_type default: PDO::FETCH_ASSOC
     * @param string $query_type
     * @return array|null
     * @access public
     */
    protected function _getArray($query = false, $fetch_type = PDO::FETCH_ASSOC, $query_type = 'default') {
        
        if (empty($query)) {
            throw new cjoException('sql query must not be empty!');
        }

        self::$pdo[$this->DBID]->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, false);
        switch($query_type) {
            //case 'DBQuery': $this->setDBQuery($query); break;
            default: $this->setQuery($query, $this->where_params);
        }
        
        self::$pdo[$this->DBID]->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, true);
        $results = $this->stmt->fetchAll($fetch_type);
        self::$pdo[$this->DBID]->setAttribute(PDO::ATTR_FETCH_TABLE_NAMES, false);
        return $results;
    }

    /**
     * Returns the error number of the last request.
     * @return int
     * @access public
     */
    public function getErrno() {
        if ($this->stmt === false) {
            return 99999;
        }
        return (int) $this->stmt->errorCode();
    }

    /**
     * Returns the error of the last request.
     * @return int|false if false no error has been registered
     * @access public
     */
    public function getError() {
        global $I18N;
        return ($this->hasError()) ? '<b>'.$I18N->msg("msg_db_error").':</b> '.$this->errorInfo() : false;
    }
    
    /**
     * Prepares the error info.
     * @return string
     * @access public
     */
    public function errorInfo() {
        if ($this->stmt === false) {
            return $this->query;
        }
        else {
            $info = $this->stmt->errorInfo();
            return $info[2];
        }
    }
    
    /**
     * Returns true if false an error has been registered
     * @return int|false 
     * @access public
     */
    public function hasError() {
        return $this->getErrno() != 0;
    }    

    /**
     * Writes the last error.
     * @param string $query
     * @return void
     * @access public
     */
    public function printError($query) {

        echo '<hr/><pre style="font-size:12px">'."\r\n";
        echo 'Query: '.htmlspecialchars($this->query)."\r\n";
        echo 'Params: '.print_r($this->where_params, true)."\r\n";
        if (strlen($this->getRows()) > 0) {
            echo 'Affected Rows: '.$this->getRows()."\r\n";
        }
        if (strlen($this->getError()) > 0) {
            echo 'Error Message: '.htmlspecialchars($this->getError())."\r\n";
            echo 'Error Code: '.$this->getErrno()."\r\n";
        }
        echo '</pre>'."\r\n";
    }

    /**
     * Returns the next auto_increment value of a field
     * @param string $field name of the field
     * @return int
     * @access public
     */
    public function setNewId($field) {
        
        $sql = new cjoSql();

        if ($sql->setQuery("SELECT `".$field."` FROM `".$this->getTable()."` ORDER BY `".$field."` DESC LIMIT 1")) {
            $id = ($sql->getRows() == 0) ? 0 : $sql->getValue($field);
            $id ++;
            $this->setValue($field, $id);
            return $id;
        }
        return false;
    }
    
    /**
     * Returns the field names of the result or table.
     * @param string|null $table name of the table
     * @param int|null $DBID id of the database connection
     * @return array()
     * @access public
     */
    public static function getFieldNames($table, $DBID=1) {
        $sql = new cjoSql($DBID);
        $sql->setDirectQuery('SELECT * FROM '.$table.' LIMIT 0');
        return $sql->_getFieldNames();
    }    
    
    /**
     * Returns the field names of the result or table.
     * @return array()
     * @access public
     */
    public function _getFieldnames() {
        $this->fetchMeta();
        return $this->fieldnames;
    }
    
    /**
     * Returns the table names of a database.
     * @return array()
     * @access protected
     */
    protected function _getTablenames() {
        $this->fetchMeta();
        return $this->tablenames;
    }

    /**
     * Reads metadata of a database
     * @return array()
     * @access protected
     */
    protected function fetchMeta() {

        if ($this->fieldnames === NULL) {
            $this->raw_fieldnames = array();
            $this->fieldnames = array();
            $this->tablenames = array();

            for ($i = 0; $i < $this->getFields(); $i++) {
                $metadata = $this->stmt->getColumnMeta($i);
                
                // strip table-name from column
                $this->fieldnames[$i] = (strpos($metadata['name'], '.') !== false)
                                      ? str_replace($metadata['table'].'.','',$metadata['name'])
                                      : $this->raw_fieldnames[$i] = $metadata['name'];

                $this->raw_fieldnames[$i] = $metadata['name'];

                if (!in_array($metadata['table'], $this->tablenames)) {
                    $this->tablenames[] = $metadata['table'];
                }
            }
        }
    }
    
    /**
     * Escaped den uebergeben Wert fuer den DB Query
     * @param $value den zu escapenden Wert
     */
    public function escape($value) {
        return self::$pdo[$this->DBID]->quote($value);
    } 

    /**
     * Frees all memory associated with the result identifier.
     * @return void
     * @access public
     */
    public function freeResult() {
        if ($this->stmt) $this->stmt->closeCursor();
    } 

    /**
     * Returns information about the columns in the given table.
     * @param string $table name of the table
     * @param int|null $DBID id of the database connection
     * @return array()
     * @access public
     */
    public function showColumns($table, $DBID=''){

        $sql = new cjoSql($DBID);
        $sql->setQuery('SHOW COLUMNS FROM `'.$table.'`');

        $columns = array();
        for($i = 0; $i < $sql->getRows(); $i++){

            $columns [] = array('field' => $sql->getValue('Field'),
                                'name' => $sql->getValue('Field'),
                                'type' => $sql->getValue('Type'),
                                'null' => $sql->getValue('Null'),
                                'key' => $sql->getValue('Key'),
                                'default' => $sql->getValue('Default'),
                                'extra' => $sql->getValue('Extra'));
            $sql->next();
        }
        return $columns;
    }

    /**
     * Returns the non-TEMPORARY tables in the given database.
     * @param int|null $DBID of the database connection
     * @param string|null $table_prefix table prefix
     * @return array()
     * @access public
     */
    public static function showTables($DBID=1, $table_prefix=null) {

        global $CJO;

        $query = 'SHOW TABLES';
        if($table_prefix != null) {
            // replace LIKE wildcards
            $table_prefix = str_replace(array('_', '%'), array('\_', '\%'), $table_prefix);
            $query .= ' LIKE "'.$table_prefix.'%"';
        }

        $sql = new cjoSql($DBID);
        $sql->setQuery('SHOW TABLES');

        $tables = $sql->getArray($query);
        $tables = array_map('reset', $tables);

        return $tables;
    }
    
    /**
     * Writes an error message, if no database connection.
     * @return void;
     * @access protected
     */
    protected function noDBConnection() {

        global $CJO, $I18N;
        
        if (!$CJO['SETUP']) {
            echo '<div style="color:red; font-family:verdana,arial; font-size:11px;">
                CONTEJO Content Management Class SQL | Database down. | Please contact
                <a href="mailto:'.$CJO['ERROR_EMAIL'].'">'.$CJO['ERROR_EMAIL'].'</a> |
                Thank you!</div>';
                exit;
        } 
        $this->error = $I18N->msg('msg_no_db_connection');   
    }

    /**
     * Adds updatedate and unpdateuser values.
     * @param string $user name of the updateuser
     * @return void;
     * @access public
     */
    public function addGlobalUpdateFields($user = false) {

        global $CJO;

        if ($user === false) $user = $CJO['USER']->getValue('name');

        $this->setValue('updatedate', time());
        $this->setValue('updateuser', $user);
    }

    /**
     * Adds createdate and createuser values.
     * @param string $user name of the updateuser
     * @return void;
     * @access public
     */
    public function addGlobalCreateFields($user = false) {

        global $CJO;

        if ($user === false) $user = $CJO['USER']->getValue('name');

        $this->setValue('createdate', time());
        $this->setValue('createuser', $user);
    }

    /**
     * Returns true if the given var is a valid OOArticleSlice object.
     * @param object $slice
     * @return boolean
     * @access public
     */
    public static function isValid($sql) {
        return is_object($sql) && is_a($sql, 'cjoSql');
    }
  
    
    /**
     * Validates the database connection
     * @param string $host
     * @param string $database
     * @param string $login
     * @param string $password
     * @param bool $create
     * @return boolean
     * @access public
     */
    static public function checkDbConnection($host, $database, $login, $password, $create = false) {
        
        global $I18N;

        if (empty($host) || empty($database) || empty($login) || empty($password)) {
            return false;
        }
        
        try {
            $conn = self::createConnection($host, $database, $login, $password);
            return true;
        }
        catch (PDOException $e) {

            if (strpos($e->getMessage(), 'SQLSTATE[42000]') !== false) {
                
                if ($create) {
                    try {
                        // use the "mysql" db for the connection
                        $conn = self::createConnection($host, 'mysql', $login, $password);

                        if ($conn->exec('CREATE DATABASE `'.$database.'`') === 1) {
                            $conn->exec('ALTER DATABASE `'.$database.'` DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci' );
                            return true;
                        }
                        // unable to create db
                        cjoMessage::addError($I18N->msg('msg_cannot_create_db'));
                    }
                    catch (PDOException $e) {
                        // unable to find database
                        cjoMessage::addError($I18N->msg('msg_cannot_find_db'));
                    }
                }
                else {
                    // unable to find database
                    cjoMessage::addError($I18N->msg('msg_cannot_find_db'));
                }
            }
            else if(strpos($e->getMessage(), 'SQLSTATE[28000]') !== false) {
                // unable to connect
                cjoMessage::addError($I18N->msg('msg_no_db_connection'));
            }
            else {
                // we didn't expected this error, so rethrow it to show it to the admin/end-user
                cjoMessage::addError($e->getMessage());
                //throw $e;
            }
        }

        // close the connection
        $conn = null;

        return  false;
    }

    /**
     * Advances to a rowset in a multi-rowset statement handle 
     * @param int $counter
     * @param constant $fetch_type The fetch mode must be one of the PDO::FETCH_* constants. 
     * return void
     * @access public
     */
    public function setCurrent($counter,$fetch_type = PDO::FETCH_ASSOC) {
        
        $counter = (int) $counter;

        if ($counter < 0 || $counter > $this->getRows() || 
           ($counter == $this->counter && $counter != 0)) return false;
           
        $this->reset();

        for ($i=0;$i<$this->getRows();$i++) {
            if ($i == $counter) {
                $this->counter = $i;
                $this->last_row = $this->stmt->fetch($fetch_type);
                return;
            }
            else {
                $this->stmt->fetch($fetch_type);
            }
        }
    }
    
    // ----------------- iterator interface

    /**
     * @see http://www.php.net/manual/en/iterator.rewind.php
     */
    public function rewind() {
        $this->reset();
    }

    /**
     * @see http://www.php.net/manual/en/iterator.current.php
     */
    public function current() {
        return $this;
    }

    /**
     * @see http://www.php.net/manual/en/iterator.key.php
     */
    public function key() {
        return $this->counter;
    }

    /**
     * @see http://www.php.net/manual/en/iterator.next.php
     */
    public function next($fetch_type = PDO::FETCH_ASSOC) {
        $this->counter++;
        $this->last_row = ($this->stmt) ? $this->stmt->fetch($fetch_type) : array();
    }

    /**
     * @see http://www.php.net/manual/en/iterator.valid.php
     */
    public function valid(){
        return $this->hasNext();
    }   

    /**
     * Returns a string representation of the result
     * for debugging purposes.
     * @return string
     * @access public
     */
    public function __toString() {

        $return = '';
        for ($i = 0; $i < $this->getRows(); $i ++) {
            foreach ($this->values as $value) {
                $return .= $value." \r\n";
            }
            $return .= "<br/>";
            $this->counter++;
        }
        echo 'sql, '.$return;
    }
}