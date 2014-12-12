$(document).ready(function(){
	
	var s, p = {
		country:"",
		type:"",
		aggregation:"None",
		filters:{}
	}

	$('#country').val("-----")

	$('#country').on('change', function(){
		
		var $blank = $('#blank_country_option')
		if ($blank.length){ 
			$blank.remove() 
		}

		p.country = $(this).val()


		switch (p.country) {
			case 'Malawi':
			case 'Nepal':
			case 'Uganda':
				p.type = "old"
				break
			case 'Senegal':
				p.type = "new"
				break
		}

	})


	$('#load button').click(function(){

		var agg_list = ["donors", "ad_sector_names", "status", ]
		// if (p.type == "new") {
		// 	agg_list = ["donors", "ad_sector_names", "status", ]
		// } else {
		// 	agg_list = ["donors", "ad_sector_names", "status"]
		// }

		process({call:"fields", country:p.country, type:p.type}, function (result){
			console.log(result)
		})

		process({call:"options", country:p.country, type:p.type}, function (result){
			console.log(result)
		})

	})

	$('#submit').click(function(){
		
		s = p

		process({call:"build", country:s.country}, function (result){
			console.log(result)
		})

	})

	// generic ajax call to process.php
	function process(data, callback) {
		$.ajax ({
	        url: "process.php",
	        data: data,
	        dataType: "json",
	        type: "post",
	        async: false,
	        success: function (result) {
			    callback(result)
			}
	    })
	}

})
