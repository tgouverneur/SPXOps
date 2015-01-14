<?php
/**
 * Test Plugin
 */

class TestPlugin extends Plugin {

  public function webDummy() {
    global $content;
    $content = new Template(dirname(__FILE__).'/tpl/test.tpl');
  }

  public function __construct($n, $v) {

    $o = new PluginWME($this, 'testtool', webDummy);
    $o->desc = 'Dummy Plugin Tool';
    Plugin::registerWeb('tools', $o);
    $this->a_web[] = $o;

    $o = new PluginWME($this, 'testtool', webDummy);
    $o->desc = 'Dummy Plugin Tool';
    $o->cat = 'Test Plugin';
    $o->is_std = false;
    Plugin::registerWeb('test', $o);
    $this->a_web[] = $o;

    Plugin::__construct($n, $v);
  }
}
 
$p = new TestPlugin('test', '0.1');
$p->author = 'Thomas Gouverneur';
Plugin::registerPlugin($p);



?>
