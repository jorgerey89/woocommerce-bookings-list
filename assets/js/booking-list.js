jQuery(document).ready(function($) {


	
	 $('#pickerfrom').datepicker({
		format: "yyyy-mm-dd",
		autoclose: true,
		todayHighlight: true,
		todayBtn: "linked"
    });
	
		
	$('#btnPrint').click(function () {   

		var doc = new jsPDF('p', 'pt');
		var elem = document.getElementById("pdf");
		var res = doc.autoTableHtmlToJson(elem);
		doc.autoTable(res.columns, res.data);
		doc.save("booking-list.pdf");
		
		
	});

});