      <div class="row">
        <div class="span12">
          <div class="hero-unit">
            <h1>Welcome to Espix Operations</h1>
            <p>You can manage different flavours of UNIX operating systems using this portal, simply browse through the menu or check the documentation to see how to get more benefit of this portal.</p>
  	    <p>
  	      <a class="btn btn-primary btn-large" href="http://spxops.espix.net/docs">Documentation</a>
	    </p>
          </div>
          <div class="row">
	    <div class="span4">
	      <h2>DB Statistics</h2>
		<dl class="dl-horizontal">
		  <dt>Servers:</dt>
		  <dd><?php echo $stats['nbsrv']; ?> registered</dd>
                  <dt>Server Groups:</dt>
                  <dd><?php echo $stats['nbsgroup']; ?> registered</dd>
		  <dt>Clusters:</dt>
		  <dd><?php echo $stats['nbcl']; ?> registered</dd>
		  <dt>Chassis:</dt>
		  <dd><?php echo $stats['nbpsrv']; ?> registered</dd>
		  <dt>Disks:</dt>
		  <dd><?php echo $stats['nbdisk']; ?> detected</dd>
		  <dt>HW Model:</dt>
		  <dd><?php echo $stats['nbmodel']; ?> detected</dd>
		  <dt>Network SW:</dt>
		  <dd><?php echo $stats['nbswitch']; ?> detected</dd>
		  <dt>Portal Users:</dt>
		  <dd><?php echo $stats['nblogin']; ?> registered</dd>
                  <dt>Portal Groups:</dt>
                  <dd><?php echo $stats['nbugroup']; ?> registered</dd>
		  <dt>SSH Users:</dt>
		  <dd><?php echo $stats['nbsuser']; ?> registered</dd>
		</dl>
	    </div>
	    <div class="span8">
              <h2>User Activities</h2>
              <ul>
<?php foreach($a_act as $act) { ?>
                <li><?php echo $act->html(); ?></li>
<?php } ?>
              </ul>
	      <a class="btn" href="/list/w/act">More..</a>
            </div>

  	  </div>
	  <div class="row">
 	    <div class="span6">
              <h2>Last Jobs</h2>
              <ul>
<?php foreach($a_job as $job) { ?>
                <li><?php echo $job; ?></li>
<?php } ?>
              </ul>
	      <a class="btn" href="/list/w/jobs">More..</a>
            </div>
            <div class="span4">
              <h2>Last Checks</h2>
              <ul>
<?php foreach($a_result as $check) { ?>
                <li><?php echo $check->html(); ?></li>
<?php } ?>
              </ul>
	      <a class="btn" href="/list/w/results">More..</a>
            </div>

	  </div>
        </div>
      </div>
