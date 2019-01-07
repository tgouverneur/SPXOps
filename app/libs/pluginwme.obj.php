<?php
/**
 * PluginWME Object
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
class PluginWME
{
    public $name = '';
    public $desc = '';
    public $cat = '';
    public $fct = null;
    public $otype = 0; // Output type, 0==web, 1==json

    public $is_std = true; /* is this category standard */

    public $n_right = null;
    public $n_level = 0;

    public $o_plugin = null;

    public function __construct($p = null, $n = '', $f = null)
    {
        $this->o_plugin = $p;
        $this->name = $n;
        $this->fct = $f;
    }

    public function call($arg)
    {
        return $this->o_plugin->{$this->fct}($arg);
    }

    public function getRootHref($a = null)
    {
        return '/plugin/p/'.$this->o_plugin->name;
    }

    public function getHref($a = null)
    {
        return '/plugin/p/'.$this->o_plugin->name.'/w/'.$this->name.(($a) ? '/r/'.$a : '');
    }
}
