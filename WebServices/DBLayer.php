<?php
include 'config.php';
include 'response.php';
include 'pluggable.php';


header('Content-type: application/json');
error_reporting(E_ERROR | E_PARSE);

function Login($username, $password)
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
	
		
	$query="SELECT *
			FROM wp_users
			WHERE user_login='{$username}'";

	
	$result = mysqli_query($con,$query);
	
	if($result != null)
        $resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
    {
			$row = mysqli_fetch_array($result);
		
			$check=wp_check_password($password,$row["user_pass"]);
			if($check)
			{
				$obj = new stdClass();
				$obj->display_name = $row["display_name"];
				
				$query2="SELECT  um.meta_value as img
						FROM  wp_usermeta um
						WHERE um.user_id={$row["ID"]}
						AND um.meta_key='profile_image'";
				
				$result2 = mysqli_query($con,$query2);
	
				if($result2 != null)
				{
					$resultCount2 = mysqli_num_rows($result2);
					
					if($resultCount2 > 0)
					{
							$row2 = mysqli_fetch_array($result2);
						
						$ImgURL=$row2["img"];
						$ImgURL=unserialize($ImgURL);
            
						$obj->img= $ImgURL['url'];
					}
					else
					{
						$obj->img="images/profile.gif";
					}
				}
				else
				{
					$obj->img="images/profile.gif";
				}
				
				$jsonResponse->code = 0;
				$jsonResponse->status = "Success";
				$jsonResponse->message = "User Found!";
				$jsonResponse->response = $obj;
				
				session_start();
				session_name('Global');
				$_SESSION['username'] = $username;
				
				return json_encode($jsonResponse);
			}
			else
			{
				 $jsonResponse->code = 1;
				$jsonResponse->status = "Error";
				$jsonResponse->message = "Invalid Username/Password";
				return json_encode($jsonResponse);
			}
		
			
		
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "Invalid Username/Password";
        return json_encode($jsonResponse);
    }

}

function Register($username, $password,$email,$dob,$gender)
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
	
	$nicename=strtolower($username);
	$hashed=wp_hash_password( $password );
	
	$query="INSERT INTO wp_users (user_login,user_pass,user_nicename,user_email,user_registered,user_status,display_name)
			VALUES ('{$username}', '{$hashed}','{$nicename}','{$email}',NOW(),0,'{$username}')";
			
		$result = mysqli_query($con,$query);
		
		if($result)
		{
				/*$key = wp_generate_password( 20, false );
				$wp_hasher = new PasswordHash( 8, true );
				$hashedKey = $wp_hasher->HashPassword( $key );
				
				$obj = new stdClass();
				$obj->key=$hashedKey;
				$jsonResponse->response = $obj;*/
		
				$jsonResponse->code = 0;
				$jsonResponse->status = "Success";
				$jsonResponse->message = "User Registered Successfully!";
				return json_encode($jsonResponse);
		}
		else
		{
			$jsonResponse->code = 1;
			$jsonResponse->status = "Error";
			$jsonResponse->message = "User Not Registered";
			return json_encode($jsonResponse);
		}
}

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

                    $News->content = str_replace("//www.","http://www.",$News->content);
                    $News->content= strip_tags($News->content);
					$News->content = preg_replace("/\[.*?\]/", "", $News->content);
					$News->content=cleanString($News->content);
					$News->content = utf8_encode($News->content);
                    $News->content=substr($News->content,0,50);
                    
                    $dateSrc = $row["post_date"];
                    $dateTime = date_create( $dateSrc);
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

                    $News->content = str_replace("//www.","http://www.",$News->content);
					$News->content = preg_replace("/\[.*?\]/", "", $News->content);
					$News->content=cleanString($News->content);
					$News->content = utf8_encode($News->content);
                    $News->content=substr($News->content,0,50);
                    
                    $dateSrc = $row["post_date"];
                    $dateTime = date_create( $dateSrc);
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

                   $News->content = str_replace("//www.","http://www.",$News->content);
				   $News->content = preg_replace("/\[.*?\]/", "", $News->content);
					$News->content=cleanString($News->content);
					$News->content = utf8_encode($News->content);
                   $News->content=substr($News->content,0,50);
				   
                   $dateSrc = $row["post_date"];
                   $dateTime = date_create( $dateSrc);
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

            $Game->content = str_replace("//www.","http://www.",$Game->content);
			$Game->content = preg_replace("/\[.*?\]/", "", $Game->content);
            $Game->content=cleanString($Game->content);
			$Game->content = utf8_encode($Game->content);
            $Game->content=substr($Game->content,0,50);
            
            $dateSrc = $row["post_date"];
            $dateTime = date_create( $dateSrc);
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
	
           $Game->content = str_replace("//www.","http://www.",$Game->content);
		   $Game->content = preg_replace("/\[.*?\]/", "", $Game->content);
            $Game->content=cleanString($Game->content);
			$Game->content = utf8_encode($Game->content);
            
            $dateSrc = $row["post_date"];
            $dateTime = date_create( $dateSrc);
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
		

			$News->content = str_replace("//www.","http://www.",$News->content);
			$News->content = preg_replace("/\[.*?\]/", "", $News->content);
            $News->content=cleanString($News->content);
			$News->content = utf8_encode($News->content);
            $dateSrc = $row["post_date"];
            $dateTime = date_create( $dateSrc);
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

