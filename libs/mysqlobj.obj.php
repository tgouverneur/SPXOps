<?php
 /**
  * mysqlObj management
  * @author Gouverneur Thomas <tgo@espix.net>
  * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
  * @version 1.0
  * @package objects
  * @subpackage mysql
  * @category classes
  * @filesource
  */

class mJT {
   public $ar = '';
   public $oc = '';
   public $jt = '';
   public $src = array();
   public $dst = array();
   public $attrs = array();

   public function __construct($ar, $oc, $jt, $src, $dst, $attrs = array()) {
     $this->ar = $ar;
     $this->oc = $oc;
     $this->jt = $jt;
     $this->src = $src;
     $this->dst = $dst;
     $this->attrs = $attrs;
   }
}

class mRL {
 
   public $ar = '';
   public $oc = '';
   public $fks = array();

   public function __construct($ar, $oc, $fk) {
     $this->ar = $ar;
     $this->oc = $oc;
     $this->fk = $fk;
   }

   public function __toString() {
     return $this->ar.'/'.$this->oc;
   }
}

class mFK {
 
   public $in = '';
   public $on = '';
   public $oc = '';

   public function fetch($pthis, $f_fetch = true) {

     $pthis->{$this->on} = new $this->oc($pthis->{$this->in});

     if ($f_fetch) {
       $rc = $pthis->{$this->on}->fetchFromId();
       if ($rc) {
         throw new SPXException('Cannot fetch '.$this->oc.' object with id '.$pthis->{$this->in});
       }
     }
     return;
   }

   public function __construct($in, $on, $oc) {
     $this->in = $in;
     $this->on = $on;
     $this->oc = $oc;
   }
}

/**
 * Base class for all object that use mysql
 */
class mysqlObj
{
  protected $_my = array();
  protected $_myc = array();
  protected $_table = "";
  protected $_nfotable = "";
  protected $_datas = array();

  protected $_fks = array();
  protected $_rel = array();
  protected $_jt = array();

  /* additionnal datas */
 
  /**
   *
   */
  public function dataCount() {

    if (!$this->_nfotable)
      return null;

    return count($this->_datas);
  }

  /**
   *
   */
  public function dataKeys() {

    if (!$this->_nfotable)
      return null;

    return array_keys($this->_datas);
  }

  /**
   *
   */
  public function data($name) {

    if (!$this->_nfotable)
      return null;

    if (isset($this->_datas[$name])) {
      return $this->_datas[$name];
    } else {
      return null;
    }
  }

  public function delAllData() {
    if (!$this->_nfotable)
      return null;

    $this->fetchData();
    foreach($this->_datas as $v) {
      $this->delData($v);
    }
  }

  /**
   *
   */
  public function delData($name) {

    if (!$this->_nfotable)
      return null;

    /* Build index list */
    $ids = array_keys($this->_my, SQL_INDEX);
    if (count($ids) == 0)
      throw new SPXException('No Index found for this object');

    $my = mysqlCM::getInstance();

    $where = "";
    $w=0;
    foreach ($ids as $id) {
      if ($id === FALSE)
        continue; /* no index in obj */

      if ($w) { $where .= " AND "; } else { $where .= "WHERE "; }
      $where .= "`".$id."`='".$this->{$this->_myc[$id]}."'";
      $w++;
    }
    if ($w) { $where .= " AND "; } else { $where .= "WHERE "; }
    $where .= "`name`='$name'";
    
    return $my->delete($this->_nfotable, $where);
  }

