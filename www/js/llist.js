$('.alert .close').live("click", function(e) {
    $(this).parent().hide();
});

function delLListReverse(from, idfrom, to, idto) {
    $.ajax({
        url: '/lldel/w/' + from + '/i/' + idfrom + '/o/' + to + '/t/' + idto,
        dataType: 'json',
        success: delSuccessReverse,
        error: addError,
        cache: false
   });
}

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

function addLListReverse(from, inputfrom, to, idto) {
   $.ajax({
        url: '/lladd/w/' + from + '/i/' + $(inputfrom).val() + '/o/' + to + '/t/' + idto + '/r/0',
        dataType: 'json',
        success: addSuccessReverse,
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

function delSuccessReverse(data, textStatus, jqXHR) {

  if (data['rc'] != 0) {
    $("#error-msg").text(data['msg']);
    $("#error-box").show();
  } else {
    $("#success-msg").text(data['msg']);
    $("#success-box").show();
    idname = '#LList' + data['llist'] + data['idr'];
    $(idname).hide();
  }
}

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


function addSuccessReverse(data, textStatus, jqXHR) {

  if (data['rc'] != 0) {
    $("#error-msg").text(data['msg']);
    $("#error-box").show();
  } else {
    $("#success-msg").text(data['msg']);
    $("#success-box").show();
    llist = data['llist'];
    srcid = data['srcid'];
    srcname = data['srcname'];
    table = '#LList' + llist + 'Table > tbody:last';
    newtr = '<tr id="LList' + llist + srcid + '">';
    newtr = newtr + '<td>' + srcname + '</td>';
    newtr = newtr + '<td><a href="#" onClick="delLListReverse(\'' + data['src'] + '\', ' + srcid + ', \'' + llist + '\', ' + data['addid'] + ');">Remove</a></td>';
    newtr = newtr + '</tr>';
    $(table).append(newtr);
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

