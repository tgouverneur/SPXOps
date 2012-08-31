<?php

class CLType
{

  public static function update($c, $f = null) {

    $oclass = get_called_class();
    if ($f) {
      if (method_exists($oclass, $f)) {
        Logger::log('Running '.$oclass.'::'.$f, $c, LLOG_INFO);
	try {
          return $oclass::$f($c);
        } catch (Exception $e) {
	  Logger::log($oclass.'::'.$f.' has failed with '.$c.': '.$e, $c, LLOG_ERR);
	}
      }
      return;
    }
    foreach ($oclass::$_update as $method) {
      if (method_exists($oclass, $method)) {
        Logger::log('Running '.$oclass.'::'.$method, $c, LLOG_INFO);
        try {
          $oclass::$method($c);
        } catch (Exception $e) {
	  Logger::log($oclass.'::'.$method.' has failed with '.$c.': '.$e, $c, LLOG_ERR);
	}
      }
    }
    $c->update();
    return 0;
  }

}

?>
