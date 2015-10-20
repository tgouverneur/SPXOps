<?php

class OSType
{

  protected static function cleanRemoved(MySqlObj &$s, $a, $p, &$a_found) {
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


  public static function update(MySqlObj &$s, $f = null)
  {
      $oclass = get_called_class();
      $moclass = get_class($s);
      if ($f) {
          if (method_exists($oclass, $f)) {
              Logger::log('Running '.$oclass.'::'.$f, $s, LLOG_INFO);
              try {
                  return $oclass::$f($s);
              } catch (Exception $e) {
                  Logger::log($oclass.'::'.$f.' has failed with '.$s.': '.$e, $s, LLOG_ERR);
                  return -1;
              }
          }
          return 0;
      }
      if (!isset($oclass::$_update[$moclass])) {
          Logger::log('[!] '.$oclass.'::$_update['.$moclass.'] Not found', $s, LLOG_ERR);
          return -1;
      }
      foreach ($oclass::$_update[$moclass] as $method) {
          if (method_exists($oclass, $method)) {
              Logger::log('Running '.$oclass.'::'.$method, $s, LLOG_INFO);
              try {
                  $oclass::$method($s);
              } catch (Exception $e) {
                  Logger::log($oclass.'::'.$method.' has failed with '.$s.': '.$e, $s, LLOG_ERR);
              }
          }
      }

      /* run through plugins hooks */
      $hooks = array();
      switch($moclass) {
          case 'Server':
              $hooks = Plugin::getHooks(P_HOOK_UPD_SRV);
              break;
          case 'VM':
              $hooks = Plugin::getHooks(P_HOOK_UPD_VM);
              break;
      }
      foreach($hooks as $hook) {
          Logger::log('[-] Launching Hook '.$hook->name, $s, LLOG_INFO);
          $hook->call($s);
      }
      $s->update();

      return 0;
  }
}
