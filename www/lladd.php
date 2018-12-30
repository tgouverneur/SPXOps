<?php
/**
 * Add object to list
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
 if (isset($_GET['o']) && !empty($_GET['o'])) {
   $o = $_GET['o'];
 }
 if (isset($_GET['t']) && !empty($_GET['t'])) {
   $t = $_GET['t'];
 }
 if (isset($_GET['r']) && !empty($_GET['r'])) {
   $r = $_GET['r'];
 }

 header('Content-Type: application/json'); 
 $ret = array();

 if (!$w || !$i || !$o || !$t) {
    throw new ExitException('You must provide proper arguments', 2);
 }

 switch ($w) {
   case 'login':
     if (!$lm->o_login->cRight('USR', R_EDIT)) {
       throw new ExitException('You don\'t have the rights to edit users', 2);
     }
     $obj = new Login($i);
     if ($obj->fetchFromId()) {
       throw new ExitException('Cannot find User provided inside the database', 2);
     }
     if (!strcmp($o, 'ugroup')) {
       $obj->fetchJT('a_ugroup');
       if (!$r || $r == 0) {
         $tobj = new UGroup($t);
         if ($tobj->fetchFromId()) {
           throw new ExitException('Cannot find User Group provided inside the database', 2);
         }
          if (!$obj->isInJT('a_ugroup', $tobj)) {
            $obj->addToJT('a_ugroup', $tobj);
            Act::add("Added User Group $tobj to User $obj", $lm->o_login);
            $ret['rc'] = 0;
            $ret['res'] = json_encode(array(
                                json_encode(array(
                                'id' => $tobj->id,
                                'value' => $tobj->link(),
                                )),
                          ));
            $ret['llist'] = 'ugroup';
            $ret['src'] = 'login';
            $ret['srcid'] = $obj->id;
            $ret['msg'] = "Added group $tobj to user $obj";

          } else {
            throw new ExitException('Specified group already assigned to this user', 2);
          }
       } else {
            throw new ExitException('Not yet implemented', 2);
       }
     } else {
        throw new ExitException('Unrecognized target class', 2);
     }
   break;
   case 'check':
     if (!$lm->o_login->cRight('CHK', R_EDIT)) {
       throw new ExitException('You don\'t have the rights to edit check', 2);
     }
     $obj = new Check($i);
     if ($obj->fetchFromId()) {
       throw new ExitException('Cannot find Check provided inside the database', 2);
     }
     if (!strcmp($o, 'sgroup')) {
       $obj->fetchJT('a_sgroup');
       if (!$r || $r == 0) {
         $tobj = new SGroup($t);
         if ($tobj->fetchFromId()) {
             throw new ExitException('Cannot find Server Group provided inside the database', 2);
         }
         $tobj->f_except[''.$obj] = 0;
          if (!$obj->isInJT('a_sgroup', $tobj)) {
            $obj->addToJT('a_sgroup', $tobj);
            Act::add("Added Server Group $tobj to Check $obj", $lm->o_login);
            $ret['rc'] = 0;
            $ret['res'] = json_encode(array(
                                json_encode(array(
                                'id' => $tobj->id,
                                'value' => $tobj->link(),
                                )),
                          ));
            $ret['llist'] = 'sgroup';
            $ret['src'] = 'check';
            $ret['srcid'] = $obj->id;
            $ret['msg'] = "Added server group $tobj to check $obj";

          } else {
              throw new ExitException('Specified group already assigned to this check', 2);
          }
       } else {
           throw new ExitException('Not yet implemented', 2);
       }
     } else if (!strcmp($o, 'esgroup')) {
       $obj->fetchJT('a_sgroup');
       if (!$r || $r == 0) {
         $tobj = new SGroup($t);
         if ($tobj->fetchFromId()) {
             throw new ExitException('Cannot find server group provided inside the database', 2);
         }
         $tobj->f_except[''.$obj] = 1; // EXCEPTED!
         $obj->f_except[''.$tobj] = 1;
          if (!$obj->isInJT('a_sgroup', $tobj)) {
            $obj->addToJT('a_sgroup', $tobj);
            Act::add("Added Exception for Server Group $tobj to Check $obj", $lm->o_login);
            $ret['rc'] = 0;
            $ret['res'] = json_encode(array(
                                json_encode(array(
                                'id' => $tobj->id,
                                'value' => $tobj->link(),
                                )),
                          ));
            $ret['llist'] = 'esgroup';
            $ret['src'] = 'check';
            $ret['srcid'] = $obj->id;
            $ret['msg'] = "Added exception for server group $tobj to check $obj";

          } else {
            $ret['rc'] = 1;
            $ret['msg'] = 'Specified group already assigned to this check';
          }
       } else {
           throw new ExitException('Not yet implemented', 2);
       }
     } else {
         throw new ExitException('Unrecognized target class', 2);
     }
   break;
   case 'ugroup':
     if (!$lm->o_login->cRight('UGRP', R_EDIT)) {
       throw new ExitException('You don\'t have the rights to edit user group', 2);
     }
     $obj = new UGroup($i);
     if ($obj->fetchFromId()) {
         throw new ExitException('Cannot find user group provided inside the database', 2);
     }
     if (!strcmp($o, 'login')) {
       $obj->fetchJT('a_login');
       if (!$r || $r == 0) {
         $tobj = new Login($t);
         if ($tobj->fetchFromId()) {
             throw new ExitException('Cannot find Login provided inside the database');
         }
          if (!$obj->isInJT('a_login', $tobj)) {
            $obj->addToJT('a_login', $tobj);
            Act::add("Added login $tobj to $obj group", $lm->o_login);
            $ret['rc'] = 0;
            $ret['res'] = json_encode(array(
				json_encode(array(
				'id' => $tobj->id,
				'value' => $tobj->link(),
			        )),
			  ));
            $ret['llist'] = 'login';
            $ret['src'] = 'ugroup';
            $ret['srcid'] = $obj->id;
            $ret['msg'] = "Added login $tobj to $obj group";

          } else {
              throw new ExitException('Specified login is already in this group');
          }
       } else {
         $q = '%'.$t.'%';
         $f = array();
         $s = array('ASC:username');
         $f['username'] = 'LIKE:'.$q;
         $a_list = Login::getAll(true, $f, $s);
         if (!count($a_list)) {
           $ret['rc'] = 1;
           $ret['msg'] = 'No Login(s) found';
         } else {
             $res = array();
             $nradd = 0;
             foreach($a_list as $tobj) {
               if (!$obj->isInJT('a_login', $tobj)) {
                 $obj->addToJT('a_login', $tobj);
                 Act::add("Added Login $tobj to $obj group", $lm->o_login);
                 $nradd++;
                 array_push($res, json_encode(array(
                                            'id' => $tobj->id,
                                            'value' => $tobj->link(),
                                    )));
               }
             }
             $ret['llist'] = 'login';
             $ret['src'] = 'ugroup';
             $ret['srcid'] = $obj->id;
             $ret['rc'] = 0;
             $ret['res'] = json_encode($res);
             $ret['msg'] = $nradd." Have been added to $obj group.";
           }
       }
     } else {
         throw new ExitException('Unrecognized target class');
     }
   break;
   case 'sgroup':
     if (!$lm->o_login->cRight('SRVGRP', R_EDIT)) {
         throw new ExitException('You don\'t have the rights to edit server groups');
     }
     $obj = new SGroup($i);
     if ($obj->fetchFromId()) {
         throw new ExitException('Cannot find server group provided inside the database');
     }
     if (!strcmp($o, 'server')) {
       $obj->fetchJT('a_server');
       if (!$r || $r == 0) {
         $tobj = new Server($t);
         if ($tobj->fetchFromId()) {
             throw new ExitException('Cannot find Server provided inside the database');
         }
          if (!$obj->isInJT('a_server', $tobj)) {
            $obj->addToJT('a_server', $tobj);
            Act::add("Added server $tobj to $obj group", $lm->o_login);
            $ret['rc'] = 0;
            $ret['res'] = json_encode(array(
				json_encode(array(
					'id' => $tobj->id,
					'value' => $tobj->link(),
				)),
			  ));
            $ret['llist'] = 'server';
            $ret['src'] = 'sgroup';
            $ret['srcid'] = $obj->id;
            $ret['msg'] = "Added Server $tobj to $obj group";

          } else {
              throw new ExitException('Specified server is already in this group');
          }
       } else {
         $q = '%'.$t.'%';
         $f = array();
         $s = array('ASC:hostname');
         $f['hostname'] = 'LIKE:'.$q;
         $a_list = Server::getAll(true, $f, $s);
         if (!count($a_list)) {
           $ret['rc'] = 1;
           $ret['msg'] = 'No Server(s) found';
         } else {
             $res = array();
             $nradd = 0;
             foreach($a_list as $tobj) {
               if (!$obj->isInJT('a_server', $tobj)) {
                 $obj->addToJT('a_server', $tobj);
                 Act::add("Added server $tobj to $obj group", $lm->o_login);
                 $nradd++;
                 array_push($res, json_encode(array(
                                            'id' => $tobj->id,
                                            'value' => $tobj->link(),
                                    )));
               }
             }
             $ret['llist'] = 'server';
             $ret['src'] = 'sgroup';
             $ret['srcid'] = $obj->id;
             $ret['srcname'] = $obj->link();
             $ret['rc'] = 0;
             $ret['res'] = json_encode($res);
             $ret['msg'] = $nradd." Have been added to $obj group.";
           }
       }
     } else if (!strcmp($o, 'vm')) {
       $obj->fetchJT('a_vm');
       if (!$r || $r == 0) {
         $tobj = new VM($t);
         if ($tobj->fetchFromId()) {
             throw new ExitException('Cannot find VM provided inside the database');
         }
          if (!$obj->isInJT('a_vm', $tobj)) {
            $obj->addToJT('a_vm', $tobj);
            Act::add("Added vm $tobj to $obj group", $lm->o_login);
            $ret['rc'] = 0;
            $ret['res'] = json_encode(array(
				json_encode(array(
					'id' => $tobj->id,
					'value' => $tobj->link(),
				)),
			  ));
            $ret['llist'] = 'vm';
            $ret['src'] = 'sgroup';
            $ret['srcid'] = $obj->id;
            $ret['srcname'] = $obj->link();
            $ret['addid'] = $tobj->id;
            $ret['msg'] = "Added vm $tobj to $obj group";

          } else {
              throw new ExitException('Specified vm is already in this group');
          }
       } else {
         $q = '%'.$t.'%';
         $f = array();
         $s = array('ASC:hostname');
         $f['hostname'] = 'LIKE:'.$q;
         $a_list = VM::getAll(true, $f, $s);
         if (!count($a_list)) {
           $ret['rc'] = 1;
           $ret['msg'] = 'No VM(s) found';
         } else {
             $res = array();
             $nradd = 0;
             foreach($a_list as $tobj) {
               if (!$obj->isInJT('a_vm', $tobj)) {
                 $obj->addToJT('a_vm', $tobj);
                 Act::add("Added vm $tobj to $obj group", $lm->o_login);
                 $nradd++;
                 array_push($res, json_encode(array(
                                            'id' => $tobj->id,
                                            'value' => $tobj->link(),
                                    )));
               }
             }
             $ret['llist'] = 'vm';
             $ret['src'] = 'sgroup';
             $ret['srcid'] = $obj->id;
             $ret['rc'] = 0;
             $ret['res'] = json_encode($res);
             $ret['msg'] = $nradd." Have been added to $obj group.";
           }
       }
     } else {
       throw new ExitException('Unrecognized target class');
     }
   break;
   default:
       throw new ExitException('Unknown class provided');
   break;
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
