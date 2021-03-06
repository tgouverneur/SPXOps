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
      $text_content = $s.':'.$cr->o_check.' status: '.Result::colorRC($cr->rc).' (was: '.Result::colorRC($oldcr->rc).') msg: '.$cr->message.': '.$cr->details;

      foreach ($a_login as $l) {
          Logger::log('Going to send notification for check '.$cr->o_check.' to '.$l, null, LLOG_DEBUG);
          $mail = new SPXMail();
          $mail->to = $l->email;
          $mail->subject = $subject;
          $mail->msg = $msg;
          $mail->headers = $headers;
          $mail->insert();
          /* send text if needed */
          if ($cr->o_check->f_text && !empty($l->phone)) {
              $l->sendText($text_content);
          }
      }
  }

  /* Notify site-admin that a new report has been sent */
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
          if ($admin->f_noalerts) {
              continue;
          }
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
          if ($admin->f_noalerts) {
              continue;
          }
          Notification::sendMail($admin->email, $short, $msg);
      }
      SlackMSG::sendMessage('New user registered: '.$obj);
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
        //mail($to, $subject, $msg, $headers);
        $mail = new SPXMail();
        $mail->to = $to;
        $mail->subject = $subject;
        $mail->msg = $msg;
        $mail->headers = $headers;
        $mail->insert();
    }

    public static function sendPasswordReset($obj) {

        $name = 'SPXOps Admin';
        $short = 'Password reset';
        $msg = "Dear $name,\n\n";
        $msg .= "You or someone else has request a password reset link for your SPXOps account.";
        $msg .= " If this is not you, you can simply discard this email, the link above will anyway expire in 24h\n\n";
        $url = HTTP::getBaseURL();
        if (!$url) {
            throw new ExitException('The base URL of SPXOps is not set, please contact your site admin');
        }
        $url .= "/reset/w/proceed/i/".$obj->id."/c/".$obj->getResetCode();
        $msg .= "To reset your password, follow this link: ".$url;
        $msg .= "\n\n\nBest,\n\n--SPXOps\n";

        Notification::sendMail($obj->email, $short, $msg);
        return;
    }

    public static function sendJobFailure(Job $j) {

        $name = 'SPXOps Admin';
        if ($j->o_login) {
            if ($j->o_login->f_noalerts) { /* do not send if user disabled alerting */
                return;
            }
            $name = $j->o_login->fullname;
        }
        $short = 'Job #'.$j->id.' '.$j->stateStr().' '.$j->class.'::'.$j->fct;
        $msg = "Dear $name,\n\n";
        if ($j->o_log) {
            $msg .= "The job mentionned below has failed with ".$j->stateStr()." status and is associated with the following log:\n\n";
            $msg .= "-------------------------------[ LOG START ]-------------------------------\n";
            $msg .= $j->o_log->log;
            $msg .= "-------------------------------[ LOG END ]-------------------------------\n";
        } else {
            $msg .= "The job mentionned below has failed with ".$j->stateStr()." status and no associated log\n";
        }
        /* @TODO: add url to complete job info on website */
        if ($j->o_login) {
          Notification::sendMail($j->o_login->email, $short, $msg);
        } else {
            $a_admin = Login::getAll(true, array('f_admin' => 'CST:1'));
            foreach ($a_admin as $admin) {
                if ($admin->f_noalerts) {
                    continue;
                }
                Notification::sendMail($admin->email, $short, $msg);
            }
        }
        return;
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
            //mail($l->email, $subject, $msg, $headers);
            $mail = new SPXMail();
            $mail->to = $l->email;
            $mail->subject = $subject;
            $mail->msg = $msg;
            $mail->headers = $headers;
            $mail->insert();
        }
    }

    

}
