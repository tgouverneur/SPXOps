<?php
/**
 * eAction object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */

class eAction {
  public $href = '';
  public $onclick = '';
  public $text = '';
  public $arg = '';
  public $fct = '';

  public $res = null;
  public function __construct($t, $h, $o, $a, $f) {
    $this->href = $h;
    $this->onclick = $o;
    $this->text = $t;
    $this->arg = $a;
    $this->fct = $f;
  }

  public function call(&$s) {
    $this->res = null;
    if ($s->o_os) {
      if (method_exists($s->o_os->class, $this->fct)) {
        $class = $s->o_os->class;
        $fct = $this->fct;
        $this->res = $class::$fct($s);
        if (!$this->res) {
	  return -1; 
	}
	return 0;
      }
    }
    return -1;
  }
  public function onclick($obj) {
    $ret = $this->onclick;
    if (!empty($this->arg)) {
      $ret = sprintf($this->onclick, $obj->{$this->arg});
    }
    return $ret;
  }
  public function href($obj) {
    $ret = $this->href;
    if (!empty($this->arg)) {
      $ret = sprintf($this->href, $obj->{$this->arg});
    }
    return $ret;
  }
}

?>
