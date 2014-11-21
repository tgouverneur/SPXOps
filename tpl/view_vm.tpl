      <div class="row">
	<h1 class="span12">Virtual Machine <?php echo $obj; ?></h1>
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
	     </tbody>
	   </table>
	  </div>
          <div class="span4">
           <h3>Hardware</h3>
           <table class="table table-condensed">
             <tbody>
  	       <tr><th>Net</th><th>MAC</th><th>Model</th></tr>
<?php foreach($obj->a_net as $net) { ?>
  	       <tr><td><?php echo $net->net; ?></td><td><?php echo $net->mac; ?></td><td><?php echo $net->model; ?></td></tr>
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
                  <li><a href="/log/w/vm/i/<?php echo $obj->id; ?>">Add Log entry</a></li>
	        </ul>
	      </li>
              <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Action <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#">Placeholder</a></li>
                </ul>
              </li>
              <li class="dropdown">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">View <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a data-toggle="modal" href="/modallist/w/logs/o/VM/i/<?php echo $obj->id; ?>" data-target="#logsModal">View Logs</a></li>
                </ul>
              </li>
            </ul>
	  </div>
	</div>
        <div class="row">
          <div class="span8">
           <h3>Disks</h3>
           <table class="table table-condensed">
             <tbody>
               <tr><th>File</th></tr>
<?php foreach($obj->a_disk as $disk) { ?>
               <tr><td><?php echo $disk->file; ?></td></tr>
<?php } ?>  
             </tbody>
           </table>
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
