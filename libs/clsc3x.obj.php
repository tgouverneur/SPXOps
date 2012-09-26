<?php

@include_once($config['rootpath'].'/libs/functions.lib.php');

class CLsc3x extends CLType
{
  public static $binPaths = array(
    "/usr/cluster/bin",
  );

  protected static $_update = array(
    "update_release",
    "update_rgs",
    "update_rs",
  );

  /* Updates function for Sun Cluster 3.x  */

/*
for i in `./scha_cluster_get -O ALL_RESOURCEGROUPS`; do nodelist=`./scha_resourcegroup_get -O NODELIST -G $i`;for n in $nodelist; do echo $n,$i; ./scha_resourcegroup_get -O RG_STATE_NODE -G $i $n; done;done
*/
  /**
   * Update RGs
   */
  public static function update_rgs(&$c) {
    
    $clget = $c->findBin('scha_cluster_get');
    $rgget = $c->findBin('scha_resourcegroup_get');
    $cmd_clget = "$clget -O ALL_RESOURCEGROUPS";

    $cmd_rg_suspend = "$rgget -O SUSPEND_AUTOMATIC_RECOVERY -G %s";
    $cmd_rg_state = "$rgget -O RG_STATE -G %s";
    $cmd_rg_nodes = "$rgget -O NODELIST -G %s";
    $cmd_rg_state_node = "$rgget -O RG_STATE_NODE -G %s %s";
    $cmd_rg_affinities = "$rgget -O RG_AFFINITIES -G %s";
    $cmd_rg_dependencies = "$rgget -O RG_DEPENDENCIES -G %s";
    $cmd_rg_description = "$rgget -O RG_DESCRIPTION -G %s";

    $out_clget = $c->exec($cmd_clget);
    $lines = explode(PHP_EOL, $out_clget);
     
    $found_rgs = array();
    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line))
	continue;
 
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

      $cmd = sprintf($cmd_rg_state, $name);
      $state = $c->exec($cmd);
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
      $found_rgs[$rg] = $rg;
    }

    foreach($c->a_clrg as $r) {
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
  public static function update_rs(&$c) {
    $rgget = $c->findBin('scha_resourcegroup_get');
    $rsget = $c->findBin('scha_resource_get');
    $cmd_rs = "$rgget -O RESOURCE_LIST -G %s";

    $cmd_rs_state = "$rsget -O RESOURCE_STATE -G %s -R %s";
    $cmd_rs_description = "$rsget -O R_DESCRIPTION -G %s -R %s";
    $cmd_rs_project = "$rsget -O RESOURCE_PROJECT_NAME -G %s -R %s";
    $cmd_rs_type = "$rsget -O TYPE -G %s -R %s";
    $cmd_rs_type_version = "$rsget -O TYPE_VERSION -G %s -R %s";

    foreach($c->a_clrg as $rg) {
      $cmd = sprintf($cmd_rs, $rg->name);
      $out = $c->exec($cmd);
      $lines = explode(PHP_EOL, $out);
      $found_rs = array();

      foreach($lines as $line) {
        $line = trim($line);
        if (empty($line))
          continue;

        $name = $line;
	$rs = new CLRs();
	$rs->name = $name;
	$rs->fk_clrg = $rg->id;
        if ($rs->fetchFromFields(array('fk_clrg', 'name'))) {
          $rs->insert();
          $rg->a_clrs[] = $rg;
          $c->log("Found resource $rs inside $rg", LLOG_INFO);
        }

        $cmd = sprintf($cmd_rs_state, $rg->name, $name);
        $state = $c->exec($cmd);
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
      foreach($rg->a_clrs as $r) {
        $r->fetchAll();
        if (isset($found_rs[''.$r])) {
          continue;
        }
        $c->log("Removing Resource $r", LLOG_INFO);
        $n->delete();
      }
    }
  }

  /**
    /etc/cluster/release
   */
  public static function update_release(&$c) {

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
    }
    if ($c->data('cl:version') != $clrelease) {
      $c->setData('cl:version', $clrelease);
      $c->log('cl:version => '.$clrelease, LLOG_INFO);
    }
    return 0;
  }






  /* Screening */
  public static function htmlDump($c) {
    return array(
	    "Version" => $c->data('cl:version'),
	   );
  }

  public static function dump($c) {

  }


}

?>
