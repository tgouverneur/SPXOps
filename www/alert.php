<?php
/**
 * Alert assignements management
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

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$lm->o_login) {
     throw new ExitException('You must be logged-in', 2);
 }

 $lm->o_login->fetchRights();

 if (!$lm->o_login->cRight('UGRP', R_EDIT)) {
     throw new ExitException('You don\'t have rights to modify group', 2);
 }

 $u = $a = $e = $g = 0;

 if (isset($_GET['u']) && !empty($_GET['u'])) {
   $u = $_GET['u'];
 }
 if (isset($_GET['a']) && !empty($_GET['a'])) {
   $a = $_GET['a'];
 }
 if (isset($_GET['e']) && !empty($_GET['e'])) {
   $e = $_GET['e'];
 }
 if (isset($_GET['g']) && !empty($_GET['g'])) {
   $g = $_GET['g'];
 }

 if ((!$g && !$a) || !$u) {
     throw new ExitException('You must provide proper arguments', 2);
 }

 $ugroup = new UGroup();
 $ugroup->id = $u;
 if ($ugroup->fetchFromId()) {
     throw new ExitException('UGroup specified not found in database', 2);
 }

 if ($a) {
   $at = new AlertType($a);
   if ($at->fetchFromId()) {
     throw new ExitException('AlertType specified not found in database', 2);
   }

   if (!is_numeric($e) && $e) {
     throw new ExitException('Incorrect level specification', 2);
   }

   $at->fetchJT('a_ugroup');
   
   if ($e && !$at->isInJT('a_ugroup', $ugroup, array())) {
     $at->addToJT('a_ugroup', $ugroup);
   } else if (!$e && $at->isInJT('a_ugroup', $ugroup, array())) {
     $at->delFromJT('a_ugroup', $ugroup);
   }

   Act::add("Changed the alert $at for group $ugroup ($e)", $lm->o_login);
   $ret['rc'] = 0;
   $ret['msg'] = "The alert $at for $ugroup has been set to $e.";
 } else if ($g) {
   $sg = new SGroup($g);
   if ($sg->fetchFromId()) {
     throw new ExitException('SGroup specified not found in database', 2);
   }

   if (!is_numeric($e) && $e) {
     throw new ExitException('Incorrect level specification', 2);
   }

   $sg->fetchJT('a_ugroup');
   
   if ($e && !$sg->isInJT('a_ugroup', $ugroup, array())) {
     $sg->addToJT('a_ugroup', $ugroup);
   } else if (!$e && $sg->isInJT('a_ugroup', $ugroup, array())) {
     $sg->delFromJT('a_ugroup', $ugroup);
   }

   Act::add("Changed the alert on server group $sg for group $ugroup ($e)", $lm->o_login);
   $ret['rc'] = 0;
   $ret['msg'] = "The alert on server group $sg for $ugroup has been set to $e.";
 }

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
