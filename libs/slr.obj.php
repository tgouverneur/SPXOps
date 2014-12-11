<?php
/**
 * SLR == Saved Live RRD object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2014, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class SLR extends mysqlObj
{
  public $id = -1;
  public $name = '';
  public $definition = '';
  public $t_add = -1;
  public $t_upd = -1;

  public $a_def = array();

  public function getArray() {
    $this->a_def = @unserialize($this->definition);
  }

  public function setDefinition($a) {
    $this->definition = @serialize($a);
  }

  public function __toString() {
    return $this->name;
  }

  public function equals($z) {
    if (!strcmp($this->name, $z->name)) {
      return true;
    }
    return false;
  }

  public static function printCols() {
    return array('Name' => 'name',
                 'Definition' => 'definition',
                 'Added' => 't_add',
                );
  }

  public function toArray() {

    return array(
                 'name' => $this->name,
                 'definition' => $this->definition,
                 't_add' => date('Y-m-d', $this->t_add),
                );
  }

  public function htmlDump() {
    return array(
        'Name' => $this->name,
        'Definition' => $this->definition,
        'Added on' => date('d-m-Y', $this->t_add),
    );
  }


  public function link() {
    return '<a href="/rrdlive/i/'.$this->id.'">'.$this.'</a>';
  }

  public function delete() {

    parent::delete();
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "list_slr";
    $this->_nfotable = NULL;
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE|SQL_EXIST,
                        "definition" => SQL_PROPE,
                        "t_add" => SQL_PROPE,
                        "t_upd" => SQL_PROPE
                 );


    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "definition" => "definition",
                        "t_add" => "t_add",
                        "t_upd" => "t_upd"
                 );

  }

}
?>
