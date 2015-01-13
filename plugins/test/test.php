<?php
/**
 * Test Plugin
 */

class TestPlugin extends Plugin {

  public function webDummy() {

  }

  public function __construct($n, $v) {

    $o = new PluginWME($this, 'testtool', webDummy);
    $o->desc = 'Dummy Plugin Tool';
    Plugin::registerWeb('tools', $o);
    Plugin::__construct($n, $v);
  }
}
 
$p = new TestPlugin('test', '0.1');
$p->author = 'Thomas Gouverneur';



?>
