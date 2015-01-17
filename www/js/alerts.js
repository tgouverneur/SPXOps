function saveATS(ugroup, at) {
  level=0;
  ena = 'at_' + ugroup + '_' + at;
  if (document.getElementById(ena).checked) level = 1;
  $.ajax({
        url: '/alert/u/' + ugroup + '/a/' + at + '/e/' + level,
        dataType: 'json',
        success: saveAlertSuccess,
        error: saveAlertError,
        cache: false
   });

}

function saveATG(ugroup, ag) {
  level=0;
  ena = 'sg_' + ugroup + '_' + ag;
  if (document.getElementById(ena).checked) level = 1;
  $.ajax({
        url: '/alert/u/' + ugroup + '/g/' + ag + '/e/' + level,
        dataType: 'json',
        success: saveAlertSuccess,
        error: saveAlertError,
        cache: false
   });

}

function saveAlertSuccess(data, textStatus, jqXHR) {
  if (data['rc'] != 0) {
    $("#error-msg").text(data['msg']);
    $("#error-box").show();
  } else {
    $("#success-msg").text(data['msg']);
    $("#success-box").show();
  }
}

function saveAlertError(jqXHR, textStatus, errorThrown) {
  var msg = 'Failed to save alert: ';
  if (errorThrown != '') {
    msg = msg + ' HTTP Error: ' + errorThrown;
  } else {
    msg = msg + jqXHR.responseText;
  }
  $("#error-msg").text(msg);
  $("#error-box").show();
}
