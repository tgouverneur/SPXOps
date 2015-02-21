<?php
/**
 * Cluster Resource Group
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
class CLRg extends MySqlObj
{
  public $id = -1;
    public $name = '';
    public $state = '';
    public $description = '';
    public $f_suspend = 0;
    public $fk_cluster = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_cluster = null;
    public $a_clrs = array();

    public $a_node = array();
    public $fk_zone = array();

    public function dumpNodes($html = true)
    {
        $txt = '';
        $i = 0;
        foreach ($this->a_node as $n) {
            if ($i++) {
                if ($html) {
                    $txt .= '<br/>';
                } else {
                    $txt .= "\n";
                }
            }
            if ($html) {
                $txt .= $n->link();
            } else {
                $txt .= $n;
            }
            if (isset($this->fk_zone[$n->id]) && $this->fk_zone[$n->id] > 0) {
                $z = new Zone($this->fk_zone[$n->id]);
                $z->fetchFromId();
                $txt .= ':';
                if ($html) {
                    $txt .= $z->link();
                } else {
                    $txt .= $z;
                }
            }
        }

        return $txt;
    }

    public function equals($z)
    {
        if (!strcmp($this->name, $z->name) && $this->fk_cluster && $z->fk_cluster) {
            return true;
        }

        return false;
    }

    public function fetchAll($all = 1)
    {
        try {
            if (!$this->o_cluster && $this->fk_cluster > 0) {
                $this->fetchFK('fk_cluster');
            }

            if ($all) {
                $this->fetchRL('a_clrs');
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
        return array('Patch-ID' => 'patch',
                 'More Info' => 'minfo',
                 'Added on' => 't_add',
                );
    }

    public function toArray($cfs = array())
    {
        return array(
                 'patch' => $this->patch,
                 'minfo' => '<a href="http://wesunsolve.net/patch/id/'.$this->patch.'" target="_blank">info</a>',
                 't_add' => date('d-m-Y', $this->t_add),
                );
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_clrg';
      $this->_nfotable = null;
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE|SQL_EXIST,
                        'state' => SQL_PROPE,
                        'description' => SQL_PROPE,
                        'f_suspend' => SQL_PROPE,
                        'fk_cluster' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );
      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'state' => 'state',
                        'description' => 'description',
                        'f_suspend' => 'f_suspend',
                        'fk_cluster' => 'fk_cluster',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );

      $this->_addFK("fk_cluster", "o_cluster", "Cluster");
      $this->_addRL("a_clrs", "CLRs", array('id' => 'fk_clrg'));
                /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
    $this->_addJT('a_node', 'Server', 'jt_clrg_server', array('id' => 'fk_clrg'), array('id' => 'fk_server'), array('fk_zone'));
  }
}
