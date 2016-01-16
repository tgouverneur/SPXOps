<?php
/**
 * Login of an user
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage authentication
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

 /* Page setup */
 $page = array();
 $page['title'] = 'Home';
 if ($lm->o_login) $page['login'] = &$lm->o_login;

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $head->set('page', $page);

 $foot = new Template("../tpl/foot.tpl");
 $content = new Template("../tpl/login.tpl");
 if (isset($_GET['r']) && $_GET['r'] == 1) {
     $content->set('error', 'You must be logged-in to access this page, please enter your credentials below');
 }

 if (isset($_POST['submit'])) {
   $OTPValue = $username = $password = '';
   if (isset($_POST['username'])) $username = $_POST['username'];
   if (isset($_POST['password'])) $password = $_POST['password'];
   if (isset($_POST['OTPValue'])) $OTPValue = $_POST['OTPValue'];
   $remember = false;
   $errors = array();
   if (isset($_POST['remember'])) {
     $remember = true;
   }
   if (empty($username)) {
     $errors[] = 'Empty username';
   }
   if (empty($password)) {
     $errors[] = 'Empty password';
   }
   try {
       $rc = $lm->login($username, $password, $OTPValue, $remember);
   } catch (SPXException $e) {
       $errors[] = $e->getMessage();
   }
   if (count($errors) || $rc) {
     $errors[] = 'Unable to authenticate your username and password, please check';
   } else {
     $page['login'] = $lm->o_login;
     $head->set('page', $page);
     $content = new Template('../tpl/message.tpl');
     $content->set('msg', "Welcome ".$lm->o_login->fullname.", you are successfully logged in");
     $content->set('redir', LoginCM::getOriginalRequest());
     goto screen;
   }
   $l = new Login(); 
   $l->username = $username;
   $l->getAddr();
   Act::add("[$l] Failed Login tentative from ".$l->i_raddr, $l);
   $content->set('error', $errors);
 }

screen:
 if (isset($a_link)) $foot->set('a_link', $a_link);
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
