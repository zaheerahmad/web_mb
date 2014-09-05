<?php
include 'config.php';
include 'response.php';

error_reporting(E_ERROR | E_PARSE);

function getNews($platform)
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
            //Query for top 3 news based on Views
            $query1="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , p2.guid
                    FROM wp_posts p, wp_users usr ,  wp_posts p2
                    WHERE p.post_type = 'post' 
                    AND p.post_author = usr.ID  
                    AND p.ID = p2.post_parent 
                    AND p2.post_type = 'attachment'
                    GROUP BY p.ID
                    ORDER BY p.viewed DESC,p2.post_modified ASC LIMIT 0,3;";
            
            
            $result1 = mysqli_query($con,$query1);
            $resultCount1 = 0;
            if($result1 != null)
                $resultCount1 = mysqli_num_rows($result1);
            if($resultCount1 > 0)
            {
                while($row = mysqli_fetch_array($result1))
                {
                    $News = new stdClass();
                    $News->postID=$row["ID"];
                    $News->title=$row["post_title"];
                    $News->content=$row["post_content"];
                    $News->postAuthor=$row["display_name"];
                    $News->img=$row["guid"];

                    /*if (strpos($News->content,'<iframe') != false) {
                        $News->content = preg_replace('/<iframe.*?>/, ', $News->content);   //Remove iframe.. Because we were getting Iframe in content somewhere.
                    }*/

                    $News->content=strip_tags($News->content);
                    $News->content = htmlentities($News->content, UTF-8);
                    $News->content=substr($News->content,0,50);
                    
                    $dateSrc = $row["post_date"];
                    $dateTime = date_create( $dateSrc);;
                    $News->postDate=date_format( $dateTime, 'F d,Y');
                    
                    array_push($jsonResponse->response,$News);
                }
            }
           else
           {
               $jsonResponse->code = 1;
               $jsonResponse->status = "Error";
               $jsonResponse->message = "No News found!";
               return json_encode($jsonResponse);
           }

             $query2="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , p2.guid
                    FROM wp_posts p, wp_users usr ,  wp_posts p2
                    WHERE p.post_type = 'post' 
                    AND p.post_author = usr.ID  
                    AND p.ID = p2.post_parent 
                    AND p2.post_type = 'attachment'
                    GROUP BY p.ID
                    ORDER BY p.post_date DESC,p2.post_modified ASC LIMIT 0,100;";
             

    }
    else 
    {
            //Query for top 3 news based on Views
            $query1="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , p2.guid
                    FROM wp_posts p,wp_posts p2 , wp_term_relationships tr , wp_term_taxonomy tt , wp_terms t, wp_users usr
                    WHERE p.ID=tr.object_id
                    AND tr.term_taxonomy_id=tt.term_taxonomy_id
                    AND tt.term_id=t.term_id
                    AND t.slug='{$platform}'
                    AND p.post_type = 'post'
                    AND p.post_author = usr.ID  
                    AND p.ID = p2.post_parent
                    AND p2.post_type = 'attachment'
                    GROUP BY p.ID
                    ORDER BY p.viewed DESC,p2.post_modified ASC LIMIT 0,3;";
        
                    $result1 = mysqli_query($con,$query1);
            $resultCount1 = 0;
            if($result1 != null)
                $resultCount1 = mysqli_num_rows($result1);
            if($resultCount1 > 0)
            {
                while($row = mysqli_fetch_array($result1))
                {
                    $News = new stdClass();
                    $News->postID=$row["ID"];
                    $News->title=$row["post_title"];
                    $News->content=$row["post_content"];
                    $News->postAuthor=$row["display_name"];
                    $News->img=$row["guid"];

                    /*if (strpos($News->content,'<iframe') != false) {
                        $News->content = preg_replace('/<iframe.*?>/, ', $News->content);   //Remove iframe.. Because we were getting Iframe in content somewhere.
                    }*/

                    $News->content=strip_tags($News->content);
                    $News->content = htmlentities($News->content, UTF-8);
                    $News->content=substr($News->content,0,50);
                    
                    $dateSrc = $row["post_date"];
                    $dateTime = date_create( $dateSrc);;
                    $News->postDate=date_format( $dateTime, 'F d,Y');
                    
                    array_push($jsonResponse->response,$News);
                }
            }
           else
           {
               $jsonResponse->code = 1;
               $jsonResponse->status = "Error";
               $jsonResponse->message = "No News found!";
               return json_encode($jsonResponse);
           }
        
        $query2="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , p2.guid
                        FROM wp_posts p,wp_posts p2 , wp_term_relationships tr , wp_term_taxonomy tt , wp_terms t, wp_users usr
                        WHERE p.ID=tr.object_id
                        AND tr.term_taxonomy_id=tt.term_taxonomy_id
                        AND tt.term_id=t.term_id
                        AND t.slug='{$platform}'
                        AND p.post_type = 'post'
                        AND p.post_author = usr.ID  
                        AND p.ID = p2.post_parent
                        AND p2.post_type = 'attachment'
                        GROUP BY p.ID
                        ORDER BY p.post_date DESC,p2.post_modified ASC LIMIT 0,100;";
    
    }
    
           $result2 = mysqli_query($con,$query2);
           $resultCount2 = 0;
           if($result2 != null)
               $resultCount2 = mysqli_num_rows($result2);
           if($resultCount2 > 0)
           {
               $jsonResponse->code = 0;
               $jsonResponse->status = "Success";
               $jsonResponse->message = "{$resultCount} News found!";
               $jsonResponse->rowCount=$resultCount2 +$resultCount1;

               while($row = mysqli_fetch_array($result2))
               {
                   $News = new stdClass();
                   $News->postID=$row["ID"];
                   $News->title=$row["post_title"];
                   $News->content=$row["post_content"];
                   $News->postAuthor=$row["display_name"];
                   $News->img=$row["guid"];

                   /*if (strpos($News->content,'<iframe') != false) {
                        $News->content = preg_replace('/<iframe.*?>/, ', $News->content);   //Remove iframe.. Because we were getting Iframe in content somewhere.
                    }*/

                   $News->content=strip_tags($News->content);
                   $News->content = htmlentities($News->content, UTF-8);
                   $News->content=substr($News->content,0,50);
                   $dateSrc = $row["post_date"];
                   $dateTime = date_create( $dateSrc);;
                   $News->postDate=date_format( $dateTime, 'F d,Y');
                   array_push($jsonResponse->response,$News);
               }
               return json_encode($jsonResponse);
           }
           else
           {
               $jsonResponse->code = 1;
               $jsonResponse->status = "Error";
               $jsonResponse->message = "No News found!";
               return json_encode($jsonResponse);
           }	
    
}
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

            /*if (strpos($News->content,'<iframe') != false) {
                        $News->content = preg_replace('/<iframe.*?>/, ', $News->content);   //Remove iframe.. Because we were getting Iframe in content somewhere.
                    }*/
            
            $Game->content=strip_tags($Game->content);
            $Game->content = htmlentities($Game->content, UTF-8);
            $Game->content=substr($Game->content,0,50);
            
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
    
    $query="SELECT p.post_title,p.post_content,p.post_date,usr.display_name,p2.guid,round(avg(g.Score),1) rating,pm.meta_value
            FROM wp_gamingordering g , wp_posts p,wp_users usr,wp_posts p2,wp_postmeta pm
            WHERE p.ID={$postID}
            AND p.post_author = usr.ID
            AND p.ID = p2.post_parent 
            AND p2.post_type = 'attachment'
            AND p.ID=pm.post_id
            AND pm.meta_key='GameInformationData'";
	
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
		
            /*if (strpos($News->content,'<iframe') != false) {
                        $News->content = preg_replace('/<iframe.*?>/, ', $News->content);   //Remove iframe.. Because we were getting Iframe in content somewhere.
                    }*/
            
            $Game->content=strip_tags($Game->content);
            $Game->content = htmlentities($Game->content, UTF-8);
            
            $dateSrc = $row["post_date"];
            $dateTime = date_create( $dateSrc);;
            $Game->postDate=date_format( $dateTime, 'F d,Y');
			
            $GameInformationData=$row["meta_value"];
            $GameInformationData=unserialize($GameInformationData);
            
            $Game->releaseDate= $GameInformationData['release_date'];
            $Game->consoles= $GameInformationData['game_console'];
            $Game->company= $GameInformationData['game_company'];
            $Game->esrb_rating= $GameInformationData['esrb_rating'];
            
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

