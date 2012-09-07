<?php
 /**
  * mIterator class
  * @author Gouverneur Thomas <tgo@espix.net>
  * @copyright Copyright (c) 2007-2011, Gouverneur Thomas
  * @version 1.0
  * @package objects
  * @subpackage mysql
  * @category classes
  * @filesource
  */

/**
 * The goal of this object is to seek through very big tables
 * without loading the whole table in memory.
 *
 */

class mIterator {
  
     private $_table = "";
     private $_where = "";
     private $_index = "";
     private $_cindex = "";
     private $_arr = array();
     private $_cc = "";
     private $_cur = 0;

     private $_pos = -1;
     private $_num = -1;
     private $_step = 1000;

     private function _fetch() {
       $m = MysqlCM::getInstance();
       if ($this->_pos >= $this->_num) return false;
       if ($this->_pos != -1) 
         $this->_pos += $this->_step;
       else
	 $this->_pos = 0;
      
       if ($idx = $m->fetchIndex($this->_index, $this->_table, $this->_where.' LIMIT '.$this->_pos.','.$this->_step)) {
         $this->_arr = array();
         foreach($idx as $v) {
	   $o = new $this->_cc();
           foreach ($v as $k => $kv) {
	     if (isset($o->{$k})) {
	       $o->{$k} = $kv;
	     } else { continue; }
 	   }
	   array_push($this->_arr, $o);
	 }
         return true;
       }
       return false;
     }


     public function __construct($cc, $index, $table, $where, $cindex) {
       $this->_index = $index;
       $this->_table = $table;
       $this->_where = $where;
       $this->_cc = $cc; /* Example object */
       $this->_cindex = $cindex.' AS n';

       $m = MysqlCM::getInstance();
       /* Count row number for query */
       if ($idx = $m->fetchIndex($this->_cindex, $this->_table, $this->_where)) {
         if (isset($idx[0]) && isset($idx[0]['n'])) {
           $this->_num = $idx[0]['n'];
           $this->_pos = -1;
           $this->_cur = 0;
	   $this->_arr = array();
 	   return;
	 }
       }
       /* @TODO: Handle errors */
     }

     /**
      * Returns the next element of the SQL table and fetch
      * next ones if needed.
      */
     public function next() {
       if ($this->_cur >= $this->_num) {
         return false;
       }

       if ($this->_cur == -1 || $this->_num == -1) {
         return false;
       }
       if (!count($this->_arr)) {
	 $rc = $this->_fetch();
	 if (!$rc) {
	   return false;
         }
       }
       /* Return first elem of the array */
       $this->_cur++;
       return array_shift($this->_arr);
     }

     public function seek($id) {
	/* @TODO */
     }

     public function pos() {
       return $this->_cur;
     }

     public function left() {
       return ($this->_num - $this->_cur);
     }
     
     public function count() {
       return $this->_num;
     }

     public function setStep($s) {
       $this->_step = $s;
     }

}


?>
