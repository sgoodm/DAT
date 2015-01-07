$(document).ready(function(){

	var page_url = "http://128.239.119.254/aiddata/DAT";	

	var s, p = {
		status:0,
		country:"",
		type:"",
		aggregate:"none",
		subaggregate:"none",
		filters:[],
		options:[],
		// query:"",
		request:0,
		start_year:2000,
		end_year:2012
	};

	var andor = " || ";

	var agg_list = ["none", "donors", "ad_sector_names", "status", ];



	// init year slider object
	$('#slider').dragslider({
		animate: true,
		range: true,
		rangeDrag: true,
		min:1950,
		max:2050,
		step: 1,
		values: [p.start_year, p.end_year]
	}); 
 	
 	// init slider years ui
    var v = $("#slider").dragslider("values")
    $('#slider_value').text(v[0]+" - "+v[1]);
    var min = $('#slider').dragslider('option', 'min')
    var max = $('#slider').dragslider('option', 'max')
    $('#slider_min').text(min);
    $('#slider_max').text(max);

    // slider events
    $('#slider').dragslider({
    	slide: function (event, ui) {
	    	v = ui.values
	        $('#slider_value').text(v[0]+" - "+v[1]);
	   	},
    	change: function (event, ui) {
	        p.start_year = $("#slider").dragslider("values")[0]
	    	p.end_year = $("#slider").dragslider("values")[1]
    	}
    });



	$('#country').val("-----");

	$('#country').on('change', function(){

		var $blank = $('#blank_country_option');
		if ($blank.length){ 
			$blank.remove(); 
		}

		p.status = 1;
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

		var fields, agg_html, field_html;

		if (p.country == "") {
			console.log("please select a country");
			return;
		}

		p.status = 2;

		$(".blank").each(function(){
			$(this).remove();
		})


		process({call:"fields", country:p.country, type:p.type}, function (result){
			console.log(result);
			fields = result;
		})

		$('#aggregate').empty();

		agg_html = "";
	    for (var i=0, ix=agg_list.length; i<ix; i++) {
			agg_html += '<option value="'+ agg_list[i] +'">'+ agg_list[i] +'</option>';
	    }

	    agg_html += '<option id="agg_geography" value="geography" disabled>geography</option>';

        $('#aggregate').append(agg_html);
		$('#subaggregate').append(agg_html);

        $('#agg_geography').click(function(){
        	alert("To aggregate data by geography, please use the Data Extraction Tool (link at bottom of page).");
        })

		$('#filter').empty();
		$('#values').empty();
        field_html = "";
	    for (var i=0, ix=fields.length; i<ix; i++) {
			field_html += '<label class="field"><input type="checkbox" value="'+fields[i]+'">'+fields[i]+'</label><br>';
		}
		$('#filter').append(field_html);

		$('.field input:checkbox').on('change', function(){
			console.log("filter change");

			var field = $(this).val();
			if ( $(this).prop('checked') ) {
				var options;
				process({call:"options", country:p.country, type:p.type, field:field}, function (result){
					console.log(result);
					options = _.values(result).sort();
				})

				// add field as group to filter values and populate with options
				var options_html = '<div id="'+field+'"><span class="group">'+field.toUpperCase()+'</span><div>';

	   	 		for (var i=0, ix=options.length; i<ix; i++) {

	   	 			options_html += '<label class="option"><input type="checkbox" value="'+options[i]+'">'+options[i]+'</label><br>';

	   	 		}
	   	 		options_html += "</div></div>";

	   	 		$('#values').append(options_html);


  
			} else {
				// remove field and options from filter values
				$('#'+field).remove();
			}

		})
		

	})

	$('#values').on('click', 'span', function(){
			$(this).next().toggle();
	})


	$('#submit').click(function(){

		if (p.country == "" || p.status == 0 || p.status == 1) {
			console.log("please select and load a country");
			return;
		}

		p.aggregate =  ( $('#aggregate').val() == null ? "none" : $('#aggregate').val() );
		p.subaggregate = ( $('#subaggregate').val() == null ? "none" : $('#subaggregate').val() );

		if (p.subaggregate != "none" && p.aggregate == p.subaggregate) {
			console.log("invalid subaggregate");
			return;
		}

		p.filters = [];
		p.options = [];
		// p.query = 	"function() { return (";
		// var first = true;

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

			// var part = 'this.'+filter+' == '+value+' || this.'+filter+' == "'+value+'"';

			// p.query += (first == true ? part : andor + part);

			// first = false;

		})

		// p.query += ")}";

		if (p.options.length > 0 && p.aggregate != "none"){
			p.request = 3;
		} else if (p.options.length > 0) {
			p.request = 2;
		} else if (p.aggregate != "none") {
			p.request = 1;
		} else {
			p.request = 0;
		}

		if (p.request == 0) {
			console.log("select an aggregation or filter");
			return;
		}

		s = p;

		console.log(s)

		var query_data = {
			call:"build", 
			country:s.country, 
			type:s.type, 
			aggregate:s.aggregate, 
			subaggregate:s.subaggregate, 
			// query:s.query, 
			filters:s.filters, 
			options:s.options, 
			request:s.request
		};
		
		process(query_data, function (result){
			console.log(result);
			$('#download').empty();
			$('#download').append('<a href="'+page_url+'/data/'+result+'.csv">Download CSV</a>');
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
