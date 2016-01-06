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
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class Notification
{
  public static function jobSendNotification(&$job, $s_arg) {
  }

  public static function sendResult(MySqlObj &$s, Result $cr, Result $oldcr = null)
  {
      $a_login = array();

      switch(get_class($s)) {
          case 'Server':
              $fk = 'fk_server';
              $oo = 'o_server';
              break;
          case 'VM':
              $fk = 'fk_vm';
              $oo = 'o_vm';
              break;
          default:
              throw new SPXException('Notification::sendResult(): unsupported server type');
              break;
      }

      if ($cr->rc == 0 && !$oldcr) { // First check result and it's OK, don't send anything.
          return;
      }

      Logger::log('Notification request...'.$cr.' / '.$oldcr, null, LLOG_DEBUG);

      if (!$cr->{$oo} && $cr->{$fk} > 0) {
          $cr->fetchFK($fk);
      }
      if (!$cr->o_check && $cr->fk_check > 0) {
          $cr->fetchFK('fk_check');
      }

    /* fetch Server groups */
    $cr->{$oo}->fetchJT('a_sgroup');

    /* fetch Server Groups link to User Groups */
    foreach ($cr->{$oo}->a_sgroup as $sg) {
        $sg->fetchJT('a_ugroup');
      /* for each user group, add each login where notifications are enabled */
      foreach ($sg->a_ugroup as $ug) {
          $ug->fetchJT('a_login');
          foreach ($ug->a_login as $ugl) {
              if (!isset($a_login[$ugl->id]) && !$ugl->f_noalerts) {
                  $a_login[$ugl->id] = $ugl;
              }
          }
      }
    }

      $mfrom = Setting::get('general', 'mailfrom')->value;
      $domain = explode('@', $mfrom);
      $domain = $domain[0];
      $subject = $s.'/'.$cr->o_check.': '.Result::colorRC($cr->rc);
      $headers = 'From: '.$mfrom."\r\n";
      $headers .= 'X-Mailer: SPXOps'."\r\n";
      $headers .= 'Reply-To: no-reply@'.$domain."\r\n";
      $headers .= 'References: <'.Result::getHash($cr, $oldcr).'>'."\r\n";
      $msg = 'Device: '.$s."\r\n";
      $msg .= 'Check: '.$cr->o_check."\r\n";
      if ($oldcr && $cr->rc != $oldcr->rc) {
          $msg .= 'Result: '.Result::colorRC($cr->rc).' (old: '.Result::colorRC($oldcr->rc).')'."\r\n";
      } else {
          $msg .= 'Result: '.Result::colorRC($cr->rc)."\r\n";
      }
      $msg .= 'Message: '.$cr->message."\r\n";
      $msg .= 'When: '.date('d-m-Y H:i:s', $cr->t_upd)."\r\n";
      $msg .= 'Details: '.$cr->details."\r\n";
      if ($oldcr && strcmp($cr->details, $oldcr->details)) {
          $msg .= 'Old Details: '.$oldcr->details;
      }

      foreach ($a_login as $l) {
          Logger::log('Going to send notification for check '.$cr->o_check.' to '.$l, null, LLOG_DEBUG);
          mail($l->email, $subject, $msg, $headers);
      }
  }

  /* Notify site-admin that a new user has requested a login */
  public static function sendReport($message, $from) {
      $a_admin = Login::getAll(true, array('f_admin' => 'CST:1'));
      $short = 'Report received';
      $msg = "Dear SPXOps Admin,\n\n";
      $msg .= "We have received a new report from someone browsing SPXOps, here is the details of his message:\n\n";
      $msg .= "Username: ".$from->username;
      $msg .= "\nEmail: ".$from->email;
      $msg .= "\nFull Name: ".$from->fullname;
      $msg .= "\nReport content: ".$message;
      $msg .= "\n\nThanks!\n";
      foreach ($a_admin as $admin) {
        Notification::sendMail($admin->email, $short, $msg);
      }
  }


  /* Notify site-admin that a new user has requested a login */
  public static function notifyNewUser($obj) {
      $a_admin = Login::getAll(true, array('f_admin' => 'CST:1'));
      $short = $obj.' Requested a login';
      $msg = "Dear SPXOps Admin,\n\n";
      $msg .= "There is a new user that requested an access, please review and activate it at your earliest convenience:\n\n";
      $msg .= "Username: ".$obj->username;
      $msg .= "\nEmail: ".$obj->email;
      $msg .= "\nFull Name: ".$obj->fullname;
      $msg .= "\n\nThanks!\n";
      foreach ($a_admin as $admin) {
        Notification::sendMail($admin->email, $short, $msg);
      }
  }

    public static function sendMail($to, $short, $msg)
    {
        $mfrom = Setting::get('general', 'mailfrom')->value;
        $domain = explode('@', $mfrom);
        $domain = $domain[0];
        $subject = '[SPXOps] '.$short;
        $headers = 'From: '.$mfrom."\r\n";
        $headers .= 'X-Mailer: SPXOps'."\r\n";
        $headers .= 'Reply-To: no-reply@'.$domain."\r\n";
        mail($to, $subject, $msg, $headers);
    }

    public static function sendAlert(AlertType $at, $short, $msg)
    {
        $a_login = array();

        Logger::log('Notification request...'.$at, LLOG_DEBUG);

    /* fetch Server groups */
    $at->fetchJT('a_ugroup');

    /* for each user group, add each login where notifications are enabled */
    foreach ($at->a_ugroup as $ug) {
        $ug->fetchJT('a_login');
        foreach ($ug->a_login as $ugl) {
            if (!isset($a_login[$ugl->id]) && !$ugl->f_noalerts) {
                $a_login[$ugl->id] = $ugl;
            }
        }
    }

        $mfrom = Setting::get('general', 'mailfrom')->value;
        $domain = explode('@', $mfrom);
        $domain = $domain[0];
        $subject = '['.$at.'] '.$short;
        $headers = 'From: '.$mfrom."\r\n";
        $headers .= 'X-Mailer: SPXOps'."\r\n";
        $headers .= 'Reply-To: no-reply@'.$domain."\r\n";
        $msg .= 'When: '.date('d-m-Y H:i:s', $cr->t_upd)."\r\n";
        $msg .= 'Message: '.$msg."\r\n";

        foreach ($a_login as $l) {
            Logger::log('Going to send notification for check '.$cr->o_check.' to '.$l, LLOG_DEBUG);
            mail($l->email, $subject, $msg, $headers);
        }
    }

    public function fetchAll($all = 1)
    {
    }

  /**
   * ctor
   */
  public function __construct()
  {
  }
}
