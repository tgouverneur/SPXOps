	<div class="page-header"><h1>Virtual Machine <?php echo $obj; ?></h1></div>
        <div class="alert alert-block alert-success fade in" id="success-box" style="display:none;">
          <button type="button" class="close"><col-md- aria-hidden="true">&times;</col-md-><col-md- class="sr-only">Close</col-md-></button>
          <h4>Success!</h4>
          <p id="success-msg"></p>
        </div>
        <div class="alert alert-block alert-warning fade in" id="warning-box" style="display:none;">
          <button type="button" class="close"><col-md- aria-hidden="true">&times;</col-md-><col-md- class="sr-only">Close</col-md-></button>
          <h4>Warning!</h4>
          <p id="warning-msg"></p>
        </div>
        <div class="alert alert-block alert-danger fade in" id="error-box" style="display:none;">
          <button type="button" class="close"><col-md- aria-hidden="true">&times;</col-md-><col-md- class="sr-only">Close</col-md-></button>
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
	     </tbody>
	   </table>
	  </div>
          <div class="col-md-4">
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
          <div class="col-md-4">
           <h3>Actions</h3>
	    <ul class="nav nav-pills nav-stacked">
	      <li class="dropdown active">
		<a class="dropdown-toggle" data-toggle="dropdown" href="#">Database <b class="caret"></b></a>
	        <ul class="dropdown-menu">
                  <li><a href="/log/w/vm/i/<?php echo $obj->id; ?>">Add Log entry</a></li>
	        </ul>
	      </li>
              <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Action <b class="caret"></b></a>
                <ul class="dropdown-menu">
                <?php foreach (Plugin::getActionLinks('VMACTION') as $l) { ?>
                  <li><a href="<?php echo $l->getHref($obj->id); ?>"><?php echo $l->desc; ?></a></li>
                <?php } ?>
                </ul>
              </li>
              <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">View <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a class="logsModalLink" href="/modallist/w/logs/o/VM/i/<?php echo $obj->id; ?>">View Logs</a></li>
                </ul>
              </li>
            </ul>
	  </div>
	</div>
        <div class="row">
          <div class="col-md-8">
           <h3>Disks</h3>
           <table class="table table-condensed">
             <tbody>
               <tr><th>File</th></tr>
<?php foreach($obj->a_disk as $disk) { ?>
               <tr><td><?php echo $disk->file; ?></td></tr>
<?php } ?>  
             </tbody>
           </table>
           <h3>Plugin data</h3>
           <table class="table table-condensed">
             <tbody>
  	       <tr><th>Name</th><th>Value</th></tr>
<?php foreach($obj->dataKeys() as $k) { 
        if (preg_match('/^plugin:([^:]+):([^:]+)$/', $k, $m)) {
            $plugin = $m[1];
            if (preg_match('/^f_(.*)/', $m[2], $n)) {
              $name = ucfirst($n[1]);
              $value = Plugin::formatFlag($obj->data($k));
            } else {
              $name = ucfirst($m[2]);
              $value = $obj->data($k);
            }
        } else {
            continue;
        }
    ?>
  	       <tr><td><?php echo $plugin.'/'.$name; ?></td><td><?php echo $value; ?></td></tr>
<?php } ?>
             </tbody>
           </table>
 
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
      </script>
      <script class="code" type="text/javascript">
        $('.alert .close').on('click', function() {
          $(this).parent().hide();
        });
      </script>
