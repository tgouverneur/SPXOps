      <div class="row">
	<h1 class="row12">Server <?php echo $obj; ?></h1>
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
                  <li><a href="/add/w/logentry/i/<?php echo $obj->id; ?>">Add Log entry</a></li>
	        </ul>
	      </li>
              <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Action <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#">Launch Update</a></li>
                  <li><a href="#">Launch Check</a></li>
                  <li><a href="#">Check ZFS Arc</a></li>
                  <li><a href="#">Zone Stats</a></li>
                </ul>
              </li>
              <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">View <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#">View Patches</a></li>
                  <li><a href="#">View Packages</a></li>
                  <li><a href="#">View Projects</a></li>
                </ul>
              </li>
            </ul>
	  </div>
	</div>
        <div class="row">
          <div class="span4">
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
		<td><a href="/view/w/zone/id/<?php echo $zone->id; ?>"><?php echo $zone->name; ?></td>
		<td><?php echo $zone->brand; ?></td>
		<td><?php echo $zone->status; ?></td>
	       </tr>
<?php } ?>
             </tbody>
	   </table>
          </div>
          <div class="span8">
           <h3>Network Interfaces</h3>
	     <div class="accordion" id="network">
<?php $i = 0; foreach($obj->getNetworks() as $net) { ?>
                <div class="accordion-group">
                  <div class="accordion-heading">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#network" href="#collapse<?php echo $i; ?>">
                      <?php echo $net; ?> <span class="caret"></span>
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
		       <tr><td>IPv</td><td>Address</td><td>Netmask</td><td>IPMP</td>
		      </thead>
		      <tbody>
<?php		foreach ($net->a_addr as $addr) { ?>
		       <tr>
			<td><?php echo $addr->version; ?></td>
			<td><?php echo $addr->address; ?></td>
			<td><?php echo $addr->netmask; ?></td>
			<td><?php echo $addr->group; ?></td>
		       </tr>
<?php		} ?>
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
