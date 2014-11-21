function saveRight(ugroup, right) {
  level=0;
  view_c = 'view_' + ugroup + '_' + right;
  add_c = 'add_' + ugroup + '_' + right;
  edit_c = 'edit_' + ugroup + '_' + right;
  del_c = 'del_' + ugroup + '_' + right;
  if (document.getElementById(view_c).checked) level += 1;
  if (document.getElementById(add_c).checked) level += 2;
  if (document.getElementById(edit_c).checked) level += 4;
  if (document.getElementById(del_c).checked) level += 8;
  $.ajax({
        url: '/right/u/' + ugroup + '/r/' + right + '/l/' + level,
        dataType: 'json',
        success: saveSuccess,
        error: saveError,
        cache: false
   });

}

function saveSuccess(data, textStatus, jqXHR) {
  if (data['rc'] != 0) {
    $("#error-msg").text(data['msg']);
    $("#error-box").show();
  } else {
    $("#success-msg").text(data['msg']);
    $("#success-box").show();
  }
}

function saveError(jqXHR, textStatus, errorThrown) {
  var msg = 'Failed to save right: ';
  if (errorThrown != '') {
    msg = msg + ' HTTP Error: ' + errorThrown;
  } else {
    msg = msg + jqXHR.responseText;
  }
  $("#error-msg").text(msg);
  $("#error-box").show();
}
