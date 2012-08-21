<?php
/**
 * SGroup object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class SGroup extends mysqlObj
{
  public $id = -1;
  public $name = '';
  public $description = '';
  public $t_add = -1;
  public $t_upd = -1;

  public function __toString() {
    return $this->name;
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = "list_sgroup";
    $this->_nfotable = NULL;
    $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE|SQL_EXIST,
                        "description" => SQL_PROPE,
                        "t_add" => SQL_PROPE,
                        "t_upd" => SQL_PROPE
                 );


    $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "description" => "description",
                        "t_add" => "t_add",
                        "t_upd" => "t_upd"
                 );
  }

}
?>
