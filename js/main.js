function bindEvents() {
	$("#request_form").change(function(){
		updateHashDigest();
  });

	$("#request_form").keyup(function(){
		updateHashDigest();
	});

	$("#update_trx_datetime").click(function() {
		updateTrxDateTime();
	});
}

function updateHashDigest() {
  var requestData = $("#request_form").serialize();
	var responseData = {};
	var hashDigestElement = document.getElementById("HashDigest");
	var errorMsg = "";
	$.ajax({
		type: "post",
		url: "index.php/hashdigest",
		timeout: 10000,
		cache: false,
		data: requestData,
		success: function(jsonData) {
			try
			{
				responseData = JSON.parse(jsonData);
				if ( ! responseData.hasOwnProperty("HashDigest"))
				{
					console.log("HashDigest not found");
				}
			}
			catch (e)
			{
				console.log("Unexpected response received: " + jsonData);
			}
			hashDigestElement.setAttribute("value", responseData.HashDigest);
		},
		error: function(XMLHttpRequest) {
			errorMsg = XMLHttpRequest.status + " " + XMLHttpRequest.statusText;
			console.log("XMLHttpRequest error: " + errorMsg);
		}
	});
}

function updateTrxDateTime() {
	var date = getTime(new Date());
	document.getElementById("TransactionDateTime").setAttribute("value", date);
	updateHashDigest();
}

function getTime(date) {
	return date.getFullYear() + "-" +
		("0" + (date.getMonth()+1)).slice(-2) + "-" +
		("0" + (date.getDate())).slice(-2) + " " +
		("0" + (date.getHours())).slice(-2) + ":" +
		("0" + (date.getMinutes())).slice(-2) + ":" +
		("0" + (date.getSeconds())).slice(-2) + " " +
		getTimeZone(date);
}

function getTimeZone(date) {
	var offset = date.getTimezoneOffset(), o = Math.abs(offset);
	return (offset > 0 ? "-" : "+") +
		("00" + Math.floor(o / 60)).slice(-2) + ":" +
		("00" + (o % 60)).slice(-2);
}

$(document).ready(function() {
	bindEvents();
	updateTrxDateTime();
});
