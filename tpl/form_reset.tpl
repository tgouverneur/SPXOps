	<div class="page-header">
      <h1>Reset your Password</h1>
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
    <p>NOTE: You can not reset your password more than once per hour.</p>
	<form method="POST" action="/reset/w/ask" role="form" class="form-horizontal">
	  <div class="form-group">
	    <label for="inputLogin" class="col-sm-2 col-sm-offset-3 control-label">Username</label>
	    <div class="col-sm-3">
	      <input type="text" class="form-control" id="inputUsername" name="username" placeholder="Username">
	    </div>
	  </div>
	  <div class="form-group">
            <div class="col-sm-offset-5 col-sm-5">
	      <button type="submit" name="submit" value="1" class="btn btn-primary">Get me the reset link!</button>
	    </div>
	  </div>
	</form>
