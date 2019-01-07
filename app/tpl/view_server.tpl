	<div class="page-header"><h1>Server <?php echo $obj; ?></h1></div>
        <div class="alert alert-block alert-success fade in" id="success-box" style="display:none;">
	  <button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4>Success!</h4>
          <p id="success-msg"></p>
        </div>
        <div class="alert alert-block alert-warning fade in" id="warning-box" style="display:none;">
	  <button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4>Warning!</h4>
          <p id="warning-msg"></p>
        </div>
        <div class="alert alert-block alert-danger fade in" id="error-box" style="display:none;">
	  <button type="button" class="close"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <h4>Error!</h4>
          <p id="error-msg"></p>
        </div>
        <div class="row">
          <div class="col-md-4">
           <h3>Basic Information</h3>
	   <table class="table table-condensed">
	     <tbody>
<?php foreach($obj->htmlDump() as $k => $v) { ?>
	      <tr><td><?php echo $k; ?></td><td><?php echo $v; ?></td></tr>
<?php } ?>
<?php if ($obj->o_os) { ?>
<?php   foreach($obj->o_os->htmlDump($obj) as $k => $v) { ?>
	      <tr><td><?php echo $k; ?></td><td><?php echo $v; ?></td></tr>
<?php   } ?>
<?php } ?>
<?php if ($obj->o_suser) { ?>
<?php   foreach($obj->o_suser->htmlDump($obj) as $k => $v) { ?>
              <tr><td><?php echo $k; ?></td><td><?php echo $v; ?></td></tr>
<?php   } ?>
<?php } ?>  
	     </tbody>
	   </table>
	  </div>
          <div class="col-md-4">
           <h3>Hardware</h3>
           <table class="table table-condensed">
             <tbody>
<?php if ($obj->o_pserver) { ?>
<?php   foreach($obj->o_pserver->htmlDump($obj) as $k => $v) { ?>
              <tr><td><?php echo $k; ?></td><td><?php echo $v; ?></td></tr>
<?php   } ?>
<?php } ?>  
             </tbody>
           </table>
          </div>
          <div class="col-md-4">
           <h3>Actions</h3>
            <ul class="nav nav-pills nav-stacked">
              <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Database <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="/edit/w/server/i/<?php echo $obj->id; ?>">Edit</a></li>
                  <li><a href="/del/w/server/i/<?php echo $obj->id; ?>">Delete</a></li>
                  <li><a href="/log/w/server/i/<?php echo $obj->id; ?>">Add Log entry</a></li>
                </ul>
              </li>
              <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Action <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#" onClick="addJob('Update', 'jobServer', '<?php echo $obj->id; ?>');">Launch Update</a></li>
                  <li><a href="#" onClick="addJob('Check', 'jobServer', '<?php echo $obj->id; ?>');">Launch Check</a></li>
<?php
  $ea = $obj->getExtraActions();
  foreach($ea as $a) { ?>
                  <li><a href="<?php echo $a->href($obj); ?>" <?php if (!empty($a->onclick)) { echo "onClick=\"".$a->onclick($obj)."\""; } ?>><?php echo $a->text; ?></a></li>
<?php } ?>
             <?php foreach (Plugin::getActionLinks('SRVACTION') as $l) { ?>
                <li><a href="<?php echo $l->getHref($obj->id); ?>"><?php echo $l->desc; ?></a></li>
             <?php } ?>
                </ul>
              </li>
              <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">View <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="/modallist/w/patches/o/Server/i/<?php echo $obj->id; ?>" class="patchesModalLink">View Patches</a></li>
                  <li><a href="/modallist/w/packages/o/Server/i/<?php echo $obj->id; ?>" class="packagesModalLink">View Packages</a></li>
                  <li><a href="/modallist/w/projects/o/Server/i/<?php echo $obj->id; ?>" class="projectsModalLink">View Projects</a></li>
                  <li><a href="/modallist/w/disks/o/Server/i/<?php echo $obj->id; ?>" class="disksModalLink">View Disks</a></li>
                  <li><a href="/modallist/w/sresults/o/Server/i/<?php echo $obj->id; ?>" class="resultsModalLink">View Check Results</a></li>
                  <li><a href="/modallist/w/logs/o/Server/i/<?php echo $obj->id; ?>" class="logsModalLink">View Logs</a></li>
                </ul>
              </li>
            </ul>
	  </div>
	</div>
        <div class="row">
          <div class="col-md-4">
