<?php

class OSType
{

  protected static function cleanRemoved(&$s, $a, $p, &$a_found) {
      foreach ($s->{$a} as $item) {
          if ($p) {
              if (isset($a_found[$item->{$p}])) {
                  unset($a_found[$item->{$p}]); // if found multiple time, remove duplicates (BUGFIX)
                  continue;
              }
          } else {
              if (isset($a_found[''.$item])) {
                  unset($a_found[''.$item]); // if found multiple time, remove duplicates (BUGFIX)
                  continue;
              }
          }
          $s->log("Removing $item from $s", LLOG_INFO);
          $item->delete();
      }  
  }


  public static function update(Server$s, $f = null)
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
