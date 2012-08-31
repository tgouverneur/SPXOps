$(function(){
        $("select#selectOS").change(function(){
                $.getJSON("/rpc",{w: "server", s: "fk_os", i: $(this).val()}, function(j){
                        var options = '';
                        for (var i = 0; i < j.length; i++) {
                                options += '<option value="' + j[i].id + '">' + j[i].hostname + '</option>';
                        }
                        $("#selectNodes").html(options);
                })
        })
})