function getMeltingPointStories()
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
		
		$query="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date 
				FROM wp_posts p, wp_users usr
				WHERE p.post_type = 'meltingpointstories' 
				AND p.post_author = usr.ID
				AND post_status='publish'
				ORDER BY p.post_date DESC;";
		
		$result = mysqli_query($con,$query);
    $resultCount = 0;
    if($result != null)
        $resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
    {
        $jsonResponse->code = 0;
        $jsonResponse->status = "Success";
        $jsonResponse->message = "{$resultCount} Stories found!";
        $jsonResponse->rowCount=$resultCount;

        while($row = mysqli_fetch_array($result))
        {
            $mpStory = new stdClass();
            $mpStory->postID=$row["ID"];
            $mpStory->title=$row["post_title"];
            $mpStory->content=$row["post_content"];
            $mpStory->postAuthor=$row["display_name"];
        
            $mpStory->content = str_replace("//www.","http://www.",$mpStory->content);
			$mpStory->content = preg_replace("/\[.*?\]/", "", $mpStory->content);
            $mpStory->content=cleanString($mpStory->content);
			$mpStory->content = utf8_encode($mpStory->content);
            $mpStory->content=substr($mpStory->content,0,50);
            
            $dateSrc = $row["post_date"];
            $dateTime = date_create( $dateSrc);
            $mpStory->postDate=date_format( $dateTime, 'F d,Y');
            
            array_push($jsonResponse->response,$mpStory);
        }
        return json_encode($jsonResponse);
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No Story found!";
        return json_encode($jsonResponse);
    }
		
}

function getMeltingPointStoryDetail($postID)
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
	
	$query="SELECT p.post_title,p.post_content,p.post_date,usr.display_name
			FROM wp_posts p,wp_users usr
			WHERE p.ID={$postID}
			AND p.post_author = usr.ID";
	
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

            $mpStory = new stdClass();
            $mpStory->title=$row["post_title"];
            $mpStory->content=$row["post_content"];
            $mpStory->postAuthor=$row["display_name"];
        
            $mpStory->content = str_replace("//www.","http://www.",$mpStory->content);
			$mpStory->content = preg_replace("/\[.*?\]/", "", $mpStory->content);
            $mpStory->content=cleanString($mpStory->content);
			$mpStory->content = utf8_encode($mpStory->content);

            $dateSrc = $row["post_date"];
            $dateTime = date_create( $dateSrc);
            $mpStory->postDate=date_format( $dateTime, 'F d,Y');
            
        array_push($jsonResponse->response,$mpStory);
        return json_encode($jsonResponse);
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No Story Detail found!";
        return json_encode($jsonResponse);
    }
}

