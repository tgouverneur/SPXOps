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
   $ret['rc'] = 1;
   $ret['msg'] = 'You must provide proper arguments';
   goto screen;
 }

 switch ($w) {
   case 'login':
     if (!$lm->o_login->cRight('USR', R_EDIT)) {
       $ret['rc'] = 1;
       $ret['msg'] = 'You don\'t have the rights to edit users';
       goto screen;
     }
     $obj = new Login($i);
     if ($obj->fetchFromId()) {
       $ret['rc'] = 1;
       $ret['msg'] = 'Cannot find User provided inside the database';
       goto screen;
     }
     if (!strcmp($o, 'ugroup')) {
       $obj->fetchJT('a_ugroup');
       if (!$r || $r == 0) {
         $tobj = new UGroup($t);
         if ($tobj->fetchFromId()) {
           $ret['rc'] = 1;
           $ret['msg'] = 'Cannot find User Group provided inside the database';
           goto screen;
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
            goto screen;

          } else {
            $ret['rc'] = 1;
            $ret['msg'] = 'Specified group already assigned to this user';
            goto screen;
          }
       } else {
       $ret['rc'] = 42;
       $ret['msg'] = 'Not yet impl';
       goto screen;

       }
     } else {
       $ret['rc'] = 1;
       $ret['msg'] = 'Unrecognized target class';
       goto screen;
     }
   break;
   case 'check':
     if (!$lm->o_login->cRight('CHK', R_EDIT)) {
       $ret['rc'] = 1;
       $ret['msg'] = 'You don\'t have the rights to edit check';
       goto screen;
     }
     $obj = new Check($i);
     if ($obj->fetchFromId()) {
       $ret['rc'] = 1;
       $ret['msg'] = 'Cannot find Check provided inside the database';
       goto screen;
     }
     if (!strcmp($o, 'sgroup')) {
       $obj->fetchJT('a_sgroup');
       if (!$r || $r == 0) {
         $tobj = new SGroup($t);
         if ($tobj->fetchFromId()) {
           $ret['rc'] = 1;
           $ret['msg'] = 'Cannot find Server Group provided inside the database';
           goto screen;
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
            goto screen;

          } else {
            $ret['rc'] = 1;
            $ret['msg'] = 'Specified group already assigned to this check';
            goto screen;
          }
       } else {
         $ret['rc'] = 42;
         $ret['msg'] = 'Not yet impl';
         goto screen;
       }
     } else if (!strcmp($o, 'esgroup')) {
       $obj->fetchJT('a_sgroup');
       if (!$r || $r == 0) {
         $tobj = new SGroup($t);
         if ($tobj->fetchFromId()) {
           $ret['rc'] = 1;
           $ret['msg'] = 'Cannot find Server Group provided inside the database';
           goto screen;
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
            goto screen;

          } else {
            $ret['rc'] = 1;
            $ret['msg'] = 'Specified group already assigned to this check';
            goto screen;
          }
       } else {
         $ret['rc'] = 42;
         $ret['msg'] = 'Not yet impl';
         goto screen;
       }
     } else {
       $ret['rc'] = 1;
       $ret['msg'] = 'Unrecognized target class';
       goto screen;
     }
   break;
   case 'ugroup':
     if (!$lm->o_login->cRight('UGRP', R_EDIT)) {
       $ret['rc'] = 1;
       $ret['msg'] = 'You don\'t have the rights to edit user group';
       goto screen;
     }
     $obj = new UGroup($i);
     if ($obj->fetchFromId()) {
       $ret['rc'] = 1;
       $ret['msg'] = 'Cannot find User Group provided inside the database';
       goto screen;
     }
     if (!strcmp($o, 'login')) {
       $obj->fetchJT('a_login');
       if (!$r || $r == 0) {
         $tobj = new Login($t);
	 if ($tobj->fetchFromId()) {
           $ret['rc'] = 1;
           $ret['msg'] = 'Cannot find Login provided inside the database';
           goto screen;
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
            goto screen;

          } else {
	    $ret['rc'] = 1;
            $ret['msg'] = 'Specified login is already in this group';
            goto screen;
	  }
       } else {
       $ret['rc'] = 42;
       $ret['msg'] = 'Not yet impl';
       goto screen;

       }
     } else {
       $ret['rc'] = 1;
       $ret['msg'] = 'Unrecognized target class';
       goto screen;
     }
   break;
   case 'sgroup':
     if (!$lm->o_login->cRight('SRVGRP', R_EDIT)) {
       $ret['rc'] = 1;
       $ret['msg'] = 'You don\'t have the rights to edit server group';
       goto screen;
     }
     $obj = new SGroup($i);
     if ($obj->fetchFromId()) {
       $ret['rc'] = 1;
       $ret['msg'] = 'Cannot find Server Group provided inside the database';
       goto screen;
     }
     if (!strcmp($o, 'server')) {
       $obj->fetchJT('a_server');
       if (!$r || $r == 0) {
         $tobj = new Server($t);
         if ($tobj->fetchFromId()) {
           $ret['rc'] = 1;
           $ret['msg'] = 'Cannot find Server provided inside the database';
           goto screen;
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
            goto screen;

          } else {
            $ret['rc'] = 1;
            $ret['msg'] = 'Specified server is already in this group';
            goto screen;
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
           goto screen;
         } 
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
         $ret['rc'] = 0;
         $ret['res'] = json_encode($res);
         $ret['msg'] = $nradd." Have been added to $obj group.";
         goto screen;
       }
     } else {
       $ret['rc'] = 1;
       $ret['msg'] = 'Unrecognized target class';
       goto screen;
     }
   break;
   default:
     $ret['rc'] = 1;
     $ret['msg'] = 'Unkown class provided';
     goto screen;
   break;
 }

screen:
 echo json_encode($ret);

?>
