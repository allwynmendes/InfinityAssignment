<?php

$directory = './Uploaded/';
$processedDirectory = 'Processed/';

//1. Get list of files in the uploaded directory
$uploadedFiles = readDirectory($directory);

//2. Read data in each of these csv files
/*echo '<br>'.'List of CSV files : ';
foreach ($uploadedFiles as $csvFile){
	echo '<br>Filename : '.$csvFile;
	readCsv($directory.$csvFile);
	break;
}*/

//3. Connect to database
echo '<br>'.'Connecting to Database : ';
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'infinity';
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
	echo 'Connection failed';
    die("Connection failed: " . $conn->connect_error);
} else {
	echo "Connected successfully";
}

//4. Insert into database
echo '<br>'.'List of CSV files : ';
foreach ($uploadedFiles as $csvFile){
	$insertStatus = insertCsvIntoDB($conn, $directory.$csvFile);	
	if($insertStatus == 'PASS'){
		copyFileToProcessed($directory.$csvFile, $processedDirectory, $csvFile);
		unlink($directory.$csvFile);
	}
}
 

// Close DB Connection
echo '<br>'.'Closing Database Connection';
$conn->close();

function copyFileToProcessed($fromFile, $toDirectory, $toFile){
	if (!file_exists($toDirectory)) {
		mkdir($toDirectory, 0777, true);
	}
	copy($fromFile, $toDirectory.$toFile);
}

function insertCsvIntoDB($conn, $filename){
	echo $filename;
	$csvFile = file($filename);
    $data = [];
	$dataFieldsOrder = [];
    foreach ($csvFile as $line) {
        $data[] = str_getcsv($line);
    }
	$arraySize = sizeof($data);
	$count = 0; $insertCount = 0;
	foreach($data as $row){
		$count++;
		if($count == $arraySize){
			break;
		} 	
		if($count == 1){
			$dataFieldsOrder = $row;
			var_dump($dataFieldsOrder);
		}
		if($count != 1){
			$query = createInsertStatements($dataFieldsOrder, $row);
			$queryArray[] = $query;
			echo '<br>'.$query;
			$result = $conn->query($query);
			if($result){
				$insertCount++;
			} else {
				echo 'FAIL';		
			}
		}
	}
	echo $insertCount.'-yay-'.$count;
	if($insertCount+2 == $count)
		return 'PASS';
	return 'FAIL';
}

function readDirectory($dir){
	if (is_dir($dir)){
	  if ($dh = opendir($dir)){
	    while (($file = readdir($dh)) !== false){
	      //echo '<br>'."filename:" . $file;
	      if(substr($file, -4) == '.csv'){
	      	echo 'found csv';
	      	$fileNames[] = $file;
	      }
	    }
	    closedir($dh);
	  }
	}
	return $fileNames;
}

function readCsv($filename){
	echo $filename;
	$csvFile = file($filename);
    $data = [];
    foreach ($csvFile as $line) {
        $data[] = str_getcsv($line);
    }
    showCsv($data);
}

function showCsv($csvData){
	print_r($csvData);
	$arraySize = sizeof($csvData);
	$count = 0;
	foreach($csvData as $row){
		$count++;
		if($count == $arraySize-1){
			break;
		} 	
		if($count != 1){
			//createInsertStatements($row);
		}
		foreach($row as $cell){
			//echo $cell;
		}
	}
}

function createInsertStatements($dataFields, $data){
		$query =  'insert into csvdata ('     .$dataFields[0].','
											  .$dataFields[1].','
											  .$dataFields[2].','
											  .$dataFields[3].','
											  .$dataFields[4].''
		.') values ('						 .'\''.$data[0].'\','
											 .'\''.$data[1].'\','
											 .'\''.$data[2].'\','
											 .'\''.$data[3].'\','
											 .'\''.$data[4].'\')';	
	return $query;
}

?>
