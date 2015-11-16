<?php
/**
 * UToken object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2015, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class UToken extends MySqlObj
{
    public $id = -1;
    public $counter = -1;
    public $secret = '';
    public $type = -1;
    public $t_add = -1;
    public $t_upd = -1;


  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_utoken';
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'counter' => SQL_PROPE,
                        'secret' => SQL_PROPE,
                        'type' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );

      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'counter' => 'counter',
                        'secret' => 'secret',
                        'type' => 'type',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );
  }
}
