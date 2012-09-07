<?php
 /**
  * Autoload
  * @author Gouverneur Thomas <tgo@espix.net>
  * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
  * @version 1.0
  * @package objects
  * @subpackage mysql
  * @category classes
  * @filesource
  */

  function __autoload($name) {
    global $config;

    $name = strtolower($name);
    $file = $config['rootpath'].'/libs/'.$name.'.obj.php';
    if (file_exists($file)) {
      require_once($file);
    } else {
      throw new SPXException("Cannot load $file...\n");
    }
  }
?>
