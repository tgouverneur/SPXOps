<?php
/**
 * MySQL Connection Manager
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2015, Gouverneur Thomas
 * @version 1.1
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
if (!defined('SQL_NONE')) {
    define('SQL_NONE',   0);  /* not used */
 define('SQL_INDEX', 1);   /* is the property an index ? */
 define('SQL_WHERE', 2);   /* is the property an part of the where condition when search for object */
 define('SQL_EXIST', 4);   /* is the property a part of the condition for the object to exist in the db */
 define('SQL_PROPE', 8);   /* is the property should be fetched ? */
 define('SQL_SORTA', 16);  /* sort with this field by ASC ? */
 define('SQL_SORTD', 32);  /* sort with this field by DESC ? */
}

/**
 * MySQL Connection Manager
 *
 * @category classes
 * @package objects
 * @subpackage config
 * @author Gouverneur Thomas <tgo@espix.net>
 */
class MySqlCM
{
  /**
   * Holds the db link
   */
  private $_link = null;
  /**
   * Keep the latest's query result
   */
  private $_res = null;
  /**
   * Keep the latest's query result count
   */
  private $_nres = null;
  /**
   * Latest error given by the server
   * @var string
   */
  private $_error = null;
  /**
   * Number of rows affected by latest query
   */
  private $_affect = null;

    private $_reconnect = true;

    private $_pid = -1;

  /**
   * Debug mode
   */
  private $_debug = false;
    private $_dfile = false;
    private $_dfd = null;
    private $_elapsed = 0;
  /**
   * Error logging
   */
  private $_errlog = false;
    private $_errfile = false;
    private $_efd = null;

  /**
   * Singleton variable
   */
  private static $_instance = array();

    public static function delInstance($name = null)
    {
        if (!$name) {
            $name = 'main';
        }
        $hash = hash('crc32', $name, false);
        self::$_instance[$hash] = null;
    }

  /**
   * is _link not null?
   */
  public function isLink()
  {
      if ($this->_link) {
          return true;
      }

      return false;
  }

  /**
   * Returns the singleton instance
   */
  public static function getInstance($name = null)
  {
      if (!$name) {
          $name = 'main';
      }
      $hash = hash('crc32', $name, false);
      if (!isset(self::$_instance[$hash])) {
          $c = __CLASS__;
          self::$_instance[$hash] = new $c();
      }
      if (self::$_instance[$hash]->_pid != getmypid()) {
          self::delInstance($name);
          $c = __CLASS__;
          self::$_instance[$hash] = new $c();
          self::$_instance[$hash]->_ePrint('['.time().']['.self::$_instance->_pid.']['.$name.'] fork() detected'."\n");
      }

      return self::$_instance[$hash];
  }

    public function quote($str)
    {
        if ($this->_link) {
            return $this->_link->quote($str);
        } else {
            throw new SPXException("Cannot use MysqlCM::quote when disconnected");
        }
    }

  /**
   * Enable error logging
   */
  private function _errLog($fname)
  {
      $this->_errlog = true;
      $this->_errfile = $fname;
      $this->_efd = null;
      if (!($this->_efd = fopen($this->_errfile, "a"))) {
          $this->_errfile = "";
          $this->_efd = null;
          $this->_errlog = false;

          return false;
      }

      return true;
  }

  /**
   * Write entry to error log file
   */
  private function _ePrint($line, $args = null)
  {
      if ($this->_errlog && $this->_efd && !empty($line)) {
          if ($args) {
              return vfprintf($this->_efd, $line, $args);
          } else {
              return fprintf($this->_efd, "%s", $line);
          }
      }

      return false;
  }

  /**
   * Enable debug mode
   */
  private function _deBug($fname)
  {
      $this->_debug = true;
      $this->_dfile = $fname;
      $this->_dfd = null;
      if (!($this->_dfd = fopen($this->_dfile, "a"))) {
          $this->_dfile = "";
          $this->_dfd = null;
          $this->_debug = false;

          return false;
      }

      return true;
  }

