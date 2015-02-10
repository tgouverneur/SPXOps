<?php
/**
 * Jobarg object
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @subpackage job
 * @category classes
 * @filesource
 */
class JobArg
{
  public $args = array();
    public function add($name, $value)
    {
        $this->args[$name] = $value;

        return;
    }
    public function get($name)
    {
        if (isset($this->args[$name])) {
            return $this->args[$name];
        } else {
            return false;
        }
    }
}