function searchGames($platform,$alphabet)
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
				WHERE p.post_type = 'game' 
				AND p.post_title LIKE '{$alphabet}%'
				AND p.post_author = usr.ID 
				AND p.ID = g.GameID 
				AND p.ID = p2.post_parent 
				AND p2.post_type = 'attachment'
				GROUP BY g.GameID
				ORDER BY p.post_title;";
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
				AND p.post_title LIKE '{$alphabet}%'
				AND p.post_author = usr.ID 
				AND p.ID = g.GameID 
				AND p.ID = p2.post_parent
				AND p2.post_type = 'attachment'
				GROUP BY g.GameID
				ORDER BY p.post_title;";
	
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

            $Game->content = str_replace("//www.","http://www.",$Game->content);
			$Game->content = preg_replace("/\[.*?\]/", "", $Game->content);
            $Game->content=cleanString($Game->content);
			$Game->content = utf8_encode($Game->content);
            $Game->content=substr($Game->content,0,50);
            
            $dateSrc = $row["post_date"];
            $dateTime = date_create( $dateSrc);
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

function getForumCategories()
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
	
	$query="SELECT ID,post_title,post_content
			FROM wp_posts
			WHERE post_type='forum';";
	
	$result = mysqli_query($con,$query);
    $resultCount = 0;
    if($result != null)
        $resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
    {
        $jsonResponse->code = 0;
        $jsonResponse->status = "Success";
        $jsonResponse->message = "{$resultCount} Categories found!";
        $jsonResponse->rowCount=$resultCount;

        while($row = mysqli_fetch_array($result))
        {
            $Forum = new stdClass();
            $Forum->postID=$row["ID"];
            $Forum->title=$row["post_title"];
            $Forum->content=$row["post_content"];

            $Forum->content = str_replace("//www.","http://www.",$Forum->content);
			$Forum->content = preg_replace("/\[.*?\]/", "", $Forum->content);
            $Forum->content=cleanString($Forum->content);
			$Forum->content = utf8_encode($Forum->content);
       
            array_push($jsonResponse->response,$Forum);
        }
        return json_encode($jsonResponse);
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No Categories found!";
        return json_encode($jsonResponse);
    }
}

function getForumTopics($postID)
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
		
		
		$query="SELECT p.ID,p.post_title, pm.meta_value as comment_count , pm2.meta_value as last_active_time
				FROM wp_posts p , wp_postmeta pm, wp_postmeta pm2
				WHERE post_type = 'topic'
				AND post_parent = {$postID}
				AND pm.post_id = p.ID
				AND pm2.post_id = p.ID
				AND pm.meta_key = '_bbp_reply_count'
				AND pm2.meta_key = '_bbp_last_active_time'
				GROUP BY p.post_title
				ORDER BY last_active_time DESC;";
	
			$result = mysqli_query($con,$query);
			$resultCount = 0;
			if($result != null)
				$resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
    {
        $jsonResponse->code = 0;
        $jsonResponse->status = "Success";
        $jsonResponse->message = "{$resultCount} Topic(s) found!";
        $jsonResponse->rowCount=$resultCount;

        while($row = mysqli_fetch_array($result))
        {
            $Topic = new stdClass();
            $Topic->postID=$row["ID"];
            $Topic->title=$row["post_title"];
			$Topic->comment_count=(int)$row["comment_count"];
			$Topic->comment_count=$Topic->comment_count+1;
			
			$dateSrc = $row["last_active_time"];
			
			$retrieveDate = new DateTime($dateSrc);
			$currDate=new DateTime(date("Y-m-d H:i:s"));
			
			$interval = $retrieveDate->diff($currDate);
			
			$Topic->last_active_time= timeFormat($interval);
		       
            array_push($jsonResponse->response,$Topic);
        }
        return json_encode($jsonResponse);
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No Topic found!";
        return json_encode($jsonResponse);
    }
		
		
}

