<?php
if (!isset($obj) || !$obj) { $obj = new Login(); }
if (!isset($action) || !$action) { 
  if (isset($page['action'])) {
    $action = $page['action'];
  } else {
    $action = 'Add'; 
  }
}
?>
      <div class="row">
        <div class="span8 offset2">
<?php if (isset($error)) { 
        if (!is_array($error)) {
          $error = array($error);
        }
        foreach($error as $e) {
?>
	<div class="alert alert-error">
	  <button type="button" class="close" data-dismiss="alert">×</button>
	  <strong>Error!</strong> <?php echo $e; ?>
	</div>
<?php   }
      }
?>
	<h2><?php echo $action; ?> an User</h2>
        <form method="POST" action="/<?php echo strtolower($action); ?>/w/user<?php if ($edit) echo "/i/".$obj->id; ?>" class="form-horizontal">
          <div class="control-group">
            <label class="control-label" for="inputFullname">Fullname</label>
            <div class="controls">
              <input type="text" name="fullname" value="<?php echo $obj->fullname; ?>" id="inputFullname" placeholder="Full Name">
            </div>
	  </div>
          <div class="control-group">
            <label class="control-label" for="inputEmail">E-Mail</label>
            <div class="controls">
              <input type="text" name="email" value="<?php echo $obj->email; ?>" id="inputEmail" placeholder="user@domain.tld">
            </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="inputUsername">Username</label>
	    <div class="controls">
	      <input type="text" name="username" value="<?php echo $obj->username; ?>" id="inputUsername" placeholder="Username">
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="inputPassword">Password</label>
	    <div class="controls">
	      <input type="password" name="password" id="inputPassword" placeholder="Password">
	    </div>
	  </div>
          <div class="control-group">
            <label class="control-label" for="inputPasswordConfirm">Confirmation</label>
            <div class="controls">
              <input type="password" name="password_c" id="inputPasswordConfirm" placeholder="Confirmation">
            </div>
          </div>
          <div class="control-group">
	    <label class="control-label" for="inputOptions">Options</label>
            <div class="controls">
              <label class="checkbox">
                <input name="f_admin" type="checkbox" <?php if ($obj->f_admin) { echo "checked"; } ?>> Administrator
              </label>
              <label class="checkbox">
                <input name="f_ldap" type="checkbox" <?php if ($obj->f_ldap) { echo "checked"; } ?>> in LDAP
              </label>
            </div>
          </div>
	  <div class="control-group">
	    <div class="controls">
	      <button type="submit" name="submit" value="1" class="btn"><?php echo $action; ?></button>
	    </div>
	  </div>
	</form>
        </div>
      </div>