function getDetailsOfNews($postID)
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
    
    $query="SELECT p.post_title,p.post_content,p.post_date,usr.display_name,p2.guid
            FROM wp_posts p,wp_users usr,wp_posts p2
            WHERE p.ID={$postID}
            AND p.post_author = usr.ID
            AND p.ID = p2.post_parent 
            AND p2.post_type = 'attachment'
            GROUP BY p.ID
            ORDER BY p.post_date DESC,p2.post_modified ASC;";
	
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

		$News = new stdClass();
                $News->categories=array();
                $News->title=$row["post_title"];
		$News->content=$row["post_content"];
		$News->postAuthor=$row["display_name"];
		$News->img=$row["guid"];
		
            /*if (strpos($News->content,'<iframe') != false) {
                        $News->content = preg_replace('/<iframe.*?>/, ', $News->content);   //Remove iframe.. Because we were getting Iframe in content somewhere.
                    }*/
            
            $News->content=strip_tags($News->content);
            $News->content = htmlentities($News->content, UTF-8);
			
            $dateSrc = $row["post_date"];
            $dateTime = date_create( $dateSrc);;
            $News->postDate=date_format( $dateTime, 'F d,Y');
			
			
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
					array_push($News->categories,$category);
					
				}
				
			}
	array_push($jsonResponse->response,$News);
        return json_encode($jsonResponse);
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No News Detail found!";
        return json_encode($jsonResponse);
    }

}


?>