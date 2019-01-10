<?php
if (!isset($obj) || !$obj) { $obj = new Login(); }
if (!isset($action) || !$action) { 
  if (isset($page['action'])) {
    $action = $page['action'];
  } else {
    $action = 'Add'; 
  }
}
if (!isset($edit)) $edit = false;
?>
<?php if (isset($error)) { 
        if (!is_array($error)) {
          $error = array($error);
        }
        foreach($error as $e) {
?>
        <div class="alert alert-danger alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <strong>Error!</strong> <?php echo $e; ?>
        </div>
<?php   }
      }
?>
	<div class="page-header"><h1><?php echo $action; ?> an User</h1></div>
        <form method="POST" action="/<?php echo strtolower($action); ?>/w/login<?php if ($edit) echo "/i/".$obj->id; ?>" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputFullname">Fullname</label>
            <div class="col-sm-3">
              <input class="form-control" type="text" name="fullname" value="<?php echo $obj->fullname; ?>" id="inputFullname" placeholder="Full Name">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputEmail">E-Mail</label>
            <div class="col-sm-3">
              <input class="form-control" type="text" name="email" value="<?php echo $obj->email; ?>" id="inputEmail" placeholder="user@domain.tld">
            </div>
	  </div>
	  <div class="form-group">
	    <label class="col-sm-2 col-sm-offset-3 control-label" for="inputUsername">Username</label>
	    <div class="col-sm-3">
	      <input class="form-control" type="text" name="username" value="<?php echo $obj->username; ?>" id="inputUsername" placeholder="Username">
	    </div>
	  </div>
	  <div class="form-group">
	    <label class="col-sm-2 col-sm-offset-3 control-label" for="inputPassword">Password</label>
	    <div class="col-sm-3">
	      <input class="form-control" type="password" name="password" id="inputPassword" placeholder="Password">
	    </div>
	  </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputPasswordConfirm">Confirmation</label>
            <div class="col-sm-3">
              <input class="form-control" type="password" name="password_c" id="inputPasswordConfirm" placeholder="Confirmation">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputPhone">Phone</label>
            <div class="col-sm-3">
              <input class="form-control" type="text" name="phone" value="<?php echo $obj->phone; ?>" id="inputFullname" placeholder="+9123456789">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputOptions">Options</label>
            <div class="col-sm-3">
              <div class="checkbox">
               <label>
                <input name="f_admin" type="checkbox" <?php if ($obj->f_admin) { echo "checked"; } ?>> Administrator
               </label>
              </div>
              <div class="checkbox">
               <label>
                <input name="f_api" type="checkbox" <?php if ($obj->f_api) { echo "checked"; } ?>> API usage
               </label>
              </div>
              <div class="checkbox">
               <label>
                <input name="f_noalerts" type="checkbox" <?php if ($obj->f_noalerts) { echo "checked"; } ?>> Disable Alerts 
               </label>
              </div>
              <div class="checkbox">
               <label>
                <input name="f_active" type="checkbox" <?php if ($obj->f_active) { echo "checked"; } ?>> Account Active 
               </label>
              </div>
            </div>
          </div>
	  <div class="form-group">
	    <div class="col-sm-offset-5 col-sm-3">
	      <button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo $action; ?></button>
	    </div>
	  </div>
	</form>
