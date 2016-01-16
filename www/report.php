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

 if ($lm->o_login) {
     $page['login'] = &$lm->o_login;
     $lm->o_login->fetchRights();
 } else {
     throw new ExitException(null, EXIT_LOGIN);
 }

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $head->set('page', $page);

 $foot = new Template("../tpl/foot.tpl");
 $content = new Template("../tpl/report.tpl");

 if (isset($_POST['submit'])) {
   $errors = array();
   if (isset($_POST['message'])) {
       $message = $_POST['message'];
   }
   if (empty($message)) {
     $errors[] = 'Empty message';
   }
   if (count($errors) || $rc) {
     $errors[] = 'Unable to send reports because of wrong fields, please check';
   } else {
     $page['login'] = $lm->o_login;
     $head->set('page', $page);
     $content = new Template('../tpl/message.tpl');
     $content->set('msg', "Your report has been sent to the Site Admin, thank you for your feedback!");
     Notification::sendReport($message, $lm->o_login);
     goto screen;
   }
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