  /**
   *
   */
  public function setData($name, $value) {

    if (!$this->_nfotable)
      throw new SPXException('No NFO table for this class');

    /* Build index list */
    $ids = array_keys($this->_my, SQL_INDEX);
    if (count($ids) == 0)
      throw new SPXException('No Index found for this object');

    $my = mysqlCM::getInstance();

    if (isset($this->_datas[$name])) {

      if ($value === $this->_datas[$name])
        return 0;

      /* Update */

      $where = "";
      $w=0;
      foreach ($ids as $id) {
        if ($id === FALSE)
          continue; /* no index in obj */

        if ($w) { $where .= " AND "; } else { $where .= "WHERE "; $w++; } 
        $where .= "`".$id."`='".$this->{$this->_myc[$id]}."'";
        
      }
      $where .= " AND `name`='".$name."'";
      $set = "`value`=".$my->quote($value).", `u`='".time()."'";
      if ($my->update($this->_nfotable, $set, $where)) {
        throw new SPXException('Unable to update NFO entry');
      }

    } else {
      /* Insert */
      $w=3;
      $fields = "`name`, `value`, `u`";
      $values = "'$name',".$my->quote($value).",".time();
      foreach ($ids as $id) {
        if ($id === FALSE)
          continue; /* no index in obj */

        if ($w) { $fields .= " , "; $values .= " , "; }
        $fields .= "`".$id."`";
        $values .= $my->quote($this->{$this->_myc[$id]});

      }
      if ($my->insert($fields, $values, $this->_nfotable)) {
        throw new SPXException('Unable to insert NFO entry');
      }
    }
    $this->_datas[$name] = $value;
    return 0;
  }

  /**
   *
   */
  public function fetchData() {

    if (!$this->_nfotable)
      return;

    /* Build index list */
    $ids = array_keys($this->_my, SQL_INDEX);
    if (count($ids) == 0)
      throw new SPXException('No Index in this object');

    $my = mysqlCM::getInstance();

    $where = "";
    $w=0;
    foreach ($ids as $id) {
      if ($id === FALSE)
        continue; /* no index in obj */

      if ($w) { $where .= " AND "; } else { $where .= "WHERE "; $w++;}
      $where .= "`".$id."`='".$this->{$this->_myc[$id]}."'";

    }
    $fields = "`name`,`value`";

    if (($data = $my->select($fields, $this->_nfotable, $where)) === FALSE)
      throw new SPXException('Select failed');
    else
    {
      if ($my->getNR() != 0)
      {
	foreach ($data as $datum) {
          $name = $datum['name'];
          $value = $datum['value'];
          $this->_datas[$name] = $value;
	}
      }
    }
    return 0;
  }

   /* mysql common functions */

  /**
   * Fetch object's index in the table
   * @return -1 on error
   */
  function fetchId()
  {
    $id = array_search(SQL_INDEX, $this->_my);
    if ($id === FALSE) 
      throw new SPXException('No Index in this object');

    $where = "WHERE ";
    $i=0;
    foreach ($this->_my as $k => $v) {
      if ($v & SQL_WHERE)
      {
        if ($i && $i < count($this->_my)) $where .= " AND ";

        $where .= "`".$k."`='".$this->{$this->_myc[$k]}."'";
        $i++;
      }
    }
    
    $my = mysqlCM::getInstance();
    if (($data = $my->select("`".$id."`", $this->_table, $where)))
    {
      if ($my->getNR() == 1)
      {
        $this->{$this->_myc[$id]} = $data[0][$id];
      }
      else return -1;
    } else return -1;
  }

  /**
   * insert object in database
   * @return -1 on error
   */
  function insert()
  {
    $values = "";
    $names = "";
    $i=0;

    if (isset($this->t_add)) {
      $this->t_add = time();
    }

    if (isset($this->t_upd)) {
      $this->t_upd = time();
    }

    $my = mysqlCM::getInstance();
    foreach ($this->_my as $k => $v) {

      if (($v & SQL_INDEX) && (empty($this->{$this->_myc[$k]}) || $this->{$this->_myc[$k]} == -1)) {
        continue; /* skip index */
      }

      if ($i && $i < count($this->_my)) { 
        $names .= ","; $values .= ","; 
      }
      $names .= "`".$k."`";
      $values .= $my->quote($this->{$this->_myc[$k]});
      $i++;
    }

    $r = $my->insert($names, $values, $this->_table);
    $id = array_search(SQL_INDEX, $this->_my);
    $vid = $this->_myc[$id];

    if ($vid !== FALSE && (empty($this->{$vid}) || $this->{$vid} == -1)) 
      $this->{$vid} = $my->getNR();

    return $r;
  }

