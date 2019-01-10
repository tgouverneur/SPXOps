	<div class="page-header"><h1>RRD <?php echo $obj; ?></h1></div>
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
          <div class="col-md-8">
           <h3>Usage</h3>
           <p id="nfo_chart">Use your mouse to have more info</p>
           <div id="chart"></div>
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
          var dataChart = [ [0,0] ];
          var dataLabels = [ [0,0] ];
	  $.jqplot.config.enablePlugins = true;
          window.options = {      
            axes: {   	    
               xaxis: {   	   	   
                  numberTicks: 4,            
                  min : dataChart[0][0],           
                  max: dataChart[dataChart.length-1][0],
		  labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                  tickOptions: {
                    formatter: function (format, val) {
                       if (typeof val == 'number') {
                         if (!format) {
                           format = '%s';
                         }
                         var date = new Date(val*1000);
                         var hours = date.getHours();
                         if (hours <= 9) hours = '0'+hours;
                         var minutes = date.getMinutes();
                         if (minutes <= 9) minutes = '0'+minutes;
                         var seconds = date.getSeconds();
                         if (seconds <= 9) seconds = '0'+seconds;
                         return String(hours+':'+minutes+':'+seconds);
                       }
                       else {
                         return String(val);
                       }
                    }
                  },
               }, 	    
               yaxis: {
                  min:0, 
                  labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                  tickOptions: {
                    formatter: function (format, val) {
                       if (typeof val == 'number') {
                         if (!format) {
                           format = '%.1f';
                         }
                         if (Math.abs(val) >= 1000000000000 ) {
                           return (val / 1000000000000).toFixed(1) + 'Ti';
                         }
                         if (Math.abs(val) >= 1000000000 ) {
                           return (val / 1000000000).toFixed(1) + 'Gi';
                         }
                         if (Math.abs(val) >= 1000000 ) {
                           return (val / 1000000 ).toFixed(1) + 'Mi';
                         }
                         if (Math.abs(val) >= 1000) {
                           return (val / 1000).toFixed(1) + 'Ki';
                         }
                         return String(val.toFixed(1));
                       }
                       else {
                         return String(val);
                       }
                    }
                  },
               }      
            },      
            seriesDefaults: {   	    
               rendererOptions: { smooth: true}      
            },
            legend: { show:true, location: 'e' },
            grid: { background:"#ffffff", drawGridLines:false, shadow:false, borderWidth:0.0 },
	    highlighter: {
	       sizeAdjust: 10,
	       tooltipLocation: 'n',
	       tooltipAxes: 'y',
	       tooltipFormatString: '%s',
	       useAxesFormatters: true,
	    },
          };  
	  $.jqplot.config.enablePlugins = true;
	  window.chartPlot1 = $.jqplot('chart', [dataLabels, dataChart], window.options);
	  setInterval(function() { getRRDData(<?php echo $obj->id; ?>); }, 1000);
        });
      </script>
      <script class="code" type="text/javascript">
        $('.alert .close').on('click', function() {
          $(this).parent().hide();
        });
      </script>
