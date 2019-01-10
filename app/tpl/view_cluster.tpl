	<div class="page-header"><h1>Cluster <?php echo $obj; ?></h1></div>
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
<?php if ($obj->o_clver) { ?>
<?php   foreach($obj->o_clver->htmlDump($obj) as $k => $v) { ?>
	      <tr><td><?php echo $k; ?></td><td><?php echo $v; ?></td></tr>
<?php   } ?>
<?php } ?>
	     </tbody>
	   </table>
	  </div>
          <div class="col-md-4">
           <h3>Nodes</h3>
             <div class="accordion" id="network">
	     <ul class="unstyled">
<?php foreach($obj->a_server as $server) { ?>
		<li><i class="icon-hand-right"></i> <a href="/view/w/server/i/<?php echo $server->id; ?>"><?php echo $server; ?></a></li>
<?php } ?>
	     </ul>
	     </div>
          </div>
          <div class="col-md-4">
           <h3>Actions</h3>
	    <ul class="nav nav-pills nav-stacked">
	      <li class="dropdown active">
		<a class="dropdown-toggle" data-toggle="dropdown" href="#">Database <b class="caret"></b></a>
	        <ul class="dropdown-menu">
                  <li><a href="/edit/w/cluster/i/<?php echo $obj->id; ?>">Edit</a></li>
                  <li><a href="/del/w/cluster/i/<?php echo $obj->id; ?>">Delete</a></li>
                  <li><a href="/log/w/cluster/i/<?php echo $obj->id; ?>">Add Log entry</a></li>
	        </ul>
	      </li>
              <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Action <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#" onClick="addJob('Update', 'jobCluster', '<?php echo $obj->id; ?>');">Launch Update</a></li>
                  <li><a href="#">Launch Check</a></li>
                </ul>
              </li>
              <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">View <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="/modallist/w/logs/o/cluster/i/<?php echo $obj->id; ?>" class="logsModalLink">View Logs</a></li>
                </ul>
              </li>
            </ul>
	  </div>
	</div>
        <div class="row">
          <div class="col-md-8">
           <h3>Resource Groups</h3>
           <table class="table table-condensed">
             <thead>
               <tr>
                <td>Name</td>
                <td>Description</td>
                <td>State</td>
                <td>Nodes</td>
                <td>Suspended</td>
                <td></td>
               </tr>
             </thead>
             <tbody>
<?php foreach($obj->a_clrg as $rg) { $rg->fetchJT('a_node'); ?>
               <tr>
                <td><?php echo $rg->name; ?></td>
                <td><?php echo $rg->description; ?></td>
                <td><?php echo $rg->state; ?></td>
                <td><?php echo $rg->dumpNodes(); ?></td>
                <td><?php echo ($rg->f_suspend)?'<i class="icon-ok-sign"></i>':'<i class="icon-remove-sign"></i>'; ?></td>
		<td><a data-toggle="modal" href="/modallist/w/rs/i/<?php echo $rg->id; ?>" data-target="#rsModal">Details</a></td>
               </tr>
<?php } ?>
             </tbody>
           </table>

          </div>
          <div class="col-md-4">
           <h3>Visual repartition</h3>
	   <div id="pieRG"></div>
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
      <!-- Resource Modal -->
      <div class="modal fade" tabindex="-1" role="dialog" id="rsModal" aria-labelledby="rsModal" aria-hidden="true">
        <div class="modal-dialog modal-lg">
          <div class="modal-content">
           <div class="modal-header">
             <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
             <h4 class="modal-title" id="rsModalLabel">Resources list:</h3>
           </div>
           <div class="modal-body">
           </div>
           <div class="modal-footer">
             <button class="btn" data-dismiss="modal" aria-hidden="true">Close</button>
           </div>
          </div>
        </div>
      </div>
      <script class="code" type="text/javascript">
	$(document).ready(function(){
	  var data = [
<?php
	$r = $obj->getRGRepartition();
	foreach($r as $n => $nb) { ?>
	    ['<?php echo $n; ?>', <?php echo $nb; ?>],
<?php   } ?>
	  ];
	  var plot1 = jQuery.jqplot ('pieRG', [data], 
	    { 
	      seriesDefaults: {
		renderer: jQuery.jqplot.PieRenderer, 
		rendererOptions: {
		  fill: false,
		  sliceMargin: 4,
		  lineWidth: 5,
		  showDataLabels: true
		}
	      }, 
	      legend: { show:true, location: 'e' },
	      grid: { drawGridLines:false, shadow:false, borderWidth:0.0 }
	    }
	  );
	});
      </script>
      <script class="code" type="text/javascript">
        $('.logsModalLink').click(function(e) {
          var modal = $('#logsModal'), modalBody = $('#logsModal .modal-body');
          modal.on('show.bs.modal', function () {
            modalBody.load(e.currentTarget.href)
          })
        .modal();
        e.preventDefault();
        });
        $('.rsModalLink').click(function(e) {
          var modal = $('#rsModal'), modalBody = $('#rsModal .modal-body');
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
