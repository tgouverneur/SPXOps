<?php
/**
 * Register login
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage data
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
 $page['title'] = 'Register  ';
 $page['action'] = 'Register';
 if ($lm->o_login) {
   throw new ExitException('You cannot register while being already logged in!');
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   $what = $_GET['w'];
 } else { 
   $what = 'login';
 }

switch($what) {
 case 'login':
   if (Setting::get('general', 'allowRegistration')->value == 0) {
       throw new ExitException('Self-Registration is disabled on this site!');
   }
   $what = 'User';
   $obj = new Login();
   $content = new Template('../tpl/form_register.tpl');
   $page['title'] .= $what;
   if (isset($_POST['submit'])) { /* clicked on the Add button */
     $fields = array('fullname', 'email', 'username', 'password', 'password_c', 'f_noalerts', 'f_admin', 'f_api');
     foreach($fields as $field) {
       if (!strncmp($field, 'f_', 2)) { // should be a checkbox
         if (isset($_POST[$field])) {
           $obj->{$field} = 1;
         } else {
           $obj->{$field} = 0;
         }
       } else {
         if ($_POST[$field]) {
           $obj->{$field} = $_POST[$field];
         }
       }
     }
     $errors = $obj->valid();
     if ($errors) {
       $content->set('error', $errors);
       $content->set('obj', $obj);
       goto screen;
     }
     /* Must crypt the password */
     $obj->encryptPassword($obj->password);
     /* We're using self-register, flag the account as not active and notice administrators */
     $obj->f_active = 0;
     $obj->insert();
     Notification::notifyNewUser($obj);
     $content = new Template('../tpl/message.tpl');
     $content->set('msg', "Your user $obj has been succesfully registered, Site-Admins have been notified. One of them must confirm your login before it gets activated.");
     goto screen;
   }
 break;
 default:
   $content = new Template('../tpl/error.tpl');
   $content->set('error', 'Unknown option or not yet implemented');
 break;
}

screen:
 $head->set('page', $page);
 if (isset($a_link)) $foot->set('a_link', $a_link);
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

} catch (ExitException $e) {

    $h = Utils::getHTTPError($e->getMessage());
    echo $h->fetch();

} catch (Exception $e) {
    /* @TODO: LOG EXCEPTION */
    $h = Utils::getHTTPError('Unexpected Exception');
    echo $h->fetch();
}



?>
