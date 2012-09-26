<?php
/**
 * Cluster Resource
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class CLRs extends mysqlObj
{
  public $id = -1;
  public $name = '';
  public $description = '';
  public $state = '';
  public $type = '';
  public $type_version = 0;
  public $f_disabled = 0;
  public $fk_clrg = -1;
  public $t_add = -1;
  public $t_upd = -1;

  public $o_clrg = null;

 
  public function equals($z) {
    if (!strcmp($this->name, $z->name) && $this->fk_clrg && $z->fk_clrg) {
      return true;
    }
    return false;
  }

  public function fetchAll($all = 1) {

    try {
      if (!$this->o_clrg && $this->fk_clrg > 0) {
        $this->fetchFK('fk_clrg');
      }

    } catch (Exception $e) {
      throw($e);
    }
  }

  public function __toString() {
    return $this->name;
  }

  public static function printCols() {
    return array('Name' => 'name',
                 'Description' => 'description',
                 'State' => 'state',
                 'Type' => 'type',
                 'Disabled' => 'f_disabled',
                 'Updated on' => 't_upd',
                 'Added on' => 't_add',
                );
  }

  public function toArray() {

    return array(
                 'name' => $this->name,
		 'description' => $this->description,
		 'state' => $this->state,
		 'type' => $this->type,
		 'f_disabled' => $this->f_disabled,
                 't_upd' => date('d-m-Y', $this->t_upd),
                 't_add' => date('d-m-Y', $this->t_add),
                );
  }




 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_clrs';
    $this->_nfotable = null;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE|SQL_EXIST,
                        'state' => SQL_PROPE,
                        'type' => SQL_PROPE,
                        'type_version' => SQL_PROPE,
                        'description' => SQL_PROPE,
                        'f_disabled' => SQL_PROPE,
                        'fk_clrg' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );
    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'description' => 'description',
                        'state' => 'state',
                        'type' => 'type',
                        'type_version' => 'type_version',
                        'f_disabled' => 'f_disabled',
                        'fk_clrg' => 'fk_clrg',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );

    $this->_addFK("fk_clrg", "o_clrg", "CLRg");
  }

}
?>