function getTopicReplies($postID)
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
		
		$query="SELECT  p.ID ,p.post_author, p.post_date , p.post_content,u.display_name
				FROM wp_posts p ,wp_posts p2 , wp_users u 
				WHERE( p.ID={$postID} OR p.post_parent={$postID})
				AND p.post_author=u.ID
				GROUP BY p.ID
				ORDER BY p.menu_order;";
				
			$result = mysqli_query($con,$query);
			$resultCount = 0;
			if($result != null)
				$resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
    {
        $jsonResponse->code = 0;
        $jsonResponse->status = "Success";
        $jsonResponse->message = "{$resultCount} Replies found!";
        $jsonResponse->rowCount=$resultCount;

        while($row = mysqli_fetch_array($result))
        {
            $Topic = new stdClass();
            $Topic->postID=$row["ID"];
			$Topic->display_name=$row["display_name"];
			$Topic->content=$row["post_content"];
			
            $Topic->content = str_replace("//www.","http://www.",$Topic->content);
			$Topic->content = preg_replace("/\[.*?\]/", "", $Topic->content);
            $Topic->content=cleanString($Topic->content);
			$Topic->content = utf8_encode($Topic->content);
			
			$dateSrc = $row["post_date"];
            $dateTime = date_create( $dateSrc);
            $Topic->postDate=date_format( $dateTime, 'F d,Y \a\t h:i a');
			
			
			$query2="SELECT meta_value as img
					FROM wp_usermeta
					WHERE user_id={$row["post_author"]}
					AND meta_key='profile_image';";
				
				$result2 = mysqli_query($con,$query2);
	
				if($result2 != null)
				{
					$resultCount2 = mysqli_num_rows($result2);
					
					if($resultCount2 > 0)
					{
							$row2 = mysqli_fetch_array($result2);
						
						$ImgURL=$row2["img"];
						$ImgURL=unserialize($ImgURL);
            
						$Topic->img= $ImgURL['url'];
					}
					else
					{
						$Topic->img="images/profile.gif";
					}
				}
				else
				{
					$Topic->img="images/profile.gif";
				}
	
            array_push($jsonResponse->response,$Topic);
        }
        return json_encode($jsonResponse);
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No Reply found!";
        return json_encode($jsonResponse);
    }
		
}
function timeFormat($interval)
{
	$timeString='';
			
			if($interval->y != '0' ) 
			{	
				$timeString=$timeString.' ' .$interval->y.' years ';
			}
			
			if($interval->m != '0' )  
			{
				$timeString =  $timeString .$interval->m.' months ';
			}
			
			if($interval->y == '0' && $interval->m != '0' )
			{
				if($interval->d != 0 )  $timeString =  $timeString .$interval->d.' days ';
			}
			
			
			if($timeString == '' )
			{
				if($interval->h != '0' ) 
				{
					$timeString=$timeString.' ' .$interval->h.' hours ';
				}
				if($interval->i != '0' ) 
				{				
					$timeString =  $timeString .$interval->i.' minutes ';
				}
			}
			
			if($timeString == '' )
			{
				if($interval->s != '0' ) 
				{
					$timeString =  $timeString . $interval->s.' secs';
				}
			}
			
			$timeString=$timeString.'ago';
			
			return $timeString;
}

