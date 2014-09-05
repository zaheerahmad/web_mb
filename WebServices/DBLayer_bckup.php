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
             $query="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , p2.guid
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
		$query="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , p2.guid
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
    $result = mysqli_query($con,$query);
    $resultCount = 0;
    if($result != null)
        $resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
    {
        $jsonResponse->code = 0;
        $jsonResponse->status = "Success";
        $jsonResponse->message = "{$resultCount} News found!";
        $jsonResponse->rowCount=$resultCount;

        while($row = mysqli_fetch_array($result))
        {
            $Game = new stdClass();
            $Game->postID=$row["ID"];
            $Game->title=$row["post_title"];
            $Game->content=$row["post_content"];
            $Game->postAuthor=$row["display_name"];
            $Game->img=$row["guid"];

            if (strpos($Game->content,'<iframe') !== false) {
                $Game->content = preg_replace('/<iframe.*?>/, ', $Game->content);   //Remove iframe.. Because we were getting Iframe in content somewhere.
            }
            
            $Game->content=strip_tags($Game->content);
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

            if (strpos($Game->content,'<iframe') !== false) {
                $Game->content = preg_replace('/<iframe.*?>/, ', $Game->content);   //Remove iframe.. Because we were getting Iframe in content somewhere.
            }
            
            $Game->content=strip_tags($Game->content);
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
    
    $query="SELECT p.post_title,p.post_content,p.post_date,usr.display_name,p2.guid,round(avg(g.Score),1) rating
			FROM wp_gamingordering g , wp_posts p,wp_users usr,wp_posts p2
			WHERE p.ID={$postID}
			AND p.post_author = usr.ID
			AND p.ID = p2.post_parent 
			AND p2.post_type = 'attachment' ;";
	
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
            AND p2.post_type = 'attachment';";
	
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
        $c = $row["post_content"];
//        echo "C : " . $c;
//        echo "\n\n\n";
        $cho = "After seeing what E3 had to offer this year, James and I decided to create a list with our top games of E3 2014. While there were a lot of great looking games shown, there were also a few surprises (many of those from Nintendo).   These are the games are looking forward to the most.   Jamie's top 5: 5. BloodBorne – I am a huge fan of the Dark Souls series, so this one is a no brainer to me. With From Software developing it, you know the graphics and gameplay mechanics will be awesome. The fact that they are trying to make the combat a little faster and more aggressive is a huge bonus. Who doesn't want to dual wield a transforming sword and a gun? 4. Mario Maker – I grew up with an NES and the Super Mario games just brings back some great memories. Now, I can re-live those memories and come up with my own diabolical level design to share with the world. Taking Nintendo’s most treasured franchise and giving players what basically amounts to an unlimited number of levels could be the fresh twist this series needed. This seems like the kind of game that will bring Nintendo back. 3. Zelda – I don't own a WiiU yet, but between this and the aforementioned Mario Maker, I will be purchasing one soon. I know the video was brief, but looking at the lush world and how open it was (I will be exploring those mountains) just left me dumbfounded. You know the gameplay and controls will be great, so how can you not be excited for this one? 2. No Man's Sky – This game may sound overly ambitious, but the trailer nearly made me fall out of my chair. The colorful graphics and fully populated worlds make the possibilities seem endless. The main gameplay mechanic is right up my alley. You start at the edge of a galaxy and need to make your way to the center. You must stop off at random planets to gather resources for upgrades to your ship so you can complete your journey. You can focus on combat, trading, or exploration.  The world is so alive and open that it comes close to feeling like an MMO for me, which peaks my interest. 1. Uncharted 4: A Thief’s End – This was definitely the best game at E3 this year. What can be said about Uncharted that already hasn't been said? This game has it all. Mechanics, graphics, sound, and on top of it the story is always great. It is so easy to get pulled into this world. I don't have as much time to play video games as I used to, but I have made sure to experience each and every Uncharted title that’s come out to the fullest extent, and I plan to do the same with this one.   Travis' top 5: 5. Xenoblade Chronicles X – This game is on the list because Xenoblade Chronicles was one of the best games the previous generation of consoles. The huge world, great combat, and compelling story kept me playing until the end. It also pushed the Wii in terms of graphics, so I expect nothing less from the follow up on the WiiU. The great graphics and smooth gameplay look to build upon the first one with more action thrown into the role playing mix. However, even if there was no gameplay to show yet, the pedigree of the original raised my excitement levels high enough to include this one on my list. 4. Sunset Overdrive – Upon first glance, this game makes me think of Infamous 1 and 2. Why? Because of the open world, and all of the grinding. But what keeps me interested are the colorful graphics and the insane weapons. Who wouldn’t want to strap a terrified teddy bear on to the end of a rocket? If Insomniac knows one thing, it’s how to make some of the most creative arsenals for players to blast enemies with, as both the Ratchet and Clanks and Resistance illustrated. 3. Mortal Kombat X – I know. It’s just another Mortal Kombat. And I don’t care. It looks awesome to me. I was a huge fan of the last Mortal Kombat, and this one looks to keep the tradition of high quality with the series. The gameplay trailer featured two different backgrounds, both of which looked amazing. Fighters can now use the background to their advantage by doing things like swinging on trees for extra hits (looks like the experience with Injustice is spilling over into the Mortal Kombat world). But Mortal Kombat is all about the hard hitting moves and fatalities, and they didn’t disappoint me in the least. This is a game that knows it’s a game by playing up the most unrealistic, but fantastic aspects of combat, resulting in a big, dumb grin on my face. 2. Zelda – Speaking of having a big, dumb grin, that’s probably how I looked after witnessing the trailer for Zelda on the WiiU. The first thing that struck me was the wide open world. It looked like a colorful version of Skyrim, with hills, trees, and mountains in the distance. But once the enemy started chasing Link, the animation and effects floored me. The fire and water were fantastic and breathtaking. The combat looked fun (even if it was scripted in the video), and the introduction of this version of Link couldn’t have been more perfect. If you don’t own a WiiU yet, this might be the reason to finally snag one. 1. Uncharted 4: A Thief’s End – I already know I’m going to love this game. I loved all of the previous Uncharted titles. And honestly, as great as the gameplay is, it has always been about the characters to me. I grew to adore each one of the mainstays within the franchise as they felt real, and honestly, quite special. Just seeing Nate, and hearing his voice against Sully’s did it for me. However, even if you view the Uncharted 4 trailer from only a technical standpoint, it shows that truly exciting things are happening for the Playstation 4. Those graphics were incredible. The mud, the water, foliage, and the facial expressions that Nate made all blended together so perfectly, and in such perfect harmony that much of the impressive detail could be lost if you pay too close attention to the words being spoken. And that’s why Uncharted 4 is my top game from E3 2014. ";
        $Game->content=$cho;
        $Game->postAuthor=$row["display_name"];
        $Game->img=$row["guid"];

        if (strpos($Game->content,'<iframe') !== false) 
        {
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

        //echo var_dump($jsonResponse);

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