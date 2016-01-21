          <div class="jumbotron">
            <h1>Welcome to Espix Operations</h1>
            <p>You can manage different flavours of UNIX operating systems using this portal, simply browse through the menu or check the documentation to see how to get more benefit of this portal.</p>
            <p>
              <a class="btn btn-primary btn-large" href="https://github.com/tgouverneur/SPXOps/wiki">Documentation</a>
              <?php if (!isset($page['login'])) { ?><a class="btn btn-primary btn-large" href="/login">Login</a> <a class="btn btn-primary btn-large" href="/register">Register</a><?php } ?>
            </p>
          </div>
          <?php if (!isset($page['login'])) { ?>
          <?php } else { ?>
      <div class="row">
        <div class="col-md-9"><p class="bg-success"><b>Online Users</b>: <?php foreach ($a_login as $l) { echo $l.'('.Utils::formatSeconds($now - $l->t_last).'), '; } ?></p></div>
        <div class="col-md-3"><p class="bg-info"><b>Currently running jobs</b>: <?php echo $n_job; ?></p></div>
      </div>
      <div class="row">
	    <div class="col-md-4">
          <h2>Key Numbers</h2>
            <dl class="dl-horizontal">
              <dt>Servers:</dt>
              <dd><?php echo $stats['nbsrv']; ?> registered</dd>
              <dt>Server Groups:</dt>
              <dd><?php echo $stats['nbsgroup']; ?> registered</dd>
              <dt>Virtual Machines:</dt>
              <dd><?php echo $stats['nbvm']; ?> detected</dd>
              <dt>Solaris Zones:</dt>
              <dd><?php echo $stats['nbzone']; ?> detected</dd>
              <dt>Clusters:</dt>
              <dd><?php echo $stats['nbcl']; ?> registered</dd>
              <dt>Chassis:</dt>
              <dd><?php echo $stats['nbpsrv']; ?> registered</dd>
              <dt>Zpools:</dt>
              <dd><?php echo $stats['nbpool']; ?> detected</dd>
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
	    <div class="col-md-4">
          <h2>Failed Jobs</h2>
        <?php if (!$page['login']->cRight('JOB', R_VIEW)) { ?>
            <p>Sorry.. it seems you don't have enough permission</p>
        <?php } else { ?>
        <?php if (!count($a_job)) { ?>
            <p>There is no failed job, woohoo!</p>
        <?php } else { ?>
            <ul>
            <?php foreach($a_job as $job) { $job->fetchOwner(); ?>
                <li><?php echo $job->link($job->id.' ('.$job->owner().') '.$job->class.'::'.$job->fct); ?></li>
            <?php } ?>
            </ul>
        <?php } ?>
        <?php } ?>
        </div>
        <div class="col-md-4">
         <h2>Last Items Added</h2>
         <table class="table table-condensed"><thead><tr><th>Name</th><th>Type</th></tr></thead>
         <tbody>
         <?php $j=0; foreach($a_litem as $i) { if ($j++ >= 10) break; ?>
            <tr><td><?php echo $i->link(); ?></td><td><?php echo get_class($i); ?></td></tr>
         <?php  } ?>
         </tbody>
         </table>
        </div>
  	  </div>
      <?php } ?>