function cleanString($inputString)
{
			$inputString=str_replace("&#145;", "'",$inputString);
			$inputString=str_replace("&#146;", "'",$inputString);
			$inputString=str_replace("&rsquo;", "'",$inputString);
			$inputString=str_replace("&lsquo;", "'",$inputString);
			$inputString=str_replace("&ldquo;", "\"",$inputString);
			$inputString=str_replace("&rdquo;", "\"",$inputString);
			$inputString=str_replace("&#147;", "\"",$inputString);
			$inputString=str_replace("&#148;", "\"",$inputString);
			$inputString=str_replace("&#8220;", "\"",$inputString);
			$inputString=str_replace("&#8221;", "\"",$inputString);
			$inputString=str_replace("&#149;", "•",$inputString);
			$inputString=str_replace("&nbsp;", " ",$inputString);
			$inputString=str_replace("&#183;", "·",$inputString);
			$inputString=str_replace("&#159;", "•",$inputString);
			$inputString=str_replace("&#151;", "-",$inputString);
			$inputString=str_replace("&#160;", " ",$inputString);
			$inputString=str_replace("&#038;", "&",$inputString);
			$inputString=str_replace("&#215;", "×",$inputString);
			$inputString=str_replace("&#216;", "Ø",$inputString);
			$inputString=str_replace("&#133;", "...",$inputString);
			$inputString=str_replace("&#150;", "-",$inputString);
			$inputString=str_replace("&#8217;", "'",$inputString);
			$inputString=str_replace("&#8226;", "•",$inputString);
			$inputString=str_replace("&#167;", "§",$inputString);
			$inputString=str_replace("&amp;", "&",$inputString);
			$inputString=str_replace("&bull;", "•",$inputString);
			$inputString=str_replace("’", "'",$inputString);
			$inputString=str_replace("‘", "'",$inputString);
			$inputString=str_replace("&quot;", "\"",$inputString);
			$inputString=str_replace("&trade;", "™",$inputString);
			$inputString=str_replace("&rsaquo;", "›",$inputString);
			$inputString=str_replace("&lsaquo;", "‹",$inputString);
			$inputString=str_replace("&dagger;", "†",$inputString);
			$inputString=str_replace("&bdquo;", "„",$inputString);
			$inputString=str_replace("&frasl;", "/",$inputString);
			$inputString=str_replace("&lt;", "<",$inputString);
			$inputString=str_replace("&gt;", ">",$inputString);
			$inputString=str_replace("&ndash;", "-",$inputString);
			$inputString=str_replace("&cent;", "¢",$inputString);
			$inputString=str_replace("&pound;", "£",$inputString);
			$inputString=str_replace("&yen;", "¥",$inputString);
			$inputString=str_replace("&sect;", "§",$inputString);
			$inputString=str_replace("&uml;", "¨",$inputString);
			$inputString=str_replace("&die;", "¨",$inputString);
			$inputString=str_replace("&copy;", "©",$inputString);
			$inputString=str_replace("&reg;", "®",$inputString);
			$inputString=str_replace("&sup2;", "²",$inputString);
			$inputString=str_replace("&sup3;", "³",$inputString);
			$inputString=str_replace("&middot;", "·",$inputString);
			$inputString=str_replace("&euro;", "€",$inputString);
			$inputString=str_replace("&brvbar;", "¦",$inputString);
			$inputString=str_replace("&#8230;", "...",$inputString);
			$inputString=str_replace("&#09;", " ",$inputString);
			$inputString=str_replace("&mdash;", "-",$inputString);
			$inputString=str_replace("â€™", "'",$inputString);
			$inputString=str_replace("â€œ", "\"",$inputString);
			$inputString=str_replace("â€ ", "\"",$inputString);
			$inputString=str_replace("â€“", "-",$inputString);
			$inputString=str_replace("â€˜", "'",$inputString);
			$inputString=str_replace("&#8217;", "'",$inputString);
			$inputString=str_replace("&amp;", "&",$inputString);
			$inputString= str_replace("\n","<br/>",$inputString);
			$inputString= str_replace("\r\r","<br/>",$inputString);
			$inputString=str_replace("&#167;", "§",$inputString);
			$inputString=str_replace("�", "–",$inputString);
			
			$inputString=str_replace("&#8212;","",$inputString);
			$inputString=str_replace("&#8216;","",$inputString);
			$inputString=str_replace("&#8220;","",$inputString);
			$inputString=str_replace("&#8221;","",$inputString);
			$inputString=str_replace("&#39;","'",$inputString);
			$inputString=str_replace("&quot;","",$inputString);
			
			return $inputString;

}

?>