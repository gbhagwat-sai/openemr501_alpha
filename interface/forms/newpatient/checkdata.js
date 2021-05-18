$(document).ready(function() {
	$("#add_cpt").live('click',function() {
		var data = $("#new_encounter").serialize();
		$.ajax({
		  url: "fee_sheet_ajax.php",
		  type: "post",
		  data: data,
		  success: function(){
			  alert("success");
			   $("#result").html('submitted successfully');
		  },
		  error:function(){
			  alert("failure");
			  $("#result").html('there is error while submit');
		  }   
    	});
		$("#reloadDiv").load(' #reloadDiv');
	});
 });