  /**
   * Update the object into database
   * @return -1 on error
   */
  function update()
  {
    //$id = array_search(SQL_INDEX, $this->_my);
    $ids = array_keys($this->_my, SQL_INDEX);
    if (count($ids) == 0)
      throw new SPXException('No Index in this object');
    $w=0;

    if (isset($this->t_upd)) {
      $this->t_upd = time();
    }

    $my = mysqlCM::getInstance();
    foreach ($ids as $id) {
      if ($id === FALSE)
        continue; /* no index in obj */

      if (!$w) {
        $where = "WHERE `".$id."`=".$my->quote($this->{$this->_myc[$id]});
        $w++;
      } else {
	$where .= " AND `".$id."`=".$my->quote($this->{$this->_myc[$id]});
      }
    }
    $set = "";
    $i = 0;
    foreach ($this->_my as $k => $v) {

      if ($v == SQL_INDEX) continue; /* skip index */

      if ($i && $i < count($this->_my)) { 
        $set .= ","; 
      }
      $set .= "`".$k."`=".$my->quote($this->{$this->_myc[$k]});
      $i++;
    }
    return $my->update($this->_table, $set, $where);

  }

  /**
   * Does the object exists in database ?
   * @return 0 = no, 1 = yes
   */
  function existsDb()
  {
    $where = " WHERE ";
    $i = 0;
    $my = mysqlCM::getInstance();
    foreach ($this->_my as $k => $v) {
      
      if ($v == SQL_INDEX) continue; /* skip index */
      if (!($v & SQL_EXIST)) continue; /* skip properties that shouldn't define unicity of object */
      if ($i && $i < count($this->_my)) $where .= " AND ";

      $where .= "`".$k."`=".$my->quote($this->{$this->_myc[$k]});
      $i++;
    }
    
    $id = array_search(SQL_INDEX, $this->_my);

    if ($id === FALSE)
    {
      $id = array_keys($this->_my); /* if no index, take the first field of the table */
      $id = $id[0];
    } 

    if (($data = $my->select("`".$id."`", $this->_table, $where)) == FALSE)
      return 0;
    else {
      if ($my->getNR()) {
        if ($this->{$this->_myc[$id]} != -1 && $data[0][$id] == $this->{$this->_myc[$id]}) { return 1; }
        if ($this->{$this->_myc[$id]} == -1) return 1;
      } else
        return 0;
    }
  }

  /**
   * Has the object changed ?
   * @return 0 = no; 1 = yes
   */
  function isChanged()
  {
    $where = " WHERE ";
    $i = 0;
    
    if (!$this->existsDb()) return 0;

    foreach ($this->_my as $k => $v) {
      
      if ($v == SQL_INDEX) continue; /* skip index */
      if (!($v & SQL_PROPE)) continue;
      if ($i && $i < count($this->_my)) $where .= " AND ";

      $where .= "`".$k."`='".$this->{$this->_myc[$k]}."'";
      $i++;
    }
    
    $id = array_search(SQL_INDEX, $this->_my);

    if ($id !== FALSE) {
      
      if ($this->{$this->_myc[$id]} != -1) $where .= " AND `".$id."`='".$this->{$this->_myc[$id]}."'";
      
      $my = mysqlCM::getInstance();
      if (($data = $my->select("`".$id."`", $this->_table, $where)) == FALSE)
        return 1;
      else {
        if ($my->getNR()) {
          return 0;
        } else
	  return 1;
      }
    }
   
  }

