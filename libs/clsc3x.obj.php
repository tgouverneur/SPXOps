<?php

@include_once Config::$rootpath.'/libs/functions.lib.php';

class CLsc3x extends CLType
{
  public static $binPaths = array(
    "/usr/cluster/bin",
  );

    protected static $_update = array(
    "updateRelease",
    "updateRgs",
    "updateRs",
  );

  /* Updates function for Sun Cluster 3.x  */

/*
for i in `./scha_cluster_get -O ALL_RESOURCEGROUPS`; do nodelist=`./scha_resourcegroup_get -O NODELIST -G $i`;for n in $nodelist; do echo $n,$i; ./scha_resourcegroup_get -O RG_STATE_NODE -G $i $n; done;done
*/
  /**
   * Update RGs
   */
  public static function updateRgs(&$c)
  {
      $clget = $c->findBin('scha_cluster_get');
      $rgget = $c->findBin('scha_resourcegroup_get');
      $cmd_clget = "$clget -O ALL_RESOURCEGROUPS";

      $cmd_rg_suspend = "$rgget -O SUSPEND_AUTOMATIC_RECOVERY -G %s";
      $cmd_rg_nodes = "$rgget -O NODELIST -G %s";
      $cmd_rg_state_node = "$rgget -O RG_STATE_NODE -G %s %s";
      $cmd_rg_affinities = "$rgget -O RG_AFFINITIES -G %s";
      $cmd_rg_dependencies = "$rgget -O RG_DEPENDENCIES -G %s";
      $cmd_rg_description = "$rgget -O RG_DESCRIPTION -G %s";

      $out_clget = $c->exec($cmd_clget);
      $lines = explode(PHP_EOL, $out_clget);

      $found_rgs = array();
      foreach ($lines as $line) {
          $line = trim($line);
          if (empty($line)) {
              continue;
          }

          $name = $line;
          $rg = new CLRg();
          $rg->name = $name;
          $rg->fk_cluster = $c->id;
          if ($rg->fetchFromFields(array('fk_cluster', 'name'))) {
              $rg->insert();
              $c->a_clrg[] = $rg;
              $c->log("Found new RG: $rg", LLOG_INFO);
          }

          $cmd = sprintf($cmd_rg_suspend, $name);
          $f_suspend = parseBool($c->exec($cmd));
          if ($rg->f_suspend != $f_suspend) {
              $rg->f_suspend = $f_suspend;
              $c->log("$rg::f_suspend => $f_suspend", LLOG_DEBUG);
          }

          $state = 'OFFLINE';
          $cmd = sprintf($cmd_rg_nodes, $name);
          $nodes = $c->exec($cmd);
          $lines = explode(PHP_EOL, $nodes);
          $found_n = array();
          $rg->fetchJT('a_node');
          foreach ($lines as $node) {
              $node = trim($node);
              if (empty($node)) {
                  continue;
              }
              $cmd = sprintf($cmd_rg_state_node, $name, $node);
              $nstate = $c->exec($cmd);
              if (strcmp($nstate, 'ONLINE')) {
                  if (strcmp($state, 'ONLINE') && $state != $nstate) {
                      $state = $nstate;
                  }
                  $c->log("found node $node ($nstate) for $rg", LLOG_DEBUG);
                  continue;
              }
              $state = $nstate;
              $nobj = $zobj = $zname = null;
              $c->log("found node $node for $rg", LLOG_DEBUG);
              if (preg_match('/:/', $node)) {
                  $node = explode(':', $node);
                  $zname = $node[1];
                  $node = $node[0];
                  $c->log("Detected zone $zname for $rg", LLOG_DEBUG);
              }
    /* Node should be found in node list of the cluster */
    foreach ($c->a_server as $s) {
        if (!strcmp($s->hostname, $node)) {
            $nobj = $s;
            break;
        }
    }
              if (!$nobj) {
                  $c->log("Unresolved server $node for $rg", LLOG_WARN);
                  continue;
              }
              if ($zname) {
                  $zobj = new Zone();
                  $zobj->name = $zname;
                  $zobj->fk_server = $nobj->id;
                  if ($zobj->fetchFromFields(array('name', 'fk_server'))) {
                      $c->log("Unresolved zone $node/$zname for $rg", LLOG_WARN);
                      continue;
                  }
                  $nobj->fk_zone[''.$rg] = $zobj->id;
                  $rg->fk_zone[''.$nobj->id] = $zobj->id;
                  if (!$rg->isInJT('a_node', $nobj, array('fk_zone'))) {
                      $c->log("add $nobj/$zobj to $rg", LLOG_INFO);
                      $rg->addToJT('a_node', $nobj);
                  }
                  $found_n[$nobj->id] = $zobj->id;
              } else {
                  $nobj->fk_zone[''.$rg] = -1;
                  $rg->fk_zone[''.$nobj->id] = -1;
                  if (!$rg->isInJT('a_node', $nobj, array('fk_zone'))) {
                      $c->log("add $nobj/-1 to $rg", LLOG_INFO);
                      $rg->addToJT('a_node', $nobj);
                  }
                  $found_n[$nobj->id] = -1;
              }
          }
          foreach ($rg->a_node as $n) {
              if (!isset($found_n[$n->id])) {
                  $c->log("removed $n/-1 from $rg", LLOG_INFO);
                  $rg->delFromJT('a_node', $n);
              } else {
                  if ($found_n[$n->id] > 0) {
                      if ($n->fk_zone[''.$rg] != $found_n[$n->id]) {
                          $c->log("removed $n/".$n->fk_zone[''.$rg]." from $rg", LLOG_INFO);
                          $rg->delFromJT('a_node', $n);
                      }
                  }
              }
          }
          if ($rg->state != $state) {
              $rg->state = $state;
              $c->log("$rg::state => $state", LLOG_DEBUG);
          }

          $cmd = sprintf($cmd_rg_description, $name);
          $description = $c->exec($cmd);
          if ($rg->description != $description) {
              $rg->description = $description;
              $c->log("$rg::description => $description", LLOG_DEBUG);
          }

          $rg->update();
          $found_rgs[''.$rg] = $rg;
      }

      foreach ($c->a_clrg as $r) {
          $r->fetchAll();
          if (isset($found_rgs[''.$r])) {
              continue;
          }
          $c->log("Removing RG $r", LLOG_INFO);
          $n->delete();
      }
  }

