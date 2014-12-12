function saveGraph(gid) {
  var gmet = 'elem'+gid;
  var gname = 'chart_'+gid+'_name';
  var graphname = $('#'+gname).val();
  
   var data = { name: graphname, mets: window[gmet] };
   $.ajax({
        type: 'POST',
        url: '/rpc/w/saveslr',
	data: data,
        dataType: 'json',
        success: SavedGraph,
        error: SavedGraphError,
        cache: false
   });
}

function addMet(gid) {
  // build the Metric id to add
  gmet = 'elem' + gid;
  var sgid = $('#chart_' + gid + '_sg').val();
  if (sgid != "") {
    $.ajax({
	  type: 'GET',
	  url: '/rpc/w/slr/i/' + sgid,
	  dataType: 'json',
          success: function(data) { window[gmet] = data.a_def; console.log(data.a_def); },
          error: UpdateRRDError,
          cache: false
     });
    return;
  }
  var sid = $('#chart_' + gid + '_srv').val();
  var rid = $('#chart_' + gid + '_rrd').val();
  var mname = $('#chart_' + gid + '_met').val();
  if (sid == '' || rid == '' || mname == '') {
    return;
  }
  window[gmet].push([ sid, rid, mname ]);
}

function SavedGraph() {
  $("#success-msg").text('Graph is now saved!');
  $("#success-box").show();
}

function addGraph() {
  if (window.cID >= 3) {
    $("#error-msg").text('Maximum graph is currently limited to 3.');
    $("#error-box").show();
  }
  window.cID++;
  divname = 'chart_' + window.cID;
  optname = 'chart_' + window.cID + '_options';
  plotname = 'plot' + window.cID;
  elements = 'elem' + window.cID;

  var dataChart = [ [0,0] ];
  var dataLabels = [ [0,0] ];
  window.options = {
    axes: {
      xaxis: {
        numberTicks: 4,
        //renderer:$.jqplot.LogAxisRenderer,
        //tickDistribution:'power'
        //tickOptions:{formatString:'%s'},
        min : dataChart[0][0],
        max: dataChart[dataChart.length-1][0]
      },
      yaxis: {
        min:0,
        //max: 1,
        //numberTicks: 6,
        //tickOptions:{formatString:'%.1f'}
      }
    },
    seriesDefaults: {
      rendererOptions: { smooth: true}
    },
    legend: { show:true, location: 'e' },
    grid: { background:"#ffffff", drawGridLines:false, shadow:false, borderWidth:0.0 },
  };
  $.jqplot.config.enablePlugins = true;
  window[plotname] = $.jqplot(divname, [dataLabels, dataChart], window.options);
  window[elements] = [];

  $.ajax({
	  type: 'GET',
	  url: '/rpc/w/lslr',
	  dataType: 'json',
          success: function(data) { var sel = $('#chart_' + window.cID + '_sg'); sel.empty(); sel.append('<option value="" selected>Choose a Saved Graph</option>'); for (var i=0; i<data.length; i++) { sel.append('<option value="' + data[i].id + '">' + data[i].name + '</option>'); } },
          error: UpdateRRDError,
          cache: false
   });

  $.ajax({
	  type: 'GET',
	  url: '/rpc/w/lserver/o/rrd',
	  dataType: 'json',
          success: function(data) { var sel = $('#chart_' + window.cID + '_srv'); sel.empty(); sel.append('<option value="" selected>Choose a Server</option>'); for (var i=0; i<data.length; i++) { sel.append('<option value="' + data[i].id + '">' + data[i].hostname + '</option>'); } },
          error: UpdateRRDError,
          cache: false
   });

  $('#chart_' + window.cID + '_srv').change(function() {
    getRRDList(window.cID);
  });

  $('#'+optname).show();

  setInterval(function() { getGRRDData(window.cID); }, 1000);
}

function getRRDList(id) {
  $.ajax({
          type: 'GET',
          url: '/rpc/w/lrrd/i/' + $('#chart_' + window.cID + '_srv').val(),
          dataType: 'json',
          success: function(data) { var sel = $('#chart_' + window.cID + '_rrd'); sel.empty(); sel.append('<option value="" selected>Choose a Source</option>'); for (var i=0; i<data.length; i++) { sel.append('<option value="' + data[i].id + '">' + data[i].type + '/' + data[i].name + '</option>'); } },
          error: UpdateRRDError,
          cache: false
   });

  $('#chart_' + window.cID + '_rrd').change(function() {
    getMETList(window.cID);
  });

}

function getMETList(id) {
  $.ajax({
          type: 'GET',
          url: '/rpc/w/lmet/i/' + $('#chart_' + window.cID + '_rrd').val(),
          dataType: 'json',
          success: function(data) { var sel = $('#chart_' + window.cID + '_met'); sel.empty(); sel.append('<option value="" selected>Choose a Metric</option>'); for (var i=0; i<data.length; i++) { sel.append('<option value="' + data[i].name + '">' + data[i].value + '</option>'); } },
          error: UpdateRRDError,
          cache: false
   });

}

function getGRRDData(gid) {
   var gm = 'elem' + gid;
   if (window[gm].length < 1) {
     return;
   }
   var data = { start: "NOW", what: "default", n: 30, cid: gid, mets: window[gm] };
   $.ajax({
        type: 'POST',
        url: '/rrd/i/group',
	data: data,
        dataType: 'json',
        success: UpdateRRDSuccess,
        error: UpdateRRDError,
        cache: false
   });
}

function SavedGraphError(jqXHR, textStatus, errorThrown) {
  var msg = 'SavedGraph failed: ';
  if (errorThrown != '') {
    msg = msg + ' HTTP Error: ' + errorThrown;
  } else {
    msg = msg + jqXHR.responseText;
  }
  $("#error-msg").text(msg);
  $("#error-box").show();
}


function UpdateRRDError(jqXHR, textStatus, errorThrown) {
  var msg = 'Action has failed: ';
  if (errorThrown != '') {
    msg = msg + ' HTTP Error: ' + errorThrown;
  } else {
    msg = msg + jqXHR.responseText;
  }
  $("#error-msg").text(msg);
  $("#error-box").show();
}


function UpdateRRDSuccess(data, textStatus, jqXHR) {
  if (data['rc'] != 0) {
    $("#error-msg").text(data['msg']);
    $("#error-box").show();
  } else {
    divname = 'chart_' + data['cid'];
    plotname = 'plot' + data['cid'];
    var plot = window[plotname];

    if (plot) {
      plot.destroy();
    }
    plot.series[0].data = data['res']['values']; 
    window.options.axes.xaxis.min = data['res']['values'][0][0][0];
    window.options.axes.xaxis.max = data['res']['values'][0][data['res']['values'][0].length-1][0];
    window.options.series = data['res']['labels'];
    plot = $.jqplot (divname, data['res']['values'], window.options);
    window[plotname] = plot1;
  }
}

$(document).ready(function(){ window.cID = 0; });
