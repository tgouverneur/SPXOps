<?php
/**
 * Reset Password
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
 $page['title'] = 'Reset Password: ';
 if ($lm->o_login) {
   throw new ExitException('You cannot be logged in to access this feature');
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
    case 'ask':
         if (!isset($_POST['username']) || empty($_POST['username'])) {
             throw new ExitException('Login username is missing');
         }
         $obj = new Login();
         $obj->username = $_POST['username'];
         if ($obj->fetchFromField('username')) {
             throw new ExitException('Username not found in database');
         }
         $now = time();
         if (($obj->t_reset + 3600) > $now) {
             throw new ExitException('You cannot reset your password more than once per hour...');
         }
         $obj->t_reset = $now;
         $obj->update();
         /* Send reset password link */
         Notification::sendPasswordReset($obj);
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', 'An email with password reset link has been sent to your account\'s email address.');
         break;
     case 'proceed':
         if (!isset($_GET['i']) || empty($_GET['i']) || !isset($_GET['c']) || empty($_GET['c'])) {
             throw new ExitException('The Reset link is malformed');
         }
         $obj = new Login($_GET['i']);
         if ($obj->fetchFromId()) {
             throw new ExitException('The specified user can not be found in the database');
         }
         $code = $_GET['c'];
         if ($obj->t_reset < (time() - (24*3600)) || $obj->t_reset < 0) {
             throw new ExitException('The reset link has expired or is not valid');
         }
         if (strcmp($obj->getResetCode(), $code)) {
             throw new ExitException('The reset link is invalid');
         }
         $content = new Template('../tpl/form_reset2.tpl');
         $content->set('obj', $obj);
     break; 
     case 'final':
         if (!isset($_GET['i']) || empty($_GET['i']) || !isset($_GET['c']) || empty($_GET['c'])) {
             throw new ExitException('The Reset link is malformed');
         }
         if (!isset($_POST['password']) || empty($_POST['password']) || !isset($_POST['password2']) || empty($_POST['password2'])) {
             throw new ExitException('Password not provided');
         }
         $password = $_POST['password'];
         $password2 = $_POST['password2'];
         $obj = new Login($_GET['i']);
         if ($obj->fetchFromId()) {
             throw new ExitException('The specified user can not be found in the database');
         }
         $code = $_GET['c'];
         if ($obj->t_reset < (time() - (24*3600)) || $obj->t_reset < 0) {
             throw new ExitException('The reset link has expired or is not valid');
         }
         if (strcmp($obj->getResetCode(), $code)) {
             throw new ExitException('The reset link is invalid');
         }
         $obj->password = $password;
         $obj->password_c = $password2;
         $errors = $obj->valid(false);
         if ($errors) {
             $content = new Template('../tpl/form_reset2.tpl');
             $content->set('error', $errors);
             $content->set('obj', $obj);
             goto screen;
         }
         $obj->encryptPassword($obj->password);
         $obj->update();
         $content = new Template('../tpl/message.tpl');
         $content->set('msg', 'Password has been successfully set, please now try to login!');
     break;
     default:
       $content = new Template('../tpl/error.tpl');
       $content->set('error', 'Unknown option or not yet implemented');
     break;
   }
 } else {
     $content = new Template('../tpl/form_reset.tpl');
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
    } else if ($e->type == EXIT_LOGIN) { /* login needed */
        LoginCM::requestLogin();
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