  /**
   * Write entry to debug log file
   */
  private function _dPrint($line, $args = null)
  {
      if ($this->_debug && $this->_dfd) {
          return vfprintf($this->_dfd, $line, $args);
      }

      return false;
  }

  /**
   * Measure the time taken between two call of this function
   */
  private function _Time()
  {
      if (!$this->_elapsed) {
          $this->_elapsed = time();

          return $this->_elapsed;
      } else {
          $ret = (time() - $this->_elapsed);
          $this->_elapsed = 0;

          return $ret;
      }
  }

  /**
   * Destructor
   */
  public function __destruct()
  {
    if ($this->_link) $this->disconnect();

    if (Config::$mysql_debug && $this->_dfd) {
        fclose($this->_dfd);
    }
  }

  /**
   * Constructor
   */
  public function __construct()
  {
      $this->_pid = getmypid();

      if (Config::$mysql_debug) {
          $this->_deBug(Config::$mysql_debug);
      }
      if (Config::$mysql_errlog) {
          $this->_errLog(Config::$mysql_errlog);
      }
  }

  /**
   * Avoid the call of __clone()
   */
  public function __clone()
  {
      trigger_error("Cannot clone a singleton object, use ::instance()", E_USER_ERROR);
  }

  /**
   * Accessors
   */
  public function getError()
  {
      return $this->_error;
  }
    public function getNR()
    {
        return $this->_nres;
    }
    public function getAffect()
    {
        return $this->_affect;
    }

  /**
   * Connect to the database
   * store the link resource in $this->_link,
   * @return 0 if ok, non-zero if any error
   */
  public function connect()
  {
      $attempts = 0;

      $dbstring = "mysql:host=".Config::$mysql_host;
      $dbstring .= "; port=".Config::$mysql_port;
      $dbstring .= "; dbname=".Config::$mysql_db;
      do {
          try {
              $this->_link = new PDO($dbstring,
                               Config::$mysql_user,
                               Config::$mysql_pass,
                   array(PDO::ATTR_PERSISTENT => false,
                     PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, ));
              $this->_link->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
          } catch (PDOException $e) {
              $this->_error = $e->getMessage();
              if (strpos($this->_error, '2006 MySQL') !== false && $this->_reconnect) {
                  $this->reconnect();
                  $this->_error = null;
              }
              if ($this->_debug) {
                  $this->_dPrint("[".time()."][$attempts] Connection failed to database ".Config::$mysql_db."@".Config::$mysql_host.":".Config::$mysql_port."\n");
              }

              return -1;
          }
      } while ($attempts++ < 3);
      if ($this->_debug) {
          $this->_dPrint("[".time()."] Connection succesfull to database ".Config::$mysql_db."@".Config::$mysql_host.":".Config::$mysql_port."\n");
      }

      return 0;
  }

  /**
   * Disconnect the database link;
   * @return 0 if ok, non-zero if any error
   */
  public function disconnect()
  {
      if ($this->_debug) {
          $this->_dPrint("[".time()."] Connection closed to database ".Config::$mysql_db."@".Config::$mysql_host.":".Config::$mysql_port."\n");
      }

      unset($this->_link);
      $this->_link = null;

      return 0;
  }

  /**
   * Count object matching criteria
   * @return -1 if error, else the number of row
   */
  public function count($table, $where = "")
  {
      $this->_nres = null;
      $args = array();

      $qf = 'SELECT COUNT(*) FROM `%s` %s';
      if (is_array($where)) {
          $query = sprintf($qf, $table, $where['q']);
          $args = $where['a'];
      } else {
          $query = sprintf($qf, $table, $where);
      }

      if (!$this->_Query($query, $args)) {
          try {
              $row = $this->_res->fetchAll(PDO::FETCH_ASSOC);
          } catch (PDOException $e) {
              return -1;
          }
          if (count($row)) {
              $row = $row[0];
          }
          if (isset($row['COUNT(*)'])) {
              $data = $row['COUNT(*)'];
          }

          $this->_res->closeCursor();
          unset($this->_res);

          return $data;
      } else {
          return -1;
      }
  }

