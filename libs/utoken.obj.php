<?php
/**
 * UToken object
 *
 * @author Gouverneur Thomas <tgo@espix.net>
 * @copyright Copyright (c) 2007-2015, Gouverneur Thomas
 * @version 1.0
 * @package objects
 * @category classes
 * @subpackage backend
 * @filesource
 * @license https://raw.githubusercontent.com/tgouverneur/SPXOps/master/LICENSE.md Revised BSD License
 */

class UToken extends MySqlObj
{
    public $id = -1;
    public $counter = null; /* Counter for HOTP, Window for TOTP */
    public $secret = '';
    public $digit = 6;
    public $type = -1;
    public $f_init = 0;
    public $f_locked = 0;
    public $t_add = -1;
    public $t_upd = -1;

    public $a_types = null;

    public function checkValue($v) {
        $secret = hex2bin($this->secret);

        if ($this->type == 1) { /* HOTP */
            return $this->checkValueHOTP($v, $secret);
        } else if ($this->type == 2) { /* TOTP */
            return $this->checkValueTOTP($v, $secret);
        }
    }

    public function getURL() {
        $url = 'otpauth://';
        $label = 'SPXOps:'.LoginCM::getInstance()->o_login->username;
        if ($this->type == 1) {
            $url .= 'hotp/'.$label.'?secret='.Base32::encode(hex2bin($this->secret)).'&digits='.$this->digit;
            $url .= '&counter='.$this->counter;
        } else if ($this->type == 2) {
            $url .= 'totp/'.$label.'?secret='.Base32::encode(hex2bin($this->secret)).'&digits='.$this->digit;
            $url .= '&period='.$this->counter;
        } else {
            throw new SPXException('Token Type invalid');
        }
        return $url;
    }

    public function checkValueTOTP($v, $secret) {
        $maxSkew = Setting::get('user', 'hotpMaxSkew');
        if (!$maxSkew) {
            $maxSkew = 1;
        } else {
            $maxSkew = $maxSkew->value;
        }
        $hotp = HOTP::generateByTime($secret, $this->counter);
        if ($hotp->toHOTP($this->digit) == $v) {
            $this->update(); /* so it updates the timestamp of last use */
            return true;
        }

        $a_hotp = HOTP::generateByTimeWindow($secret, $this->counter, (0 - $maxSkew), $maxSkew);
        foreach($a_hotp as $hotp) {
            if ($hotp->toHOTP($this->digit) == $v) {
                $this->update(); /* so it updates the timestamp of last use */
                return true;
            }
        }
        throw new SPXException('Cannot verify TOTP token value');
        return false;
    }

    public function checkValueHOTP($v, $secret) {

        /* first check the value with $counter */
        $hotp = HOTP::generateByCounter($secret, $this->counter);
        if ($hotp->toHOTP($this->digit) == $v) {
            /* increments the counter first */
            $this->counter++;
            $this->update();
            return true;
        }

        /* Now check the user:hotpMaxSkew counter value above current counter */
        $maxSkew = Setting::get('user', 'hotpMaxSkew');
        if (!$maxSkew) {
            throw new SPXException('Cannot verify HOTP value, skew is disabled');
        }

        for ($i=1; $i <= $maxSkew->value; $i++) {
            $hotp = HOTP::generateByCounter($secret, $this->counter + $i);
            if ($hotp->toHOTP($this->digit) == $v) {
                /* first increment the counter by 1 + skew */
                $this->counter += $i + 1;
                $this->update();
                return true;
            }
        }

        /* Now check the user:maxSkew counters values behind current to
         * make sure we are not in front of a replication attack.
         *
         * If we do, lock that token if policy say so. (user:oathLockReplayAttack)
         *
         * Anyway, record the event in the logs.
         */

        $lockReplay = Setting::get('user', 'oathLockReplayAttack');

        for ($i=1; $i <= $maxSkew->value; $i++) {
            $hotp = HOTP::generateByCounter($secret, $this->counter - $i);
            if ($hotp->toHOTP($this->digit) == $v) {
                /* we have a replay attack here */
                if ($lockReplay && $lockReplay->value) {
                    $this->f_locked = 1;
                    $this->update();
                }
                Act::add('Detected replay attack for this user\'s token!', LoginCM::getInstance()->o_login);
                throw new SPXException('This token has already been used; Replay attack detected!');
            }
        }

        return false;
    }


    public function valid($new = true)
    { 
        /* validate form-based fields */
        $ret = array();

        if (empty($this->secret)) {
            /* generate 20 bytes */
            $this->secret = strtolower(bin2hex(openssl_random_pseudo_bytes(20)));
        } else {
            /* @TODO: check for length and proper hex */
        }

        if ($this->digit != 6 && $this->digit != 8) {
            $this->digit = 6;
            $ret[] = '# of Digit must be either 6 or 8';
        }

        if (!isset($this->a_types[$this->type])) {
            $this->type = '';
            $ret[] = 'Token type should be either TOTP or HOTP';
        }

        if ($this->type == 1 && ($this->counter === null || !is_numeric($this->counter))) { /* check counter only for HOTP */
            $ret[] = 'Invalid Counter';
        }

        if ($this->type == 2) { /* defaults to 30 sec */
            $this->counter = 30;
        }

        if (count($ret)) {
            return $ret;
        } else {
            return;
        }
    }

    public function htmlDump()
    {
        return array(
        'Type' => strtoupper($this->a_types[$this->type]),
        'Init' => ($this->f_init) ? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span>',
        'Locked' => ($this->f_locked) ? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span>',
        'Last Used' => date('d-m-Y', $this->t_upd),
        'Added on' => date('d-m-Y', $this->t_add),
    );
    }

 

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->a_types = array(1 => 'hotp', 2 => 'totp');

      $this->id = $id;
      $this->_table = 'list_utoken';
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'counter' => SQL_PROPE,
                        'secret' => SQL_PROPE,
                        'digit' => SQL_PROPE,
                        'type' => SQL_PROPE,
                        'f_init' => SQL_PROPE,
                        'f_locked' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );

      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'counter' => 'counter',
                        'secret' => 'secret',
                        'digit' => 'digit',
                        'type' => 'type',
                        'f_init' => 'f_init',
                        'f_locked' => 'f_locked',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );
  }
}
