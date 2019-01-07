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
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class VMdisk
{
    public $file = '';
    public $type = '';
    public function __construct($f, $t='')
    {
        $this->file = $f;
        $this->type = $t;
    }
}
