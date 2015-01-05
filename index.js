$(document).ready(function(){
	
	var s, p = {
		country:"",
		type:"",
		aggregation:"None",
		filters:[],
		options:[],
		query:""
	};

	var andor = " || ";

	$('#country').val("-----");

	$('#country').on('change', function(){
		
		var $blank = $('#blank_country_option');
		if ($blank.length){ 
			$blank.remove(); 
		}

		p.country = $(this).val();


		switch (p.country) {
			case 'Malawi':
			case 'Nepal':
			case 'Uganda':
				p.type = "old";
				break;
			case 'Senegal':
				p.type = "new";
				break;
		}

	})


	$('#load button').click(function(){

		$(".blank").each(function(){
			$(this).remove();
		})


		var fields;
		process({call:"fields", country:p.country, type:p.type}, function (result){
			console.log(result);
			fields = result;
		})

		$('#aggregate').empty();
		var agg_list = ["donors", "ad_sector_names", "status", ];

		var agg_html = "";
	    for (var i=0, ix=agg_list.length; i<ix; i++) {
			agg_html += '<option value="'+ agg_list[i] +'">'+ agg_list[i] +'</option>';
	    }   

        $('#aggregate').append(agg_html);

		$('#filter').empty();
		$('#values').empty();
        var field_html = "";
	    for (var i=0, ix=fields.length; i<ix; i++) {
			field_html += '<label class="field"><input type="checkbox" value="'+fields[i]+'">'+fields[i]+'</label><br>';
		}
		$('#filter').append(field_html);

		$('.field input:checkbox').on('change', function(){
			console.log("filter change");

			var field = $(this).val();
			if ( $(this).prop('checked') ) {
				console.log('on')
				var options;
				process({call:"options", country:p.country, type:p.type, field:field}, function (result){
					console.log(result);
					options = _.values(result).sort();
				})

				// add field as group to filter values and populate with options
				var options_html = '<div id="'+field+'"><span class="group">'+field.toUpperCase()+'</span>';

	   	 		for (var i=0, ix=options.length; i<ix; i++) {

	   	 			options_html += '<label class="option"><input type="checkbox" value="'+options[i]+'">'+options[i]+'</label><br>';

	   	 		}
	   	 		options_html += "</div>";

	   	 		$('#values').append(options_html);


			} else {
				console.log('off')

				// remove field and options from filter values
				$('#'+field).remove();
			}

		})
		

	})



	$('#submit').click(function(){
		p.aggregation = $('#aggregate').val();

		p.filters = [];
		p.options = [];
		p.query = 	"function() { return (";
		var first = true;

		$('#values input:checkbox:checked').each(function(){
			console.log( $(this).parent().parent().attr('id') +" "+ $(this).val() );

			var filter =  $(this).parent().parent().attr('id');
			var value = $(this).val();
			var index = _.indexOf(p.filters, filter);

			if (index == -1) {
				p.filters.push(filter);
				p.options.push([value]);
			} else {
				if (_.indexOf(p.options[index], value) == -1) {
					p.options[index].push(value);
				}
			}

			var part = 'this.'+filter+' == '+value+' || this.'+filter+' == "'+value+'"';

			p.query += (first == true ? part : andor + part);

			first = false;

		})

		p.query += ")}";

		s = p;

		console.log(s)

		process({call:"build", country:s.country, type:s.type, query:s.query, filters:s.filters, options:s.options}, function (result){
			console.log(result);
			$('#download').empty();
			$('#download').append('<a href="http://128.239.119.254/aiddata/DAT/data/'+result+'.csv">Download CSV</a>');
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
			    callback(result);
			}
	    })
	}

})
