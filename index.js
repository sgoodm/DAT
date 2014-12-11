$(document).ready(function(){

	$('#country').val("-----")

	$('#country').on('change', function(){
		
		var $blank = $('#blank_country_option')
		if ($blank.length){ 
			$blank.remove() 
		}

		var country = $(this).val()
		
		var csv = "data/" + country + ".csv"
		console.log(csv)

	    var request = $.ajax({
	    	type: "GET",
			dataType: "text",
			url: csv,
			async: false,
	    })
	    
	    var content = request.responseText

		console.log(content)

		var data = $.csv.toArrays(content)
		console.log(data)
		

	})


})