  /**
   * Query mysql server for select
   * @return datas selected or -1 if error
   */
  public function select($fields, $table, $where = "", $sort = "")
  {
      $this->_nres = null;
      $args = array();

      $qf = 'SELECT %s FROM `%s` %s %s';
      if (is_array($where)) {
          $query = sprintf($qf, $fields, $table, $where['q'], $sort);
          $args = $where['a'];
      } else {
          $query = sprintf($qf, $fields, $table, $where, $sort);
      }

      if (!$this->_Query($query, $args)) {
          $data = array();
      try {
          $data = $this->_res->fetchAll(PDO::FETCH_ASSOC);
      } catch (PDOException $e) {
          return false;
      }
          $this->_nres = count($data);
          $this->_res->closeCursor();
          unset($this->_res);
          return $data;

      } else {
          return false;
      }
  }

  /**
   * Call stored procedure with provided arguments
   * and get back values
   * @return 0=success -1=error
   */
  public function call($proc, $args = array(), &$ret = array())
  {
      $cq = 'CALL '.$proc.'(';
      $first = 1;
      foreach ($args as $name => $value) {
          if ($first-- < 1) {
              $cq .= ',';
          }
          $cq .= ':'.$name;
      }
      foreach ($ret as $name => $value) {
          if ($first-- < 1) {
              $cq .= ',';
          }
          $cq .= '@'.$name;
      }
      $cq .= ')';
      $this->_res = $this->_link->prepare($cq);
      $keys = array_keys($args);
      $c_keys = count($keys);
      for ($i=0;$i < $c_keys; $i++) {
          $this->_res->bindParam($keys[$i], $args[$keys[$i]]);
      }
      if (!$this->_res->execute()) {
          $this->_res->closeCursor();
          $this->_res = null;

          return -1;
      }
      $this->_res->closeCursor();
      if (count($ret)) {

        /* fetch params */
        $pq = 'SELECT ';
          $first = 1;
          foreach ($ret as $name => $value) {
              if (!$first--) {
                  $pq .= '.';
              }
              $pq .= '@'.$name.' AS '.$name;
          }
          $pq .= ';';
          $r = $this->_link->query($pq)->fetch(PDO::FETCH_ASSOC);
          foreach ($ret as $name => $value) {
              if (isset($r[$name])) {
                  $ret[$name] = $r[$name];
              }
          }

      }
      return 0;
  }

  /**
   * Insert data into table
   * @return -1 if error, 0 if ok
   */
  public function insert($fields, $values, $table)
  {
      $args = array();
      if (is_array($values)) {
         $args = $values['a'];
         $values = $values['v'];
      }
      $query = "INSERT INTO ".$table."(".$fields.") VALUES(".$values.")";

      if (!$this->_rQuery($query, $args)) {
          $this->_nres = $this->_link->lastInsertId();

          return 0;
      } else {
          return -1;
      }
  }

  /**
   * Remove data from table
   * @return -1 if error, else the number of affected rows
   */
  public function delete($table, $cond)
  {
      $args = array();
      if (is_array($cond)) {
         $args = $cond['a'];
         $cond = $cond['q'];
      }
   
      $qf = 'DELETE FROM `%s` %s';
      $query = sprintf($qf, $table, $cond);


      if (!$this->_rQuery($query, $args)) {
          return $this->_affect;
      } else {
          return -1;
      }
  }

  /**
   * update data in table
   * @return -1 if error, else the number of updated rows
   */
  public function update($table, $set, $where)
  {
      $args = array();
      if (is_array($set)) {
         $args = $set['a'];
         $set = $set['v'];
      }
      if (is_array($where)) {
	 foreach($where['a'] as $k => $v) {
           $args[$k] = $v;
	 }
         $where = $where['v'];
      }

      $qf = 'UPDATE `%s` SET %s %s';
      $query = sprintf($qf, $table, $set, $where);

      if (!$this->_rQuery($query, $args)) {
          return 0;
      } else {
          return -1;
      }
  }

