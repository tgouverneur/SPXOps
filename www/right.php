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

 if (!$h->isAjax()) {
   /* Page setup */
   $page = array();
   $page['title'] = 'Error';
   if ($lm->o_login) $page['login'] = &$lm->o_login;

   $index = new Template("../tpl/index.tpl");
   $head = new Template("../tpl/head.tpl");
   $head->set('page', $page);
   $foot = new Template("../tpl/foot.tpl");

   $content = new Template("../tpl/error.tpl");
   $content->set('error', "The page you requested cannot be called as-is...");

   $index->set('head', $head);
   $index->set('content', $content);
   $index->set('foot', $foot);
   echo $index->fetch();
   exit(0);
 }

 if (!$lm->o_login) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must be logged-in';
   goto screen;
 }

 if (!$lm->o_login->f_admin) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must be administrator to update some rights...';
   goto screen;
 }

 $u = $r = $l = null;

 if (isset($_GET['u']) && !empty($_GET['u'])) {
   $u = $_GET['u'];
 }
 if (isset($_GET['r']) && !empty($_GET['r'])) {
   $r = $_GET['r'];
 }
 if (isset($_GET['l']) && !empty($_GET['l'])) {
   $l = $_GET['l'];
 }

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$u || !$r) {
   $ret['rc'] = 1;
   $ret['msg'] = 'You must provide proper arguments';
   goto screen;
 }

 $ugroup = new UGroup();
 $ugroup->id = $u;
 if ($ugroup->fetchFromId()) {
   $ret['rc'] = 1;
   $ret['msg'] = 'UGroup specified not found in database';
   goto screen;
 }

 $right = new Right();
 $right->id = $r;
 if ($right->fetchFromId()) {
   $ret['rc'] = 1;
   $ret['msg'] = 'Right specified not found in database';
   goto screen;
 }

 if (!is_numeric($l) && $l) {
   $ret['rc'] = 1;
   $ret['msg'] = 'Incorrect level speicification';
   goto screen;
 }

 $right->fetchJT('a_ugroup');
 $ugroup->level[''.$right] = $right->getRight($ugroup);
 if ($right->isInJT('a_ugroup', $ugroup, array('level'))) {
   $right->delFromJT('a_ugroup', $ugroup);
 }

 $ugroup->level[''.$right] = $l;
 $right->level[''.$ugroup] = $l;
 $right->addToJT('a_ugroup', $ugroup);

 Act::add("Changed the right $right for group $ugroup", $lm->o_login);
 $ret['rc'] = 0;
 $ret['msg'] = "The right $right for $ugroup has been updated.";
 goto screen;

screen:
 echo json_encode($ret);

?>
