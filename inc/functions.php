<?php
include("dbconfig.php");

function connectDB() {
	mysql_connect(DB_HOST, DB_USER, DB_PASS) or die(mysql_error());
	mysql_select_db(DB_TABLE) or die(mysql_error());
}

function eyeball($chkSeen) {
	if($chkSeen) {
		return "imgs/seen_k.png";
	}
	elseif(!$chkSeen) {
		return "imgs/unseen_k.png";
	} else {
		return "imgs/unseen_k.png";
	}
}

function watchlist($chkList) {
	if($chkList) {
		return "imgs/list.png";
	}
	elseif(!$chkList) {
		return "imgs/notlist.png";
	} else {
		return "imgs/notlist.png";
	}
}

function seenOrNot($id) {
	if(isset($_SESSION['user'])){
		$user = $_SESSION['user'];
		connectDB();
		$query = mysql_query("SELECT * FROM users WHERE username='$user' AND FIND_IN_SET('$id',seen)");
		while($row = mysql_fetch_array($query))
		{
		$tbl_user = $row['username'];
		$arr_seen = explode(",", $row['seen']);
		
			foreach($arr_seen as $seen){
				if($seen == $id) {
					return true;
				}
			}
		}	
	} else {
		return false;
	}
}

function listOrNot($id) {
	if(isset($_SESSION['user'])){
		$user = $_SESSION['user'];
		connectDB();
		$query = mysql_query("SELECT * FROM users WHERE username='$user' AND FIND_IN_SET('$id',watchlist)");
		while($row = mysql_fetch_array($query))
		{
		$tbl_user = $row['username'];
		$arr_list = explode(",", $row['watchlist']);
		
			foreach($arr_list as $list){
				if($list == $id) {
					return true;
				}
			}
		}	
	} else {
		return false;
	}
}

function getSeen() {
	if(isset($_SESSION['user'])){
		$user = $_SESSION['user'];
		connectDB();
		$query = mysql_query("SELECT * FROM users WHERE username='$user'");
		while($row = mysql_fetch_array($query))
		{
			$tbl_user = $row['username'];
			$tbl_seen = $row['seen'];
			return $tbl_seen;
		}	
	} else {
		return false;
	}
}

function putSeen($seen) {
	if(isset($_SESSION['user'])){
		$user = $_SESSION['user'];
		connectDB();
		
		mysql_query("UPDATE users SET seen = '$seen' WHERE username='$user'") or die(mysql_error());	
	} else {
		return false;
	}
}

function getList() {
	if(isset($_SESSION['user'])){
		$user = $_SESSION['user'];
		connectDB();
		$query = mysql_query("SELECT * FROM users WHERE username='$user'");
		while($row = mysql_fetch_array($query))
		{
		$tbl_user = $row['username'];
		$tbl_list = $row['watchlist'];
		return $tbl_list;
		}	
	} else {
		return false;
	}
}

function putList($list) {
	if(isset($_SESSION['user'])){
		$user = $_SESSION['user'];
		connectDB();
		
		mysql_query("UPDATE users SET watchlist = '$list' WHERE username='$user'") or die(mysql_error());	
	} else {
		return false;
	}
}

function modifyList($action, $id, $list) {
	if($action == "add") {
		if($list == "seen") {
			$chkSeen = seenOrNot($id);
			if(!$chkSeen) {
				print("Adding ".$id." to ".$list."<br/>");
				if(getSeen()) {
					$seen = getSeen().",".$id;
				} else {
					$seen = $id;
				}
				putSeen($seen);
				header("location:profile.php");
			}
		} elseif($list == "watchlist") {
			$chkList == listOrNot($id);
			if(!$chkList) {
				print("Adding ".$id." to ".$list."<br/>");
				if(getList()) {
					$list = getList().",".$id;
				} else {
					$list = $id;
				}
				putList($list);
				header("location:profile.php");
			}
		}
	} elseif($action == "remove") {
		if($list == "seen") {
			$chkSeen = seenOrNot($id);
			if($chkSeen) {
				print("Removing ".$id." from ".$list."<br/>");
				$seen = getSeen();
				$newSeen = removeID($id, $seen);
				putSeen($newSeen);
				header("location:profile.php");
			}
		} elseif($list == "watchlist") {
			$chkList = listOrNot($id);
			if($chkList){
				print("Removing ".$id." from ".$list);
				$list = getList();
				$newList = removeID($id, $list);
				putList($newList);
				header("location:profile.php");
			}
		}
	}
}

function removeID($id, $string) {
	$idArr = Array($id);
	$boom = explode(',',$string);
	$diff = array_diff($boom, $idArr);
	$res = implode(',',$diff);
	return $res;
}

function fetchJSON($parameter, $term) {
	if($parameter == "i") {
		$url = "http://www.omdbapi.com/?i=";
	} 
	
	if ($parameter == "s") {
		$url = "http://www.omdbapi.com/?s=";
	}
	
	if ($parameter == "h") {
		$url = "http://www.omdbapi.com/?i=";
	}
	
	$json = file_get_contents($url.$term);
	$obj = json_decode($json, true);
	return $obj;
}

