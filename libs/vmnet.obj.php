<?php
/**
 * VMnet object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2014, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */
class VMnet
{
  public $mac = '';
    public $net = '';
    public $model = '';
    public function __construct($m, $n, $mo)
    {
        $this->mac = $m;
        $this->net = $n;
        $this->model = $mo;
    }
}
