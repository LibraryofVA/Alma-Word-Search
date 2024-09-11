<?php require $_SERVER['DOCUMENT_ROOT'] . '\path\to\database'; ?>
<?php 

// declare arrays 
$terms = []; 
$words = []; 
$categories = []; 

// Pushing all the terms into an array
if (($result = sqlsrv_query($connection, "select * from alma_arm_term at join alma_arm_category ac on ac.category_id = at.category_id"))!== false){
      while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) {
		  	array_push($terms, $row['term_name']); 
		  	array_push($categories, ["term" => $row['term_name'], "category" => $row["category_name"]]); 
		}
	}


if ($connection) {
   if (($result = sqlsrv_query($connection, "SELECT TOP 2500 * from alma_api_data WHERE api_complete = '0'"))!== false){
      while ($row = sqlsrv_fetch_array( $result, SQLSRV_FETCH_ASSOC)) {
			$mms = $row['api_mms_id']; 
		  	$id = $row['api_id']; 
		  	$sentence = htmlspecialchars($row['api_text'], ENT_QUOTES); 
		  	$title = htmlspecialchars($row['api_title'], ENT_QUOTES); 
		  	$section = $row['api_section']; 
		  	$series = htmlspecialchars($row['api_series'], ENT_QUOTES); 
		  	$accession = htmlspecialchars($row['api_accession'], ENT_QUOTES); 

		  	
		  	// Looping through each term in terms array
		 	foreach ($terms as $term){
				
				// Checking if exact word exists in the sentence
				if (preg_match("/\b$term\b/i", $sentence)){
					
					// pushing terms to array
				 	array_push($words, $term); 	
				}
		 	}
		  
		  	// Declare $categoryArray array
		 	$categoryArray = []; 
		  	
		  	// Looping through to find categories of terms
		  	foreach ($words as $word){
				foreach ($categories as $category){
					$term = $category["term"]; 
					$cat = $category["category"]; 
					
					if ($term == $word){
						array_push($categoryArray, $cat); 
					}
				}
			}
		  		  
		  	// Formatting Terms 
		  	if (!empty($words)){
				$arm_word = implode(", ", $words);
			} else {
				$arm_word = "N/A";
			}
			
		  	if (!empty($categoryArray)){
				$arm_category = implode(", ", array_unique($categoryArray));
			} else {
				$arm_category = "N/A"; 
			}
			
		   	
		  	// Insert into alma_arm table 
		  	$insert =  "INSERT INTO [Automation].[dbo].[alma_arm] 
							(arm_section, arm_mms_id, arm_accession, arm_series, arm_title, arm_text, arm_terms, arm_category)
					   		VALUES
							('$section', '$mms', '$accession', '$series', '$title', '$sentence', '$arm_word', '$arm_category')";
		  	
		  	
		  	$insert_stmt = sqlsrv_query($connection, $insert);
		    if ($insert_stmt === false) { die( print_r( sqlsrv_errors(), true ) ); } 
		  	echo "Inserted Record for mms_id: $mms" . "<br>"; 
			
		  	
		  	// Update status in alma_api_data
		  	$update = "UPDATE alma_api_data set api_complete = '1' where api_id = '$id'"; 
		  	$update_stmt = sqlsrv_query($connection, $update);
		  	if ($update_stmt === false) { die( print_r( sqlsrv_errors(), true ) ); } 
		  	echo "Updated Record for api_id: $id" . "<br>"; 
		  	
		    // Clearing Arrays
		    $categoryArray = []; 
		    $words = []; 
		  
		}
	}
}


?>