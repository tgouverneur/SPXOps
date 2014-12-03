          <div class="row">
	    <div class="col-md-6">
	      <h2>Storage Statistics</h2>
		<dl class="dl-horizontal">
		  <dt>Total Raw Storage:</dt>
		  <dd><?php echo Pool::formatBytes($stats['storage_total']); ?> </dd>
                  <dt>Used Raw Storage:</dt>
                  <dd><?php echo Pool::formatBytes($stats['storage_used']); ?> </dd>
                  <dt>Free Raw Storage:</dt>
                  <dd><?php echo Pool::formatBytes($stats['storage_free']); ?> </dd>
                  <dt># Data Pools:</dt>
                  <dd><?php echo $stats['storage_nbp']; ?> </dd>
		</dl>
	    </div>
	    <div class="col-md-6">
              <h2>Hardware</h2>
                <dl class="dl-horizontal">
                  <dt># of CPU:</dt>
                  <dd><?php echo $stats['hw_nrcpu']; ?> </dd>
                  <dt># of Core:</dt>
                  <dd><?php echo $stats['hw_nrcore']; ?> </dd>
                  <dt>Total Memory:</dt>
                  <dd><?php echo Pool::formatBytes($stats['hw_memory'] * 1024 * 1024); ?> </dd>
                </dl>
            </div>
  	  </div>
	  <div class="row">
 	    <div class="col-md-6">
              <h2>Virtualization</h2>
                <dl class="dl-horizontal">
                  <dt># of VMs:</dt>
                  <dd><?php echo $stats['vm_nb']; ?> </dd>
                </dl>
            </div>
            <div class="col-md-4">
              <h2>Placeholder</h2>
              <ul>
              </ul>
	      <a class="btn" href="/list/w/results">More..</a>
            </div>
	  </div>
