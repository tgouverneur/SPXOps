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
 */
class mJT
{
   public $ar = '';
    public $oc = '';
    public $jt = '';
    public $src = array();
    public $dst = array();
    public $attrs = array();
    public function __construct($ar, $oc, $jt, $src, $dst, $attrs = array())
    {
        $this->ar = $ar;
        $this->oc = $oc;
        $this->jt = $jt;
        $this->src = $src;
        $this->dst = $dst;
        $this->attrs = $attrs;
    }
}

?>
