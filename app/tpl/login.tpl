	<div class="page-header">
          <h1>Login</h1>
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
	<form method="POST" action="/login" role="form" class="form-horizontal">
	  <div class="form-group">
	    <label for="inputLogin" class="col-sm-2 col-sm-offset-3 control-label">Username</label>
	    <div class="col-sm-3">
	      <input type="text" class="form-control" id="inputUsername" name="username" placeholder="Username">
	    </div>
	  </div>
	  <div class="form-group">
	    <label for="inputPassword" class="col-sm-2 col-sm-offset-3 control-label">Password</label>
	    <div class="col-sm-3">
	      <input type="password" class="form-control" id="inputPassword" name="password" placeholder="Password">
	    </div>
	  </div>
     <hr class="col-sm-5 col-sm-offset-3">
	  <div class="form-group">
	    <label for="inputOATH" class="col-sm-2 col-sm-offset-3 control-label">Token Value (if any)</label>
	    <div class="col-sm-3">
	      <input type="text" class="form-control" id="inputOTPValue" name="OTPValue" placeholder="00000000">
	    </div>
	  </div>
	
	  <div class="form-group">
	    <div class="col-sm-offset-5 col-sm-2">
	      <div class="checkbox">
		<label>
		  <input type="checkbox" name="remember"> Remember me
		</label>
	      </div>
	    </div>
	  </div>
	  <div class="form-group">
            <div class="col-sm-offset-5 col-sm-5">
	      <button type="submit" name="submit" value="1" class="btn btn-primary">Sign in</button>
	    </div>
	  </div>
	  <div class="form-group">
            <div class="col-sm-offset-5 col-sm-5">
	      <a href="/reset">Forgot your password?</a>
	    </div>
	  </div>
      
	</form>
