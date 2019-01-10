<?php
/**
 * Hook Object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2015, Gouverneur Thomas
 * @version 1.0
 * @package libs
 * @subpackage various
 * @category libs
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class Hook
{
  private $_class = null;
    private $_fct = null;
    private $_hn = -1; // Hook Number

  public function __construct($hn, $class, $fct)
  {
      $this->_class = $class;
      $this->_fct = $fct;
      $this->_hn = $hn;
  }

    public function call($arg = null)
    {
        if ($this->_class) {
            return call_user_func_array(array($this->_class, $this->_fct), $arg);
        } else {
            return call_user_func_array($this->_fct, $arg);
        }
    }
}
