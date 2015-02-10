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
class SGroup extends MySqlObj
{
  public $id = -1;
    public $name = '';
    public $description = '';
    public $t_add = -1;
    public $t_upd = -1;

    public $a_server = array();
    public $a_check = array();
    public $f_except = array();

  /* for alerts */
  public $a_ugroup = array();

    public function __toString()
    {
        return $this->name;
    }

    public function equals($z)
    {
        if (!strcmp($this->name, $z->name)) {
            return true;
        }

        return false;
    }

    public function link()
    {
        return '<a href="/view/w/sgroup/i/'.$this->id.'">'.$this.'</a>';
    }

    public function valid($new = true)
    { /* validate form-based fields */
    global $config;
        $ret = array();

        if (empty($this->name)) {
            $ret[] = 'Missing Name';
        } else {
            if ($new) { /* check for already-exist */
        $check = new SGroup();
                $check->name = $this->name;
                if (!$check->fetchFromField('name')) {
                    $this->name = '';
                    $ret[] = 'Server Group Name already exist';
                    $check = null;
                }
            }
        }

        if (count($ret)) {
            return $ret;
        } else {
            return;
        }
    }

    public static function printCols($cfs = array())
    {
        return array('Name' => 'name',
                 'Description' => 'description',
                 'Last Update' => 't_upd',
                );
    }

    public function toArray($cfs = array())
    {
        return array(
                 'name' => $this->name,
                 'description' => $this->description,
                 't_upd' => date('Y-m-d', $this->t_upd),
                );
    }

    public function htmlDump()
    {
        return array(
        'Name' => $this->name,
        'Description' => $this->description,
        'Updated on' => date('d-m-Y', $this->t_upd),
        'Added on' => date('d-m-Y', $this->t_add),
    );
    }

    public function delete()
    {
        parent::_delAllJT();
        parent::delete();
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = "list_sgroup";
      $this->_nfotable = null;
      $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE|SQL_EXIST,
                        "description" => SQL_PROPE,
                        "t_add" => SQL_PROPE,
                        "t_upd" => SQL_PROPE,
                 );

      $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "description" => "description",
                        "t_add" => "t_add",
                        "t_upd" => "t_upd",
                 );

                /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
    $this->_addJT('a_server', 'Server', 'jt_server_sgroup', array('id' => 'fk_sgroup'), array('id' => 'fk_server'), array());
      $this->_addJT('a_check', 'Check', 'jt_check_sgroup', array('id' => 'fk_sgroup'), array('id' => 'fk_check'), array('f_except'));
      $this->_addJT('a_ugroup', 'UGroup', 'jt_sgroup_ugroup', array('id' => 'fk_sgroup'), array('id' => 'fk_ugroup'), array());
  }
}
