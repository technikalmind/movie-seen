<?php
include("inc/header.php");
include("inc/functions.php");

if(empty($_GET['id'])) {
	echo "No movie selected";
} elseif(isset($_GET['id'])) {
	// Fetch JSON object from OMDb API
	$id = $_GET['id'];
	$obj = fetchJSON("i", $_GET['id']);
	
	$cached = false;
	
	connectDB();
	$query = mysql_query("SELECT * FROM movies");
	?>
		<?php
			// Go through the query and find out if the request is already cached
			// If cached, display data from local database
			while($row = mysql_fetch_array($query))
			{
				$tbl_movies = $row['mv_imdbID']; 	// LOCAL
				$obj_movies = $obj['imdbID']; 		// JSON
				
				if($obj_movies == $tbl_movies) // If JSON matches LOCAL
				{
					$cached = true;
					displayMovieData($row);			
				}
			}
		?>
	<?php

	
	// If not cached, fetch info from API and store it locally
	// Then refresh page to display the data we just stored
	if(!$cached) {
		insertMovie($obj);
		header("location:movie.php?id=".$id);
	}
	
}
include("inc/footer.php");
?>
