<?php
/**
 * Acknowledge a Check Result Page
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2012-2015, Gouverneur Thomas
 * @version 1.0
 * @package frontend
 * @category www
 * @subpackage JSON
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

 if (!$h->isAjax()) {
   throw new ExitException('The page you requested cannot be called as-is...', 1);
 }

 if (!$lm->o_login) {
   throw new ExitException('You must be logged-in', 2);
 }
 $lm->o_login->fetchRights();
 if (!$lm->o_login->cRight('CHKBOARD', R_DEL)) {
   throw new ExitException('You don\'t have the rights to ack check', 2);
 }

 $i = $n = null;

 if (isset($_GET['i']) && !empty($_GET['i'])) {
   $i = $_GET['i'];
 }
 if (isset($_GET['n']) && !empty($_GET['n'])) {
   $n = $_GET['n'];
 }

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$i) {
   throw new ExitException('You must provide proper arguments', 2);
 }

 $r = new Result($i);
 if ($r->fetchFromId()) {
   throw new ExitException('Result specified not found in database', 2);
 }
 if ($r->f_ack && !$n) {
   throw new ExitException('This result is already acknowledged', 2);
 }
 if (!$r->f_ack && $n) {
   throw new ExitException('This result is not acknowledged', 2);
 }
 if (!$n) {
   $r->f_ack = 1;
   $r->fk_login = $lm->o_login->id;
   $r->o_login = $lm->o_login;
   $a = Act::add("Acknowledged $r", $lm->o_login);
   $ret['msg'] = "$r successfully acknowledged";
   $ret['ackWho'] = $r->o_login->username;
   $ret['ackId'] = $r->fk_login;
 } else {
   $r->f_ack = 0;
   $r->fk_login = -1;
   $r->o_login = null;
   $a = Act::add("Un-Acknowledged $r", $lm->o_login);
   $ret['msg'] = "$r successfully unacknowledged";
 }
 $ret['rc'] = 0;
 $ret['id'] = $r->id;
 $r->update();
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