  /**
   * Update Resources
   */
  public static function updateRs(&$c)
  {
      $rgget = $c->findBin('scha_resourcegroup_get');
      $rsget = $c->findBin('scha_resource_get');
      $cmd_rs = "$rgget -O RESOURCE_LIST -G %s";

      $cmd_rs_state_node = "$rsget -O RESOURCE_STATE_NODE -G %s -R %s %s";
      $cmd_rs_description = "$rsget -O R_DESCRIPTION -G %s -R %s";
      $cmd_rs_project = "$rsget -O RESOURCE_PROJECT_NAME -G %s -R %s";
      $cmd_rs_type = "$rsget -O TYPE -G %s -R %s";
      $cmd_rs_type_version = "$rsget -O TYPE_VERSION -G %s -R %s";

      foreach ($c->a_clrg as $rg) {
          $rg->fetchJT('a_node');
          $cmd = sprintf($cmd_rs, $rg->name);
          $out = $c->exec($cmd);
          $lines = explode(PHP_EOL, $out);
          $found_rs = array();

          foreach ($lines as $line) {
              $line = trim($line);
              if (empty($line)) {
                  continue;
              }

              $name = $line;
              $rs = new CLRs();
              $rs->name = $name;
              $rs->fk_clrg = $rg->id;
              if ($rs->fetchFromFields(array('fk_clrg', 'name'))) {
                  $rs->insert();
                  $rg->a_clrs[] = $rs;
                  $c->log("Found resource $rs inside $rg", LLOG_INFO);
              }

              $state = 'OFFLINE';
              foreach ($rg->a_node as $n) {
                  $node = ''.$n;
                  if (isset($rg->fk_zone[$n->id]) && $rg->fk_zone[$n->id] > 0) {
                      $z = new Zone($rg->fk_zone[$n->id]);
                      $z->fetchFromId();
                      $node .= ':'.$z;
                  }
                  $cmd = sprintf($cmd_rs_state_node, $rg->name, $name, $node);
                  $nstate = $c->exec($cmd);
                  if (strcmp($nstate, 'ONLINE')) {
                      if (strcmp($state, 'ONLINE') && $state != $nstate) {
                          $state = $nstate;
                      }
                  }
              }
              if ($rs->state != $state) {
                  $rs->state = $state;
                  $c->log("$rs::state => $state", LLOG_DEBUG);
              }

              $cmd = sprintf($cmd_rs_description, $rg->name, $name);
              $description = $c->exec($cmd);
              if ($rs->description != $description) {
                  $rs->description = $description;
                  $c->log("$rs::description => $description", LLOG_DEBUG);
              }

              $cmd = sprintf($cmd_rs_type, $rg->name, $name);
              $type = $c->exec($cmd);
              if ($rs->type != $type) {
                  $rs->type = $type;
                  $c->log("$rs::type => $type", LLOG_DEBUG);
              }

              $cmd = sprintf($cmd_rs_type_version, $rg->name, $name);
              $type_version = $c->exec($cmd);
              if ($rs->type_version != $type_version) {
                  $rs->type_version = $type_version;
                  $c->log("$rs::type_version => $type_version", LLOG_DEBUG);
              }

              $rs->update();
              $found_rs[''.$rs] = $rs;
          }
          foreach ($rg->a_clrs as $r) {
              $r->fetchAll();
              if (isset($found_rs[''.$r])) {
                  continue;
              }
              $c->log("Removing Resource $r", LLOG_INFO);
              $r->delete();
          }
      }
  }

  /**
   /etc/cluster/release
   */
  public static function updateRelease(&$c)
  {

    /* get uname -a */
    $cat = $c->findBin('cat');
      $cmd_cat = "$cat /etc/cluster/release";
      $out_cat = $c->exec($cmd_cat);
      $lines = explode(PHP_EOL, $out_cat);
      $clrelease = '';
      if (count($lines)) {
          $vline = trim($lines[0]);
          $rel_expl = explode(' ', $vline);
          $clrelease = $rel_expl[2];
          if (!strcmp($clrelease, 'Cluster')) {
              $clrelease = $rel_expl[3];
          }
      }
      if ($c->data('cl:version') != $clrelease) {
          $c->setData('cl:version', $clrelease);
          $c->log('cl:version => '.$clrelease, LLOG_INFO);
      }

      return 0;
  }

  /* Screening */
  public static function htmlDump($c)
  {
      return array(
        "Version" => $c->data('cl:version'),
       );
  }

    public static function dump($c)
    {
    }
}
