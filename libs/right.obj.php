<?php
/**
 * Right object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
if (!defined('R_NONE')) {
    define('R_NONE',   0);
    define('R_VIEW',   1);
    define('R_ADD', 2);
    define('R_EDIT', 4);
    define('R_DEL', 8);
}

class Right extends MySqlObj
{
  public $id = -1;
    public $name = '';
    public $short = '';
    public $t_add = -1;
    public $t_upd = -1;

    public $a_ugroup = array();
    public $level = array();

    public function equals($z)
    {
        if (!strcmp($this->short, $z->short)) {
            return true;
        }

        return false;
    }

    public function fetchAll($all = 1)
    {
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getRight($ugroup)
    {
        if (isset($this->level[''.$ugroup])) {
            return $this->level[''.$ugroup];
        }

        return 0;
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_right';
      $this->_nfotable = null;
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'short' => SQL_PROPE|SQL_EXIST,
                        'name' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'short' => 'short',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

                /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
    $this->_addJT('a_ugroup', 'UGroup', 'jt_right_ugroup', array('id' => 'fk_right'), array('id' => 'fk_ugroup'), array('level'));
  }
}
