<?php
include 'config.php';
include 'response.php';

error_reporting(E_ERROR | E_PARSE);

function getTopTenGames($platform) 
{
    
    $jsonResponse = new responsejson();
    $config = new configuration();
    $con = mysqli_connect($config->server, $config->user, $config->password, $config->db);
    if (!$con) 
    {
        $jsonResponse->code = -1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "Connection to db failed!";
        return json_encode($jsonResponse);
    }
    
    if($platform=='all')
    {
         $query="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , round(avg(g.Score),1) rating , p2.guid
                FROM wp_posts p, wp_users usr , wp_gamingordering g , wp_posts p2
                WHERE p.post_type = 'game' AND p.post_author = usr.ID AND p.ID = g.GameID AND p.ID = p2.post_parent AND p2.post_type = 'attachment'
				GROUP BY g.GameID
				ORDER BY rating DESC LIMIT 0,10";
    }
    else 
    {
		$query="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , round(avg(g.Score),1) rating , p2.guid
				FROM wp_posts p,wp_posts p2 , wp_term_relationships tr , wp_term_taxonomy tt , wp_terms t, wp_users usr, wp_gamingordering g
				WHERE p.ID=tr.object_id
				AND tr.term_taxonomy_id=tt.term_taxonomy_id
				AND tt.term_id=t.term_id
				AND t.slug='{$platform}'
				AND p.post_type = 'game'
				AND p.post_author = usr.ID 
				AND p.ID = g.GameID 
				AND p.ID = p2.post_parent
				AND p2.post_type = 'attachment'
				GROUP BY g.GameID
				ORDER BY rating DESC LIMIT 0,10;";
	
    }
    $result = mysqli_query($con,$query);
    $resultCount = 0;
    if($result != null)
        $resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
    {
        $jsonResponse->code = 0;
        $jsonResponse->status = "Success";
        $jsonResponse->message = "{$resultCount} Game(s) found!";
        $jsonResponse->rowCount=$resultCount;

        while($row = mysqli_fetch_array($result))
        {
            $Game = new stdClass();
            $Game->postID=$row["ID"];
            $Game->title=$row["post_title"];
            $Game->content=$row["post_content"];
            $Game->postAuthor=$row["display_name"];
            $Game->rating=$row["rating"];
            $Game->img=$row["guid"];

            if (strpos($Game->content,'<iframe') !== false) {
                $Game->content = preg_replace('/<iframe.*?>/, ', $Game->content);   //Remove iframe.. Because we were getting Iframe in content somewhere.
            }
            
            $Game->content=strip_tags($Game->content);
            $Game->content=substr($Game->content,0,40);
			$dateSrc = $row["post_date"];
			$dateTime = date_create( $dateSrc);;
			$Game->postDate=date_format( $dateTime, 'F d,Y');
            array_push($jsonResponse->response,$Game);
        }
        return json_encode($jsonResponse);
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No Game found!";
        return json_encode($jsonResponse);
    }
  
}

function getDetailsOfGame($postID)
{
	$jsonResponse = new responsejson();
    $config = new configuration();
    $con = mysqli_connect($config->server, $config->user, $config->password, $config->db);
    if (!$con) 
    {
        $jsonResponse->code = -1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "Connection to db failed!";
        return json_encode($jsonResponse);
    }
    
    $query="SELECT p.post_title,p.post_content,p.post_date,usr.display_name,p2.guid,round(avg(g.Score),1) rating
			FROM wp_gamingordering g , wp_posts p,wp_users usr,wp_posts p2
			WHERE p.ID={$postID} 
			AND p.ID = g.GameID;";
	
	$result = mysqli_query($con,$query);
    $resultCount = 0;
    if($result != null)
        $resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
    {
        $jsonResponse->code = 0;
        $jsonResponse->status = "Success";
        $jsonResponse->message = "Detail(s) found!";
        
		$row = mysqli_fetch_array($result);

		$Game = new stdClass();
        $Game->title=$row["post_title"];
		$Game->content=$row["post_content"];
		$Game->postAuthor=$row["display_name"];
		$Game->rating=$row["rating"];
		$Game->img=$row["guid"];
		
            if (strpos($Game->content,'<iframe') !== false) {
                $Game->content = preg_replace('/<iframe.*?>/, ', $Game->content);   //Remove iframe.. Because we were getting Iframe in content somewhere.
            }
            
            $Game->content=strip_tags($Game->content);
			$dateSrc = $row["post_date"];
			$dateTime = date_create( $dateSrc);;
			$Game->postDate=date_format( $dateTime, 'F d,Y');
			$Game->categories=array();
			
			$query2="SELECT t.name
					FROM wp_posts p , wp_term_relationships tr, wp_term_taxonomy tt , wp_terms t
					WHERE p.ID={$postID}
					AND tt.taxonomy='category'
					AND p.ID=tr.object_id
					AND tr.term_taxonomy_id=tt.term_taxonomy_id
					AND tt.term_id=t.term_id;";
					
			$result2 = mysqli_query($con,$query2);
			$resultCount2 = 0;
			if($result2 != null)
				$resultCount2 = mysqli_num_rows($result2);
			if($resultCount2 > 0)
			{
				$jsonResponse->rowCount=$resultCount2;
				
				while($row2 = mysqli_fetch_array($result2))
				{
					$category=$row2["name"];
					array_push($Game->categories,$category);
					
				}
				
			}
			
			
        array_push($jsonResponse->response,$Game);
     
        return json_encode($jsonResponse);
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No Game Detail found!";
        return json_encode($jsonResponse);
    }
}
?>