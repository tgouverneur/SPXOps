<?php
  /**
   * Autoload
   * @author Gouverneur Thomas <tgo@espix.net>
   * @copyright Copyright (c) 2007-2015, Gouverneur Thomas
   * @version 1.0
   * @package objects
   * @subpackage mysql
   * @category classes
   * @filesource
   */
  function __autoload($name)
  {
      $name = strtolower($name);
      $file = Config::$rootpath.'/libs/'.$name.'.obj.php';
      if (file_exists($file)) {
          require_once $file;
      } else {
          throw new Exception("Cannot load $file...\n");
      }
  }
