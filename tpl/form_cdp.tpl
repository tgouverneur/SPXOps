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
      <div class="row">
        <form method="POST" action="/tool/w/cdp" class="form-horizontal">
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
         <textarea name="packet" rows="25" class="input-xxlarge">
         </textarea>
        </div>
       </form>
      </div>
