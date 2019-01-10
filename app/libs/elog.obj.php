<?php
/**
 * ELog object
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
class ELog extends MySqlObj
{
  public $id = -1;
    public $fk_login = '';
    public $what = '';
    public $t_add = -1;

    public $o_login = null;

    public function __toString()
    {
        return $this->what;
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_logs';
      $this->_nfotable = null;
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'fk_login' => SQL_PROPE,
                        'what' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                 );

      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'fk_login' => 'fk_login',
                        'what' => 'what',
                        't_add' => 't_add',
                 );
  }
}
