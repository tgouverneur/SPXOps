      <div class="row">
        <div class="span8 offset2">
<?php if (isset($error)) { 
        if (!is_array($error)) {
          $error = array($error);
        }
        foreach($error as $e) {
?>
	<div class="alert alert-error">
	  <button type="button" class="close" data-dismiss="alert">×</button>
	  <strong>Error!</strong> <?php echo $e; ?>
	</div>
<?php   }
      }
?>
	<h2>CDP Packet Parser</h2>
       </div>
      </div>
	<div class="span12 alert alert-info">
	  <strong>Howto gather packet</strong>
          <ul>
	   <li>Linux: tcpdump -xx -c 1 -s1600 -n -i &lt;IFACE&gt; ether dst 01:00:0c:cc:cc:cc and greater 150</li>
	   <li>Solaris: snoop -P -x 0 -c 1 -r -s 1600 -d &lt;IFACE&gt; ether dst 01:00:0c:cc:cc:cc and greater 150</li>
	  </ul>
	</div>
      <div class="row">
        <form method="POST" action="/tools/w/cdp" class="form-horizontal">
        <div class="span5">
	  <div class="control-group">
	    <label class="control-label" for="selectFrequency">Type of output</label>
	    <div class="controls">
	      <select name="type" id="selectType">
		<option value="1">tcpdump</option>
		<option value="2">snoop</option>
	      </select>
	    </div>
	  </div>
	  <div class="control-group">
	    <div class="controls">
	      <button type="submit" name="submit" value="1" class="btn">Parse</button>
	    </div>
	  </div>
        </div>
        <div class="span7">
         <textarea name="packet" rows="25" class="input-xxlarge"></textarea>
        </div>
       </form>
      </div>
