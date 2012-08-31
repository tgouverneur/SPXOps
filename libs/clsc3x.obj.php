<?php

class CLsc3x extends CLType
{
  public static $binPaths = array(
    "/usr/cluster/bin",
  );

  protected static $_update = array(
    "update_release",
  );

  /* Updates function for Sun Cluster 3.x  */

/*
for i in `./scha_cluster_get -O ALL_RESOURCEGROUPS`; do nodelist=`./scha_resourcegroup_get -O NODELIST -G $i`;for n in $nodelist; do echo $n,$i; ./scha_resourcegroup_get -O RG_STATE_NODE -G $i $n; done;done
*/

  /**
   * /etc/cluster/release
   */
  public static function update_release(&$c) {

    /* get uname -a */
    $cat = $s->findBin('cat');
    $cmd_cat = "$cat /etc/cluster/release";
    $out_cat = $s->exec($cmd_cat);

    $s->setData('cl:version', $clversion);

    return 0;
  }





  /* Screening */
  public static function htmlDump($s) {
    return array(
	   );
  }

  public static function dump($s) {

  }


}

?>
