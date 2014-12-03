<?php
if (!isset($obj) || !$obj) { $obj = new Server(); }
if (!isset($action) || !$action) { 
  if (isset($page['action'])) {
    $action = $page['action'];
  } else {
    $action = 'Add'; 
  }
}
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
	  <h1><?php echo $action; ?> a Physical Server</h1>
	</div>
        <form method="POST" action="/<?php echo strtolower($action); ?>/w/pserver" class="form-horizontal">
          <div class="form-group">
            <label class="col-sm-2 col-sm-offset-3 control-label" for="inputName">Name</label>
            <div class="col-sm-3">
              <input class="form-control" type="text" name="name" value="<?php echo $obj->name; ?>" id="inputName" placeholder="Physical Name">
            </div>
          </div>
	  <div class="form-group">
	    <div class="col-sm-5 col-sm-offset-5">
	      <button type="submit" name="submit" value="1" class="btn btn-primary"><?php echo $action; ?></button>
	    </div>
	  </div>
	</form>
