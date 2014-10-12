$("#team").change(function() { //display or hide the appropriate fields for the team members
    if($(this).val()=="null") {
        $("#member3").fadeOut(400);
        $("#member2").fadeOut(400);
        $("#member1").fadeOut(400);
    }
    else if($(this).val()=="1") {
        $("#member3").fadeOut(400);
        $("#member2").fadeOut(400);
        $("#member1").fadeIn(400);
    }
    else if($(this).val()=="2") {
        $("#member3").fadeOut(400);
        $("#member2").fadeIn(400);
        $("#member1").fadeIn(400);
    }
    else {
        $("#member3").fadeIn(400);
        $("#member2").fadeIn(400);
        $("#member1").fadeIn(400);
    }
});

function readBlob() {
    var files = document.getElementById('files').files;
    if (!files.length) {
      alert('Please select a file!');
      return;
    }
    var file = files[0];
    var start = 0;
    var stop = file.size - 1;

    var reader = new FileReader();

    reader.onloadend = function(evt) {
      if (evt.target.readyState == FileReader.DONE) {
        $("#byte_content").fadeIn(0);
        $("#byte_content").text(evt.target.result);
        $("#code").val(evt.target.result);
        $('.prettyprinted').removeClass('prettyprinted');
        prettyPrint();
        $("#compile_question_select").fadeOut(0);
        $("#upload_form").fadeOut(0);
        $("#upload").fadeOut(0);
        $("#compile").fadeIn(0);
      }
    };

    var blob = file.slice(start, stop + 1);
    reader.readAsBinaryString(blob);
}
$("#upload").click(function() {
    var ext = $('#files').val().split('.').pop().toLowerCase();
    if($.inArray(ext, ['java']) == -1) {
        alert('Only Java files are supported!');
    } else {
        $('#upload').button('loading');
        readBlob(0, 0);
    }
});

