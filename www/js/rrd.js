function getRRDData(id) {
   var data = { start: "NOW", what: "default", n: 30 };
   $.ajax({
        type: 'POST',
        url: '/rrd/i/' + id,
	data: data,
        dataType: 'json',
        success: UpdateRRDSuccess,
        error: UpdateRRDError,
        cache: false
   });
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
    var plot1 = window.chartPlot1;
    if (plot1) {
      plot1.destroy();
    }
    plot1.series[0].data = data['res']['values']; 
    window.options.axes.xaxis.min = data['res']['values'][0][0][0];
    window.options.axes.xaxis.max = data['res']['values'][0][data['res']['values'][0].length-1][0];
    window.options.series = data['res']['labels'];
    plot1 = $.jqplot ('chart', data['res']['values'],window.options);
    window.chartPlot1 = plot1;
  }
}

