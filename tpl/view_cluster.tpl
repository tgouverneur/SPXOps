      <div class="row">
	<h1 class="span12">Cluster <?php echo $obj; ?></h1>
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
<?php if ($obj->o_clver) { ?>
<?php   foreach($obj->o_clver->htmlDump($obj) as $k => $v) { ?>
	      <tr><td><?php echo $k; ?></td><td><?php echo $v; ?></td></tr>
<?php   } ?>
<?php } ?>
	     </tbody>
	   </table>
	  </div>
          <div class="span4">
           <h3>Nodes</h3>
             <div class="accordion" id="network">
	     <ul class="unstyled">
<?php foreach($obj->a_server as $server) { ?>
		<li><i class="icon-hand-right"></i> <a href="/view/w/server/i/<?php echo $server->id; ?>"><?php echo $server; ?></a></li>
<?php } ?>
	     </ul>
	     </div>
          </div>
          <div class="span4">
           <h3>Actions</h3>
	    <ul class="nav nav-tabs nav-stacked">
	      <li class="dropdown">
		<a class="dropdown-toggle" data-toggle="dropdown" href="#">Database <b class="caret"></b></a>
	        <ul class="dropdown-menu">
                  <li><a href="/edit/w/cluster/i/<?php echo $obj->id; ?>">Edit</a></li>
                  <li><a href="/del/w/cluster/i/<?php echo $obj->id; ?>">Delete</a></li>
                  <li><a href="/add/w/logentry/i/<?php echo $obj->id; ?>">Add Log entry</a></li>
	        </ul>
	      </li>
              <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Action <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#" onClick="addJob('Update', 'jobCluster', '<?php echo $obj->id; ?>');">Launch Update</a></li>
                  <li><a href="#">Launch Check</a></li>
                </ul>
              </li>
              <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">View <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a data-toggle="modal" href="/modallist/w/logs/i/<?php echo $obj->id; ?>" data-target="#logsModal">View Logs</a></li>
                </ul>
              </li>
            </ul>
	  </div>
	</div>
        <div class="row">
          <div class="span8">
           <h3>Resource Groups</h3>
           <table class="table table-condensed">
             <thead>
               <tr>
                <td>Name</td>
                <td>Description</td>
                <td>State</td>
                <td>Suspended</td>
                <td></td>
               </tr>
             </thead>
             <tbody>
<?php foreach($obj->a_clrg as $rg) { ?>
               <tr>
                <td><?php echo $rg->name; ?></td>
                <td><?php echo $rg->description; ?></td>
                <td><?php echo $rg->state; ?></td>
                <td><?php echo ($rg->f_suspend)?'<i class="icon-ok-sign"></i>':'<i class="icon-remove-sign"></i>'; ?></td>
		<td><a data-toggle="modal" href="/modallist/w/rs/i/<?php echo $rg->id; ?>" data-target="#rsModal">Details</a></td>
               </tr>
<?php } ?>
             </tbody>
           </table>

          </div>
          <div class="span4">
           <h3>Free</h3>
          </div>
       </div>
      </div>
      <!-- Logs Modal -->
      <div class="modal large hide fade in" id="logsModal" tabindex="-1" role="dialog" aria-labelledby="logsModalLabel" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
          <h3 id="logsModalLabel">Cluster Logs</h3>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
      </div>
      <!-- Resource Modal -->
      <div class="modal large hide fade in" id="rsModal" tabindex="-1" role="dialog" aria-labelledby="rsModalLabel" aria-hidden="true">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
          <h3 id="rsModalLabel">Resource List</h3>
        </div>
        <div class="modal-body">
        </div>
        <div class="modal-footer">
          <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
        </div>
      </div>