<?php if ($obj->o_os && $obj->o_os->f_zone) { ?>
           <h3>Zones</h3>
	   <table class="table table-condensed">
             <thead>
	       <tr>
		<td>Name</td>
		<td>Brand</td>
		<td>Status</td>
	       </tr>
	     </thead>
	     <tbody>
<?php foreach($obj->a_zone as $zone) { ?>
	       <tr>
		<td><a href="/view/w/zone/i/<?php echo $zone->id; ?>"><?php echo $zone->name; ?></td>
		<td><?php echo $zone->brand; ?></td>
		<td><?php echo $zone->status; ?></td>
	       </tr>
<?php } ?>
             </tbody>
	   </table>
<?php } else { ?>
           <h3>VMs</h3>
	   <table class="table table-condensed">
             <thead>
	       <tr>
		<td>Name</td>
		<td>Status</td>
	       </tr>
	     </thead>
	     <tbody>
<?php foreach($obj->a_vm as $vm) { ?>
	       <tr>
		<td><a href="/view/w/vm/i/<?php echo $vm->id; ?>"><?php echo $vm->name; ?></td>
		<td><?php echo $vm->status; ?></td>
	       </tr>
<?php } ?>
             </tbody>
	   </table>

<?php } ?>
          </div>
<?php if (count($obj->a_pool)) {  ?>
          <div class="col-md-8">
            <h3>Zpool</h3>
 	    <table class="table table-condensed">
             <thead>
	       <tr>
		<td>Name</td>
		<td>Size</td>
		<td>Free</td>
		<td>Status</td>
	       </tr>
	     </thead>
	     <tbody>
<?php foreach($obj->a_pool as $pool) { ?>
	       <tr>
		<td><a href="/view/w/pool/i/<?php echo $pool->id; ?>"><?php echo $pool->name; ?></td>
		<td><?php echo Pool::formatBytes($pool->size); ?></td>
		<td><?php echo Pool::formatBytes($pool->size - $pool->used); ?></td>
		<td><?php echo $pool->status; ?></td>
	       </tr>
<?php } ?>
             </tbody>
	    </table>
          </div>
	</div>
        <div class="row">
<?php } ?>
          <div class="col-md-8">
           <h3>Network Interfaces</h3>
	     <div class="panel-group" id="network">
