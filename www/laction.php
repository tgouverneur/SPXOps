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

 if (!$h->isAjax()) {
     throw new ExitException('The page you requested cannot be called as-is...', 1);
 }

 if (!$lm->o_login) {
     throw new ExitException('You must be logged-in', 2);
 }
 $lm->o_login->fetchRights();

 $w = $i = $o = $t = $r = null;

 if (isset($_GET['w']) && !empty($_GET['w'])) {
   $w = $_GET['w'];
 }
 if (isset($_GET['i']) && !empty($_GET['i'])) {
   $i = $_GET['i'];
 }

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$w || !$i) {
     throw new ExitException('You must provide proper arguments', 2);
 }

 if (!$lm->o_login->cRight('SRV', R_EDIT)) {
     throw new ExitException('You don\'t have the rights to ack check', 2);
 }
 $obj = new Server($i);
 if ($obj->fetchFromId()) {
   throw new ExitException('Cannot find Server provided inside the database', 2);
 }

 $obj->fetchFK('fk_os');
 if (!$obj->o_os) {
   throw new ExitException('Cannot find OS for the provided server', 2);
 }

 $eas = $obj->getExtraActions();
 $action = null;
 foreach($eas as $a) {
   if (!strcmp($a->fct, $w)) {
     $action = $a;
     break;
   }
 }
 if (!$action) {
   throw new ExitException('Cannot find this action', 2);
 }

 $ret['rc'] = $action->call($obj);
 $ret['res'] = $action->res;

 echo json_encode($ret);

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
