<?php
/**
 * VMdisk object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2014, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */
class VMdisk
{
  public $file = '';
    public function __construct($f)
    {
        $this->file = $f;
    }
}