  /**
   * Fetch index of a table following $where condition
   * @return The index datas of the table
   */
  public function fetchEntries($index, $table, $where)
  {
      $this->_nres = null;
      $args = array();

      if (is_array($index)) {

          $str_index = '';
          $i = 0;
          foreach ($index as $k) {

            if ($i && $i < count($index)) {
                $str_index .= ',';
            }
            $str_index .= sprintf('`%s`', $k);
            $i++;
          }
          $index = $str_index;
      }

      $qf = 'SELECT %s FROM %s %s';
      if (is_array($where)) {
          $query = sprintf($qf, $index, $table, $where['q']);
          $args = $where['a'];
      } else {
          $query = sprintf($qf, $index, $table, $where);
      }

      if (!$this->_Query($query, $args)) {
          $data = array();
          try {
              $data = $this->_res->fetchAll(PDO::FETCH_ASSOC);
          } catch (PDOException $e) {
              throw($e);
          }

          $this->_res->closeCursor();
          unset($this->_res);

          return $data;
      } else {
          return 0;
      }
  }


  /**
   * Fetch index of a table following $where condition
   * @return The index datas of the table
   */
  public function fetchIndex($index, $table, $where)
  {
      $this->_nres = null;
      $args = array();

      $qf = 'SELECT %s FROM %s %s';
      if (is_array($where)) {
          $query = sprintf($qf, $index, $table, $where['q']);
          $args = $where['a'];
      } else {
          $query = sprintf($qf, $index, $table, $where);
      }

      if (!$this->_Query($query, $args)) {
          $data = array();
          try {
              $data = $this->_res->fetchAll(PDO::FETCH_ASSOC);
          } catch (PDOException $e) {
              throw($e);
          }

      $this->_res->closeCursor();
          unset($this->_res);

          return $data;
      } else {
          return 0;
      }
  }

  /**
   * RAW Query database and handle errors
   * @return 0 if ok, non-zero if any error
   */
  private function _rQuery($query, $args = array())
  {
      if ($this->_debug) {
          $this->_Time();
      }
      if (!$this->_link) {
          return -1;
      }
      $attempts = 0;

      if (isset($this->_res) && $this->_res) {
          $this->_res->closeCursor();
          unset($this->_res);
      }

      do {
          try {

              unset($this->_res);
              $this->_res = $this->_link->prepare($query);

              $this->bindQueryParams($args);

              if ($this->_res->execute() === false) {

                  $this->_error = $this->_link->errorInfo();
                  $this->_error = $this->_error[2];

                  if ($this->recoCheck()) {
                      continue;
                  }

                  if ($this->_debug) {
                      $this->_Time();
                  }
                  if ($this->_errlog) {
                      $this->_ePrint("[".time()."][".$this->_pid."] Failed _rquery (".$this->_affect."): $query\n");
                      $this->_ePrint("\tError: ".$this->_error."\n");
                  }

                  return -1;
              } else {
                  $this->_affect = $this->_res->rowCount();
                  if ($this->_debug) {
                      $this->_dPrint("[".time()."] (".$this->_Time().") ".$query."\n");
                  }

                  return 0;
              }
          } catch (PDOException $e) {

              if ($this->recoCheck($e)) {
                  continue;
              }
              if ($this->_debug) {
                  $this->_Time();
              }
              if ($this->_errlog) {
                  $this->_ePrint("[".time()."][$attempts] Failed _query: $query\n");
                  $this->_ePrint("\tError: ".$e->getMessage()."\n");
                  $e = new Exception();
                  $this->_ePrint("\tBT: ".$e->getTraceAsString()."\n");
              }
          }
      } while ($attempts++ < 3);
  }

    public function rawQuery($q)
    {
        return $this->_Query($q);
    }

