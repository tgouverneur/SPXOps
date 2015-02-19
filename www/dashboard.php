<?php
 require_once("../libs/utils.obj.php");

try {

 $m = MySqlCM::getInstance();
 if ($m->connect()) {
   HTTP::getInstance()->errMysql();
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
   if (!$lm->o_login->cRight('CHKBOARD', R_VIEW)) {
     throw new ExitException('Access Denied, please check your access rights!');
   }
 } else {
   throw new ExitException('You must be logged-in to access this page');
 }

 $index = new Template("../tpl/index.tpl");
 $head = new Template("../tpl/head.tpl");
 $head->set('page', $page);
 $foot = new Template("../tpl/foot.tpl");

 $i = null;
 if (isset($_GET['i']) && !empty($_GET['i'])) {
   $i = $_GET['i'];
 }

 if ($i) {
   $obj = new Server($i);
   if ($obj->fetchFromId()) {
       throw new ExitException('Provided server ID not found in the database..');
   }
   $obj->fetchJT('a_sgroup');
   $obj->buildCheckList(true);
   $content = new Template("../tpl/dashboard_server.tpl");
   $content->set('obj', $obj);
   $a_link = array(
	array('href' => '/dashboard',
	      'name' => 'Back to dashboard',
	     ),
        array('href' => '/view/w/server/i/'.$obj->id,
              'name' => 'Back to server',
             ),
	);
   $js = array('check.js');
   $head->set('js', $js);
 } else {
   $content = new Template("../tpl/dashboard.tpl");
   $content->set('a_list', Server::dashboardArray());
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