<?php $i = 0; foreach($obj->getNetworks() as $net) { ?>
                <div class="panel panel-default">
                  <div class="panel-heading">
		    <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $i; ?>" aria-expanded="true" aria-controls="collapseOne">
                      <?php echo $net; ?> 
<?php if ($net->f_ipmp) { ?>
		      (Group: <?php echo $net->group; ?>)
<?php } ?>
		      (<?php echo count($net->a_addr); ?> address found)
	              <col-md- class="caret"></col-md->
                    </a>
                  </div>
                  <div id="collapse<?php echo $i; ?>" class="accordion-body collapse">
                    <div class="accordion-inner">
		     <table class="table table-condensed table-striped">
<?php $switch = $net->getSwitch();
      if ($switch) { ?>
			<caption>Connected to switch <?php echo $switch->name; ?> interface <?php echo $net->o_net->ifname; ?></caption>
<?php } ?>
		      <thead>
		       <tr><td>IPv</td><td>Address</td><td>Netmask</td><td>Zone</td>
		      </thead>
		      <tbody>
<?php		foreach ($net->a_addr as $addr) { ?>
		       <tr>
			<td><?php echo $addr->version; ?></td>
			<td><?php echo $addr->address; ?></td>
			<td><?php echo $addr->netmask; ?></td>
			<td><?php if ($addr->o_zone) { echo $addr->o_zone; } else { echo 'global'; } ?></td>
		       </tr>
<?php } ?>
		      </tbody>
		     </table>
                    </div>
                  </div>
                </div>
<?php $i++; } ?>
              </div>
          </div>
       </div>
      </div>
      <!-- Patches Modal -->
      <div class="modal fade" tabindex="-1" role="dialog" id="patchesModal" aria-labelledby="patchesModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
           <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
             <h4 class="modal-title" id="patchesModalLabel">Patches Installed</h3>
           </div>
           <div class="modal-body">
           </div>
           <div class="modal-footer">
             <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
           </div>
          </div>
        </div>
      </div>
      <!-- Packages Modal -->
      <div class="modal fade" tabindex="-1" role="dialog" id="packagesModal" aria-labelledby="packagesModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
           <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
             <h4 class="modal-title" id="packagesModalLabel">Packages Installed</h3>
           </div>
           <div class="modal-body">
           </div>
           <div class="modal-footer">
             <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
           </div>
          </div>
        </div>
      </div>
      <!-- Projects Modal -->
      <div class="modal fade" tabindex="-1" role="dialog" id="projectsModal" aria-labelledby="projectsModal" aria-hidden="true">
        <div class="modal-dialog modal-lg"> 
          <div class="modal-content">
           <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
             <h4 class="modal-title" id="projectsModalLabel">Project list:</h3>
           </div>
           <div class="modal-body">
           </div>
           <div class="modal-footer">
             <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
           </div>
          </div>
        </div>
      </div>
      <!-- Disks Modal -->
      <div class="modal fade" tabindex="-1" role="dialog" id="disksModal" aria-labelledby="disksModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
           <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
             <h4 class="modal-title" id="disksModalLabel">Disks list:</h3>
           </div>
           <div class="modal-body">
           </div>
           <div class="modal-footer">
             <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
           </div>
          </div>
        </div>
      </div>
      <!-- Result Modal -->
      <div class="modal fade" tabindex="-1" role="dialog" id="resultsModal" aria-labelledby="resultsModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
           <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
             <h4 class="modal-title" id="resultsModalLabel">Checks Results:</h3>
           </div>
           <div class="modal-body">
           </div>
           <div class="modal-footer">
             <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
           </div>
          </div>
        </div>
      </div>
      <!-- Logs Modal -->
      <div class="modal fade" tabindex="-1" role="dialog" id="logsModal" aria-labelledby="logsModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
           <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
             <h4 class="modal-title" id="logsModalLabel">Logs entries:</h3>
           </div>
           <div class="modal-body">
           </div>
           <div class="modal-footer">
             <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
           </div>
          </div>
        </div>
      </div>
      <!-- Action Modal -->
      <div class="modal fade" tabindex="-1" role="dialog" id="actionModal" aria-labelledby="actionModal" aria-hidden="true">
        <div class="modal-dialog modal-lg"> 
          <div class="modal-content">
           <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
             <h4 class="modal-title" id="actionModalLabel"></h3>
           </div>
           <div id="actionModalBody" class="modal-body">
           </div>
           <div class="modal-footer">
             <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
           </div>
          </div>
        </div>
      </div>
      <script class="code" type="text/javascript">
        $('.patchesModalLink').click(function(e) {
          var modal = $('#patchesModal'), modalBody = $('#patchesModal .modal-body');
          modal.on('show.bs.modal', function () {
            modalBody.load(e.currentTarget.href)
          })
        .modal();
        e.preventDefault();
        });
        $('.projectsModalLink').click(function(e) {
          var modal = $('#projectsModal'), modalBody = $('#projectsModal .modal-body');
          modal.on('show.bs.modal', function () {
            modalBody.load(e.currentTarget.href)
          })
        .modal();
        e.preventDefault();
        });
        $('.packagesModalLink').click(function(e) {
          var modal = $('#packagesModal'), modalBody = $('#packagesModal .modal-body');
          modal.on('show.bs.modal', function () {
            modalBody.load(e.currentTarget.href)
          })
        .modal();
        e.preventDefault();
        });
        $('.disksModalLink').click(function(e) {
          var modal = $('#disksModal'), modalBody = $('#disksModal .modal-body');
          modal.on('show.bs.modal', function () {
            modalBody.load(e.currentTarget.href)
          })
        .modal();
        e.preventDefault();
        });
        $('.resultsModalLink').click(function(e) {
          var modal = $('#resultsModal'), modalBody = $('#resultsModal .modal-body');
          modal.on('show.bs.modal', function () {
            modalBody.load(e.currentTarget.href)
          })
        .modal();
        e.preventDefault();
        });
        $('.logsModalLink').click(function(e) {
          var modal = $('#logsModal'), modalBody = $('#logsModal .modal-body');
          modal.on('show.bs.modal', function () {
            modalBody.load(e.currentTarget.href)
          })
        .modal();
        e.preventDefault();
        });
        $('.alert .close').on('click', function() {
          $(this).parent().hide();
        });
      </script>