  /**
   * Fetch object with XXX
   * @return -1 on error
   */
  function fetchFromFields($on_fields)
  {
    $i = 0;
    $fields = "";
    foreach ($this->_my as $k => $v) {
      if ($i && $i < count($this->_my)) $fields .= ",";

      $fields .= "`".$k."`";
      $i++;
    }    
     
    $my = mysqlCM::getInstance();
    $i=0;
    foreach ($on_fields as $field) {
      if ($i) 
        $where .= " AND ";
      else
        $where = "WHERE ";
    
      $where .= "`".$field."`=".$my->quote($this->{$this->_myc[$field]});
      $i++;
    }

    if (($data = $my->select($fields, $this->_table, $where)) == FALSE)
      return -1;
    else
    {
      if ($my->getNR() != 0)
      {
        foreach ($data[0] as $k => $v) {
          if (array_key_exists($k, $this->_myc))
          {
            $this->{$this->_myc[$k]} = $v;
          }
        }
      } else return -1;
    }
  }


  /**
   * Fetch object with XXX
   * @return -1 on error
   */
  function fetchFromField($field)
  {
    $my = mysqlCM::getInstance();
    $i = 0;
    $fields = "";
    foreach ($this->_my as $k => $v) {
      if ($i && $i < count($this->_my)) $fields .= ",";

      $fields .= "`".$k."`";
      $i++;
    }    

    $where = "WHERE `".$field."`=".$my->quote($this->{$this->_myc[$field]});

    if (($data = $my->select($fields, $this->_table, $where)) == FALSE)
      return -1;
    else
    {
      if ($my->getNR() != 0)
      {
        foreach ($data[0] as $k => $v) {
          if (array_key_exists($k, $this->_myc))
          {
            $this->{$this->_myc[$k]} = $v;
          }
        }
      } else return -1;
    }
  }


  /**
   * Fetch object with INDEX
   * @return -1 on error
   */
  function fetchFromId()
  {
    $i = 0;
    $fields = "";
    foreach ($this->_my as $k => $v) {
      if ($v != SQL_INDEX)
      {
        if ($i && $i < count($this->_my)) $fields .= ",";

        $fields .= "`".$k."`";
        $i++;
      }
    }    
    $ids = array_keys($this->_my, SQL_INDEX);
    if (count($ids) == 0)
      return -1;
    $w=0;
    foreach ($ids as $id) {
      if ($id === FALSE)
        continue; /* no index in obj */

      if (!$w) {
        $where = "WHERE `".$id."`='".$this->{$this->_myc[$id]}."'";
        $w++;
      } else {
        $where .= " AND `".$id."`='".$this->{$this->_myc[$id]}."'";
      }
    }

    //$where = "WHERE `".$id."`='".$this->{$this->_myc[$id]}."'";
    $my = mysqlCM::getInstance();
    if (($data = $my->select($fields, $this->_table, $where)) == FALSE)
      return -1;
    else
    {
      if ($my->getNR() != 0)
      {
        foreach ($data[0] as $k => $v) {
          if (array_key_exists($k, $this->_myc))
          {
            $this->{$this->_myc[$k]} = $v;
          }
        }
      } else return -1;
    }

    return 0;
  }

  /**
   * delete object in db
   * @return -1 on error, 0 on success
   */
  function delete()
  {
    $where = "";
    $w = 0;

    $my = mysqlCM::getInstance();
    /* Build index list */
    $ids = array_keys($this->_my, SQL_INDEX);
    if (count($ids) == 0)
      return -1;

    foreach ($ids as $id) {
      if ($id === FALSE)
        continue; /* no index in obj */

      if ($w) { $where .= " AND "; } else { $where .= "WHERE "; }
      $where .= "`".$id."`=".$my->quote($this->{$this->_myc[$id]});
      $w++;
    }
    $this->delAllData();
    return $my->delete($this->_table, $where);
  }

