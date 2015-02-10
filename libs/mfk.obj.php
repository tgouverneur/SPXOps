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
 
class mFK {
 
   public $in = '';
   public $on = '';
   public $oc = '';

   public function fetch($pthis, $f_fetch = true) {

     $pthis->{$this->on} = new $this->oc($pthis->{$this->in});

     if ($f_fetch) {
       $rc = $pthis->{$this->on}->fetchFromId();
       if ($rc) {
         throw new SPXException('Cannot fetch '.$this->oc.' object with id '.$pthis->{$this->in});
       }
     }
     return;
   }

   public function __construct($in, $on, $oc) {
     $this->in = $in;
     $this->on = $on;
     $this->oc = $oc;
   }
}

?>
