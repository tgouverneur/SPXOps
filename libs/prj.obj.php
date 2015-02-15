<?php
/**
 * Solaris Project object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */
class Prj extends MySqlObj
{
  public $id = -1;
    public $name = '';
    public $prjid = -1;
    public $comment = '';
    public $ulist = '';
    public $glist = '';
    public $attrs = '';
    public $fk_server = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_server = null;


    public function log($str)
    {
        Logger::log($str, $this);
    }

    public function equals($z)
    {
        if ($this->prjid == $z->prjid && $this->fk_server && $z->fk_server) {
            return true;
        }

        return false;
    }

    public function fetchAll($all = 1)
    {
        try {
            if (!$this->o_server && $this->fk_server > 0) {
                $this->fetchFK('fk_server');
            }
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function __toString()
    {
        return $this->name;
    }

    public static function printCols($cfs = array())
    {
        return array('Name' => 'name',
                 'PrjID' => 'prjid',
                 'Added on' => 't_add',
                 'Details' => 'details',
                );
    }

    public function toArray($cfs = array())
    {
        return array(
                 'name' => $this->name,
                 'prjid' => $this->prjid,
                 'details' => '<a href="/view/w/project/i/'.$this->id.'">View</a>',
                 't_add' => date('d-m-Y', $this->t_add),
                );
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_prj';
      $this->_nfotable = null;
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE|SQL_EXIST,
                        'prjid' => SQL_PROPE,
                        'comment' => SQL_PROPE,
                        'ulist' => SQL_PROPE,
                        'glist' => SQL_PROPE,
                        'attrs' => SQL_PROPE,
                        'fk_server' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'prjid' => 'prjid',
                        'comment' => 'comment',
                        'ulist' => 'ulist',
                        'glist' => 'glist',
                        'attrs' => 'attrs',
                        'fk_server' => 'fk_server',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_server", "o_server", "Server");
  }
}
