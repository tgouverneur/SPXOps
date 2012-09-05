<?php
if (!isset($a_cat)) $a_cat = array();
?>
      <div class="row">
        <div class="span10 offset1">
	<h2>Settings</h2>
        <form method="POST" action="/settings" class="form-horizontal">
	<ul class="nav nav-tabs" id="settingsTab">
<?php $i=0; foreach($a_cat as $cat) { ?>
	  <li <?php if (!$i++) { echo 'class="active"'; } ?>><a href="#<?php echo $cat; ?>" data-toggle="tab"><?php echo ucfirst($cat); ?></a></li>
<?php } ?>
	</ul>
	<div class="tab-content" id="settingsTabContent">
<?php $i=0; foreach($a_cat as $cat) { $a_setting = Setting::getSettings($cat); ?>
	  <div class="tab-pane<?php if (!$i++) { echo ' active'; } ?>" id="<?php echo $cat; ?>">
	<?php foreach($a_setting as $s) { ?>
          <div class="control-group">
            <label class="control-label" for="input<?php echo $s->cat.'_'.$s->name; ?>"><?php echo $s->textname; ?></label>
            <div class="controls">
              <input type="text" name="<?php echo $s->cat.'_'.$s->name; ?>" value="<?php echo $s->value; ?>" id="input<?php echo $s->cat.'_'.$s->name; ?>" placeholder="<?php echo $s->placeholder; ?>">
            </div>
          </div>
	<?php } ?>
	  </div>
<?php } ?>
	</div>
	  <div class="control-group">
	    <div class="controls">
	      <button type="submit" name="submit" value="1" class="btn">Save</button>
	    </div>
	  </div>
	</form>
        </div>
      </div>
