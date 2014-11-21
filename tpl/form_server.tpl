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
	<h2><?php echo $action; ?> a Server</h2>
        <form method="POST" action="/<?php echo strtolower($action); ?>/w/server<?php if ($edit) echo "/i/".$obj->id; ?>" class="form-horizontal">
          <div class="control-group">
            <label class="control-label" for="inputHostname">Hostname</label>
            <div class="controls">
              <input type="text" <?php if ($edit) echo "disabled"; ?> name="hostname" value="<?php echo $obj->hostname; ?>" id="inputHostname" placeholder="Server Hostname">
            </div>
	  </div>
          <div class="control-group">
            <label class="control-label" for="inputDescription">Description</label>
            <div class="controls">
              <input type="text" name="description" value="<?php echo $obj->description; ?>" id="inputDescription" placeholder="Server Description">
            </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="selectPhysical">Physical</label>
	    <div class="controls">
	      <select name="fk_pserver" id="selectPhysical">
		<option value="-1">Same as hostname</option>
<?php foreach($pservers as $pserver) { ?>
                <option value="<?php echo $pserver->id; ?>" <?php if ($obj->fk_pserver == $pserver->id) echo "selected"; ?>><?php echo $pserver; ?></option>
<?php } ?>
	      </select>
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="inputSSHUser">SSH User</label>
	    <div class="controls">
	      <select name="fk_suser" id="inputSSHUser">
<?php foreach($susers as $suser) { ?>
		<option value="<?php echo $suser->id; ?>" <?php if ($obj->fk_suser == $suser->id) echo "selected"; ?>><?php echo $suser; ?></option>
<?php } ?>
	      </select>
	    </div>
	  </div>
          <div class="control-group">
            <label class="control-label" for="inputPasswordConfirm">Options</label>
            <div class="controls">
              <label class="checkbox">
                <input name="f_rce" type="checkbox" <?php if ($obj->f_rce) { echo "checked"; } ?>>
		<a href="#" rel="tooltip" title="Allow Remote Code Execution from the portal">RCE</a>
              </label>
              <label class="checkbox">
                <input name="f_upd" type="checkbox" <?php if ($obj->f_upd) { echo "checked"; } ?>>
		<a href="#" rel="tooltip" title="Enable automatic updates of this Server">Update</a>
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
