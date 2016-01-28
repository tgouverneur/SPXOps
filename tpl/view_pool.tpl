	<div class="page-header"><h1>ZFS Pool <?php echo $obj; ?></h1></div>
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
           <h3>Usage</h3>
           <p id="nfo_pieUsage">Use your mouse to have more info</p>
           <div id="pieUsage"></div>
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
                  <li><a href="#">Placeholder</a></li>
                </ul>
              </li>
              <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">View <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="/modallist/w/logs/o/VM/i/<?php echo $obj->id; ?>" class="logsModalLink">View Logs</a></li>
                </ul>
              </li>
            </ul>
	  </div>
	</div>
        <div class="row">
          <div class="col-md-7">
           <h3>Dataset</h3>
           <table class="table table-condensed">
             <thead>
               <tr>
		 <th>Name</th>
		 <th>Size</th>
		 <th>Used</th>
		 <th>Added on</th>
	       </tr>
             </thead>
             <tbody>
<?php foreach ($obj->a_dataset as $ds) { ?>
		<tr>
		  <td><?php echo $ds->name; ?></td>
		  <td><?php echo Pool::formatBytes($ds->size); ?></td>
		  <td><?php echo Pool::formatBytes($ds->used); ?></td>
		  <td><?php echo date('d-m-Y', $ds->t_add); ?></td>
		</tr>
<?php } ?>
             </tbody>
           </table>
          </div>
	  <div class="col-md-5">
	    <h3>Disks</h3>
           <table class="table table-condensed">
             <thead>
               <tr>
                 <th>Dev</th>
                 <th>Role</th>
                 <th>Size</th>
               </tr>
             </thead>
             <tbody>
<?php foreach ($obj->a_disk as $disk) { ?>
                <tr>
                  <td><?php echo $disk->link(); ?></td>
                  <td><?php echo $disk->role[''.$obj]; ?></td>
                  <td><?php echo Pool::formatBytes($disk->size); ?></td>
                </tr>
<?php } ?>  
	     </tbody>
	   </table>
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
        $(document).ready(function(){
          var data = [
            ['Used', <?php echo $obj->used; ?>],
            ['Free', <?php echo ($obj->size - $obj->used); ?>],
          ];
	  var dataStats = [
<?php foreach($obj->getTypeStats() as $t => $s) { ?>
            ['<?php echo ucfirst($t); ?>', <?php echo $s; ?>],
<?php } ?>
	  ];
	  $.jqplot.config.enablePlugins = true;
	  var plot1 = $.jqplot('pieUsage', [data, dataStats], {
	    series: [
		    {seriesColors: ["#4bb2c5", "#EAA228", "#c5b47f", "#579575", "#839557", "#958c12"]},
		    {seriesColors: ["#bd70c7", "#26B4E3", "#FBD178", '#cddf54', '#c747a3', '#0085cc', '#ff5800', '#d8b83f', '#4b5de4', '#953579']}
            ],
	    seriesDefaults: {
	      renderer:$.jqplot.DonutRenderer,
	      rendererOptions:{
		sliceMargin: 3,
		startAngle: -90,
		showDataLabels: true,
		highlightMouseOver: true,
	      },
	    },
            legend: { show:true, location: 'e' },
            grid: { background:"#ffffff", drawGridLines:false, shadow:false, borderWidth:0.0 },
	  });
          $('#pieUsage').bind('jqplotDataHighlight', 
            function (ev, seriesIndex, pointIndex, data) {
                $('#nfo_pieUsage').text(data[0]);
            }
          );    
        $('#pieUsage').bind('jqplotDataUnhighlight', 
            function (ev) {
                $('#nfo_pieUsage').text('Use your mouse to have more info');
            }
        );
        });
      </script>
      <script class="code" type="text/javascript">
        $('.disksModalLink').click(function(e) {
          var modal = $('#disksModal'), modalBody = $('#disksModal .modal-body');
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
