<?php if (isset($error)) { 
        if (!is_array($error)) {
          $error = array($error);
        }
        foreach($error as $e) {
?>
        <div class="alert alert-danger alert-dismissible" role="alert">
          <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
          <strong>Error!</strong> <?php echo $e; ?>
        </div>
<?php   }
      }
?>
	<div class="page-header"><h1>CDP Packet Parser</h1></div>
	<div class="alert alert-info">
	  <strong>Howto gather packet</strong>
          <ul>
	   <li>Linux: tcpdump -xx -c 1 -s1600 -n -i &lt;IFACE&gt; ether dst 01:00:0c:cc:cc:cc and greater 150</li>
	   <li>Solaris: snoop -P -x 0 -c 1 -r -s 1600 -d &lt;IFACE&gt; ether dst 01:00:0c:cc:cc:cc and greater 150</li>
	  </ul>
	</div>
        <div class="row">
        <form method="POST" action="/tools/w/cdp" class="form-horizontal">
        <div class="col-sm-5">
	  <div class="form-group">
	    <label class="col-sm-4 col-sm-offset-3 control-label" for="selectFrequency">Type of output</label>
	    <div class="col-sm-5">
	      <select class="form-control" name="type" id="selectType">
		<option value="1">tcpdump</option>
		<option value="2">snoop</option>
	      </select>
	    </div>
	  </div>
	  <div class="form-group">
	    <div class="col-sm-3 col-sm-offset-7">
	      <button type="submit" name="submit" value="1" class="btn btn-primary">Parse</button>
	    </div>
	  </div>
        </div>
        <div class="col-sm-7">
         <textarea class="form-control" name="packet" rows="25" class="input-xxlarge"></textarea>
        </div>
       </form>
      </div>
