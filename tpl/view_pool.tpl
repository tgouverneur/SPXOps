      <div class="row">
	<h1 class="span12">ZFS Pool <?php echo $obj; ?></h1>
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
           <h3>Usage</h3>
           <p id="nfo_pieUsage">Use your mouse to have more info</p>
           <div id="pieUsage"></div>
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
