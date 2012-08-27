            <h4>That's some bad hat Harry</h1>
<?php if (isset($error)) { ?>
            <p><?php echo $error; ?></p>
<?php } else { ?>
            <p>It looks as if you've either clicked on a bad link or that the page you requested doesn't exist.</p>
<?php } ?>
  	    <p>
  	      <a class="btn btn-primary btn-large" href="/report">Report</a>
	    </p>
