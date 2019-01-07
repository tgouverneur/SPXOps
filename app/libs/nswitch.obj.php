<?php
/**
 * Network Switch object
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
class NSwitch extends MySqlObj
{
  public $id = -1;
    public $did = '';
    public $name = '';
    public $sfver = '';
    public $platform = '';
    public $location = '';
    public $oid = '';
    public $t_add = -1;
    public $t_upd = -1;

    public $a_net = array();

    public function log($str, $level)
    {
        Logger::log($str, $this, $level);
    }

    public function fetchAll($all = 1)
    {
        try {
            $this->fetchRL('a_net');
        } catch (Exception $e) {
            throw($e);
        }
    }

    public function delete()
    {
        $this->log("Asked to delete $this", LLOG_DEBUG);
        foreach ($this->_rel as $r) {
            $this->log("Treating $r", LLOG_DEBUG);
            if ($this->{$r->ar} && count($this->{$r->ar})) {
                foreach ($this->{$r->ar} as $e) {
                    $this->log("Deleting $e", LLOG_DEBUG);
                    $e->delete();
                }
            }
        }

        $this->log('Deleting now myself...', LLOG_INFO);
        parent::delete();
    }

    public function __toString()
    {
        if (!empty($this->name)) {
            return $this->name;
        }

        return $this->did;
    }

    public function dump()
    {
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_switch';
      $this->_nfotable = null;
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'did' => SQL_PROPE|SQL_EXIST,
                        'name' => SQL_PROPE,
                        'sfver' => SQL_PROPE,
                        'platform' => SQL_PROPE,
                        'location' => SQL_PROPE,
                        'oid' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'did' => 'did',
                        'name' => 'name',
                        'sfver' => 'sfver',
                        'platform' => 'platform',
                        'location' => 'location',
                        'oid' => 'oid',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addRL("a_net", "Net", array('id' => 'fk_switch'));
  }
}
