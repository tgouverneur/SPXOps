<?php
 /**
  * Job object
  * @author Gouverneur Thomas <tgo@espix.net>
  * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
  * @version 1.0
  * @package objects
  * @subpackage job
  * @category classes
  * @filesource
  */


class JobLog extends mysqlObj
{
  public $id = -1;		/* ID in the MySQL table */
  public $rc = 0;
  public $log = '';

  /* ctor */
  public function __construct($id=-1)
  { 
    $this->id = $id;
    $this->_table = 'joblogs';
    $this->_my = array( 
			'id' => SQL_INDEX, 
		        'rc' => SQL_PROPE,
			'log' => SQL_PROPE
 		 );

    $this->_myc = array( /* mysql => class */
			'id' => 'id', 
			'rc' => 'rc',
			'log' => 'log'
 		 );
  }
}

?>
