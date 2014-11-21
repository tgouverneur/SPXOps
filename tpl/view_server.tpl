      <div class="row">
	<h1 class="span12">Server <?php echo $obj; ?></h1>
        <div class="row">
	 <div class="span12">
	  <div class="alert alert-block alert-success fade in" id="success-box" style="display:none;">
	    <button type="button" class="close">×</button>
	    <h4>Success!</h4>
	    <p id="success-msg"></p>
	  </div>
          <div class="alert alert-block fade in" id="warning-box" style="display:none;">
            <button type="button" class="close">×</button>
            <h4>Warning!</h4>
            <p id="warning-msg"></p>
          </div>
          <div class="alert alert-block alert-error fade in" id="error-box" style="display:none;">
            <button type="button" class="close">×</button>
            <h4>Error!</h4>
            <p id="error-msg"></p>
          </div>
	 </div>
	</div>
        <div class="row">
          <div class="span4">
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
          <div class="span4">
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
          <div class="span4">
           <h3>Actions</h3>
	    <ul class="nav nav-tabs nav-stacked">
	      <li class="dropdown">
		<a class="dropdown-toggle" data-toggle="dropdown" href="#">Database <b class="caret"></b></a>
	        <ul class="dropdown-menu">
                  <li><a href="/edit/w/server/i/<?php echo $obj->id; ?>">Edit</a></li>
                  <li><a href="/del/w/server/i/<?php echo $obj->id; ?>">Delete</a></li>
                  <li><a href="/log/w/server/i/<?php echo $obj->id; ?>">Add Log entry</a></li>
	        </ul>
	      </li>
              <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Action <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#" onClick="addJob('Update', 'jobServer', '<?php echo $obj->id; ?>');">Launch Update</a></li>
                  <li><a href="#" onClick="addJob('Check', 'jobServer', '<?php echo $obj->id; ?>');">Launch Check</a></li>
                  <li><a href="#">Check ZFS Arc</a></li>
                  <li><a href="#">Zone Stats</a></li>
                </ul>
              </li>
              <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">View <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a data-toggle="modal" href="/modallist/w/patches/i/<?php echo $obj->id; ?>" data-target="#patchesModal">View Patches</a></li>
                  <li><a data-toggle="modal" href="/modallist/w/packages/i/<?php echo $obj->id; ?>" data-target="#packagesModal">View Packages</a></li>
                  <li><a data-toggle="modal" href="/modallist/w/projects/i/<?php echo $obj->id; ?>" data-target="#projectsModal">View Projects</a></li>
                  <li><a data-toggle="modal" href="/modallist/w/disks/i/<?php echo $obj->id; ?>" data-target="#disksModal">View Disks</a></li>
                  <li><a data-toggle="modal" href="/modallist/w/sresults/i/<?php echo $obj->id; ?>" data-target="#resultsModal">View Check Results</a></li>
                  <li><a data-toggle="modal" href="/modallist/w/logs/o/Server/i/<?php echo $obj->id; ?>" data-target="#logsModal">View Logs</a></li>
                </ul>
              </li>
            </ul>
	  </div>
	</div>
        <div class="row">
          <div class="span4">
<?php if ($obj->o_os->f_zone) { ?>
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
          <div class="span8">
           <h3>Network Interfaces</h3>
	     <div class="accordion" id="network">
<?php $i = 0; foreach($obj->getNetworks() as $net) { ?>
                <div class="accordion-group">
                  <div class="accordion-heading">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#network" href="#collapse<?php echo $i; ?>">
                      <?php echo $net; ?> 
<?php if ($net->f_ipmp) { ?>
		      (Group: <?php echo $net->group; ?>)
<?php } ?>
		      (<?php echo count($net->a_addr); ?> address found)
	              <span class="caret"></span>
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
      <div class="modal hide fade in" id="patchesModal" tabindex="-1" role="dialog" aria-labelledby="patchesModalLabel" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
          <h3 id="patchesModalLabel">Patches Installed</h3>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
      </div>
      <!-- Packages Modal -->
      <div class="modal large hide fade in" id="packagesModal" tabindex="-1" role="dialog" aria-labelledby="packagesModalLabel" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
          <h3 id="packagesModalLabel">Packages Installed</h3>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
      </div>
      <!-- Projects Modal -->
      <div class="modal hide fade in" id="projectsModal" tabindex="-1" role="dialog" aria-labelledby="projectsModalLabel" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
          <h3 id="projectsModalLabel">Project list</h3>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
      </div>
      <!-- Disks Modal -->
      <div class="modal large hide fade in" id="disksModal" tabindex="-1" role="dialog" aria-labelledby="disksModalLabel" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
          <h3 id="disksModalLabel">Disks list</h3>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
      </div>
      <!-- Result Modal -->
      <div class="modal large hide fade in" id="resultsModal" tabindex="-1" role="dialog" aria-labelledby="resultsModalLabel" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
          <h3 id="resultsModalLabel">Results list</h3>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
      </div>
      <!-- Logs Modal -->
      <div class="modal large hide fade in" id="logsModal" tabindex="-1" role="dialog" aria-labelledby="logsModalLabel" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
          <h3 id="logsModalLabel">Log Entries</h3>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
      </div>
