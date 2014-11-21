$('.alert .close').live("click", function(e) {
    $(this).parent().hide();
});

function delLList(from, idfrom, to, idto) {
    $.ajax({
        url: '/lldel/w/' + from + '/i/' + idfrom + '/o/' + to + '/t/' + idto,
        dataType: 'json',
        success: delSuccess,
        error: addError,
        cache: false
   });
}

function addLListR(from, idfrom, to, idinput) {
    $.ajax({
	url: '/lladd/w/' + from + '/i/' + idfrom + '/o/' + to + '/t/' + $(idinput).val() + '/r/1',
        dataType: 'json',
        success: addSuccess,
        error: addError,
        cache: false
   });
}

function addLList(from, idfrom, to, idinput) {
   $.ajax({
        url: '/lladd/w/' + from + '/i/' + idfrom + '/o/' + to + '/t/' + $(idinput).val() + '/r/0',
        dataType: 'json',
        success: addSuccess,
        error: addError,
        cache: false
   });
}

// LListlogin

function delSuccess(data, textStatus, jqXHR) {

  if (data['rc'] != 0) {
    $("#error-msg").text(data['msg']);
    $("#error-box").show();
  } else {
    $("#success-msg").text(data['msg']);
    $("#success-box").show();
    idname = '#LList' + data['llist'] + data['id'];
    $(idname).hide();
  }
}


function addSuccess(data, textStatus, jqXHR) {

  if (data['rc'] != 0) {
    $("#error-msg").text(data['msg']);
    $("#error-box").show();  
  } else {
    $("#success-msg").text(data['msg']);
    $("#success-box").show();
    res = jQuery.parseJSON(data['res']);
    llist = data['llist'];
    src = data['src'];
    srcid = data['srcid'];
    table = '#LList' + data['llist'] + 'Table > tbody:last';
    for(i=0; i<res.length; i++) {
      resd = jQuery.parseJSON(res[i]);
      resid = resd['id'];
      resvalue = resd['value'];
      newtr = '<tr id="LList' + llist + resid + '">';
      newtr = newtr + '<td>' + resvalue + '</td>';
      newtr = newtr + '<td><a href="#" onClick="delLList(\'' + src + '\', ' + srcid + ', \'' + llist + '\', ' + resid + ');">Remove</a></td>';
      newtr = newtr + '</tr>';
      $(table).append(newtr);
    }
  }
}

function addError(jqXHR, textStatus, errorThrown) {
  var msg = 'Insertion of a new Job has failed: ';
  if (errorThrown != '') {
    msg = msg + ' HTTP Error: ' + errorThrown;
  } else {
    msg = msg + jqXHR.responseText;
  }
  $("#error-msg").text(msg);
  $("#error-box").show();
}

