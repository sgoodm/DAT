<?php
set_time_limit(0);

switch ($_POST['call']) {

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
		$type = $_POST['type'];

		$collection = "projects";
		if ($type == "old") {
			$collection = "complete";
		}

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
		$type = $_POST['type'];
		$field = $_POST['field'];

		$collection = "projects";
		if ($type == "old") {
			$collection = "complete";
		}

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

		$database = $_POST['country'];
		$type = $_POST['type'];

		$aggregate = $_POST['aggregate'];
		$subaggregate = $_POST['subaggregate'];

		$filters = $_POST['filters'];
		$options = $_POST['options'];

		// 0 - error, 1 - aggregate only, 2 - filter only, 3 - aggregate and filter
		$request = $_POST['request'];

		$start_year = $_POST['start_year'];
		$end_year = $_POST['end_year'];

		$testhandle = fopen("/var/www/html/aiddata/DAT/data/test.csv", "a");

		$collection = "projects";
		$div = 1;
		if ($type == "old") {
			$collection = "complete";
			$div = '$location_count';
		}
		
		$m = new MongoClient();

		$db = $m->selectDB($database);

		$col = $db->selectCollection($collection);


		$query = array();


		$regex_map = function($value) {
		    return new MongoRegex("/.*" . $value . ".*/");
		};


		// year filter
		// $and = array( array( 'transaction_year': array( '$gte':$start_year ) ), array( array( 'transaction_year': array( '$lte':$end_year ) ) );


		if ($request == 2 || $request == 3) {
			
			// general filter
			$or = array();
			foreach ($filters as $k => $v) {
				$strings = array_map('strval', $options[$k]);

				$strings = array_map($regex_map, $strings);

				$floats = array_map('floatval', $options[$k]);
				$sub_options = array_merge($strings, $floats);
				fwrite( $testhandle, ($sub_options) );
				$or[] = array( $v => array('$in' => $sub_options) );
			}

			// TEMP while adding in year filter
			$query[] = array( '$match' => array('$or' => $or) );
			
			// add year and general filter to $match
			// $query[] = array( '$match' => array( '$and' => array( array('$and' => $and), array('$or' => $or) ) ) );

		} else {
			// add just year filter to $match
			// $query[] = array( '$match' => array('$and' => $and) );
		}

		$agg_array = array();
		$agg_array[$aggregate] = "$".$aggregate;
		if ($subaggregate != "none") {
			$agg_array[$subaggregate] = "$".$subaggregate;
		}


		if ($request == 1 || $request == 3) {
			$group = array(
				'_id' => $agg_array,
				'total_commitments' => array('$sum' => array( '$divide' => array('$total_commitments', $div) ) ),
				'total_disbursements' => array('$sum' => array( '$divide' => array('$total_disbursements', $div) ) )
			);

			$query[] = array( '$group' => $group );
		}

		fwrite( $testhandle, json_encode($query) );

		$cursor = $col->aggregate($query);

		$time = time();
		$file = fopen("/var/www/html/aiddata/DAT/data/".$time.".csv", "w");

		$c = 0;
		foreach ($cursor["result"] as $item) {
    	    $row = (array) $item;
            array_shift($row_raw);
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
		 	$array_values = array_values($row); 
		 	if ($request == 1 || $request == 3) {

		 		$temp_val = array_shift($array_values);

		 		if ($subaggregate != "none") {
		 			array_unshift($array_values, $temp_val[$subaggregate]);
		 		}
		 		array_unshift($array_values, $temp_val[$aggregate]);

		 	}
		 	fputcsv($file, $array_values);
	    }

		$out = $time;
		echo json_encode($out);
		break;

}

?>
