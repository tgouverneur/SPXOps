$('.alert .close').live("click", function(e) {
    $(this).parent().hide();
});

function ackCheck(id) {
  $.ajax({
	url: '/ack/i/' + id,
        dataType: 'json',
        success: ackSuccess,
        error: ackError,
        cache: false
   });
}

function ackError(jqXHR, textStatus, errorThrown) {
  var msg = 'Acknowledge of the check result failed: ';
  if (errorThrown != '') {
    msg = msg + ' HTTP Error: ' + errorThrown;
  } else {
    msg = msg + jqXHR.responseText;
  }
  $("#error-msg").text(msg);
  $("#error-box").show();
}

function ackSuccess(data, textStatus, jqXHR) {

  if (data['rc'] != 0) {
    $("#error-msg").text(data['msg']);
    $("#error-box").show();  
  } else {
    $("#success-msg").text(data['msg']);
    $("#success-box").show();
    idtd = '#ackBtn' + data['id'];
    tdc = '<a href="/view/w/login/i/' + data['ackId'] + '">' + data['ackWho'] + '</a>';
    tdc = tdc + '<button type="button" class="close" onClick="nackCheck(' + data['ackId'] + ');">×</button>';
    $(idtd).empty().append(tdc);
  }
}
function nackCheck(id) {
  $.ajax({
	url: '/ack/n/1/i/' + id,
        dataType: 'json',
        success: nackSuccess,
        error: nackError,
        cache: false
   });
}

function nackError(jqXHR, textStatus, errorThrown) {
  var msg = 'Un-Acknowledge of the check result failed: ';
  if (errorThrown != '') {
    msg = msg + ' HTTP Error: ' + errorThrown;
  } else {
    msg = msg + jqXHR.responseText;
  }
  $("#error-msg").text(msg);
  $("#error-box").show();
}

function nackSuccess(data, textStatus, jqXHR) {

  if (data['rc'] != 0) {
    $("#error-msg").text(data['msg']);
    $("#error-box").show();  
  } else {
    $("#success-msg").text(data['msg']);
    $("#success-box").show();
    idtd = '#ackBtn' + data['id'];
    tdc = '<button type="button" class="btn btn-primary btn-mini" onClick="ackCheck(' + data['ackId'] + ');">Ack!</button>';
    $(idtd).empty().append(tdc);
  }
}
