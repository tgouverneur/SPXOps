<?php
/**
 * Token interface
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage tools
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
 require_once("../libs/utils.obj.php");

try {

 $m = MySqlCM::getInstance();
 if ($m->connect()) {
   throw new ExitException('An error has occurred with the SQL Server and we were unable to process your request...');
 }
 $lm = LoginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $foot = new Template("../tpl/foot.tpl");
 $page = array();
 $page['title'] = 'User Token: ';
 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   throw new ExitException('You must be logged-in to access this page');
 }

 $uto = null;
 if ($lm->o_login->fk_utoken > 0) {
     $lm->o_login->fetchFK('fk_utoken');
     $uto = $lm->o_login->o_utoken;
 }

 if (!$uto) {
     throw new ExitException('You must add a User Token before accessing this page');
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
    case 'remove':
         if ($uto->f_init <= 0) {
             $uto->delete();
             $lm->o_login->fk_utoken = -1;
             $lm->o_login->o_utoken = null;
             $lm->o_login->update();
             $content = new Template('../tpl/message.tpl');
             $content->set('msg', 'Your token was not yet initialized, so we just removed it.');
         } else {
             $content = new Template('../tpl/form_utoken_init.tpl');
             $content->set('token', $uto);
             $content->set('action', 'remove');
             if (isset($_POST['submit'])) {
                 /* check OTPValue against the token */
                 if (!isset($_POST['OTPValue']) || empty($_POST['OTPValue'])) {
                     throw new ExitException('You have not provided the Token Value');
                 }
                 $OTPValue = $_POST['OTPValue'];
                 if (!preg_match('/^[0-9]*$/', $OTPValue)) {
                     throw new ExitException('The Value you provided contains something other than just numbers.. that cannot be good.');
                 }
                 try {
                     if ($uto->checkValue($OTPValue)) {
                         $uto->delete();
                         $lm->o_login->fk_utoken = -1;
                         $lm->o_login->o_utoken = null;
                         $lm->o_login->update();
                         $content = new Template('../tpl/message.tpl');
                         $content->set('msg', 'Token value succesfully validated, your token is now removed!');
                     } else {
                         $content->set('error', 'Cannot validate token value');
                     }
                 } catch (SPXException $e) {
                     $error = $e->getMessage();
                     $content->set('error', $error);
                 }
             }
         }

         break;
     case 'check':
         if ($uto->f_init <= 0) {
             throw new ExitException('You must first initialize your token.');
         }
         $content = new Template('../tpl/form_utoken_init.tpl');
         $content->set('token', $uto);
         $content->set('action', 'check');
         if (isset($_POST['submit'])) {
             /* check OTPValue against the token */
             if (!isset($_POST['OTPValue']) || empty($_POST['OTPValue'])) {
                 throw new ExitException('You have not provided the Token Value');
             }
             $OTPValue = $_POST['OTPValue'];
             if (!preg_match('/^[0-9]*$/', $OTPValue)) {
                 throw new ExitException('The Value you provided contains something other than just numbers.. that cannot be good.');
             }
             try {
                 if ($uto->checkValue($OTPValue)) {
                     $content = new Template('../tpl/message.tpl');
                     $content->set('msg', 'Token value succesfully validated, your token is working fine!');
                 } else {
                     $content->set('error', 'Cannot validate token value');
                 }
             } catch (SPXException $e) {
                 $error = $e->getMessage();
                 $content->set('error', $error);
             }
         }

     break;
     case 'init':
         if ($uto->f_init == 1) {
             throw new ExitException('Your token has already been initialized');
         }
         $js = array('jquery.qrcode.min.js');
         $head->set('js', $js);
         $content = new Template('../tpl/form_utoken_init.tpl');
         $content->set('action', 'init');
         $content->set('token', $uto);
         if ($uto->f_init == 0) {
             $content->set('qrcode', $uto->getURL());
             $content->set('msg', 'Step 1 of 3: Enter the first value of your OTP token.');
         } else if ($uto->f_init == -1) {
             $content->set('msg', 'Step 2 of 3: Enter the second value of your OTP token.');
         } else if ($uto->f_init == -2) {
             $content->set('msg', 'Step 3 of 3: This is the last time you need to enter your OTP Token!.');
         }
         if (isset($_POST['submit'])) {
             /* check OTPValue against the token */
             if (!isset($_POST['OTPValue']) || empty($_POST['OTPValue'])) {
                 throw new ExitException('You have not provided the Token Value');
             }
             $OTPValue = $_POST['OTPValue'];
             if (!preg_match('/^[0-9]*$/', $OTPValue)) {
                 throw new ExitException('The Value you provided contains something other than just numbers.. that cannot be good.');
             }
             try {
                 if ($uto->checkValue($OTPValue)) {
                     $uto->f_init -= 1;
                     if ($uto->f_init == -3) {
                         $uto->f_init = 1;
                         $uto->update();
                         $content = new Template('../tpl/message.tpl');
                         $content->set('msg', 'Your token is now initialized');
                     } else {
                         $uto->update();
                         HTTP::redirect('/token/w/init');
                     }
                 } else {
                     $content->set('error', 'Cannot validate token value');
                 }
             } catch (SPXException $e) {
                 $error = $e->getMessage();
                 $content->set('error', $error);
             }
         }

     break;
     default:
       $content = new Template('../tpl/error.tpl');
       $content->set('error', 'Unknown option or not yet implemented');
     break;
   }
 } else {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "I don't know what tool to use...");
 }

screen:
 if (isset($a_link)) $foot->set('a_link', $a_link);
 $head->set('page', $page);
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

} catch (ExitException $e) {
     
    if ($e->type == 2) { 
        echo Utils::getJSONError($e->getMessage());
    } else {
        $h = Utils::getHTTPError($e->getMessage());
        echo $h->fetch();
    }    
     
} catch (Exception $e) {
    /* @TODO: LOG EXCEPTION */
    $h = Utils::getHTTPError('Unexpected Exception');
    echo $h->fetch();
}
?>
