 <?php
/**
 * Mysql management objects
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2012, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @subpackage mysql
 * @category classes
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */
class mRL
{

  public $ar = '';
    public $oc = '';
    public $fks = array();

    public function __construct($ar, $oc, $fk)
    {
        $this->ar = $ar;
        $this->oc = $oc;
        $this->fk = $fk;
    }

    public function __toString()
    {
        return $this->ar.'/'.$this->oc;
    }
}

?>
