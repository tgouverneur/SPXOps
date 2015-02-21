<?php
/**
 * CLVer object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class CLVer extends MySqlObj
{
  public $id = -1;
    public $name = '';
    public $class = '';
    public $version = '';

    public static $binPaths = array(
    "/usr/cluster/bin",
  );

    public static function detect($c)
    {
        if (!$c) {
            throw new SPXException('No cluster provided');
        }

        try {
            $cat = $c->findBin('cat');
            if ($c->isFile('/etc/cluster/release')) { // Sun Cluster

    $cmd_cat = "$cat /etc/cluster/release";
                $out_cat = $c->exec($cmd_cat);

                $rel_lines = explode("\n", $out_cat);
                $rel_expl = explode(" ", trim($rel_lines[0]));
                $clrelease = $rel_expl[2];
                if ($clrelease == "Cluster") {
                    $clrelease = $rel_expl[3];
                }
                $clrelease = substr($clrelease, 0, 3);
                $oclv = new CLVer();
                $oclv->name = "Sun Cluster";
                $oclv->version = $clrelease;
                if ($oclv->fetchFromFields(array('name', 'version'))) {
                    throw new SPXException('CLVer: Unknown Sun Cluster release: '.$clrelease);
                }
            } else {
                throw new SPXException('Unable to detect Cluster brand');
            }

            return $oclv;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function __toString()
    {
        return $this->name;
    }

    public function dump($s)
    {
    }

    public function htmlDump($s)
    {
        $oclass = $this->class;
        $spec = $oclass::htmlDump($s);

        $myar = array(
        'Name' => $this->name,
            );

        return array_merge($myar, $spec);
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = "list_clver";
      $this->_nfotable = null;
      $this->_my = array(
                        "id" => SQL_INDEX,
                        "name" => SQL_PROPE|SQL_EXIST,
                        "version" => SQL_PROPE,
                        "class" => SQL_PROPE,
                 );

      $this->_myc = array( /* mysql => class */
                        "id" => "id",
                        "name" => "name",
                        "version" => "version",
                        "class" => "class",
                 );
  }
}