$('#files').change(function() {
    $("#real_file").val($(this).val());
});
window.onbeforeunload = function() {$('.btn').button('reset'); $('form').each(function() { this.reset() });}
function onCompileResubmit() {
    $('#compile').button('reset');
    $('#upload').button('reset');
    $("input[type=text], textarea").val("");
    $("#output_container").fadeOut(0);
    $("#resubmit").fadeOut(0);
    $('#upload').fadeIn(0);
    $("#compile_question_select").fadeIn(0);
    $("#upload_form").fadeIn(0);
}
$('#compile').click(function(){
    $('#compile').button('loading');
    var code = 'code='+encodeURIComponent($('#code').val())+'&problem='+$('#compile_question_select').val();
    $.ajax({
        type: "POST",
        url: "/api/compile",
        data: code,
        success: function(data){
            $("#byte_content").fadeOut(0);
            $("#compile_legend").fadeOut(0);
            console.log(data);
            var d = eval('('+data+')');
            if(d.success!=undefined&&d.success=="true") {
                //$("#output").text(d.exec.output);
                prettyPrint();
                $('#compile').fadeOut(0);
                $("#output_container").fadeIn(0);
                $("#resubmit").fadeIn(0);
                $("#compile_success_alert").fadeIn(0);
            } else {
                $('#compile').fadeOut(0);
                $("#output_container").fadeIn(0);
                $("#resubmit").fadeIn(0);
                $("#output").fadeOut(0);
                $("#error_divider").fadeOut(0);
                $("#compile_danger_alert").fadeIn(0).append(d.error);
            }

        }
     });
});
$("#register-form").submit(function(e){
    e.preventDefault();
    $('#reg').button('loading');

    var dat = 'team='+encodeURIComponent($('#register-team').val())
    +'&password='+encodeURIComponent($('#register-password').val())
    +'&division='+encodeURIComponent($('[name=\'division\']:checked').val())
    +'&teamselect='+encodeURIComponent($('#team').val())
    +'&school='+encodeURIComponent($('#school').val())
    +'&member1='+encodeURIComponent($('#member1').val())
    +'&member2='+encodeURIComponent($('#member2').val())
    +'&member3='+encodeURIComponent($('#member3').val());
    $.post('/api/register', dat, function(data) {
        $('#reg').button('reset');
        var d = eval('('+data+')');
        if(d.error!=undefined) {
            $("#register_error_msg").fadeIn(0).html('<h4>Error!</h4>'+d.error);
        } else {
            $("#register_error_msg").fadeOut(0);
            $("#register-form").fadeOut(0);
            $("#register_success_msg").fadeIn(0);
        }
    });
});
$("#login-form").submit(function(e){
    e.preventDefault();
    $('#sign').button('loading');

    var dat = 'team='+encodeURIComponent($('#login-team').val())+'&password='+encodeURIComponent($('#login-password').val());
    $.post('/api/login', dat, function(data) {
        $('#sign').button('reset');
        console.log(data);
        var d = eval('('+data+')');
        if(d.error!=undefined) {
            $("#login_error_msg").fadeIn(0).html('<h4>Error!</h4>'+d.error);
        } else {
            location.reload();
        }
    });
});
$("#pizzaform input[type=text]").change(function () {
  var str = 0;
  $("#pizzaform input[type=text]").each(function () {
    if( !isNaN(parseInt($(this).val())))
        str += parseInt($(this).val(),10)*11;
      });
  $("#total").text((str>55)?"$69":"$"+str);
});
$("#order").click(function() {
    var dat = 'cheese='+encodeURIComponent($('#cheese').val()==""?0:parseInt($('#cheese').val()))
    +'&pepperoni='+encodeURIComponent($('#pepperoni').val()==""?0:parseInt($('#pepperoni').val()))
    +'&sausage='+encodeURIComponent($('#sausage').val()==""?0:parseInt($('#sausage').val()));
    $.post('/api/pizza', dat, function(data) {
        var d = eval('('+data+')');
        if(d.error==undefined) {
            $("#pizza_form").fadeOut(0);
            $("#cost_preview").fadeOut(0);
            $("#pizza_error").fadeOut(0);
            $("#order_div").fadeOut(0);
            $("#pizza_success").fadeIn(0).html('<h4>Success!</h4>'+d.success);
        } else {

            $("#pizza_error").fadeIn(0).html('<h4>Error!</h4>'+d.error);
        }
    });
});
if(navigator.userAgent.match(/MSIE/i)) {
    $(".main").remove();
    $(".badbrowsermsg").fadeIn(0);
}
function msToTime(duration) {var milliseconds = parseInt((duration%1000)/100), seconds = parseInt((duration/1000)%60), minutes = parseInt((duration/(1000*60))%60), hours = parseInt((duration/(1000*60*60))%24);hours = (hours < 10) ? "0" + hours : hours;minutes = (minutes < 10) ? "0" + minutes : minutes;seconds = (seconds < 10) ? "0" + seconds : seconds;return hours + ":" + minutes + ":" + seconds;}
$.getJSON( "/api/user/team", function(data) {
  if(data.team!="null") {
    var socket;
    try {
        socket = io.connect('http://'+document.domain+':8008');
    } catch(E) {
        alert("Unable to connect to server. You may still submit problems, but you will not receive a response until the server is back up. This means you shouldn't submit duplicate things.");
    }

    team = data.team;
    auth = data.auth;

    setInterval(function() {
        if(!socket.socket.connected) {
            //alert("Lost connection to the server. This page will attempt to connect every second.");
            var a = function(b) {
                socket.socket.reconnect();
                b();
            }
            a(function(){});

        }
    },1000);

    socket.on('connect', function() {
        socket.emit('team',{team:data.team, auth:data.auth});
        socket.emit('get_clars',{team:data.team, auth:data.auth});
        socket.emit('get_score', {team:data.team, auth:data.auth});
        socket.emit('get_subs',{team:data.team, auth:data.auth});
        socket.emit('advanced_scoreboard',{});
        socket.emit('novice_scoreboard',{});
    });


    socket.on('show_advanced_scoreboard', function(data) {
        console.log(data);
        $('#advanced_bdy').html();
        $.each(data, function(key, value,cnt) {
            $('#advanced_bdy').append('<tr>\
                                    <td class="sno"></td>\
                                    <td>'+value.team+'</td>\
                                    <td>'+value.score+'</td>\
                                </tr>');
        });
        $('#advanced_scoreboard').find('tr').not(':eq(0)').each(function(i){
            $(this).children('td:eq(0)').addClass('sno').text(i+1);
        });
    });
    socket.on('show_novice_scoreboard', function(data) {
        $('#novice_bdy').html();
        $.each(data, function(key, value,cnt) {
            $('#novice_bdy').append('<tr>\
                                    <td class="sno"></td>\
                                    <td>'+value.team+'</td>\
                                    <td>'+value.score+'</td>\
                                </tr>');
        });
        $('#novice_scoreboard').find('tr').not(':eq(0)').each(function(i){
            $(this).children('td:eq(0)').addClass('sno').text(i+1);
        });
    });
    socket.on('time', function(data) {
        if(data.time==0&&data.status=="stopped") {
            $('.disable_end').find('input, textarea, button, select').attr('disabled','disabled');
        } else if (data.time==7200000&&data.status=="stopped") {
            $('.disable_start').find('input, textarea, button, select').attr('disabled','disabled');
        } else if(data.status=="paused") {
            $('.disable_start').find('input, textarea, button, select').attr('disabled','disabled');
            $('.disable_end').find('input, textarea, button, select').attr('disabled','disabled');
            $('.disable_in').find('input, textarea, button, select').attr('disabled','disabled');
        }
        else {
            $('.disable_start').find('input, textarea, button, select').removeAttr('disabled');
            $('.disable_end').find('input, textarea, button, select').removeAttr('disabled');
            $('.disable_in').find('input, textarea, button, select').attr('disabled','disabled');
        }
        $("#time").html(msToTime(data.time));
    });
    socket.on('clarifications', function(data){
        if(data.length!=0)
            $('#clar_box').empty();
        $.each(data, function(key, value) {
            $('#clar_box').append('\
                <div class="well submission" id="clar'+value.id+'"> \
                <div class="noselect_clar" style="width:100%;"><h4 style="display:inline">ID #<span class="run_id">'+value.id+' for Problem #'+value.problem+'</span><div class="'+(value.reply==""?"fail":"success")+'" style="float:right">'+(value.reply==""?"No Reply":"Replied"+(value.global=='yes'?" (GLOBAL)":""))+'</div></h4></div> \
                <div id="clar'+value.id+'detail" class="detail" style="display:none"><br><br><p>Your message:</p><blockquote>'+nl2br(value.message,false)+'</blockquote><p>Judge\'s response:</p><blockquote>'+(value.reply==""?"No Reply":value.reply)+'</blockquote></div></div> \
            ');
        });
        $(".noselect_clar").click(function() {
            var a = "#"+$(this).parent().attr('id')+"detail";
            $(a).toggle();
        });
    });
    socket.on('submissions', function(data){
        if(data.length!=0)
            $('#sub_box').empty();
        $.each(data, function(key, value) {
           $('#sub_box').append('<div class="well submission disable_start disable_in" id="sub'+value.subid+'"><div class="noselect_sub" style="width:100%;"><h4 style="display:inline">Problem #<span class="run_id">'+value.problem+' ('+value.subid+')</span><div class="'+(value.success=="Yes"?"success":"fail")+'" style="float:right">'+(value.success=="No"?value.error=="None"?"Incorrect":value.error:"Success")+'</div></h4></div><div id="sub'+value.subid+'detail" class="detail" style="display:none"><br><div class="row"><div class="col-lg-6"><legend style="font-size:16px">Your Output:</legend><pre id="sub'+value.subid+'toutput">'+(value.output==""?"Not available yet, contest is still running.":value.output)+'</pre></div><div class="col-lg-6"><legend style="font-size:16px">Judge\'s Output:</legend><pre id="sub'+value.subid+'routput">'+(value.real_output==""?"Not available yet, contest is still running.":value.real_output)+'</pre></div></div><br><legend></legend>'+(value.appealed=="Yes"||value.success=="Yes"?'<span style="color:#aaa;font-size:11px">You can\'t appeal, because you either got it right or you have already appealed.</span>':'<span style="color:#aaa;font-size:11px">Output matches judges output? Submit an </span><button class="btn btn-danger btn-small appeal_btn" id="'+value.subid+'">Appeal</button></span></div>')+'</div>');
        });
        $(".appeal_btn").click(function(){
            socket.emit('appeal', {team: team, auth:auth,id:$(this).attr('id')});
        });
        $(".noselect_sub").click(function() {
            var a = "#"+$(this).parent().attr('id')+"detail";
            $(a).toggle();
        });
        socket.emit('get_score', {team:data.team, auth:data.auth});
    });
    socket.on('refresh', function (data) {
        location.reload();
    });
    socket.on('score', function (data) {
        $("#prog_score").html(data.score);
    });
    socket.on('soft_refresh', function (d) {
        socket.emit('get_clars',{team:data.team, auth:data.auth});
        socket.emit('get_subs',{team:data.team, auth:data.auth});
        socket.emit('get_score', {team:data.team, auth:data.auth});
        socket.emit('advanced_scoreboard',{});
        socket.emit('novice_scoreboard',{});
    });
    $(".clar_submit").click(function(){
        socket.emit('clarification', {auth: data.auth,from:data.team, problem:$("#clar_question_select").val(), message: $("#clarification_message").val()});
    });
  }
});

function nl2br (str, is_xhtml) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br ' + '/>' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}
