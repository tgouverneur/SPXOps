	<div class="page-header">
      <h1>Reset your password (step 2)</h1>
    </div>
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
	<form method="POST" action="/reset/w/final/i/<?php echo $obj->id; ?>/c/<?php echo $obj->getResetCode(); ?>" role="form" class="form-horizontal">
	  <div class="form-group">
	    <label for="inputPassword" class="col-sm-2 col-sm-offset-3 control-label">Password</label>
	    <div class="col-sm-3">
	      <input type="password" class="form-control" id="inputPassword" name="password" placeholder="Password">
	    </div>
	  </div>
	  <div class="form-group">
	    <label for="inputPassword" class="col-sm-2 col-sm-offset-3 control-label">Confirmation</label>
	    <div class="col-sm-3">
	      <input type="password" class="form-control" id="inputPassword" name="password2" placeholder="Password">
	    </div>
	  </div>
	  <div class="form-group">
            <div class="col-sm-offset-5 col-sm-5">
	      <button type="submit" name="submit" value="1" class="btn btn-primary">Proceed</button>
	    </div>
	  </div>
	</form>
