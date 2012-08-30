<?php
if (!isset($obj) || !$obj) { $obj = new SUser(); }
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
	<h2><?php echo $action; ?> an SSH User</h2>
        <form method="POST" action="/<?php echo strtolower($action); ?>/w/suser" class="form-horizontal">
	  <div class="control-group">
	    <label class="control-label" for="inputUsername">Username</label>
	    <div class="controls">
	      <input type="text" name="username" value="<?php echo $obj->username; ?>" id="inputUsername" placeholder="Username">
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="inputPassword">Password</label>
	    <div class="controls">
	      <input type="text" name="password" value="<?php echo $obj->password; ?>"  id="inputPassword" placeholder="Password">
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="inputDescription">Description</label>
	    <div class="controls">
	      <input type="text" name="description" value="<?php echo $obj->description; ?>" id="inputDescription" placeholder="Description">
	    </div>
	  </div>
          <div class="control-group">
            <label class="control-label" for="inputPubKey">Public Key</label>
            <div class="controls">
              <input type="text" name="pubkey" id="inputPubKey" placeholder="Public Key file path">
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
