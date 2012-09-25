<?php
/**
 * Model object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Model extends mysqlObj
{
  public $id = -1;
  public $name = '';
  public $vendor = '';
  public $t_add = -1;
  public $t_upd = -1;

  public function __toString() {
    return $this->vendor.'/'.$this->name;
  }

  public function htmlDump($s = null) {

    if (!$s) $s = new Server();

    return array(
      'Vendor' => $this->vendor,
      'Model' => $this->name,
      'Platform' => $s->data('hw:platform'),
      'HW Class' => $s->data('hw:class'),
      'Memory' => $s->data('hw:memory'),
      'CPU Type' => $s->data('hw:cpu'),
      'CPU Speed' => $s->data('hw:cpuspeed'),
      'Num CPU' => $s->data('hw:nrcpu'),
      'Num Cores' => $s->data('hw:nrcore'),
      'Num Threads' => $s->data('hw:nrstrand'),
    );
  }

  public function dump($s) {

    $platform = $s->data('hw:platform');
    $hwclass = $s->data('hw:class');
    $cputype = $s->data('hw:cpu');
    $memsize = $s->data('hw:memory');
    $hwnrcore = $s->data('hw:nrcore');
    $hwnrcpu = $s->data('hw:nrcpu');
    $hwnrthreads = $s->data('hw:nrstrand');

    if (!$memsize || empty($memsize)) $memsize = 'undef';

    $s->log(sprintf("%15s: %s", 'Model', ''.$this), LLOG_INFO);
    if (!empty($platform)) {
      $s->log(sprintf("%15s: %s", 'Platform', "$platform / $hwclass / $memsize MB Memory"), LLOG_INFO);
    }
    $s->log(sprintf("%15s: %s", 'CPU', "$cputype / $hwnrcpu CPU(s) / $hwnrcore Core(s) / $hwnrthreads Thread(s)"), LLOG_INFO);
  }

 /**
  * ctor
  */
  public function __construct($id=-1)
  {
    $this->id = $id;
    $this->_table = 'list_model';
    $this->_nfotable = NULL;
    $this->_my = array(
                        'id' => SQL_INDEX,
                        'name' => SQL_PROPE|SQL_EXIST,
                        'vendor' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE
                 );


    $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'name' => 'name',
                        'vendor' => 'vendor',
                        't_add' => 't_add',
                        't_upd' => 't_upd'
                 );
  }

}
?>
