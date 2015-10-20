
function addJob(cla, fct, arg) {
  $.ajax({
	url: '/jobadd/c/' + cla + '/f/' + fct + '/a/' + arg,
        dataType: 'json',
        success: jobSuccess,
        error: jobError,
        cache: false
   });
}

function jobError(jqXHR, textStatus, errorThrown) {
  var msg = 'Insertion of a new Job has failed: ';
  if (errorThrown != '') {
    msg = msg + ' HTTP Error: ' + errorThrown;
  } else {
    msg = msg + jqXHR.responseText;
  }
  $("#error-msg").text(msg);
  $("#error-box").show();
}

function jobSuccess(data, textStatus, jqXHR) {

  if (data['rc'] != 0) {
    $("#error-msg").text(data['msg']);
    $("#error-box").show();  
  } else {
    href = ' (<a href="/view/w/job/i/' + data['id'] + '">'+data['id'] + '</a>)'
    $("#success-msg").html(data['msg'] + href);
    $("#success-box").show();
  }
}