  protected function _delAllJT() {

    foreach($this->_jt as $jt) {
      $this->fetchJT($jt->ar);
      foreach($this->{$jt->ar} as $l) {
        $this->delFromJT($jt->ar, $l);
      }
    }
  }

  protected function _addJT($ar, $oc, $jt, $src, $dst, $attrs = array()) {
    
    try {
      $this->_jt[$ar] = new mJT($ar, $oc, $jt, $src, $dst, $attrs);
    } catch (Exception $e) {
      throw($e);
    }

    return;
  }

  public function fetchJT($ar, $f_fetch = true) {
    $this->_fetchJT($ar, $f_fetch);
  }

  protected function _addRL($ar, $oc, $fk) {
    
    try {
      $this->_rel[$ar] = new mRL($ar, $oc, $fk);
    } catch (Exception $e) {
      throw($e);
    }

    return;
  }

  public function fetchRL($ar, $f_fetch = true, $where = '') {
    $this->_fetchRL($ar, $f_fetch, $where);
  }

  public function getTable() {
    return $this->_table;
  }

  public function getIdx($f_array = false) {

    $rc = '';
    $ids = array_keys($this->_my, SQL_INDEX);
    if (count($ids) == 0)
      throw new SPXException('No Index found for this class');

    if ($f_array) 
      return $ids;

    $i = 0;
    foreach ($ids as $id) {
      if ($id === FALSE)
        continue; /* no index in obj */

        if ($i) {
          $rc .= ',';
        }
        $rc .= '`'.$id.'`';
    }
    return $rc;
  }

  public function delFromJT($name, $fobj) {

    if (!isset($this->_jt[$name])) {
      throw new SPXException("Rel association $name not found");
    }

    $my = mysqlCM::getInstance();

    try {
     $rel = $this->_jt[$name];
     $sc = get_called_class();
     
     $table = $rel->jt;
     $where = '';
     $w = 0;

     foreach($rel->dst as $obj => $sql) {
       if ($w++) { $where .= ' AND '; } else { $where .= 'WHERE '; }
       $where .= '`'.$sql.'`=\''.$fobj->{$obj}.'\'';
     }

     foreach($rel->src as $obj => $sql) {
       if ($w++) { $where .= ' AND '; } else { $where .= 'WHERE '; }
       $where .= '`'.$sql.'`=\''.$this->{$obj}.'\'';
     }

     foreach($rel->attrs as $r) {
       if (isset($fobj->{$r}[''.$this])) {
         if ($w++) { $where .= ' AND '; } else { $where .= 'WHERE '; }
         $where .= '`'.$r.'`=\''.$fobj->{$r}[''.$this].'\'';
       }
     }

     $my->delete($table, $where);
     $ak = array_keys($this->{$rel->ar});
     foreach ($ak as $i) {
       if ($this->{$rel->ar}[$i]->equals($fobj)) {
	 $good = true;
         foreach($rel->attrs as $name) {
           if (isset($this->{$rel->ar}[$i]->{$name}[''.$this]) &&
               isset($fobj->{$name}[''.$this]) &&
               strcmp($this->{$rel->ar}[$i]->{$name}[''.$this], $fobj->{$name}[''.$this])) {
	     $good = false;
             break;
           }
         }
	 if ($good) {
	   unset($this->{$rel->ar}[$i]);
	   break;
	 }
       }
     }

    } catch (Exception $e) {
      throw($e);
    }
  }

