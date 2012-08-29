<?php
/**
 * Update class
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Update
{

  public static function jobServer(&$job, $sid) {
    $s = new Server($sid);
    if ($s->fetchFromId()) {
      throw new SPXException('Server not found in database');
    }
    $s->_job = $job;

    try {
      $s->log("Connecting to server", LLOG_INFO);
      $s->connect();
      $s->log("Launching the Update", LLOG_DEBUG);
      Update::server($s);
      $s->log("Disconnecting from server", LLOG_INFO);
      $s->disconnect();
    } catch (Exception $e) {
      throw($e);
    }
  }

  public static function server($s, $f = null) {

    if (!$s) {
      throw new SPXException('Update::server: $s is null');
    }
    
    if (!$s->fk_os || $s->fk_os == -1) {
      Logger::log('Trying to detect OS for '.$s, $s, LLOG_INFO);
      $oso = OS::detect($s);
      $s->fk_os = $oso->id;
      $s->update();
      $s->o_os = $oso;
      Logger::log('Detected OS for '.$s.' is '.$oso, $s, LLOG_INFO);
    }

    $s->fetchAll();

    $classname = $s->o_os->class;
    if (class_exists($classname)) {
      if ($f) {
        return $classname::update($s, $f);
      } else {
        Logger::log('Launching '.$classname.'::update', $s, LLOG_INFO);
        return $classname::update($s);
      }
    }
    return -1;
  }

  public static function cluster($c) {
    $classname = $c->o_clver->class;
    if (class_exists($classname)) {
      return $classname::update($c);
    }
    return -1;
  }
 
  public function __construct()
  {
    die("Cannot instanciate Update class!");
  }
}
?>
