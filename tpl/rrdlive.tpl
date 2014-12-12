	<div class="page-header"><h1>Live RRD</h1></div>
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
          </div>
          <div class="col-md-4">
	    <ul class="nav nav-pills nav-stacked">
              <li class="dropdown active">
                <a class="dropdown-toggle" data-toggle="dropdown" href="#">Action <b class="caret"></b></a>
                <ul class="dropdown-menu">
                  <li><a href="#" onClick="addGraph();">Add Graph</a></li>
                </ul>
              </li>
            </ul>
	  </div>
	</div>
        <div class="row">
          <div id="chart_1" class="col-md-8 graphdiv">
          </div>
          <div id="chart_1_options" class="col-md-4" style="display:none;">
	      <select class="form-control input-sm" id="chart_1_sg">
                <option value="-1">Load a saved graph</option>
              </select>
	      <select class="form-control input-sm" id="chart_1_srv">
                <option value="-1">Choose a Server</option>
              </select>
	      <select class="form-control input-sm" id="chart_1_rrd">
                <option value="-1">Choose a Source</option>
              </select>
	      <select class="form-control input-sm" id="chart_1_met">
                <option value="-1">Choose a Metric</option>
              </select>
              <div class="form-inline row">
                <div class="form-group col-sm-3">
		  <div class="input-group">
	            <button type="submit" onClick="addMet(1);" name="submit" class="btn btn-sm btn-primary">Add / Load</button>
   	          </div>
   	        </div>
                <div class="form-group col-sm-6">
                  <div class="input-group">
                    <input type="text" class="form-control input-sm" id="chart_1_name" placeholder="Graph Name">
                  </div>
                </div>
                <div class="form-group col-sm-3">
                  <div class="input-group">
                    <button type="submit" onClick="saveGraph(1);" name="submit" class="btn btn-sm btn-primary">Save!</button>
                  </div>
                </div>
              </div>
          </div>
	</div>
        <div class="row">
          <div id="chart_2" class="col-md-8 graphdiv">
          </div>
          <div id="chart_2_options" class="col-md-4" style="display:none;">
              <select class="form-control input-sm" id="chart_2_sg">
                <option value="-1">Load a saved graph</option>
              </select>
              <select class="form-control input-sm" id="chart_2_srv">
                <option value="-1">Choose a Server</option>
              </select>
              <select class="form-control input-sm" id="chart_2_rrd">
                <option value="-1">Choose a Source</option>
              </select>
              <select class="form-control input-sm" id="chart_2_met">
                <option value="-1">Choose a Metric</option>
              </select>
              <div class="form-inline row">
                <div class="form-group col-sm-3">
                  <div class="input-group">
                    <button type="submit" onClick="addMet(2);" name="submit" class="btn btn-sm btn-primary">Add / Load</button>
                  </div>
                </div>
                <div class="form-group col-sm-6">
                  <div class="input-group">
                    <input type="text" class="form-control input-sm" id="chart_2_name" placeholder="Graph Name">
                  </div>
                </div>
                <div class="form-group col-sm-3">
                  <div class="input-group">
                    <button type="submit" onClick="saveGraph(2);" name="submit" class="btn btn-sm btn-primary">Save!</button>
                  </div>
                </div>
              </div>
          </div>
        </div>
        <div class="row">
          <div id="chart_3" class="col-md-8 graphdiv">
          </div>
          <div id="chart_3_options" class="col-md-4" style="display:none;">
              <select class="form-control input-sm" id="chart_3_sg">
                <option value="-1">Load a saved graph</option>
              </select>
              <select class="form-control input-sm" id="chart_3_srv">
                <option value="-1">Choose a Server</option>
              </select>
              <select class="form-control input-sm" id="chart_3_rrd">
                <option value="-1">Choose a Source</option>
              </select>
              <select class="form-control input-sm" id="chart_3_met">
                <option value="-1">Choose a Metric</option>
              </select>
              <div class="form-inline row">
                <div class="form-group col-sm-3">
                  <div class="input-group">
                    <button type="submit" onClick="addMet(3);" name="submit" class="btn btn-sm btn-primary">Add / Load</button>
                  </div>
                </div>
                <div class="form-group col-sm-6">
                  <div class="input-group">
                    <input type="text" class="form-control input-sm" id="chart_3_name" placeholder="Graph Name">
                  </div>
                </div>
                <div class="form-group col-sm-3">
                  <div class="input-group">
                    <button type="submit" onClick="saveGraph(3);" name="submit" class="btn btn-sm btn-primary">Save!</button>
                  </div>
                </div>
              </div>
          </div>
        </div>
       </div>
      <script class="code" type="text/javasocript">
        $('.alert .close').on('click', function() {
          $(this).parent().hide();
        });
      </script>
