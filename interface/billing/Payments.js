function hideShowPaymentList(id) {

if ($('#containerDiv').contents().length == 0)
{
$('#loading-image').show();
    $.ajax({
    	type: "GET",
    	url: "payment_list.php",
    	dataType: 'html',
    	data: {payment_id: id},
		
    	success: function(html){
				 $('#loading-image').hide(); 
				  $("#containerDiv").html(html);
    	},
		complete: function(){
		}
    });
}
	else
	{
	   $("#containerDiv").html("");
	}
}