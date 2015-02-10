<?php
/**
 * JobLog object
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @subpackage job
 * @category classes
 * @filesource
 */
class JobLog extends MySqlObj
{
  public $id = -1;        /* ID in the MySQL table */
  public $rc = 0;
    public $log = '';
    public $fk_job = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $o_job = null;

    public function fetchAll($all = 0)
    {
        try {
            if (!$this->o_job && $this->fk_job > 0) {
                $this->fetchFK('fk_job');
            }
        } catch (Exception $e) {
            throw($e);
        }
    }

  /* ctor */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_joblog';
      $this->_my = array(
            'id' => SQL_INDEX,
                'rc' => SQL_PROPE,
            'log' => SQL_PROPE,
            'fk_job' => SQL_PROPE,
            't_add' => SQL_PROPE,
            't_upd' => SQL_PROPE,
         );

      $this->_myc = array( /* mysql => class */
            'id' => 'id',
            'rc' => 'rc',
            'log' => 'log',
            'fk_job' => 'fk_job',
            't_add' => 't_add',
            't_upd' => 't_upd',
         );
      $this->_addFK("fk_job", "o_job", "Job");
  }
}
