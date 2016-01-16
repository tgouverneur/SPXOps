<?php
/**
 * View/Add the log for a given object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage management
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
 $page['title'] = 'Add log for';
 $page['action'] = 'Add';
 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   throw new ExitException(null, EXIT_LOGIN);
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {

   $o_name = $_GET['w'];
   if (!class_exists($o_name) ||
       !method_exists($o_name, 'addLog')) {
     throw new ExitException('This kind of object doesn\'t support Logs');
   }

   if (!$lm->o_login->cRight($o_name::$RIGHT, R_EDIT)) {
     throw new ExitException('Access Denied, please check your access rights!');
   }
   if (!isset($_GET['i']) || empty($_GET['i'])) {
     throw new ExitException("No $o_name ID provided");
   }
   $obj = new $o_name($_GET['i']);
   if ($obj->fetchFromId()) {
     throw new ExitException("Object not found inside database");
   }
   $content = new Template('../tpl/form_log.tpl');
   $content->set('obj', $obj);
   $what = strtolower($o_name);
   $page['title'] .= ' '.$what;
   $content->set('page', $page);
   if (isset($_POST['submit'])) { /* clicked on the Add button */
     if (!isset($_POST['msg']) || empty($_POST['msg'])) {
       $content->set('error', 'No message specified');
       goto screen;
     }
     $obj->addLog($_POST['msg']);
     $content = new Template('../tpl/message.tpl');
     $content->set('msg', "Log entry for $what $obj has been added to database");
     $a_link = array(
          array('href' => '/view/w/'.$what.'/i/'.$obj->id,
                'name' => 'Back to '.$what,
               ),
          );
     goto screen;
   }

 } else {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "I don't know what to add a comment on...");
 }

screen:
 $head->set('page', $page);
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
