<?php

class OSType
{

  public static function update($s, $f = null)
  {
      $oclass = get_called_class();
      if ($f) {
          if (method_exists($oclass, $f)) {
              Logger::log('Running '.$oclass.'::'.$f, $s, LLOG_INFO);
              try {
                  return $oclass::$f($s);
              } catch (Exception $e) {
                  Logger::log($oclass.'::'.$f.' has failed with '.$s.': '.$e, $s, LLOG_ERR);
              }
          }

          return;
      }
      foreach ($oclass::$_update as $method) {
          if (method_exists($oclass, $method)) {
              Logger::log('Running '.$oclass.'::'.$method, $s, LLOG_INFO);
              try {
                  $oclass::$method($s);
              } catch (Exception $e) {
                  Logger::log($oclass.'::'.$method.' has failed with '.$s.': '.$e, $s, LLOG_ERR);
              }
          }
      }
      $s->update();

      return 0;
  }
}
