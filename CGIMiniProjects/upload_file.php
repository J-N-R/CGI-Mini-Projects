<html>
<body>

<!--     upload_roster.php     -->
<!--  To use with upload.html  -->

<div style = "margin-left: 2.5%; font-size: 200%">

<h1>File Information:</h1>

<?php


  // VARIABLES
  $target_dir = "upload/";
  $target_file = $target_dir . basename($_FILES["filename"]["name"]);

  $FileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
  $FileSize = $_FILES["filename"]["size"];

  $uploadOk = True;


  // ###   FILE VERIFICATION  ### //

  // Check file type. Only .csv allowed.
  if($FileType != "csv") {
     echo "\nFile is the wrong type. Please only upload .csv files.<br>";
     $uploadOk = False;
  }

  // Is the file in size range? (10 bytes > x > 1000 bytes)
  elseif($FileSize > 1000 || $FileSize < 10) {
     echo "\nFile Size: " . $FileSize . " bytes.<br>";	  
     echo "\nFile size is not within the range, upload failed.<br>";
     $uploadOk = False;
  }

  //Final Check. Does the CSV have 4 Columns?
  elseif($uploadOk) {
     $ColumnsAllowed = 4;
     $ColumnsInFile = 0;
     
     $file = fopen($_FILES["filename"]["tmp_name"], "r");

     $ColumnsInFile = count(fgetcsv($file));

     if($ColumnsInFile !== 4) {
        echo "\nUpload Failed. Incorrect number of columns in file.<br>";
	echo "\nColumns in file: $ColumnsInFile<br>";
	echo "\nColumns allowed: $ColumnsAllowed<br>";
	$uploadOk = False;
     }

     fclose($file);
  }


  // ###   FILE VERIFICATION COMPLETE   ### //
  // If file has been approved, save file. Otherwise, print error message
  if($uploadOk) {
     $tmp_path = $_FILES["filename"]["tmp_name"];

     // ### FILE HAS BEEN APPROVED ### //
     // SAVE FILE, PRINT RESULTS
     if(move_uploaded_file($tmp_path, $target_file)) {
	echo "\nTemp File: $tmp_path<br>";
	echo "\nFile Path: $target_file<br>";
	echo "\nFile Size: $FileSize bytes<br>";
        echo "\n<br>The file <b>" . htmlspecialchars( basename( $_FILES["filename"]["name"])) . "</b> has been successfully uploaded.<br><br>";
	chmod($target_file, 0777);

	
	// Print Original Data Table:
	echo "\n\nOriginal Data:<br>";
	echo "\n<table border = \"1\">";

	$requestedColumn = $_POST["subject"];
	$file = fopen($target_file, "r");
	$header = fgetcsv($file, 1000);
	
	// Code for Table Header
	echo "\n<tr> \n <th>#</th> \n <th>Name</th> \n <th>$header[1]</th> \n <th>$header[2]</th> \n <th>$header[3]</th> \n <th>Avg</th> \n</tr>";

	   // Array used to help calculate averages. Each index should be full sum of a column. Final index is average of all averages (bottom right corner of table)
           $sumArray = array( 0, 0, 0, 0 );
           $row = 1;
	   
	   // Parse Data from CSV to table. Add Grade values to arrays for averages.
           while($data = fgetcsv($file, 1000)) {
              $avg = number_format(($data[1] + $data[2] + $data[3])/3, 1);

              echo "\n<tr> \n <td>$row</td> \n <td>$data[0]</td> \n <td>$data[1]</td> \n <td>$data[2]</td> \n <td>$data[3]</td> \n <td>$avg</td>\n</tr>";

              $sumArray[0] += $data[1];
              $sumArray[1] += $data[2];
              $sumArray[2] += $data[3];
              $sumArray[3] += $avg;
	      $row++;
	   }

	   $row--; 			// Corrective adjustment. If there are 13 rows, this counter will end at 14. correct back to 13 for accurate average

           // Calculate Averages
           $averages = array( number_format($sumArray[0]/$row, 1), number_format($sumArray[1]/$row, 1), number_format($sumArray[2]/$row, 1), number_format($sumArray[3]/$row, 1) );

        echo "\n<tr> \n <td></td> \n <td>Avg</td> \n <td>$averages[0]</td> \n <td>$averages[1]</td> \n <td>$averages[2]</td> \n <td>$averages[3]</td> \n</tr>";
	echo "\n</table><br>";
	rewind($file);
	fgetcsv($file); //skip header for next code
	// ## ORIGINAL DATA TABLE COMPLETE ## //


	// ## PRINT SORTED TABLE ## //
	if($requestedColumn !== "All") {
           
	   echo "\nAfter Sorting by Subject: $requestedColumn<br>";
	   echo "\n<table border = \"1\">";

	   $columnIndex = array_search($requestedColumn, $header);
	   $sum = 0;
	   $row = 0;

	   // Dataset to hold table entries for sort. Fill in while loop
	   $TableEntries = array();
	   
	   echo "\n<tr> \n <th>#</th> \n <th>Name</th> \n <th>$requestedColumn</th> \n</tr>";

	   // Fill Dataset, calculate average
	   while($data = fgetcsv($file, 1000)) {
	      $TableEntries[$data[0]] = $data[ $columnIndex ];   // Push new Key(Name) => Value (grades)

	      $sum += $data[$columnIndex];
	      $row++;
	   }
	   
	   $average = number_format( $sum/$row, 1 );
	   
	   // Print table
	   $row = 0;
	   arsort($TableEntries);
	   foreach ($TableEntries as $name => $grade) {
	      $row++;
   	      echo "\n<tr> \n <td>$row</td> \n <td>$name</td> \n <td>$grade</td> \n</tr>";
	   }

	   echo "\n<tr> \n <td></td> \n <td>Avg</td> \n <td>$average</td> \n</tr>";

	}
	else
	{
	       
	   echo "\nAfter Sorting by: $requestedColumn<br>";
           echo "\n<table border = \"1\">";

           $sumArray = array( 0, 0, 0, 0 );
           $row = 0;

           // Dataset to hold table entries for sort. Fill in while loop
           $TableEntries = [];

	   echo "\n<tr> \n <th>#</th> \n <th>Name</th> \n <th>$header[1]</th> \n <th>$header[2]</th> \n <th>$header[3]</th> \n <th>Avg</th> \n</tr>";
           

           // Fill Dataset, calculate average
           while($data = fgetcsv($file, 1000)) {
	      $avg = number_format(($data[1]+$data[2]+$data[3])/3, 1);	   
	      $TableEntries[$avg] = array($data[0], $data[1], $data[2], $data[3]);   // Push new Key(Name) => Value (grades)

              $sumArray[0] += $data[1];
              $sumArray[1] += $data[2];
              $sumArray[2] += $data[3];
              $sumArray[3] += $avg;
              $row++;
           }

           $averages = array( number_format($sumArray[0]/$row, 1), number_format($sumArray[1]/$row, 1), number_format($sumArray[2]/$row, 1), number_format($sumArray[3]/$row, 1) );


           // Print table
	   $row = 0;
	  
           krsort($TableEntries);

           foreach ($TableEntries as $avg => $gradeArray) {
	      $row++;

	      echo "\n<tr> \n <td>$row</td> ";
	      foreach ($gradeArray as $value) {
		 echo "\n <td>$value</td> ";
	      }
		      
 	      echo "\n <td>$avg</td> \n</tr>";
           }

           echo "\n<tr> \n <td></td> \n <td>Avg</td> \n <td>$averages[0]</td> \n <td>$averages[1]</td> \n <td>$averages[2]</td> \n <td>$averages[3]</td> \n</tr>";
	  
	}
	
	echo "\n</table>";
	fclose($file);
     } // If-END (if file moved & uploaded correctly)

  // If-END (if file has been validated ($uploadOk))
  } else {
     echo "\n\n<br>Error uploading your file. Please try again.";
  }
?>

</div>

<!-- special thanks to W3Schools for help with Super Globals and PHP convenience methods -->

</body>
</html>

