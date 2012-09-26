<?php


class CDPPacket {

  public static $IS_ROUTER = 1;
  public static $IS_TRBRIDGE = 2;
  public static $IS_SRBRIDGE = 4;
  public static $IS_SWITCH = 8;
  public static $IS_HOST = 16;
  public static $IS_IGMP = 32;
  public static $IS_REPEAT = 64;

  public $raw = '';
  public $type  = '';

  private $_hex = array();
  private $_size = 0;

  public $dstMAC = null;
  public $srcMAC = null;
  public $pktLen = null;
  public $pktLLC = null;
  public $cdpVer = null;
  public $cdpTTL = null;
  public $cdpCksum = null;
  public $ent = array();
  public $rent = array();

  public function treat() {

    if ($this->_size <= 32) {
      return;
    }
    $pos = 0;

    // 6 bytes dst mac
    $this->dstMAC = $this->stringBytes($pos, 6);
    $pos += 6;

    // 6 bytes src mac
    $this->srcMAC = $this->stringBytes($pos, 6);
    $pos += 6;

    // Packet length (2 bytes)
    $this->pktLen = $this->decBytes($pos, 2);
    $pos += 2;

    // LLC header (8 bytes)
    $this->pktLLC = $this->stringBytes($pos, 8);
    $pos += 8;

    // CDP Ver (1 byte)
    $this->cdpVer = $this->decBytes($pos, 1);
    $pos += 1;
    
    // CDP TTL (1 byte)
    $this->cdpTTL = $this->decBytes($pos, 1);
    $pos += 1;

    // CDP checksum (2 byte)
    $this->cdpCksum = $this->stringBytes($pos, 2);
    $pos += 2;

    // CDP Entries, parse them until the end...
    while($pos < $this->_size) {
      // Type of entry (2bytes)
      $type = $this->decBytes($pos, 2);
      $pos += 2;

      // Length of entry (2bytes)
      $len = $this->decBytes($pos, 2);
      $pos += 2;

      // value, $len bytes
      $value = $this->hexBytes($pos, $len-4);
      $pos += $len - 4;

      $this->rent[$type] = array('type' => $type, 'len' => $len, 'value' => $value);
    }

    /* parse CDP entries */
    foreach($this->rent as $ent) {
      switch($ent['type']) {
	case 1: /* Device ID */
	  $this->ent['deviceid'] = $this->stringBytes(0, $ent['len'] - 4, false, $ent['value']);
	break;
	case 2: /* Addresses */
	  /**
	   * @TODO: Implement address parsing
	   */
	break;
	case 3: /* Port ID */
	  $this->ent['port'] = $this->stringBytes(0, $ent['len'] - 4, false, $ent['value']);
	break;
	case 4: /* Capabilities */
          $this->ent['cap'] = $this->decBytes(0, $ent['len'] - 4, $ent['value']);
	break;
	case 5: /* Software Version */
	  $this->ent['sfversion'] = $this->stringBytes(0, $ent['len'] - 4, false, $ent['value']);
	break;
	case 6: /* Platform */
	  $this->ent['platform'] = $this->stringBytes(0, $ent['len'] - 4, false, $ent['value']);
	break;
	case 10: /* Native VLAN */
	  $this->ent['vlan'] = $this->decBytes(0, $ent['len'] - 4, $ent['value']);
	break;
	case 18: /* Trust Bitmap */
	break;
	case 19: /* Untrusted port CoS */
	break;
	case 11: /* Duplex */
	break;
	case 17: /* MTU */
          $this->ent['mtu'] = $this->decBytes(0, $ent['len'] - 4, $ent['value']);
	break;
	case 20: /* System Name */
          $this->ent['name'] = $this->stringBytes(0, $ent['len'] - 4, false, $ent['value']);
	break;
	case 21: /* OID */
          $this->ent['oid'] = $this->stringBytes(0, $ent['len'] - 4, true, $ent['value']);
	break;
	case 22: /* Management Address */
	break;
	case 23: /* Location */
          $this->ent['location'] = $this->stringBytes(0, $ent['len'] - 4, false, $ent['value']);
	break;
	default:
	break;
      }
    }
    return;
  }

  private function decBytes($start, $count, $a = null) {

    if (!$a) $a = $this->_hex;
    $str = '';
    for($i=0; $i<$count; $i++) {
     $str .= $a[$i+$start];
    }
    return hexdec($str);
  }

