function updatePies(data) {
  window.pies = [];
  window.piesValues = [];
  for (var key in data) {
    if (key === 'length' || !data.hasOwnProperty(key)) continue;
    var pieValues = data[key];
    var pieData = [ ];
    for (var pk in pieValues) {
      if (pk === 'length' || !pieValues.hasOwnProperty(pk)) continue;
      var pv = pieValues[pk];
      pieData.push([ pk , Number(pv) ]);
    }
    window.pies.push(key);
    window.piesValues[key] = pieData;
  }
}

function lAction(fct, arg) {
   $.ajax({
        url: '/laction/w/' + fct + '/i/' + arg,
        dataType: 'json',
        success: lActionSuccess,
        error: lActionError,
        cache: false
   });
}

function lActionError(jqXHR, textStatus, errorThrown) {
  var msg = 'Action has failed: ';
  if (errorThrown != '') {
    msg = msg + ' HTTP Error: ' + errorThrown;
  } else {
    msg = msg + jqXHR.responseText;
  }
  $("#error-msg").text(msg);
  $("#error-box").show();
}

function coucou() {
    for (index = 0; index < window.pies.length; ++index) {
        console.log(window.pies[index]);
        $.jqplot (window.pies[index], [window.piesValues[window.pies[index]]],
          {
            seriesDefaults: {
              renderer: $.jqplot.PieRenderer,
              rendererOptions: {
                fill: false,
                sliceMargin: 4,
                lineWidth: 5,
                showDataLabels: true
              }
            },
            legend: { show:true, location: 'e' },
            grid: { drawGridLines:false, shadow:false, borderWidth:0.0 }
          }
        );
    }
}

function lActionSuccess(data, textStatus, jqXHR) {
  if (data['rc'] != 0) {
    $("#error-msg").text(data['msg']);
    $("#error-box").show();
  } else {
    var c = document.getElementById('actionModalBody');
    c.innerHTML = data['res']['html'];
    if (data['res']['pie']) {
      updatePies(data['res']['pie']);
      $('#actionModal').on('shown.bs.modal', coucou());
    }
    $('#actionModal').modal('show');
  }
}

$('body').on('hidden', '.modal', function() {
  $(this).removeData('modal');
 }
);
