<?php
 require_once("../libs/utils.obj.php");


 $m = MySqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
 }
 $lm = LoginCM::getInstance();
 $lm->startSession();

 $h = HTTP::getInstance();
 $h->parseUrl();

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $foot = new Template("../tpl/foot.tpl");
 $page = array();
 $page['title'] = 'Edit settings';

 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   HTTP::errWWW('You must be logged-in to access this page');
 }

 if (!$lm->o_login->cRight('CFG', R_VIEW)) {
   HTTP::errWWW('Access Denied, please check your access rights!');
 }

 $what = 'Setting';
 $content = new Template('../tpl/settings.tpl');
 Setting::fetchAll();
 $a_cat = Setting::getCat();
 $content->set('a_cat', $a_cat);
 $page['title'] .= $what;
 $content->set('page', $page);
 if (isset($_POST['submit'])) { /* clicked on the Edit button */
 
   if (!$lm->o_login->cRight('CFG', R_EDIT)) {
     HTTP::errWWW('Access Denied, please check your access rights!');
   }
   $u=0;
   foreach (Setting::getSettings() as $s) { 
     if (!isset($_POST[$s->cat.'_'.$s->name])) { continue; }

     $value = $_POST[$s->cat.'_'.$s->name];
     if (strcmp($value, $s->value)) {
       Setting::set($s->cat, $s->name, $value);
       $u++;
     }
     $a_link = array(
            array('href' => '/settings',
                  'name' => 'Back to configuration',
                 ),
            );

   }
   $content = new Template('../tpl/message.tpl');
   $content->set('msg', "Settings have been updated ($u)");
   goto screen;
 }

screen:
 if (isset($a_link)) $foot->set('a_link', $a_link);
 $head->set('page', $page);
 $index->set('head', $head);
 $index->set('content', $content);
 $index->set('foot', $foot);

 echo $index->fetch();

?>