  private function hexBytes($start, $count, $a = null) {

    if (!$a) $a = $this->_hex;
    $ret = array();
    for($i=0; $i<$count; $i++) {
       $ret[$i] = $a[$i+$start];
    }
    return $ret;
  }

  private function stringBytes($start, $count, $f_hex = true, $a = null) {

    if (!$a) $a = $this->_hex;
    $str = '';
    for($i=0; $i<$count; $i++) {
     if ($f_hex) {
       $str .= $a[$i+$start];
     } else {
       $str .= hex2bin($a[$i+$start]);
     }
    }
    return $str;
  }

  public function parseSnoop() {

    $lines = explode(PHP_EOL, $this->raw);

    $hdump = false;
    $this->_hex = array();
    $this->_size = 0;
    

    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) {
	if ($hdump) {
          $hdump = false;
	  break; // no need to go further
        }
	continue;
      }

      if (!$hdump && preg_match('/^0:/', $line)) {
        $hdump = true;
      }

      if ($hdump && preg_match('/^[0-9]*:/', $line)) {
        $f = preg_split('/\s/', $line);
        $bump = false;
 	$ecount = 0;
        foreach($f as $b) {
	  $b = trim($b);
          if (empty($b)) {
	    $ecount++;
	  } else { 
	    $ecount = 0;
	  }
	  if ($ecount >= 3) break; // EOL
	  if (!$bump && preg_match('/^[0-9]*:$/', $b)) {
	    $bump = true;
	  }
	  if ($bump && preg_match('/^[0-9A-F]{2,4}$/i', $b)) {
	    $nb = strlen($b);
	    $i=0;
	    while($nb > 0) {
	      $byte = substr($b, $i, 2);
	      $this->_hex[$this->_size++] = $byte;
	      $nb -= 2;
	      $i += 2;
	    }
	  }

	}
      }
    }
  }

  public function parseTcpdump() {

    $lines = explode(PHP_EOL, $this->raw);
    $hdump = false;
    $this->_hex = array();
    $this->_size = 0;

    foreach($lines as $line) {
      $line = trim($line);
      if (empty($line)) {
	continue;
      }

      if (!$hdump && preg_match('/^0x/', $line)) {
        $hdump = true;
      }

      if ($hdump && preg_match('/^0x/', $line)) {
        $f = preg_split('/\s/', $line);
        $bump = false;
        foreach($f as $b) {
	  $b = trim($b);
          if (empty($b)) {
	    continue;
	  }
	  if (!$bump && preg_match('/^0x[0-9a-f]*:$/', $b)) {
	    $bump = true;
	    continue;
	  }
	  if ($bump && preg_match('/^[0-9A-F]{2,4}$/i', $b)) {
	    $nb = strlen($b);
	    $i=0;
	    while($nb > 0) {
	      $byte = substr($b, $i, 2);
	      $this->_hex[$this->_size++] = $byte;
	      $nb -= 2;
	      $i += 2;
	    }
	  }
	}
      }
    }
  }

  public function htmlDump() {

    $ar =  array(
	'Source MAC' => $this->srcMAC,
	'Dest MAC' => $this->dstMAC,
	'Packet Length' => $this->pktLen,
	'LLC' => $this->pktLLC,
	'CDP Version' => $this->cdpVer,
	'CDP TTL' => $this->cdpTTL,
	'CDP Checksum' => $this->cdpCksum,
    );
    foreach($this->ent as $k => $v) {
      $ar['Router '.$k] = $v;
    }
    return $ar;
  }


  public function dump() {
    echo "DST MAC: ".$this->dstMAC."\n";
    echo "SRC MAC: ".$this->srcMAC."\n";
    echo "PKT LEN: ".$this->pktLen."\n";
    echo "LLC    : ".$this->pktLLC."\n";
    echo "CDP VER: ".$this->cdpVer."\n";
    echo "CDP TTL: ".$this->cdpTTL." seconds\n";
    echo "CDP SUM: 0x".$this->cdpCksum."\n";
    foreach($this->ent as $k => $v) {
      echo "Router $k: $v\n";
    }
  }

  public function __construct($type = null, $packet = null) {

    if ($type && $packet) {
      $this->type = $type;
      $this->raw = $packet;

      switch ($type) {
	case 'snoop':
          $this->parseSnoop();
	break;
	case 'tcpdump':
          $this->parseTcpdump();
	break;
        default:
	  throw (new SPXException('Unkown CDP Packet type'));
	break;
      }

    }
  }

}

?>
