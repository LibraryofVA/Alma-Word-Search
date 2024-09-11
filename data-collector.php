<?php require $_SERVER['DOCUMENT_ROOT'] . '\path\to\database'; ?>
<?php 


// Set Array 
$mms_to_exclude = array();

// Search Database to see what api data has been imported
if ($connection) {
   if (($result = sqlsrv_query( $connection, "select * from alma_api_data"))!== false){
      while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) {
			$mms = $row['api_mms_id']; 
		  		if (!in_array($mms, $mms_to_exclude)){
			  		$mms_to_exclude[] = $mms;
		  		}  
		}
	}
}

// Loop through the digest file and start adding Alma data

//////////////////////////
/// Column0 = ? 	   ///
/// Column1 = Content  /// 
/// Column2 = Title	   ///
/// Column3 = MMS ID   ///
/////////////////////////
if (file_exists('alma-digest.xml')) {
    $xml = simplexml_load_file('alma-digest.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
 	$json = json_decode(json_encode((array)$xml), TRUE);
	
	foreach($json as $records){
		foreach($records as $record){
			
			$section = is_array($record["C0"]) ? ' ' : $record["C0"];
			$mms_id = is_array($record["C1"]) ? ' ' : $record["C1"];
			$series = is_array($record["C2"]) ? ' ' : htmlentities($record["C2"], ENT_QUOTES);
			$title = is_array($record["C3"]) ? ' ' : htmlentities($record["C3"], ENT_QUOTES);
			$content = htmlentities($record["C4"], ENT_QUOTES);
			$accession = is_array($record["C5"]) ? ' ' : htmlentities($record["C5"], ENT_QUOTES);
			
			if (!in_array($mms_id, $mms_to_exclude) ) {
          		if($connection){
					$sql = "INSERT into alma_api_data 
						(api_mms_id, api_text, api_title, api_section, api_series, api_accession) 
						VALUES 
						('$mms_id', '$content', '$title', '$section', '$series', '$accession')";
          		
			  			$insert_stmt = sqlsrv_query( $connection, $sql);
          		
			  		if ($insert_stmt === false) {
            			die( print_r( sqlsrv_errors(), true ) );
          			} 
				}
     		}
		}
	}
} else {
    exit('Failed to open test.xml.');
}


?>