<?php
/**
 * Mail object
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2016, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @subpackage job
 * @category classes
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class SPXMail extends MySqlObj
{
    public $id = -1;        /* ID in the MySQL table */
    public $to = '';
    public $subject = '';
    public $headers = '';
    public $msg = '';
    public $t_add = -1;
    public $t_upd = -1;

  public function send() {
      mail($this->to, $this->subject, $this->msg, $this->headers);
  }

  /* ctor */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_mail';
      $this->_my = array(
            'id' => SQL_INDEX,
            'to' => SQL_PROPE,
            'subject' => SQL_PROPE,
            'headers' => SQL_PROPE,
            'msg' => SQL_PROPE,
            't_add' => SQL_PROPE,
            't_upd' => SQL_PROPE,
         );

      $this->_myc = array( /* mysql => class */
            'id' => 'id',
            'to' => 'to',
            'subject' => 'subject',
            'headers' => 'headers',
            'msg' => 'msg',
            't_add' => 't_add',
            't_upd' => 't_upd',
         );
  }
}
