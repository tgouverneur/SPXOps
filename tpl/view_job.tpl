	<div class="page-header"><h1>Job details</h1></div>
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
           <h3>Job Information</h3>
	   <table class="table table-condensed">
	     <tbody>
<?php foreach($obj->htmlDump() as $k => $v) { ?>
	      <tr><td><?php echo $k; ?></td><td id="job<?php echo preg_replace('/ /', '_', $k); ?>"><?php echo $v; ?></td></tr>
<?php } ?>
	     </tbody>
	   </table>
	  </div>
          <div class="col-md-8">
           <h3>Log</h3>
<?php if (isset($obj->o_log)) { ?>
	   <pre id="jobLog" class="pre-scrollable">
<?php echo $obj->o_log->log; ?>
	   </pre>
<?php } ?>
          </div>
       </div>
      </div>
      <script class="code" type="text/javascript">
        $('.alert .close').on('click', function() {
          $(this).parent().hide();
        });
         window.refreshJob = 1;
         $('.alert .close').on('click', function() {
           $(this).parent().hide();
         });
         function refreshJobInfo() {
           if (window.refreshJob == 1) {
              $.ajax({
             url: '/rpc/w/job' + '/i/<?php echo $obj->id; ?>',
             dataType: 'json',
             success: updateLogInfo,
             error: failedRPC,
             cache: false
            });
           }
         }
         function updateLogInfo(data, textStatus, jqXHR) {
           $('#jobLog').html(data['log']);
           $('#jobState').text(data['state']);
           if (data['state'] == 'DONE' || data['state'] == 'STALLED' || data['state'] == 'FAILED') {
             window.refreshJob = 0;
           } else {
             setTimeout(function(){refreshJobInfo()}, 1000);
           }
         }
         function failedRPC(jqXHR, textStatus, errorThrown) {
           var msg = 'RPC Call Failure: ';
           if (errorThrown != '') {
             msg = msg + ' HTTP Error: ' + errorThrown;
           } else {
             msg = msg + jqXHR.responseText;
           }
           $("#error-msg").text(msg);
           $("#error-box").show();
         }
         setTimeout(function(){refreshJobInfo();}, 1000);
       </script>
