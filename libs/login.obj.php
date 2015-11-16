<?php
/**
 * Login object
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
class Login extends MySqlObj
{
    public $id = -1;
    public $username = '';
    public $password = '';
    public $password_c = ''; /* only for form-based validation */
    public $fullname = '';
    public $email = '';
    public $fk_utoken = -1;
    public $f_noalerts = 0;
    public $f_active = 1;
    public $f_admin = 0;
    public $f_api = 0;
    public $t_last = -1;
    public $t_add = -1;
    public $t_upd = -1;

    public $a_ugroup = array();
    public $a_right = array();

    public $o_utoken = null;

    public $i_raddr = '';

    public function setListPref($list, $val)
    {
        return $this->setData('list:'.$list, serialize($val));
    }

    public function getListPref($list)
    {
        if (!empty($this->_datas)) {
            $this->fetchData();
        }
        $val = $this->data('list:'.$list);
        if (!$val) {
            return;
        }

        return unserialize($val);
    }

    public function getAddr()
    {
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $this->i_raddr = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $this->i_raddr = $_SERVER['REMOTE_ADDR'];
        }
    }

    public function link()
    {
        return '<a href="/view/w/login/i/'.$this->id.'">'.$this.'</a>';
    }

    public function equals($z)
    {
        if (!strcmp($this->username, $z->username)) {
            return true;
        }

        return false;
    }

    public function __toString()
    {
        return $this->username;
    }

    public function valid($new = true)
    { /* validate form-based fields */
        $ret = array();

        if (empty($this->username)) {
            $ret[] = 'Missing Username';
        } else {
            if ($new) { /* check for already-exist */
                $check = new Login();
                $check->username = $this->username;
                if (!$check->fetchFromField('username')) {
                    $this->username = '';
                    $ret[] = 'Username already exist';
                    $check = null;
                }
            }
        }

        $lm = LoginCM::getInstance();
        if ($this->f_admin && !$lm->o_login->f_admin) {
            $ret[] = 'You cannot add an admin user as you aren\'t administrator yourself.';
            $this->f_admin = false;
        }

        if (empty($this->email)) {
            $ret[] = 'Missing E-Mail';
        } else {
            if (!HTTP::checkEmail($this->email)) {
                $this->email = '';
                $ret[] = 'Wrong E-mail address';
            }
        }

        if (empty($this->password) && $new) {
            $ret[] = 'Missing Password';
            $this->password = $this->password_c = '';
        }

        if (empty($this->password_c) && (!empty($this->password) && $new)) {
            $ret[] = 'Missing Password confirmation';
            $this->password = $this->password_c = '';
        }

        $minpassword = Setting::get('user', 'minpassword');
        if ($minpassword) {
            $minpassword = $minpassword->value;
        } else {
            $minpassword = 5;
        }

        if (strlen($this->password) < $minpassword && !empty($this->password_c)) {
            $ret[] = 'Password is too short, should be '.$minpassword.' length minimum';
            $this->password = $this->password_c = '';
        }

        if (strcmp($this->password, $this->password_c) && $new && !empty($this->password_c)) {
            $ret[] = 'Password and its confirmation doesn\'t match';
            $this->password = $this->password_c = '';
        }

        if (empty($this->fullname)) {
            $ret[] = 'Missing Full Name';
        }

        if (count($ret)) {
            return $ret;
        } else {
            return;
        }
    }

    public function fetchRights()
    {
        $this->a_right = array();
        $this->fetchJT('a_ugroup');
        foreach ($this->a_ugroup as $ug) {
            $ug->fetchJT('a_right');
            foreach ($ug->a_right as $r) {
                if (!isset($this->a_right[$r->short])) {
                    $this->a_right[$r->short] = $ug->level[''.$r];
                } else {
                    $ra = array(R_VIEW, R_ADD, R_EDIT, R_DEL);
                    foreach ($ra as $rr) {
                        if (!($this->a_right[$r->short] & $rr) &&
             ($ug->level[''.$r])) {
                            $this->a_right[$r->short] |= $rr;
                        }
                    }
                }
            }
        }
    }

    public function cRight($short, $right)
    {
        if (isset($this->a_right[$short]) &&
    $this->a_right[$short] & $right) {
            return true;
        }

        return false;
    }

    public function bcrypt($input, $rounds = 7)
    {
        $salt = "";
        $salt_chars = array_merge(range('A', 'Z'), range('a', 'z'), range(0, 9));
        for ($i = 0; $i < 22; $i++) {
            $salt .= $salt_chars[array_rand($salt_chars)];
        }
        $this->password = crypt($input, sprintf('$2y$%02d$', $rounds).$salt);
    }


    public function auth($pwd)
    {
        if (crypt($pwd, $this->password) === $this->password) {
            return true;
        } else {
            return false;
        }
    }

    public function getAPIKey() {
        $apikey = $this->username;
        $apikey .= $this->password;
        $apikey .= md5(Config::$api_salt);
        return base64_encode(md5($apikey));
    }

    public static function printCols($cfs = array())
    {
        return array('Username' => 'username',
                 'Fullname' => 'fullname',
                 'E-Mail' => 'email',
                 'No Alerting' => 'f_noalerts',
                 'Active' => 'f_active',
                 'Admin' => 'f_admin',
                 'API' => 'f_api',
                 'Added' => 't_add',
                );
    }

    public function toArray($cfs = array())
    {
        return array(
                 'username' => $this->username,
                 'fullname' => $this->fullname,
                 'email' => $this->email,
                 'f_noalerts' => $this->f_noalerts,
                 'f_active' => $this->f_active,
                 'f_admin' => $this->f_admin,
                 'f_api' => $this->f_api,
                 't_add' => date('d-m-Y', $this->t_add),
                );
    }

    public function htmlDump()
    {
        return array(
        'Username' => $this->username,
        'Full Name' => $this->fullname,
        'E-Mail' => $this->email,
        'No Alerts?' => ($this->f_noalerts) ? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span>',
        'Active?' => ($this->f_active) ? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span>',
        'Admin?' => ($this->f_admin) ? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span>',
        'api?' => ($this->f_api) ? '<span class="glyphicon glyphicon-ok-sign" aria-hidden="true"></span>' : '<span class="glyphicon glyphicon-remove-circle" aria-hidden="true"></span>',
        'Last seen' => date('d-m-Y', $this->t_last),
        'Updated on' => date('d-m-Y', $this->t_upd),
        'Added on' => date('d-m-Y', $this->t_add),
    );
    }

    public function delete()
    {
        parent::_delAllJT();
        parent::delete();
    }

  /**
   * ctor
   */
  public function __construct($id = -1)
  {
      $this->id = $id;
      $this->_table = 'list_login';
      $this->_nfotable = 'nfo_login';
      $this->_my = array(
                        'id' => SQL_INDEX,
                        'username' => SQL_PROPE|SQL_EXIST,
                        'password' => SQL_PROPE,
                        'fullname' => SQL_PROPE,
                        'email' => SQL_PROPE,
                        'fk_utoken' => SQL_PROPE,
                        'f_active' => SQL_PROPE,
                        'f_noalerts' => SQL_PROPE,
                        'f_admin' => SQL_PROPE,
                        'f_api' => SQL_PROPE,
                        't_last' => SQL_PROPE,
                        't_add' => SQL_PROPE,
                        't_upd' => SQL_PROPE,
                 );

      $this->_myc = array( /* mysql => class */
                        'id' => 'id',
                        'username' => 'username',
                        'password' => 'password',
                        'fullname' => 'fullname',
                        'email' => 'email',
                        'fk_utoken' => 'fk_utoken',
                        'f_noalerts' => 'f_noalerts',
                        'f_active' => 'f_active',
                        'f_admin' => 'f_admin',
                        'f_api' => 'f_api',
                        't_last' => 't_last',
                        't_add' => 't_add',
                        't_upd' => 't_upd',
                 );
                /* array(),  Object, jt table,     source mapping, dest mapping, attribuytes */
    $this->_addJT('a_ugroup', 'UGroup', 'jt_login_ugroup', array('id' => 'fk_login'), array('id' => 'fk_ugroup'), array());
    $this->_addFK("fk_utoken", "o_utoken", "UToken");
  }
}