function displayMovieData($row){
	$imdbID = $row['mv_imdbID'];
	$title = $row['mv_title'];
	$year = $row['mv_year'];
	$rated = $row['mv_rated'];
	$released = $row['mv_released'];
	$runtime = $row['mv_runtime'];
	$genre = $row['mv_genre'];
	$director = $row['mv_director'];
	$writer = $row['mv_writer'];
	$actors = $row['mv_actors'];
	$plot = $row['mv_plot'];
	$lang = $row['mv_lang'];
	$country = $row['mv_country'];
	$awards = $row['mv_awards'];
	$poster = $row['mv_posterURL'];
	$metascore = $row['mv_metascore'];
	$imdbRating = $row['mv_imdbRating'];
	$imdbVotes = $row['mv_imdbVotes'];
	$type = $row['mv_type'];
	
	$chkSeen = seenOrNot($imdbID);
	$chkList = listOrNot($imdbID);
	
	$eyeball = eyeball($chkSeen);
	$watchlist = watchlist($chkList);
	
	if(isset($_SESSION['user'])){
		if($chkSeen){
			$eyeCon = "<span title=\"Remove from Seen List\"><a href=\"modify.php?action=remove&list=seen&id=".$imdbID."\"><img src=\"".$eyeball."\"></a></span> ";
		} 
		
		if(!$chkSeen) {
			$eyeCon = "<span title=\"Add to Seen List\"><a href=\"modify.php?action=add&list=seen&id=".$imdbID."\"><img src=\"".$eyeball."\"></a></span> ";
		}
		
		if($chkList) {
			$listIcon = " <span title=\"Remove from Watchlist\"><a href=\"modify.php?action=remove&list=watchlist&id=".$imdbID."\"><img src=\"".$watchlist."\"></a></span>";
		}
		
		if(!$chkList) {
			$listIcon = " <span title=\"Add to Watchlist\"><a href=\"modify.php?action=add&list=watchlist&id=".$imdbID."\"><img src=\"".$watchlist."\"></a></span>";
		}
		$icons = $eyeCon.$listIcon;
	} else {
		$icons = "<span title=\"register\"><a href=\"register.php\"><img src=\"".$eyeball."\"></a></span> <span title=\"register\"><a href=\"register.php\"><img src=\"".$watchlist."\"></a></span>";
	}
	
	print("
		<div class=\"row\">
			<div class=\"col-sm-7\">
				<div class=\"panel panel-primary\">
					<div class=\"panel-heading\">
						<h4 class=\"media-heading\">".$title." (".$year.")</h4>
					</div>
					<div class=\"panel-body\">
						<div class=\"media\">
							<div class=\"media-left media-middle\">
								<img class=\"media-object\" width=\"128\" height=\"189\" src=\"".$poster."\">
								".$icons."
								<a href=\"http://imdb.com/title/".$imdbID."\"><span title=\"View on IMDb\" class=\"glyphicon glyphicon-info-sign\" aria-hidden=\"true\"></span></a>
							</div>
							<div class=\"media-body\">
								<div class=\"media-heading\">
									<span title=\"Rating\">".$rated."</span> | <span title=\"Runtime\">".$runtime."</span> | <span title=\"Genre\">".$genre."</span> | <span title=\"Released\">".$released."</span>
								</div>
							".$plot."
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	");
}

function insertMovie($obj) {
	$imdbID = mysql_real_escape_string($obj['imdbID']);
	$title = mysql_real_escape_string($obj['Title']);
	$year = mysql_real_escape_string($obj['Year']);
	$rated = mysql_real_escape_string($obj['Rated']);
	$released = mysql_real_escape_string($obj['Released']);
	$runtime = mysql_real_escape_string($obj['Runtime']);
	$genre = mysql_real_escape_string($obj['Genre']);
	$director = mysql_real_escape_string($obj['Director']);
	$writer = mysql_real_escape_string($obj['Writer']);
	$actors = mysql_real_escape_string($obj['Actors']);
	$plot = mysql_real_escape_string($obj['Plot']);
	$lang = mysql_real_escape_string($obj['Language']);
	$country = mysql_real_escape_string($obj['Country']);
	$awards = mysql_real_escape_string($obj['Awards']);
	$poster = mysql_real_escape_string($obj['Poster']);
	$metascore = mysql_real_escape_string($obj['Metascore']);
	$imdbRating = mysql_real_escape_string($obj['imdbRating']);
	$imdbVotes = mysql_real_escape_string($obj['imdbVotes']);
	$type = mysql_real_escape_string($obj['Type']);
	
	mysql_query("INSERT INTO movies (mv_id, mv_imdbID, mv_title, mv_year, mv_rated, mv_released, mv_runtime, mv_genre, mv_director, mv_writer, mv_actors, mv_plot, mv_lang, mv_country, mv_awards, mv_posterURL, mv_metascore, mv_imdbRating, mv_imdbVotes, mv_type) VALUES (NULL, '$imdbID', '$title', '$year', '$rated', '$released', '$runtime', '$genre', '$director', '$writer', '$actors', '$plot', '$lang', '$country', '$awards', '$poster', '$metascore', '$imdbRating', '$imdbVotes', '$type')") or die(mysql_error());
}

?>