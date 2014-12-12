<?php

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
		$data = iterator_to_array($col->find());

		$out = array_keys( $data[array_keys($data)[0]] );
		array_shift($out);

		echo json_encode($out);
		break;

	// return options for specific field
	case "options":
		$database = $_POST['country'];
		$type = $_POST['type'];

		$collection = "projects";
		if ($type == "old") {
			$collection = "complete";
		}

		$m = new MongoClient();

		$db = $m->selectDB($database);

		$col = $db->$collection;
		$data = $col->distinct('ad_sector_names');

		for ($i=0; $i<count($data);$i++) {
			if (strpos($data[$i], "|") !== false) {
				$temp = $data[$i];
				unset($data[$i]);
				$new = explode("|", $temp);
				foreach ($new as $item) {
					$data[] = $item;
				}
			}
		}

		$out = $data;

		echo json_encode($out);
		break;

	// create csv using mongodb query
	case 'build':

		$database = $_POST['country'];
		$collection = "complete";
		
		$m = new MongoClient();

		$db = $m->selectDB($database);

		$col = $db->$collection;
		$data = iterator_to_array($col->find());

		$file = fopen("data/results/test.csv", "w");

		$c = 0;
		foreach ($data as $row) {
		 	array_shift($row);
		 	if ($c == 0) {
		    	fputcsv($file, array_keys($row));
		    	$c = 1;
		 	}
		 	fputcsv($file, $row);
		}
		$out = "done";
		echo json_encode($out);
		break;

}

?>
