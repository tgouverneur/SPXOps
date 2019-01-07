<?php
if (!isset($a_cat)) $a_cat = array();
?>
	<div class="page-header"><h1>Settings</h1></div>
        <form method="POST" role="form" action="/settings" class="form-horizontal">
	<ul class="nav nav-tabs" id="settingsTab">
<?php $i=0; foreach($a_cat as $cat) { ?>
	  <li <?php if (!$i++) { echo 'class="active"'; } ?>><a href="#<?php echo $cat; ?>" data-toggle="tab"><?php echo ucfirst($cat); ?></a></li>
<?php } ?>
	</ul>
	<div class="tab-content" id="settingsTabContent">
<?php $i=0; foreach($a_cat as $cat) { $a_setting = Setting::getSettings($cat); ?>
	  <div class="tab-pane<?php if (!$i++) { echo ' active'; } ?>" id="<?php echo $cat; ?>">
	<?php foreach($a_setting as $s) { ?>
          <div class="form-group">
            <label class="col-sm-4 col-sm-offset-1 control-label" for="input<?php echo $s->cat.'_'.$s->name; ?>"><?php echo $s->textname; ?></label>
            <div class="col-sm-3">
              <input class="form-control" type="text" name="<?php echo $s->cat.'_'.$s->name; ?>" value="<?php echo $s->value; ?>" id="input<?php echo $s->cat.'_'.$s->name; ?>" placeholder="<?php echo $s->placeholder; ?>">
            </div>
          </div>
	<?php } ?>
	  </div>
<?php } ?>
	</div>
	  <div class="form-group">
	    <div class="col-sm-3 col-sm-offset-5">
	      <button type="submit" name="submit" value="1" class="btn btn-primary">Save</button>
	    </div>
	  </div>
	</form>
