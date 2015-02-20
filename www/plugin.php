<?php
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
 $page['title'] = 'List of ';

 if ($lm->o_login) {
   $page['login'] = &$lm->o_login;
   $lm->o_login->fetchRights();
 } else {
   throw new ExitException('You must be logged-in to access this page');
 }

 $js = array();
 $css = array();
 $a_link = null;

 if (isset($_GET['p']) && !empty($_GET['p']) &&
     isset($_GET['w']) && !empty($_GET['w'])) {

       $p = $_GET['p'];
       $w = $_GET['w'];

       $wa = Plugin::getWebAction($p, $w);
       if (!$wa) {
	 throw new ExitException('The Plugin or Action you requested is not registered');
       }
       
       if ($wa->n_right) { /* Preliminary right check */
         if (!$lm->o_login->cRight($wa->n_right, $wa->n_level)) {
	   throw new ExitException('Access Denied, please check your access rights!');
         }
       }
       $content = null;
       $wa->call($wa); /* supposed to fill $content */

       if (!$content) {
	 throw new ExitException('Something wrong happened in plugin '.$wa->o_plugin->name);
       }

       $page['title'] .= $wa->desc;

 } else {
   $content = new Template('../tpl/error.tpl');
   $content->set('error', "I don't know what you're talking about...");
 }

screen:

 $head->set("js", $js);
 $head->set("css", $css);
 $head->set('page', $page);
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