  public function addToJT($name, $fobj) {

    if (!isset($this->_jt[$name])) {
      throw new SPXException("JT association $name not found");
    }

    $my = mysqlCM::getInstance();

    try {
     $rel = $this->_jt[$name];
     $sc = get_called_class();
     
     $table = $rel->jt;
     $names = '';
     $values = '';
     $i = 0;

     foreach($rel->dst as $obj => $sql) {
       if ($i++) { $names .= ','; $values .= ','; }
       $names .= "`$sql`";
       $values .= '\''.$fobj->{$obj}.'\'';
     }

     foreach($rel->src as $obj => $sql) {
       if ($i++) { $names .= ','; $values .= ','; }
       $names .= "`$sql`";
       $values .= '\''.$this->{$obj}.'\'';
     }

     foreach($rel->attrs as $r) {
       if (isset($fobj->{$r}[''.$this])) {
         if ($i++) { $names .= ','; $values .= ','; }
         $names .= '`'.$r.'`';
         $values .= '\''.$fobj->{$r}[''.$this].'\'';
       }
     }

     $my->insert($names, $values, $table);
     array_push($this->{$rel->ar}, $fobj);

    } catch (Exception $e) {
      throw($e);
    }
  }

  public function isInJT($name, $obj, $attrs = array()) {

    if (!isset($this->_jt[$name])) {
      throw new SPXException("Rel association $name not found");
    }

    $my = mysqlCM::getInstance();

    try {

      $rel = $this->_jt[$name];

      foreach($this->{$rel->ar} as $o) {
        if ($o->equals($obj)) {
	  $good = true;
	  foreach($attrs as $name) {
	    if (isset($o->{$name}[''.$this]) &&
	        isset($obj->{$name}[''.$this]) &&
	        strcmp($o->{$name}[''.$this], $obj->{$name}[''.$this])) {
	      $good = false;
	      break;
	    }
	  }
	  if ($good) return true;
	}
      }
      return false;

    } catch (Exception $e) {
      throw($e);
    }
  }

  protected function _fetchJT($name, $f_fetch = true) {

    if (!isset($this->_jt[$name])) {
      throw new SPXException("JT association $name not found");
    }

    $my = mysqlCM::getInstance();
    
    try {
     $rel = $this->_jt[$name];
     $this->{$rel->ar} = array();

     $sc = get_called_class();
     
     $table = $rel->jt;
     $index = '';
     $where = '';
     $w = 0;
     $i = 0;

     foreach($rel->dst as $obj => $sql) {
       if ($i++) { $index .= ','; }
       $index .= "`$sql`";
     }

     foreach($rel->attrs as $r) {
       if ($i++) { $index .= ','; }
       $index .= '`'.$r.'`';
     }

     foreach($rel->src as $obj => $sql) {
       if ($w) { $where .= " AND "; } else { $where .= "WHERE "; }
       $where .= "`".$sql."`=".$my->quote($this->{$obj});
       $w++;
     }
     if (($idx = $my->fetchIndex($index, $table, $where))) {
       foreach($idx as $t) {
         $d = new $rel->oc();
         foreach($rel->dst as $obj => $sql) {
	   $d->{$obj} = $t[$sql];
	 }
         if ($f_fetch) {
	   $d->fetchFromId();
 	 }
	 foreach($rel->attrs as $a) {
	   $d->{$a}[''.$this] = $t[$a];
	   $this->{$a}[''.$d] = $t[$a];
	 }
         array_push($this->{$rel->ar}, $d);
       }
     }
   }
   catch (Exception $e) {
     throw($e);
   }
 }


  protected function _fetchRL($name, $f_fetch = true, $where = '') {


    if (!isset($this->_rel[$name])) {
      throw new SPXException("Rel association $name not found");
    }

    $my = mysqlCM::getInstance();
    
    try {
     $rel = $this->_rel[$name];
     $this->{$rel->ar} = array();

     $obj = new $rel->oc();
     $table = $obj->getTable();
     $index = $obj->getIdx();
     $a_idx = $obj->getIdx(true);
     $w = 0;
     if (!empty($where)) $w = 1;

     foreach($rel->fk as $src => $dst) {
       if ($w) { $where .= " AND "; } else { $where .= "WHERE "; }
       if (!strncmp('CST:', $src, 4)) {
         $sstring = preg_replace('/^CST:/', '', $src);
         $where .= "`".$dst."`='$sstring'";
       } else {
         $where .= "`".$dst."`=".$my->quote($this->{$src});
       }
       $w++;
     }
     if (($idx = $my->fetchIndex($index, $table, $where))) {
       foreach($idx as $t) {
         $d = new $rel->oc();
         foreach($a_idx as $idx) { 
           /* TODO: we assume here that id obj == id sql, this is not necessarly true,
	       but it's sunday, it's hot and I'm way too lazy to do it better now even
	       if some copy paste would have taken more time than this silly comment
	    */
	   $d->{$idx} = $t[$idx];
	 }
         if ($f_fetch) {
	   $d->fetchFromId();
 	 }
         array_push($this->{$rel->ar}, $d);
       }
     }
   }
   catch (Exception $e) {
     throw($e);
   }
 }


