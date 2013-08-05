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
      	$("#byte_content").fadeIn(600);
        $("#byte_content").text(evt.target.result);
        $("#code").val(evt.target.result);
        prettyPrint();
        $("#files").fadeOut(0);
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
window.onbeforeunload = function() {$('.btn').button('reset'); $('form').each(function() { this.reset() });}
function onCompileResubmit() {
	$('#compile').button('reset');
	$('#upload').button('reset');
	window.location.reload();	
}
$('#compile').click(function(){
	$('#compile').button('loading');
    var code = 'code='+encodeURIComponent($('#code').val());
    $.ajax({
        type: "POST",
        url: "/api/compile",
        data: code,
        success: function(data){
        	$("#byte_content").fadeOut(0);
        	$("#compile_legend").fadeOut(0);
        	var d = eval('('+data+')');
        	if(d.compile!=undefined&&d.exec!=undefined&&d.compile.success=="true"&&d.exec.success=="true") {
        		$("#output").text(d.exec.output);
	    		prettyPrint();
	    		$('#compile').fadeOut(0);
	        	$("#output_container").fadeIn(600);
	        	$("#resubmit").fadeIn(600);
	        	$("#compile_success_alert").fadeIn(600).append("Your program ran for " + parseFloat(d.exec.time) + "s.");
        	} else {
        		$('#compile').fadeOut(0);
	        	$("#output_container").fadeIn(600);
	        	$("#resubmit").fadeIn(600);
	        	$("#output").fadeOut(0);
	        	$("#error_divider").fadeOut(0);
	        	$("#compile_danger_alert").fadeIn(600).append(d.error);
        	}
	        	
        }
     });
});
/*!function ($) {
    $(function(){
    	window.prettyPrint && prettyPrint()
    })
}(window.jQuery)*/