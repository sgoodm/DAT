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

		$filters = $_POST['filters'];
		$options = $_POST['options'];

		$testhandle = fopen("/var/www/html/aiddata/DAT/data/test.csv", "w");

		$collection = "projects";
		if ($type == "old") {
			$collection = "complete";
		}
		
		$m = new MongoClient();

		$db = $m->selectDB($database);

		$col = $db->selectCollection($collection);


		// $js = $_POST['query'];
		// $query = array('$where' => $js);


		$sub_query2 = array();
		foreach ($filters as $k => $v) {
			$strings = array_map('strval', $options[$k]);
			$ints = array_map('intval', $options[$k]);
			$options = array_merge($strings, $ints);

			$sub_query2[] = array( $v => array('$in' => $options) );

		}


		$query2 = array('$or' => $sub_query2);
		fwrite( $testhandle, json_encode($query2) );

		// array('$or' => array(
		//   array("x" => array(1,2,3)),
		//   array("y" => array(1,2,3))
		// ));
		
		$cursor = $col->find($query2);

		$time = time();
		$file = fopen("/var/www/html/aiddata/DAT/data/".$time.".csv", "w");

		$c = 0;
		foreach ($cursor as $item) {
    	    $row = (array) $item;
            array_shift($row_raw);
		 	if ($c == 0) {
		    	fputcsv($file, array_keys($row));
		    	$c = 1;
		 	}
		 	fputcsv($file, array_values($row));
	    }

		$out = $time;
		echo json_encode($out);
		break;

}

?>