  protected function _addFK($iname, $oname, $oclass) {
    
    try {
      $this->_fks[$iname] = new mFK($iname, $oname, $oclass);
    } catch (Exception $e) {
      throw($e);
    }

    return;
  }

  public function fetchFK($name, $f_fetch = true) {
    $this->_fetchFK($name, $f_fetch);
  }

  protected function _fetchFK($name, $f_fetch = true) {

    if (!isset($this->_fks[$name])) {
      throw new SPXException('FK association $name not found');
    }
    
    try {
      $this->_fks[$name]->fetch($this, $f_fetch);
    }
    catch (Exception $e) {
      throw($e);
    }
  }

  public static function getAll($f_fetch = true, $f = array(), $s = array(), $l_start = 0, $l_count = 0) {

    $oc = get_called_class();
    $obj = new $oc();

    $table = $obj->getTable();
    $index = $obj->getIdx();
    $a_idx = $obj->getIdx(true);
    $where = '';
    $sort = '';
    $limit = '';
    $w = 0;
    $si = 0;
    $ret = array();

    if ($l_count) {
      $limit .= " LIMIT $l_start, $l_count";
    }
    $my = MysqlCM::getInstance();

    foreach($s as $src) {
      if ($si) { $sort .= ", "; } else { $sort .= "ORDER BY "; }
      if (!strncmp('ASC:', $src, 4)) {
        $src = preg_replace('/^ASC:/', '', $src);
        $sort .= "`".$src."` ASC ";
      } else if (!strncmp('DESC:', $src, 5)) {
        $src = preg_replace('/^DESC:/', '', $src);
        $sort .= "`".$src."` DESC ";
      } else {
        $sort .= "`".$src."`";
      }
      $si++;
    }

    foreach($f as $dst => $src) {
      if ($w) { $where .= " AND "; } else { $where .= "WHERE "; }
      if (!strncmp('CST:', $src, 4)) {
        $sstring = preg_replace('/^CST:/', '', $src);
        $where .= "`".$dst."`=".$my->quote($sstring);
      } else if (!strncmp('LIKE:', $src, 5)) {
        $sstring = preg_replace('/^LIKE:/', '', $src);
        $where .= "`".$dst."` LIKE ".$my->quote($sstring);
      } else {
        $where .= "`".$dst."`=".$my->quote($src);
      }
      $w++;
    }
    try {
      if (($idx = $my->fetchIndex($index, $table, $where.' '.$sort.' '.$limit))) {
        foreach($idx as $t) {
          $d = new $oc();
          foreach($a_idx as $idx) {
            /* @TODO: we assume here that id obj == id sql, this is not necessarly true,
                but it's sunday, it's hot and I'm way too lazy to do it better now even
                if some copy paste would have taken more time than this silly comment
             */
            $d->{$idx} = $t[$idx];
          }
          if ($f_fetch) {
            $d->fetchFromId();
          }
          array_push($ret, $d);
        }
      }
    }
    catch (Exception $e) {
      throw($e);
    }
    return $ret;
  }

  public function copyToTable($table) {
    $oldtable = $this->_table;
    $this->_table = $table;
    $rc = $this->insert(1);
    $this->_table = $oldtable;
    return $rc;
  }

}
?>
