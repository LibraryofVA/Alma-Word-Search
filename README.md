# Alma-Word-Search Script 

This script is designed to analyze a large dataset of sentences from Alma, identifying and categorizing sentences based on specific keywords. It efficiently processes thousands of entries using PHP in conjunction with Microsoft SQL Server. The script classifies sentences into relevant groups, streamlining data organization and enhancing searchability.

<details>

<summary><h2>data-collector.php</h2></summary>

This file first queries the alma_api_data table to fetch all existing records. The script iterates through each record, extracts the api_mms_id, and adds it to the $mms_to_exclude array if it is not already present. This helps in preventing the insertion of duplicate records.

```php
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
```

The script then checks if the alma-digest.xml file exists. If it does, the file is loaded and parsed using simplexml_load_file(), and then converted into a JSON array for easier manipulation. This is achieved by first converting the SimpleXML object into a regular array and then encoding it into JSON before decoding it back to a PHP associative array.

** alma-digest.xml is a download directly from alma of your sentences you would like to search through

```php
if (file_exists('alma-digest.xml')) {
    $xml = simplexml_load_file('alma-digest.xml', 'SimpleXMLElement', LIBXML_NOCDATA);
    $json = json_decode(json_encode((array)$xml), TRUE);
```

This section processes each record in the JSON array. For each record, it extracts values for different fields and performs HTML encoding to ensure special characters are safely stored in the database. If the mms_id is not in the $mms_to_exclude list, a SQL INSERT query is constructed to add the new record into the alma_api_data table. The script executes the query using sqlsrv_query() and handles any potential errors by printing them out and terminating the script if an error occurs.

```php
foreach ($json as $records) {
    foreach ($records as $record) {
        $section = is_array($record["C0"]) ? ' ' : $record["C0"];
        $mms_id = is_array($record["C1"]) ? ' ' : $record["C1"];
        $series = is_array($record["C2"]) ? ' ' : htmlentities($record["C2"], ENT_QUOTES);
        $title = is_array($record["C3"]) ? ' ' : htmlentities($record["C3"], ENT_QUOTES);
        $content = htmlentities($record["C4"], ENT_QUOTES);
        $accession = is_array($record["C5"]) ? ' ' : htmlentities($record["C5"], ENT_QUOTES);
        
        if (!in_array($mms_id, $mms_to_exclude)) {
            if ($connection) {
                $sql = "INSERT INTO alma_api_data 
                    (api_mms_id, api_text, api_title, api_section, api_series, api_accession) 
                    VALUES 
                    ('$mms_id', '$content', '$title', '$section', '$series', '$accession')";
                
                $insert_stmt = sqlsrv_query($connection, $sql);
                
                if ($insert_stmt === false) {
                    die(print_r(sqlsrv_errors(), true));
                }
            }
        }
    }
}
```
</details>


<details>

<summary><h2>word-search.php</h2></summary>

The script queries the database to fetch term and category data by joining the alma_arm_term and alma_arm_category tables. It populates the $terms array with term names and $categories with associative arrays containing both terms and their corresponding categories.

```php
if (($result = sqlsrv_query($connection, "select * from alma_arm_term at join alma_arm_category ac on ac.category_id = at.category_id")) !== false) {
    while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
        array_push($terms, $row['term_name']); 
        array_push($categories, ["term" => $row['term_name'], "category" => $row["category_name"]]);
    }
}
```

Here, the script processes up to 2500 records from the alma_api_data table that are marked as incomplete (api_complete = '0'). It extracts and sanitizes various fields from each record. It then checks if any of the terms from the $terms array are present in the sentence field, storing matched terms in the $words array.

```php
if ($connection) {
    if (($result = sqlsrv_query($connection, "SELECT TOP 2500 * from alma_api_data WHERE api_complete = '0'")) !== false) {
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            // Extract and sanitize data
            $mms = $row['api_mms_id']; 
            $id = $row['api_id']; 
            $sentence = htmlspecialchars($row['api_text'], ENT_QUOTES); 
            $title = htmlspecialchars($row['api_title'], ENT_QUOTES); 
            $section = $row['api_section']; 
            $series = htmlspecialchars($row['api_series'], ENT_QUOTES); 
            $accession = htmlspecialchars($row['api_accession'], ENT_QUOTES);

            // Search for terms in the sentence
            foreach ($terms as $term) {
                if (preg_match("/\b$term\b/i", $sentence)) {
                    array_push($words, $term); 
                }
            }

```

For each matched word, the script finds associated categories from the $categories array and populates the $categoryArray with these categories.

```php
            $categoryArray = [];
            foreach ($words as $word) {
                foreach ($categories as $category) {
                    $term = $category["term"]; 
                    $cat = $category["category"]; 
                    if ($term == $word) {
                        array_push($categoryArray, $cat); 
                    }
                }
            }

```

The script formats the matched terms and categories, ensuring that if no terms or categories are found, "N/A" is used instead. It then inserts this information into the alma_arm table and updates the corresponding record in alma_api_data to mark it as complete. Finally, it clears the $categoryArray and $words arrays to prepare for the next record.

```php
            $arm_word = !empty($words) ? implode(", ", $words) : "N/A";
            $arm_category = !empty($categoryArray) ? implode(", ", array_unique($categoryArray)) : "N/A";

            $insert = "INSERT INTO [Automation].[dbo].[alma_arm] 
                        (arm_section, arm_mms_id, arm_accession, arm_series, arm_title, arm_text, arm_terms, arm_category)
                        VALUES
                        ('$section', '$mms', '$accession', '$series', '$title', '$sentence', '$arm_word', '$arm_category')";
            $insert_stmt = sqlsrv_query($connection, $insert);
            if ($insert_stmt === false) { die(print_r(sqlsrv_errors(), true)); }
            echo "Inserted Record for mms_id: $mms" . "<br>";

            $update = "UPDATE alma_api_data set api_complete = '1' where api_id = '$id'";
            $update_stmt = sqlsrv_query($connection, $update);
            if ($update_stmt === false) { die(print_r(sqlsrv_errors(), true)); }
            echo "Updated Record for api_id: $id" . "<br>";

            $categoryArray = [];
            $words = [];
        }
    }
}


```

</details>

