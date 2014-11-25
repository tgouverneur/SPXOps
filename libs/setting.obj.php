<?php
/**
 * Setting object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Setting extends mysqlObj
{
  public $id = -1;
  public $cat = '';
  public $name = '';
  public $textname = '';
  public $description = '';
  public $placeholder = '';
  public $value = '';
  public $t_add = -1;
  public $t_upd = -1;

  private static $_s = array();

  public static function fetchAll() {
    Setting::$_s = Setting::getAll(true, array(), array('ASC:cat', 'ASC:name'));
  }

  public static function getSettings($cat = null) {

    if (!count(Setting::$_s)) {
      Setting::fetchAll();
    }
    if (!$cat) {
      return Setting::$_s;
    }
    $v = array();
    foreach(Setting::$_s as $s) {
      if (!strcmp($s->cat, $cat))
	$v[] = $s;
    }
    return $v;
  }


  public static function getCat() {

    if (!count(Setting::$_s)) {
      Setting::fetchAll();
    }
    $cat = array();
    foreach(Setting::$_s as $s) {
      if (!isset($cat[$s->cat]))
	$cat[$s->cat] = true;
    }
    return array_keys($cat);
  }

  public static function get($cat, $name) {

    if (!count(Setting::$_s)) {
      Setting::fetchAll();
    }

    foreach(Setting::$_s as $s) {
      if (!strcmp($s->cat, $cat) &&
	  !strcmp($s->name, $name)) {
        return $s;
      }
    }
    return null;
  }

  public static function set($cat, $name, $value) {

    if (!count(Setting::$_s)) {
      Setting::fetchAll();
    }
 
    foreach(Setting::$_s as $s) {
      if (!strcmp($s->cat, $cat) &&
	  !strcmp($s->name, $name)) {
        $s->value = $value;
	$s->update();
        return 0;
      }
    }
    return -1;
   
  }

  public function __toString() {
    return $this->cat.':'.$this->name.'='.$this->value;
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_setting';
    $this->_nfotable = null;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'cat' => SQL_PROPE,
                        'name' => SQL_PROPE,
                        'textname' => SQL_PROPE,
                        'description' => SQL_PROPE,
                        'placeholder' => SQL_PROPE,
                        'value' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'cat' => 'cat',
                        'name' => 'name',
                        'textname' => 'textname',
                        'description' => 'description',
                        'placeholder' => 'placeholder',
                        'value' => 'value',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );
  }

}
?>
