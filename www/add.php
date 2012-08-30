<?php
 require_once("../libs/autoload.lib.php");
 require_once("../libs/config.inc.php");

 $m = mysqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = loginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $foot = new Template("../tpl/foot.tpl");
 $page = array();
 $page['title'] = 'Add ';
 $page['action'] = 'Add';
 if ($lm->o_login) $page['login'] = &$lm->o_login;

 if (!$lm->o_login) {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "You should be logged in to access this page...");
   goto screen;
 }

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   switch($_GET['w']) {
     case 'user':
       if (!$lm->o_login->f_admin) {
         $content = new Template('../tpl/error.tpl');
         $content->set('error', "You should be administrator in to access this page...");
         goto screen;
       }
       $what = 'User';
       $obj = new Login();
       $content = new Template('../tpl/form_user.tpl');
       $page['title'] .= $what;
       if (isset($_POST['submit'])) { /* clicked on the Add button */
         $fields = array('fullname', 'email', 'username', 'password', 'password_c', 'f_admin', 'f_ldap');
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
	 $obj->bcrypt($obj->password);
         $obj->insert();
         $content = new Template('../tpl/message.tpl');
	 $content->set('msg', "User $obj has been added to database");
	 goto screen;
       }
     break;
     default:
       $content = new Template('../tpl/error.tpl');
       $content->set('error', 'Unknown option or not yet implemented');
     break;
   }
 } else {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "I don't know what to list...");
 }

screen:
 $head->set('page', $page);
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>
