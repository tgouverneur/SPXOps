<?php
if (!isset($obj) || !$obj) { $obj = new Server(); }
if (!isset($action) || !$action) { 
  if (isset($page['action'])) {
    $action = $page['action'];
  } else {
    $action = 'Add'; 
  }
}
if (!isset($susers)) $susers = array();
if (!isset($susers)) $edit = false;
if (!isset($pservers)) $pservers = array();
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
	<div class="page-header">
	  <h1><?php echo $action; ?> a Server</h1>
	</div>
        <form method="POST" role="form" action="/<?php echo strtolower($action); ?>/w/server<?php if ($edit) echo "/i/".$obj->id; ?>" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputHostname">Hostname</label>
            <div class="col-sm-3">
              <input class="form-control" type="text" <?php if ($edit) echo "disabled"; ?> name="hostname" value="<?php echo $obj->hostname; ?>" id="inputHostname" placeholder="Server Hostname">
            </div>
	  </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputDescription">Description</label>
            <div class="col-sm-3">
              <input class="form-control" type="text" name="description" value="<?php echo $obj->description; ?>" id="inputDescription" placeholder="Server Description">
            </div>
	  </div>
	  <div class="form-group">
	    <label class="col-sm-2 col-sm-offset-3 control-label" for="selectPhysical">Physical</label>
	    <div class="col-sm-3">
	      <select class="form-control" name="fk_pserver" id="selectPhysical">
		<option value="-1">Same as hostname</option>
<?php foreach($pservers as $pserver) { ?>
                <option value="<?php echo $pserver->id; ?>" <?php if ($obj->fk_pserver == $pserver->id) echo "selected"; ?>><?php echo $pserver; ?></option>
<?php } ?>
	      </select>
	    </div>
	  </div>
	  <div class="form-group">
	    <label class="col-sm-2 col-sm-offset-3 control-label" for="inputSSHUser">SSH User</label>
	    <div class="col-sm-3">
	      <select class="form-control" name="fk_suser" id="inputSSHUser">
<?php foreach($susers as $suser) { ?>
		<option value="<?php echo $suser->id; ?>" <?php if ($obj->fk_suser == $suser->id) echo "selected"; ?>><?php echo $suser; ?></option>
<?php } ?>
	      </select>
	    </div>
	  </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputPasswordConfirm">Options</label>
            <div class="col-sm-3">
              <div class="checkbox">
		<label>
                  <input name="f_rce" type="checkbox" <?php if ($obj->f_rce) { echo "checked"; } ?>>
		  <a href="#" rel="tooltip" title="Allow Remote Code Execution from the portal">RCE</a>
                </label>
	      </div>
              <div class="checkbox">
	       <label>
                <input name="f_upd" type="checkbox" <?php if ($obj->f_upd) { echo "checked"; } ?>>
		<a href="#" rel="tooltip" title="Enable automatic updates of this Server">Update</a>
               </label>
              </div>
            </div>
          </div>
	  <div class="form-group">
	    <div class="col-sm-offset-5 col-sm-5">
	      <button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo $action; ?></button>
	    </div>
	  </div>
	</form>
