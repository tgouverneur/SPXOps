$('.alert .close').live("click", function(e) {
    $(this).parent().hide();
});

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
    $("#success-msg").text(data['msg']);
    $("#success-box").show();
  }
}