  private function bindQueryParams($a) {

      if (is_array($a)) {
          $keys = array_keys($a);
          $c_keys = count($keys);
          for ($i=0;$i < $c_keys; $i++) {
              if (is_array($a[$keys[$i]])) {
                  $this->_res->bindParam($keys[$i], $a[$keys[$i]][0], $a[$keys[$i]][1]);
                  if ($this->_debug) {
                      $this->_dPrint("[".time()."] (".$this->_Time().") Param ".$keys[$i]." bound with ".$a[$keys[$i]][0]." \n");
                  }  
              } else {
                  $this->_res->bindParam($keys[$i], $a[$keys[$i]]);
                  if ($this->_debug) {
                      $this->_dPrint("[".time()."] (".$this->_Time().") Param ".$keys[$i]." bound with ".$a[$keys[$i]]." \n");
                  }
              }
          }
      }
      return;
  }

  /* returns true if continue; needed */
  private function recoCheck(Exception $e = null) {

      if ($e) {
        $this->_error = $e->getMessage();
      }

      $c = false;

      if (strpos($this->_error, 'has gone away') !== false && $this->_reconnect) {
          $this->reconnect();
          $c = true;
      }
      if (strpos($this->_error, 'Cannot execute queries while other unbuffered queries are active') !== false && $this->_reconnect) {
          $this->reconnect();
          $c = true;
      }
      if ($e) {
          return false;
      } else {
          return $c;
      }
  }

  /**
   * Query database and handle errors
   * @return 0 if ok, non-zero if any error
   */
  private function _Query($query, $args = array())
  {
      $attempts = 0;
      if ($this->_debug) {
          $this->_Time();
      }
      if (!$this->_link) {
          return -1;
      }

      do {
          try {
              if (isset($this->_res) && $this->_res) {
                  $this->_res->closeCursor();
                  unset($this->_res);
              }

              $this->_res = $this->_link->prepare($query);
              $this->bindQueryParams($args);

              if ($this->_res->execute()) {
                  if ($this->_debug) {
                      $this->_dPrint("[".time()."] (".$this->_Time().") ".$query."\n");
                  }
                  $this->_nres = $this->_res->rowCount();

                  return 0;
              } else {
                  $this->_error = $this->_res->errorInfo();
                  $this->_error = $this->_error[2];

                  if ($this->recoCheck()) {
                      continue;
                  }

                  if ($this->_debug) {
                      $this->_Time();
                  }
                  if ($this->_errlog) {
                      $this->_ePrint("[".time()."][".$this->_pid."] Failed _query: $query\n");
                      $this->_ePrint("\tError: ".$this->_error."\n");
                      $e = new Exception();
                      $this->_ePrint("\tBT: ".$e->getTraceAsString()."\n");
                  }

                  return -1;
              }
          } catch (PDOException $e) {

              if ($this->recoCheck($e)) {
                continue;
              }

              if ($this->_debug) {
                  $this->_Time();
              }
              if ($this->_errlog) {
                  $this->_ePrint("[".time()."][$attempts][".$this->_pid."] Failed _query: $query\n");
                  $this->_ePrint("\tError: ".$e->getMessage()."\n");
                  $this->_ePrint("\tBT: ".$e->getTraceAsString()."\n");
              }
          }
      } while ($attempts++ < 3);

      return -1;
  }

  /**
   * Lock specified table
   * @return -1 if error, 0 if ok
   */
  public function lockTable($table, $what = "WRITE")
  {
      $query = "LOCK TABLE `$table` $what";

      if (!$this->_rQuery($query)) {
          return 0;
      } else {
          return -1;
      }
  }

  /**
   * Unlock every previously locked tables
   * @return -1 if error, 0 if ok
   */
  public function unlockTables()
  {
      $query = "UNLOCK TABLES";

      if (!$this->_rQuery($query)) {
          return 0;
      } else {
          return -1;
      }
  }

    private function reconnect()
    {
        $this->disconnect();
        if ($this->_errlog) {
            $this->_ePrint("[".time()."] Reconnection in progress...\n");
        }
        $this->_ePrint("\tError: ".$this->_error."\n");
        $this->connect();
    }
}
