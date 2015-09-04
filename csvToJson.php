<?php

# http://stackoverflow.com/questions/32393879
# http://stackoverflow.com/a/32394708/1163786

/**
 * Turns a CSV file with column header into JSON.
 */
function csvToJson($file)
{
	$csvString = file_get_contents($file);
	$csvRows = str_getcsv($csvString, "\n"); // split new lines, gives you rows
	$rows = array();
	foreach($csvRows as $row) {
		$rows[] = str_getcsv($row, ";"); // parse the rows
	}

	// remove the first row. its the CSV column heading line.
	// keep the keys to reuse them for the array items
	$itemKeys = array_shift($rows);

	// run over all rows and combine keys with values
	// so that you get a named array
	foreach($rows as $rowKeys => $rowValues) {
		$array[] = array_combine($itemKeys, $rowValues);
	}

	echo json_encode($array);
}

echo csvToJson('/path/to/csvToJson.csv');