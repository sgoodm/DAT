<?php
set_time_limit(0);

switch ($_POST['call']) {
	
	// check if a file exists
	case 'exists':
		$name = $_POST['name'];
		if ( file_exists("/var/www/html/aiddata/".$name) ) {
			echo "true";
		} else {
			echo "false";
		}
		break;

	// returns directory contents
	case 'scan':
		$path = $_POST['path'];
		$dir = "/var/www/html/aiddata/DET/resources" . $path;
		$rscan = scandir($dir);
		$scan = array_diff($rscan, array('.', '..'));
		$out = json_encode($scan);
		echo $out;
		break;

	// return list of fields for selected country
	case "fields":
		$database = $_POST['country'];
		// $type = $_POST['type'];

		// $collection = "projects";
		// if ($type == "old") {
			$collection = "complete";
		// }

		$m = new MongoClient();

		$db = $m->selectDB($database);

		$col = $db->$collection;
		$cursor = $col->find();

	    $first = true;
	    foreach ($cursor as $item) {
	        if ( $first == true ){
	    	    $data = (array) $item;
	            $out = array_keys( $data );
	            array_shift($out);
	            $first = false;
	        }
	    }

		echo json_encode($out);
		break;

	// return options for specific field
	case "options":
		$database = $_POST['country'];
		// $type = $_POST['type'];
		$field = $_POST['field'];

		// $collection = "projects";
		// if ($type == "old") {
			$collection = "complete";
		// }

		$m = new MongoClient();

		$db = $m->selectDB($database);

		$col = $db->$collection;
		$data = $col->distinct($field);


		// initial split
		for ($i=0; $i<count($data);$i++) {
			if (strpos($data[$i], "|") !== false) {
				$new = explode("|", $data[$i]);
				$data[$i] = array_shift($new);
				foreach ($new as $item) {
					$data[] = $item;
				}
			}
		}

		$out = array_unique($data);

		echo json_encode($out);
		break;

	// create csv using mongodb query
	case 'build':

		// load input

		$database = $_POST['country'];
		$type = $_POST['type'];

		$aggregate = $_POST['aggregate'];
		$subaggregate = $_POST['subaggregate'];
		$geoaggregate = $_POST['geoaggregate'];

		$filters = $_POST['filters'];
		$options = $_POST['options'];

		$filter_type_options = array(
								"or" => '$or',
								"and" => '$and'
							);

		$filter_type = $filter_type_options[$_POST["filter_type"]];

		// 0 - error, 1 - aggregate only, 2 - filter only, 3 - aggregate and filter
		$request = $_POST['request'];

		$start_year = intval($_POST['start_year']);
		$end_year = intval($_POST['end_year']);

		$transaction_type = $_POST['transaction_type'];

		// $testhandle = fopen("/var/www/html/aiddata/DAT/data/test.csv", "w");
		// $testhandle2 = fopen("/var/www/html/aiddata/DAT/data/test2.csv", "w");


		$div = 1;

		$collection = "complete";


		// init mongo
		$m = new MongoClient();
		$db = $m->selectDB($database);
		$col = $db->selectCollection($collection);


		// generate query

		$query = array();

		$regex_map = function($value) {
		    return new MongoRegex("/.*" . $value . ".*/");
		};

		// general (project) filter
		if ($request == 2 || $request == 3) {

			$andor = array();
			foreach ($filters as $k => $v) {
				$strings = array_map('strval', $options[$k]);

				$strings = array_map($regex_map, $strings);

				$floats = array_map('floatval', $options[$k]);
				$sub_options = array_merge($strings, $floats);
				fwrite( $testhandle, ($sub_options) );
				$andor[] = array( $v => array('$in' => $sub_options) );
			}

			$query[] = array( '$match' => array($filter_type => $andor) );
			
		} 




		// unwind transactions
		$query[] = array( '$unwind' => '$transactions' );

		// transactions filter
		$query[] = array(
							'$match' => array(
												'transactions.transaction_value_code' => $transaction_type,
												'transactions.transaction_year' => array( '$gte' => $start_year, '$lte' => $end_year )
											)
						);




		$group_list = array(  
								'_id' => '$project_id', 
								'project_id' => array( '$last' => '$project_id' ), 
								'is_geocoded' => array( '$last' => '$is_geocoded' ),	
								'project_title' => array( '$last' => '$project_title' ),	
								'start_actual_isodate' => array( '$last' => '$start_actual_isodate' ),	
								'start_actual_type' => array( '$last' => '$start_actual_type' ),	
								'end_actual_isodate' => array( '$last' => '$end_actual_isodate' ),	
								'end_actual_type' => array( '$last' => '$end_actual_type' ),	
								'donors' => array( '$last' => '$donors' ), 
								'donors_iso3' => array( '$last' => '$donors_iso3' ),
								'sector_name_trans' => array( '$last' => '$sector_name_trans' ), 	
								'ad_sector_codes' => array( '$last' => '$ad_sector_codes' ), 
								'ad_sector_names' => array( '$last' => '$ad_sector_names' ),	 
								'status' => array( '$last' => '$status' ), 
								'total_commitments' => array( '$last' => '$total_commitments' ),	
								'total_disbursements' => array( '$last' => '$total_disbursements' ),
								'transaction_sum' => array( '$sum' => '$transactions.transaction_value' ) 
							);

		// add locations to group_list for geo agg
	 	if ( ($request == 1 || $request == 3) && $aggregate == "geography" ) {

	 		$group_list['locations'] = array( '$last' => '$locations' );

	 	}


		// group transactions
		$query[] = array( '$group' => $group_list );



	 	if ( ($request == 1 || $request == 3) && $aggregate == "geography" ) {

			// unwind locations for geo agg
			$query[] = array( '$unwind' => '$locations' );



			// pass specific fields
			$project_list = array(  
									'project_id' => 1, 
									'longitude' => '$locations.longitude',	
									'latitude' => '$locations.latitude',
									'location_count' => '$locations.location_count',
									'precision_code' => '$locations.precision_code',
									'total_commitments' => 1,
									'total_disbursements' => 1,
									'transaction_sum' => 1 
								);

			$query[] = array( '$project' => $project_list );



			// filter based on precision code
			$precision_codes = $_POST['precision_codes'];

			$precision_list = array();
			foreach ($precision_codes as $v) {
				$precision_list[] = $v;
				$precision_list[] = intval($v);
			}

			$query[] = array(
								'$match' => array( 'precision_code' => array( '$in' => $precision_list ) )
							);


		}



		// aggregate using mongo for non geo agg
		if ( ($request == 1 || $request == 3) && $aggregate != "geography" ) {

			$agg_array = array();

			$agg_array[$aggregate] = "$".$aggregate;
			
			if ($subaggregate != "none") {
				$agg_array[$subaggregate] = "$".$subaggregate;
			}

			$group = array(
							'_id' => $agg_array,
							'total_commitments' => array('$sum' => array( '$divide' => array('$total_commitments', $div) ) ),
							'total_disbursements' => array('$sum' => array( '$divide' => array('$total_disbursements', $div) ) ),
							'transaction_sum' => array('$sum' => array( '$divide' => array('$transaction_sum', $div) ) )
						);

			$query[] = array( '$group' => $group );

		}




		// fwrite( $testhandle, json_encode($query) );

		$cursor = $col->aggregate($query);

		// fwrite( $testhandle2, json_encode($cursor) );


		//build csv if query produced results
		if ( count($cursor["result"]) > 0 ) {

			$time = time();
			$file = fopen("/var/www/html/aiddata/DAT/data/".$time.".csv", "w");

			$c = 0;
			foreach ($cursor["result"] as $item) {
	    	    $row = (array) $item;

	    	    $array_values = array_values($row); 

	            if ($request == 2 || $request == 0 || $aggregate == "geography") {
		    	    
		    	    // get rid of mongo _id field
	           		array_shift($row);

	           		// manage csv header
				 	if ($c == 0) {
				 		$array_keys = array_keys($row);
				    	fputcsv($file, $array_keys);
				    	$c = 1;
				 	}

				 	// get rid of extra mongo _id field
				 	array_shift($array_values);
				 	

	           	} else {
	           		
	           		// manage csv header
				 	if ($c == 0) {
				 		$array_keys = array_keys($row);
				 		$temp_key = array_shift($array_keys);
				 		if ($subaggregate != "none") {
					 		array_unshift($array_keys, $subaggregate);
				 		}
				 		array_unshift($array_keys, $aggregate);
				    	fputcsv($file, $array_keys);
				    	$c = 1;
				 	}

			 		$temp_val = array_shift($array_values);

			 		if ($subaggregate != "none") {
			 			array_unshift($array_values, $temp_val[$subaggregate]);
			 		}

			 		array_unshift($array_values, $temp_val[$aggregate]);
			 	
	           	}
			 	
			 	fputcsv($file, $array_values);
		    }

			$out = $time;

			// call python geo aggregation script
			if ( $aggregate == "geography" ) {
				$directory = dirname(__FILE__); 
				$exec_str = "python ".$directory."/geoagg.py ".$database." ".$geoaggregate." ".$out;
				exec($exec_str);
				$out = $out ."_geoagg";
			}

		} else {
			$out = "no data";
		}

		echo json_encode($out);
		break;

}

?>
