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

 if (!$h->isAjax()) {
     throw new ExitException('The page you requested cannot be called as-is...', 1);
 }

 if (!$lm->o_login) {
     throw new ExitException('You must be logged-in', 2);
 }

 if (!$lm->o_login->f_admin) {
     throw new ExitException('You must be administrator to update rights...');
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
     throw new ExitException('You must provide proper arguments...');
 }

 $ugroup = new UGroup();
 $ugroup->id = $u;
 if ($ugroup->fetchFromId()) {
     throw new ExitException('UGroup specified not found in database');
 }

 $right = new Right();
 $right->id = $r;
 if ($right->fetchFromId()) {
     throw new ExitException('Right specified not found in database');
 }

 if (!is_numeric($l) && $l) {
     throw new ExitException('Incorrect level specification');
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
