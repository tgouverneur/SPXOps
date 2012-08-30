      <div class="row">
        <div class="span5 offset3">
          <h2>Login</h2>
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
	<form method="POST" action="/login" class="form-horizontal">
	  <div class="control-group">
	    <label class="control-label" for="inputLogin">Username</label>
	    <div class="controls">
	      <input type="text" id="inputUsername" name="username" placeholder="Username">
	    </div>
	  </div>
	  <div class="control-group">
	    <label class="control-label" for="inputPassword">Password</label>
	    <div class="controls">
	      <input type="password" id="inputPassword" name="password" placeholder="Password">
	    </div>
	  </div>
	  <div class="control-group">
	    <div class="controls">
	      <label class="checkbox">
		<input type="checkbox" name="remember"> Remember me
	      </label>
	      <button type="submit" name="submit" value="1" class="btn">Sign in</button>
	    </div>
	  </div>
	</form>
        </div>
      </div>
