<?php
/**
 * Notification object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2015, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 */


class Notification
{
  public static function sendResult(&$s, $cr, $oldcr = null) {
    $a_login = array();

    Logger::log('Notification request...'.$cr.' / '.$oldcr, LLOG_DEBUG);

    if (!$cr->o_server && $cr->fk_server > 0) {
      $cr->fetchFK('fk_server');
    }
    if (!$cr->o_check && $cr->fk_check > 0) {
      $cr->fetchFK('fk_check');
    }

    /* fetch Server groups */
    $cr->o_server->fetchJT('a_sgroup');

    /* fetch Server Groups link to User Groups */
    foreach($cr->o_server->a_sgroup as $sg) {
      $sg->fetchJT('a_ugroup');
      /* for each user group, add each login where notifications are enabled */
      foreach($sg->a_ugroup as $ug) {
        $ug->fetchJT('a_login');
        foreach($ug->a_login as $ugl) {
          if (!isset($a_login[$ugl->id]) && !$ugl->f_noalerts) {
	    $a_login[$ugl->id] = $ugl;
	  }
        }
      }
    }

    $mfrom = Setting::get('general', 'mailfrom')->value;
    $mname = Setting::get('general', 'sitename')->value;
    $domain = explode('@', $mfrom);
    $domain = $domain[0];
    $subject = $s.'/'.$cr->o_check.': '.Result::colorRC($cr->rc);
    $headers = 'From: '. $mfrom . "\r\n";
    $headers .= 'X-Mailer: SPXOps' . "\r\n";
    $headers .= 'Reply-To: no-reply@'.$domain . "\r\n"; 
    $msg = 'Device: '.$s . "\r\n";
    $msg .= 'Check: '.$cr->o_check. "\r\n";
    if ($oldcr && $cr->rc != $oldcr->rc) {
      $msg .= 'Result: '.Result::colorRC($cr->rc).' (old: '.Result::colorRC($oldcr->rc).')' . "\r\n";
    } else {
      $msg .= 'Result: '.Result::colorRC($cr->rc). "\r\n";
    }
    $msg .= 'Message: '.$cr->message."\r\n";
    $msg .= 'When: '.date('d-m-Y H:m:s', $cr->t_upd) . "\r\n";
    $msg .= 'Details: '.$cr->details."\r\n";
    if ($oldcr && strcmp($cr->details, $oldcr->details)) {
      $msg .= 'Details was: '.$oldcr->details;
    }

    foreach($a_login as $l) {
     Logger::log('Going to send notification for check '.$cr->o_check.' to '.$l, LLOG_DEBUG);
     mail($l->email, $subject, $msg, $headers);
    }
  }

  public static function sendAlert($at, $short, $msg) {
    $a_login = array();

    Logger::log('Notification request...'.$at, LLOG_DEBUG);

    /* fetch Server groups */
    $at->fetchJT('a_ugroup');

    /* for each user group, add each login where notifications are enabled */
    foreach($at->a_ugroup as $ug) {
      $ug->fetchJT('a_login');
      foreach($ug->a_login as $ugl) {
        if (!isset($a_login[$ugl->id]) && !$ugl->f_noalerts) {
          $a_login[$ugl->id] = $ugl;
        }
      }
    }

    $mfrom = Setting::get('general', 'mailfrom')->value;
    $mname = Setting::get('general', 'sitename')->value;
    $domain = explode('@', $mfrom);
    $domain = $domain[0];
    $subject = '['.$at.'] '.$short;
    $headers = 'From: '. $mfrom . "\r\n";
    $headers .= 'X-Mailer: SPXOps' . "\r\n";
    $headers .= 'Reply-To: no-reply@'.$domain . "\r\n"; 
    $msg .= 'When: '.date('d-m-Y H:m:s', $cr->t_upd) . "\r\n";
    $msg .= 'Message: '.$msg."\r\n";

    foreach($a_login as $l) {
     Logger::log('Going to send notification for check '.$cr->o_check.' to '.$l, LLOG_DEBUG);
     mail($l->email, $subject, $msg, $headers);
    }

  }

  public function fetchAll($all = 1) {
    
  }

 /**
  * ctor
  */
  public function __construct()
  {
  }

}
?>
