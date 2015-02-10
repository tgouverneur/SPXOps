<?php
/**
 * Plugin class
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2015, Gouverneur Thomas
 * @version 1.0
 * @package libs
 * @subpackage various
 * @category libs
 * @filesource
 */

if (!defined('PLUGIN_LOADED')) {

  /* Hooks definition */

  define('PLUGIN_LOADED', true);
}

class Plugin
{
  /* Make sure to init this only once, this is our gatekeeper: */
  private static $_done = false;

  /* Loaded Plugin list */
  private static $_plugins = array();

  /* Hooks array */
  private static $_hooks = array();

  /* Links added to the web page menu */
  private static $_wmenu = array();

  /* Links added to any given action page */
  private static $_amenu = array();

  public static function registerAction($n, $o) {
    if (!isset(Plugin::$_amenu[$n])) {
      Plugin::$_amenu[$n] = array();
    }
    array_push(Plugin::$_amenu[$n], $o);
    return true;
  }

  public static function registerWeb($n, $o) {
    if (!isset(Plugin::$_wmenu[$n])) {
      Plugin::$_wmenu[$n] = array();
    }
    array_push(Plugin::$_wmenu[$n], $o);
    return true;
  }

  public static function registerHook($n, $h) {
    array_push(Plugin::$_hooks[$n], $h);
    return true;
  }

  public static function registerPlugin($p) {
    array_push(Plugin::$_plugins, $p);
    return true;
  }

  public static function registerPlugins() {
    global $config;
    if (Plugin::$_done) {
      return false;
    }
    foreach ($config['plugins'] as $name => $options) {
      if (!file_exists($config['pluginspath'].'/'.$name.'/'.$name.'.php')) {
        Logger::log("Plugins $name is enabled in config but not found", null, LLOG_ERR);
        continue; // Skip this plugin and log an error
      }
      @require_once($config['pluginspath'].'/'.$name.'/'.$name.'.php');
      //Logger::log("Plugin $name is now active..", null, LLOG_DEBUG);
    }
    Plugin::$_done = true;
  }

  public static function getActionLinks($n) {
    if (isset(Plugin::$_amenu[$n])) {
      return Plugin::$_amenu[$n];
    } else {
      return array();
    }
  }

  public static function getWebLinks($n) {
    if (isset(Plugin::$_wmenu[$n])) {
      return Plugin::$_wmenu[$n];
    } else {
      return array();
    }
  }

  public static function getWebCat() {
    $ret = array();
    foreach(Plugin::$_wmenu as $name => $cat) {
      if (is_array($cat) && count($cat) > 0) { /* at least one element here */
	if (!$cat[0]->is_std) { /* non std, use it! */
	  $ret[] = $name;
	}
      }
    }
    return $ret;
  }

  public static function getWebAction($p, $n) {
    foreach(Plugin::$_plugins as $plugin) {
      if (!strcmp($p, $plugin->name)) {
        foreach($plugin->a_web as $wa) {
	  if (!strcmp($wa->name, $n)) {
	    return $wa;
	  }
        }
        return null;
      }
    }
  }

  /**
   * Below is the actual object instance plugin code
   */
  public $name = '';
  public $version = '0.0';
  public $author = '';

  public $a_web = array();

  public function __construct($n = '', $v = '0.0') {
    $this->name = $n;
    $this->version = $v;
  }

}

?>
