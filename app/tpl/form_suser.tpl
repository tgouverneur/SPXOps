<?php
if (!isset($obj) || !$obj) { $obj = new SUser(); }
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
	<div class="page-header"><h1><?php echo $action; ?> an SSH User</h1></div>
        <form method="POST" action="/<?php echo strtolower($action); ?>/w/suser<?php if ($edit) echo "/i/".$obj->id; ?>" class="form-horizontal">
	  <div class="form-group">
	    <label class="col-sm-2 col-sm-offset-3 control-label" for="inputUsername">Username</label>
	    <div class="col-sm-3">
	      <input class="form-control" type="text" name="username" value="<?php echo $obj->username; ?>" id="inputUsername" placeholder="Username">
	    </div>
	  </div>
	  <div class="form-group">
	    <label class="col-sm-2 col-sm-offset-3 control-label" for="inputPassword">Password</label>
	    <div class="col-sm-3">
	      <input class="form-control" type="text" name="password" value="<?php echo $obj->password; ?>"  id="inputPassword" placeholder="Password">
	    </div>
	  </div>
	  <div class="form-group">
	    <label class="col-sm-2 col-sm-offset-3 control-label" for="inputDescription">Description</label>
	    <div class="col-sm-3">
	      <input class="form-control" type="text" name="description" value="<?php echo $obj->description; ?>" id="inputDescription" placeholder="Description">
	    </div>
	  </div>
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputPubKey">Public Key</label>
            <div class="col-sm-3">
              <input class="form-control" type="text" name="pubkey" value="<?php echo $obj->pubkey; ?>" id="inputPubKey" placeholder="Public Key file path">
            </div>
          </div>
	  <div class="form-group">
	    <div class="col-sm-offset-5 col-sm-3">
	      <button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo $action; ?></button>
	    </div>
	  </div>
	</form>
