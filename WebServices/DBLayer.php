<?php
require_once ('config.php');
require_once ('utils.php');
require_once ('response.php');
require_once ('pluggable.php');

ini_set('memory_limit', '-1');

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
				$ActiveQuery="SELECT meta_value
				FROM wp_usermeta
				WHERE user_id=(SELECT ID FROM wp_users WHERE user_login='{$username}')
				AND meta_key='activation_key'";


				$ActiveResult = mysqli_query($con,$ActiveQuery);
				
				if($ActiveResult != null)
					$resultCountActive = mysqli_num_rows($ActiveResult);
				if($resultCountActive > 0)
				{
					$jsonResponse->code = 2;
					$jsonResponse->status = "Not Active";
					$jsonResponse->message = "Your email is not verified.Please check your inbox for activation link.";
					return json_encode($jsonResponse);
				}
				else
				{
					$obj = new stdClass();
					$obj->display_name = $row["display_name"];
					$obj->ID = $row["ID"];
					
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
					
					updateLastActiveTime($row["ID"]);
					
					array_push($jsonResponse->response,$obj);
					
					session_start();
					session_name('Global');
					$_SESSION['username'] = $username;
					$_SESSION['userID'] = $row["ID"];
					
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
	$SERVER="http://meterbreak.com/app";

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
	
	$msg=checkUser($username,$email);
	
	if($msg=='')
	{
		$nicename=strtolower($username);
		$nicename=str_replace(" ","-",$nicename);
		
		$hashedPassword=wp_hash_password( $password );
		
		$Active_key = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 32);
				
				$dateOfBirth= new DateTime($dob);
				$currDate=new DateTime(date("Y-m-d H:i:s"));
				$interval = $dateOfBirth->diff($currDate);
				$age=$interval->y;
				
				$dob=str_replace("/","-",$dob);
				
				$credentialsArray["user_login"] = $username;
				$credentialsArray["user_password"] = $password;
				$credentialsArray["remember"] = (boolean) 1;
				$credentialStr = serialize($credentialsArray);
				
				$capabilityArray["subscriber"] = (boolean) 1;
				$capabilityStr = serialize($capabilityArray);
				
				
				
				$begin="START TRANSACTION";
				$beginResult = mysqli_query($con,$begin);
				
				if($beginResult != null)
				{
							
							$timeNow = date('Y-m-d H:i:s');
							
							$query="INSERT INTO wp_users (user_login,user_pass,user_nicename,user_email,user_registered,user_status,display_name)
									VALUES ('{$username}', '{$hashedPassword}','{$nicename}','{$email}','{$timeNow}',0,'{$username}')";		
				
							$result = mysqli_query($con,$query);
							
							
							$IDquery="SELECT ID
									FROM wp_users
									WHERE user_login='{$username}'";
		
							$IDresult = mysqli_query($con,$IDquery);
							$row = mysqli_fetch_array($IDresult);
							$userID=$row["ID"];
						
							// INSERT 1
							$insert1="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'gender','{$gender}')";
									  
							$result1 = mysqli_query($con,$insert1); 
							
							// INSERT 2
							$insert2="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'age','{$age}')";
									  
							$result2 = mysqli_query($con,$insert2); 
							
							// INSERT 3
							$insert3="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'yearofbirth','{$dob}')";
									 
							$result3 = mysqli_query($con,$insert3); 
															
							// INSERT 4
							$insert4="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'activation_key','{$Active_key}')";
									  
							$result4 = mysqli_query($con,$insert4);  
							
							// INSERT 5
							$insert5="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'first_name','')";
									  
							$result5 = mysqli_query($con,$insert5);
							
							// INSERT 6
							$insert6="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'last_name','')";
									  
							$result6 = mysqli_query($con,$insert6);
							
							// INSERT 7
							$insert7="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'description','')";
									  
							$result7 = mysqli_query($con,$insert7);
							
							// INSERT 8
							$insert8="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'nickname','{$username}')";
									  
							$result8 = mysqli_query($con,$insert8);
							
							// INSERT 9
							$insert9="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'rich_editing','true')";
									  
							$result9 = mysqli_query($con,$insert9);
							
							// INSERT 10
							$insert10="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'comment_shortcuts','false')";
									  
							$result10 = mysqli_query($con,$insert10);
							
							// INSERT 11
							$insert11="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'admin_color','fresh')";
									  
							$result11 = mysqli_query($con,$insert11);
							
							// INSERT 12
							$insert12="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'show_admin_bar_front','true')";
									  
							$result12 = mysqli_query($con,$insert12);
							
							// INSERT 13
							$insert13="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'use_ssl','0')";
									  
							$result13 = mysqli_query($con,$insert13);
							
							// INSERT 14
							$insert14="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'wp_user_level','0')";
									  
							$result14 = mysqli_query($con,$insert14);
							
							// INSERT 15
							$insert15="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'user_credentials_for_login','{$credentialStr}')";
									  
							$result15 = mysqli_query($con,$insert15);
							
							
							// INSERT 16
							$insert16="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
									  VALUES ('{$userID}', 'wp_capabilities','{$capabilityStr}')";
									  
							$result16 = mysqli_query($con,$insert16);
							
							
							
							
							if($result!= null && $IDresult!=null && $result1!= null && $result2!= null && $result3!= null && $result4!= null && $result5!= null  && $result6!= null && $result7!= null && $result8!= null && $result9!= null && $result10!= null && $result11!= null && $result12!= null && $result13!= null && $result14!= null && $result15!= null && $result16!= null )
							{
								// COMMIT
								$COMITTQuery="COMMIT";
								$COMITTresult = mysqli_query($con,$COMITTQuery);
								
								//Sending Email for Activation of account
				
								$to = $email;
								$subject='Meter Break Account Information';
								$headers  = 'MIME-Version: 1.0' . "\r\n";
								$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
								$message='<html>
											<head>
											  <title>Meter Break </title>
											</head>
											<body>
											  <p>Your account successfully created</p>
											  <p>Your account Information: </p>
											  <p>Username : ' . $username . ' </p>
											  <p><strong>Verification Link : <a href="'.$SERVER.'/WebServices/userActivation.php?userid='.$username.'&verified='.$Active_key.'" target="_blank">Verify</a> </strong></p>   
											</body>
											</html>';
								
								$check= mail($to,$subject,$message,$headers);
								
								$regInfo = new stdClass();
								$regInfo->username=$username;
								$regInfo->active_key=$Active_key;
								$regInfo->email=$email;
								
								array_push($jsonResponse->response,$regInfo);
						
								$jsonResponse->code = 0;
								$jsonResponse->status = "Success";
								$jsonResponse->message = "User Registered Successfully!";
								return json_encode($jsonResponse);
								
							}
							else
							{
								// ROLLBACK
							
								$ROLLBACKQuery="ROLLBACK";
								$ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);
								
								$jsonResponse->code = 1;
								$jsonResponse->status = "Error";
								$jsonResponse->message ="User Registration Failed.";
								return json_encode($jsonResponse);
								
							}
				
				}
				else
				{
					$jsonResponse->code = 1;
					$jsonResponse->status = "Error";
					$jsonResponse->message ="User Registration Failed.";
					return json_encode($jsonResponse);
				}
	
	}
	else
	{
		$jsonResponse->code = 1;
		$jsonResponse->status = "Error";
		$jsonResponse->message = $msg;
		return json_encode($jsonResponse);
	}

}

function resendMail($uName,$A_key,$mail)
{
		$SERVER="http://meterbreak.com/app";

			$jsonResponse = new responsejson();
			

			$username=$uName;
			$Active_key=$A_key;
			$email=$mail;
			
			$to = $email;
			$subject='Meter Break Account Information';
			$headers  = 'MIME-Version: 1.0' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$message='<html>
						<head>
						  <title>Meter Break </title>
						</head>
						<body>
						  <p>Your account successfully created</p>
						  <p>Your account Information: </p>
						  <p>Username : ' . $username . ' </p>
						  <p><strong>Verification Link : <a href="'.$SERVER.'/WebServices/userActivation.php?userid='.$username.'&verified='.$Active_key.'" target="_blank">Verify</a> </strong></p>   
						</body>
						</html>';
			
			$check= mail($to,$subject,$message,$headers);
			
			if($check)
			{
					$jsonResponse->code = 0;
				   $jsonResponse->status = "Success";
				   $jsonResponse->message = "Email sent!";
				   return json_encode($jsonResponse);
			}
			else
			{
					$jsonResponse->code = 1;
				   $jsonResponse->status = "Error";
				   $jsonResponse->message = "Email not sent!";
				   return json_encode($jsonResponse);
			}
	
}


function userActivation($username,$key,$tz)
{
	$jsonResponse = new responsejson();
    $config = new configuration();
	
	$check=0;

    $con = mysqli_connect($config->server, $config->user, $config->password, $config->db);

    if (!$con) 
    {	
        $jsonResponse->code = -1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "Connection to db failed!";
        return json_encode($jsonResponse);
    }
	
		$query="SELECT meta_value
				FROM wp_usermeta
				WHERE user_id=(SELECT ID FROM wp_users WHERE user_login='{$username}')
				AND meta_key='activation_key'";


		$result = mysqli_query($con,$query);
		
		if($result != null)
        $resultCount = mysqli_num_rows($result);
        if($resultCount > 0)
        {
			$row = mysqli_fetch_array($result);
			$retrieveKey=$row["meta_value"];
			
					if($key==$retrieveKey)
					{
						$capabilityArray["subscriber"] = (boolean) 1;
						$capabilityArray["bbp_participant"] = (boolean) 1;
						$capabilityStr = serialize($capabilityArray);
						
						$timeNow = date('Y-m-d H:i:s');
					
						$begin="START TRANSACTION";
						$beginResult = mysqli_query($con,$begin);
							
						if($beginResult == null)
						{
							$check=1;
							return $check;		
						}
						else
						{
						
								$IDquery="SELECT ID
									FROM wp_users
									WHERE user_login='{$username}'";
		
								$IDresult = mysqli_query($con,$IDquery);
								$row = mysqli_fetch_array($IDresult);
								$userID=$row["ID"];
								
								//Activation key deletion
								$DELquery="DELETE FROM wp_usermeta
											WHERE user_id={$userID}
											AND meta_key='activation_key'";
							
								$DELresult = mysqli_query($con,$DELquery);
								
									// INSERT 1
								$insert1="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
										  VALUES ('{$userID}', 'current_user_timezone','{$tz}')";
										  
								$result1 = mysqli_query($con,$insert1);
								
								// INSERT 2
								$insert2="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
										  VALUES ('{$userID}', 'last_activity','{$timeNow}')";
										  
								$result2 = mysqli_query($con,$insert2);
								
								// INSERT 3
								$insert3="UPDATE wp_usermeta
										SET meta_value='{$capabilityStr}'
										WHERE meta_key='wp_capabilities'
										AND user_id={$userID}";
										  
								$result3 = mysqli_query($con,$insert3);
								
								// INSERT 4
								$insert4="INSERT INTO wp_bp_xprofile_data
											(field_id,user_id,`value`,last_updated)
											VALUES (1,{$userID},'{$username}','{$timeNow}')";
										  
								$result4 = mysqli_query($con,$insert4);
								
								// INSERT 5
								$insert5="INSERT INTO wp_bp_activity (user_id,component,type,item_id,date_recorded,hide_sitewide,mptt_left,mptt_right,is_spam)
											VALUES ({$userID},'members','last_activity',0,'{$timeNow}',0,0,0,0)";
										  
								$result5 = mysqli_query($con,$insert5);
								
								if($IDresult && $DELresult && $result1 && $result2 && $result3 && $result4 && $result5 )
								{
											// COMMIT
											$COMITTQuery="COMMIT";
											$COMITTresult = mysqli_query($con,$COMITTQuery);
											return $check;
								}
								else
								{
									// ROLLBACK
							
									$ROLLBACKQuery="ROLLBACK";
									$ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);
									
										$check=1;
										return $check;
								}
		
						}
					}				
					else
					{
							$check=2;
							return $check;
					}
		}
		else
		{
				$check=3;
				return $check;
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
                    $News->content=	strip_tags($News->content);
					$News->content = preg_replace("/\[.*?\]/", "", $News->content);
					$News->content=cleanString($News->content);
					$News->content = utf8_encode($News->content);
					$News->content = substr($News->content,0,50);
					
                    
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
					$News->content=	strip_tags($News->content);
					$News->content = substr($News->content,0,50);
                    
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
					$News->content=	strip_tags($News->content);
                   
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
			$Game->content=	strip_tags($Game->content);
            
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

function getTop250Games()
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
    
    
    
         $query="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , round(avg(g.Score),1) rating , p2.guid
				FROM wp_posts p, wp_users usr , wp_gamingordering g , wp_posts p2
				WHERE p.post_type = 'game' AND p.post_author = usr.ID AND p.ID = g.GameID AND p.ID = p2.post_parent AND p2.post_type = 'attachment'
				GROUP BY g.GameID
				ORDER BY rating DESC LIMIT 0,250;";
    
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
            $Game->content=cleanString($Game->content);
			$Game->content = utf8_encode($Game->content);
			$Game->content=	strip_tags($Game->content);
            
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

function getUserTopGames($limit,$userID)
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
	
	
	if($limit == 10)
	{
			$query="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , round(g.Score,1) rating , p2.guid
			FROM wp_posts p, wp_users usr , wp_gamingordering g , wp_posts p2
			WHERE g.UserID={$userID} 
			AND p.post_author = usr.ID 
			AND p.ID = g.GameID 
			AND p.ID = p2.post_parent 
			AND p2.post_type = 'attachment'
			AND g.ExpiryRemainingDays > 0
			GROUP BY p.ID
			ORDER BY rating DESC LIMIT 0,{$limit}";
	}
	else
	{
			$query="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , round(g.Score,1) rating , p2.guid
			FROM wp_posts p, wp_users usr , wp_gamingordering g , wp_posts p2
			WHERE g.UserID={$userID} 
			AND p.post_author = usr.ID 
			AND p.ID = g.GameID 
			AND p.ID = p2.post_parent 
			AND p2.post_type = 'attachment'
			GROUP BY p.ID
			ORDER BY rating DESC LIMIT 0,{$limit}";
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
			$Game->content=	strip_tags($Game->content);
            
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
			
			$updateViews = "UPDATE wp_posts
							SET viewed=viewed+1
							WHERE ID={$postID}";
					
			$result = mysqli_query($con,$updateViews);
			
			if($result)
			{
				$jsonResponse->code = 0;
				$jsonResponse->status = "Success";
				$jsonResponse->message = "Detail(s) found!";
			
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
			
			$updateViews = "UPDATE wp_posts
							SET viewed=viewed+1
							WHERE ID={$postID}";
					
			$result = mysqli_query($con,$updateViews);
			
			if($result)
			{
				$jsonResponse->code = 0;
				$jsonResponse->status = "Success";
				$jsonResponse->message = "Detail(s) found!";
			
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
			$mpStory->content=	strip_tags($mpStory->content);
            
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
			
			$updateViews = "UPDATE wp_posts
							SET viewed=viewed+1
							WHERE ID={$postID}";
					
			$result = mysqli_query($con,$updateViews);
			
			if($result)
			{
				$jsonResponse->code = 0;
				$jsonResponse->status = "Success";
				$jsonResponse->message = "Detail(s) found!";
			
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
			$Game->content=	strip_tags($Game->content);
            
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
		
		$updateViews = "UPDATE wp_posts
							SET viewed=viewed+1
							WHERE ID={$postID}";
					
			$result = mysqli_query($con,$updateViews);
			
			if($result)
			{
				$jsonResponse->code = 0;
				$jsonResponse->status = "Success";
				$jsonResponse->message = "{$resultCount} Topic(s) found!";
				$jsonResponse->rowCount=$resultCount;
			
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
		
		$updateViews = "UPDATE wp_posts
							SET viewed=viewed+1
							WHERE ID={$postID}";
					
			$result = mysqli_query($con,$updateViews);
			
			if($result)
			{
				$jsonResponse->code = 0;
				$jsonResponse->status = "Success";
				$jsonResponse->message = "{$resultCount} Replies found!";
				$jsonResponse->rowCount=$resultCount;
			
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
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No Reply found!";
        return json_encode($jsonResponse);
    }
		
}


function checkUser($username,$email)
{
		$config = new configuration();

    $con = mysqli_connect($config->server, $config->user, $config->password, $config->db);

    if (!$con) 
    {	
        $jsonResponse->code = -1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "Connection to db failed!";
        return json_encode($jsonResponse);
    }
	
	$msg='';
	
	$query="SELECT *
			FROM wp_users
			WHERE user_login='{$username}'";
			
			$result = mysqli_query($con,$query);
			$resultCount = 0;
			if($result != null)
				$resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
	{
		$msg=$msg . '<p>* Username Already Exists. </p>';
	}
	
	
	$query="SELECT *
			FROM wp_users
			WHERE user_email='{$email}'";
			
			$result = mysqli_query($con,$query);
			$resultCount = 0;
			if($result != null)
				$resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
	{
		$msg=$msg . '<p>* Email Already Registered. </p>';
	}
	
	return $msg;
			
}


function replyToPost($postContent, $parentPostID)
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
		session_start(); 
		$username = $_SESSION['username'];
		$query= "SELECT *
				FROM wp_users
				WHERE user_login='{$username}';";

		$ru = mysqli_query($con,$query);
		$rr = mysqli_fetch_array($ru);
		$userID = $rr["ID"];

		$q1="SELECT (max(ID)+1) as 'NewPostID' FROM wp_posts;";
		$r1 = mysqli_query($con,$q1);

		$row1 = mysqli_fetch_array($r1);
		$newPostID = $row1["NewPostID"];

		$q2 = "SELECT  (max(menu_order)+1) as 'newMenuOrder'
				FROM wp_posts
				WHERE( ID='{$parentPostID}' OR post_parent='{$parentPostID}' );";
		$r2 = mysqli_query($con,$q2);

		$row2 = mysqli_fetch_array($r2);
		$menuOrder = $row2["newMenuOrder"];

		$replyDate = date('Y-m-d H:i:s');

		$q3 = "SELECT p1.meta_value as 'v1', p2.meta_value as 'v2'
				FROM wp_postmeta p1 , wp_postmeta p2 
				WHERE p1.post_id = '{$parentPostID}'
				AND p2.post_id = '{$parentPostID}'
				AND p1.meta_key='_bbp_forum_id'
				AND p2.meta_key='_bbp_topic_id';";
		$r3 = mysqli_query($con,$q3);
		$row3 = mysqli_fetch_array($r3);
		$forumID = $row3["v1"];
		$topicID = $row3["v2"];
		$userIP = $_SERVER['REMOTE_ADDR'];
		
		
	$begin="START TRANSACTION";
	$beginResult = mysqli_query($con,$begin);

	if($beginResult != null)
	{
	
			$guidLink = 'http://meterbreak.com/forum/reply/' . $newPostID . '/';
		$insert1 = "INSERT INTO wp_posts (ID,topthree,viewed,post_author,post_date,post_date_gmt,post_content,post_status,comment_status,ping_status,post_name,post_modified,post_modified_gmt,post_parent,guid,menu_order,post_type,comment_count)
									VALUES ('{$newPostID}',0,0,'{$userID}','{$replyDate}','{$replyDate}','{$postContent}','publish','closed','closed','{$newPostID}','{$replyDate}','{$replyDate}','{$parentPostID}','{$guidLink}','{$menuOrder}','reply',0);";
				
		$result1 = mysqli_query($con,$insert1);

		$insert2 = "INSERT INTO wp_postmeta (post_id,meta_key,meta_value)
					VALUES ('{$newPostID}','_bbp_forum_id','{$forumID}');";
		$result2 = mysqli_query($con,$insert2);

		$insert3 = "INSERT INTO wp_postmeta (post_id,meta_key,meta_value)
					VALUES ('{$newPostID}','_bbp_topic_id','{$topicID}');";
		$result3 = mysqli_query($con,$insert3);			

		$insert4 = "INSERT INTO wp_postmeta (post_id,meta_key,meta_value)
					VALUES ('{$newPostID}','_bbp_author_ip','{$userIP}');";
		$result4 = mysqli_query($con,$insert4);

		$update1 = "UPDATE wp_postmeta
					SET meta_value='{$newPostID}'
					WHERE post_id='{$topicID}'
					AND ( meta_key='_bbp_last_reply_id'
							OR meta_key='_bbp_last_active_id');";
		$result6 = mysqli_query($con,$update1);

		$update2 = "UPDATE wp_postmeta
					SET meta_value='{$replyDate}'
					WHERE post_id='{$topicID}'
					AND meta_key='_bbp_last_active_time';";
		$result7 = mysqli_query($con,$update2);

		$update3 = "UPDATE wp_postmeta
					SET meta_value=meta_value+1
					WHERE post_id='{$topicID}'
					AND meta_key='_bbp_reply_count';";
		$result8 = mysqli_query($con,$update3);

		$update4 = "UPDATE wp_postmeta
					SET meta_value=meta_value+1
					WHERE post_id='{$forumID}'
					AND (meta_key='_bbp_reply_count' OR meta_key='_bbp_total_reply_count');";
		$result9 = mysqli_query($con,$update4);

		$update5 = "UPDATE wp_postmeta
					SET meta_value='{$replyDate}'
					WHERE post_id='{$forumID}'
					AND meta_key='_bbp_last_active_time';";
		$result10 = mysqli_query($con,$update5);

		$update6 = "UPDATE wp_postmeta
					SET meta_value='{$newPostID}'
					WHERE post_id='{$forumID}'
					AND ( meta_key='_bbp_last_reply_id'
							OR meta_key='_bbp_last_active_id');";
		$result11 = mysqli_query($con,$update6);

		$update7 = "UPDATE wp_postmeta
					SET meta_value='{$topicID}'
					WHERE post_id='{$forumID}'
					AND meta_key='_bbp_last_topic_id';";
		$result12 = mysqli_query($con,$update7);
	
	
	
			if( $result1 && $result2 && $result3 && $result4 && $result6 && $result7 && $result8 && $result9 && $result10 && $result11 && $result12 )
			{
				// COMMIT
				$COMITTQuery="COMMIT";
				$COMITTresult = mysqli_query($con,$COMITTQuery);
				
		
				$jsonResponse->code = 0;
				$jsonResponse->status = "Success";
				$jsonResponse->message = "Your reply has been submitted";
				return json_encode($jsonResponse);
				
			}
			else
			{
				// ROLLBACK
			
				$ROLLBACKQuery="ROLLBACK";
				$ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);
				
				$jsonResponse->code = 1;
				$jsonResponse->status = "Error";
				$jsonResponse->message = "Reply Failed!";
				return json_encode($jsonResponse);
				
			}
	}
	else
	{
		$jsonResponse->code = 1;
		$jsonResponse->status = "Error";
		$jsonResponse->message = "Reply Failed!";
		return json_encode($jsonResponse);
	}
		
	
}



function createTopic($topicTitle, $topicContent,$forumID)
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
		session_start(); 
		$username = $_SESSION['username'];
		$query= "SELECT *
				FROM wp_users
				WHERE user_login='{$username}';";

		$ru = mysqli_query($con,$query);
		$rr = mysqli_fetch_array($ru);
		$userID = $rr["ID"];

		$q1="SELECT (max(ID)+1) as 'NewPostID' FROM wp_posts;";
		$r1 = mysqli_query($con,$q1);

		$row1 = mysqli_fetch_array($r1);
		$newPostID = $row1["NewPostID"];
		$topicID = $newPostID;

		$q2 = "SELECT  (max(menu_order)+1) as 'newMenuOrder'
				FROM wp_posts
				WHERE( ID='{$parentPostID}' OR post_parent='{$parentPostID}' );";
		$r2 = mysqli_query($con,$q2);

		$row2 = mysqli_fetch_array($r2);
		$menuOrder = $row2["newMenuOrder"];

		$topicDate = date('Y-m-d H:i:s');

		$userIP = $_SERVER['REMOTE_ADDR'];

		$niceTitle=strtolower($topicTitle);
		$niceTitle=str_replace(" ","-",$niceTitle);
		
		
	$begin="START TRANSACTION";
	$beginResult = mysqli_query($con,$begin);

	if($beginResult != null)
	{
	
			$guidLink = 'http://meterbreak.com/forum/reply/' . $niceTitle . '/';
		$insert1 = "INSERT INTO wp_posts (ID,topthree,viewed,post_author,post_date,post_date_gmt,post_content,post_title,post_status,comment_status,ping_status,post_name,post_modified,post_modified_gmt,post_parent,guid,menu_order,post_type,comment_count)
							VALUES ('{$newPostID}',0,0,'{$userID}','{$topicDate}','{$topicDate}','{$topicContent}','{$topicTitle}','publish','closed','open','{$niceTitle}','{$topicDate}','{$topicDate}','{$forumID}','{$guidLink}',0,'topic',0);";
				
		$result1 = mysqli_query($con,$insert1);

		$insert2 = "INSERT INTO wp_postmeta (post_id,meta_key,meta_value)
					VALUES ('{$newPostID}','_bbp_forum_id','{$forumID}');";
		$result2 = mysqli_query($con,$insert2);

		$insert3 = "INSERT INTO wp_postmeta (post_id,meta_key,meta_value)
					VALUES ('{$newPostID}','_bbp_topic_id','{$topicID}');";
		$result3 = mysqli_query($con,$insert3);			

		$insert4 = "INSERT INTO wp_postmeta (post_id,meta_key,meta_value)
					VALUES ('{$newPostID}','_bbp_author_ip','{$userIP}');";
		$result4 = mysqli_query($con,$insert4);

		$insert5 = "INSERT INTO wp_postmeta (post_id,meta_key,meta_value)
					VALUES ('{$newPostID}','_bbp_last_reply_id',0);";
		$result5 = mysqli_query($con,$insert5);

		$insert6 = "INSERT INTO wp_postmeta (post_id,meta_key,meta_value)
					VALUES ('{$newPostID}','_bbp_last_active_id','{$topicID}');";
		$result6 = mysqli_query($con,$insert6);			

		$insert7 = "INSERT INTO wp_postmeta (post_id,meta_key,meta_value)
					VALUES ('{$newPostID}','_bbp_last_active_time','{$topicDate}');";
		$result7 = mysqli_query($con,$insert7);		

		$insert8 = "INSERT INTO wp_postmeta (post_id,meta_key,meta_value)
					VALUES ('{$newPostID}','_bbp_reply_count',0);";
		$result8 = mysqli_query($con,$insert8);	

		$insert9 = "INSERT INTO wp_postmeta (post_id,meta_key,meta_value)
					VALUES ('{$newPostID}','_bbp_reply_count_hidden',1);";
		$result9 = mysqli_query($con,$insert9);	

		$insert10 = "INSERT INTO wp_postmeta (post_id,meta_key,meta_value)
					VALUES ('{$newPostID}','_bbp_voice_count',1);";
		$result10 = mysqli_query($con,$insert10);	

		$update1 = "UPDATE wp_postmeta
					SET meta_value='{$newPostID}'
					WHERE post_id='{$forumID}'
					AND ( meta_key='_bbp_last_active_id');";
		$result11 = mysqli_query($con,$update1);

		$update2 = "UPDATE wp_postmeta
					SET meta_value='{$replyDate}'
					WHERE post_id='{$forumID}'
					AND meta_key='_bbp_last_active_time';";
		$result12 = mysqli_query($con,$update2);

		$update3 = "UPDATE wp_postmeta
					SET meta_value=meta_value+1
					WHERE post_id='{$forumID}'
					AND (meta_key='_bbp_topic_count' OR meta_key='_bbp_total_topic_count');";
		$result13 = mysqli_query($con,$update3);

		$update4 = "UPDATE wp_postmeta
					SET meta_value='{$topicDate}'
					WHERE post_id='{$forumID}'
					AND meta_key='_bbp_last_active_time';";
		$result14 = mysqli_query($con,$update4);

		$update5 = "UPDATE wp_postmeta
					SET meta_value='{$topicID}'
					WHERE post_id='{$forumID}'
					AND ( meta_key='_bbp_last_active_id');";
		$result15 = mysqli_query($con,$update5);

		$update6 = "UPDATE wp_postmeta
					SET meta_value='{$topicID}'
					WHERE post_id='{$forumID}'
					AND meta_key='_bbp_last_topic_id';";
		$result16 = mysqli_query($con,$update6);	
	
	
	
		if( $result1 && $result2 && $result3 && $result4 && $result5 && $result6 && $result7 && $result8 && $result9 && $result10 && $result11 && $result12 && $result13 && $result14 && $result15 && $result16 )
		{
			// COMMIT
			$COMITTQuery="COMMIT";
			$COMITTresult = mysqli_query($con,$COMITTQuery);
			
	
			$jsonResponse->code = 0;
			$jsonResponse->status = "Success";
			$jsonResponse->message = "Your topic has been created";
			return json_encode($jsonResponse);
			
		}
		else
		{
			// ROLLBACK
		
			$ROLLBACKQuery="ROLLBACK";
			$ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);
			
			$jsonResponse->code = 1;
			$jsonResponse->status = "Error";
			$jsonResponse->message = "Topic Creation Failed!";
			return json_encode($jsonResponse);
			
		}
	}
	else
	{
		$jsonResponse->code = 1;
		$jsonResponse->status = "Error";
		$jsonResponse->message = "Topic Creation Failed!";
		return json_encode($jsonResponse);
	}
	
}

function getDailyPoll() 
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
    
    $query= "SELECT p.ID , p.post_date , p.post_title , m.meta_value
			FROM wp_posts p , wp_postmeta m
			WHERE p.post_type = 'pollsystem'
			AND p.ID = m.post_id
			AND m.meta_key = 'poll_answers_saved'
			ORDER BY p.post_date DESC LIMIT 0,1";

    $result = mysqli_query($con,$query);
    $resultCount = 0;
    if($result != null)
        $resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
    {
        $jsonResponse->code = 0;
        $jsonResponse->status = "Success";
        $jsonResponse->message = "{$resultCount} Poll(s) found!";
        $jsonResponse->rowCount=$resultCount;
		
		$row = mysqli_fetch_array($result);
        
            $Poll = new stdClass();
			$Poll->answers=array();
            $Poll->postID=$row["ID"];
			$Poll->question=$row["post_title"];
			
			
			$answers=unserialize($row["meta_value"]);

			$count1 = count($answers);

			$i=0;
			for($i=0;$i<$count1;$i++)
			{

				$obj = new stdClass();
				$obj->id=$answers[$i]['id'];
				$obj->answer=$answers[$i]['answer'];
				$obj->counter=$answers[$i]['counter'];
				
				array_push($Poll->answers,$obj);

			}
			
			array_push($jsonResponse->response,$Poll);
			
		
		return json_encode($jsonResponse);        
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No Poll found!";
        return json_encode($jsonResponse);
    }
  
}

function updatePollResults($pollID,$answers)
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
    
	$updatedAnswers = serialize($answers);
	
    $query="UPDATE wp_postmeta
			SET meta_value='{$updatedAnswers}'
			WHERE meta_key='poll_answers_saved'
			AND post_id={$pollID}";
	
    $result = mysqli_query($con,$query);
    
	if($result != null)
	{
			$jsonResponse->code = 0;
			$jsonResponse->status = "Success";
			$jsonResponse->message = "Answers Updated !";
			return json_encode($jsonResponse);
	}
	else
	{
			$jsonResponse->code = 1;
			$jsonResponse->status = "Error";
			$jsonResponse->message = "Answers Not Updated !";
			return json_encode($jsonResponse);
	}
    

}

function getSurveyQuestions()
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

	
	 $query="SELECT  tt.term_id , t.`name` ,tt.description
			FROM `wp_term_taxonomy` tt , wp_terms t
			WHERE tt.taxonomy='genre'
			AND tt.term_id=t.term_id
			ORDER BY t.`name`";

		$result = mysqli_query($con,$query);

		if($result != null)
			$resultCount = mysqli_num_rows($result);
		if($resultCount > 0)
		{
			$jsonResponse->code = 0;
			$jsonResponse->status = "Success";
			$jsonResponse->message = "Questions Found !";
			$jsonResponse->rowCount = $resultCount;
			
			
			while($row = mysqli_fetch_array($result))
			{
				$Question = new stdClass();
				$Question->label=$row["name"];
				$Question->point=0;
				$Question->id=$row["term_id"];
				$Question->description=$row["description"];
				
				array_push($jsonResponse->response,$Question);
				
				
			}
			
			return json_encode($jsonResponse);
		
		}
		else
		{
			$jsonResponse->code = 1;
			$jsonResponse->status = "Error";
			$jsonResponse->message = "No Questions Found !";
			return json_encode($jsonResponse);
		}
	
}

function checkSurvey()
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


	    $count1=0;
	    $count2=0;

	    session_start(); 
		$userID = $_SESSION['userID'];

	     $query="SELECT * FROM `wp_usermeta`
				WHERE user_id={$userID}
				AND meta_key='GamePropertiesCat_draft'";
	
            $result = mysqli_query($con,$query);

            if($result != null)
        		$resultCount = mysqli_num_rows($result);
    		if($resultCount > 0)
    		{

    			$jsonResponse->code = 0;
		        $jsonResponse->status = "Success";
		        $jsonResponse->message = "Details Found !";
		        


		        $row = mysqli_fetch_array($result);
           		$GamePropertiesArray = unserialize($row['meta_value']);

           		$count1 = count($GamePropertiesArray);

           		$i=0;
           		for($i=0;$i<$count1;$i++)
           		{

           			$obj = new stdClass();
                    $obj->label=$GamePropertiesArray[$i]['lable'];
                    $obj->value=$GamePropertiesArray[$i]['value'];
                    $obj->id=$GamePropertiesArray[$i]['id'];
                    
                    array_push($jsonResponse->response,$obj);

           		}

    		}


    	$query2="SELECT * FROM `wp_usermeta`
				WHERE user_id={$userID}
				AND meta_key='GameUserGeneres_draft'";
	
            $result2 = mysqli_query($con,$query2);

            if($result2 != null)
        		$resultCount2 = mysqli_num_rows($result2);
    		if($resultCount2 > 0)
    		{
    			$jsonResponse->code = 0;
		        $jsonResponse->status = "Success";
		        $jsonResponse->message = "Details Found !";
		        

    			$row2 = mysqli_fetch_array($result2);
           		$GameUserGeneresArray = unserialize($row2['meta_value']);

           		$count2 = count($GameUserGeneresArray);

           		$i=0;
           		for($i=0;$i<$count2;$i++)
           		{

           			$obj = new stdClass();
                    $obj->label=$GameUserGeneresArray[$i]['label'];
                    $obj->value=$GameUserGeneresArray[$i]['point'];
                    $obj->id=$GameUserGeneresArray[$i]['id'];
                    
                    array_push($jsonResponse->response,$obj);

           		}

    		}
			
			
				$query3="SELECT * FROM `wp_usermeta`
				WHERE user_id={$userID}
				AND meta_key='GamePropertiesCat'";
	
				$result3 = mysqli_query($con,$query3);

				if($result3 != null)
					$resultCount3 = mysqli_num_rows($result3);
				if($resultCount3 > 0)
				{
						$jsonResponse->code = 2;
						$jsonResponse->status = "Completed";
						$jsonResponse->message = "Survey Already Completed";
						return json_encode($jsonResponse);
			
				}
			
			
			

    		if($resultCount==0 && $resultCount2==0)
    		{
    			$jsonResponse->code = 1;
        		$jsonResponse->status = "Error";
        		$jsonResponse->message = "No Saved Survey found!";
        		return json_encode($jsonResponse);

    		}


    		$jsonResponse->rowCount=$count1 + $count2;
    		return json_encode($jsonResponse);

}

function saveSurveyDraft($gameProperties,$genre)
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

	    session_start(); 
		$userID = $_SESSION['userID'];

		if(count($gameProperties) > 0)
		{
				$gameProperties = serialize($gameProperties);
				
				 $gameProperties=addslashes($gameProperties);
				

				$query="SELECT * FROM `wp_usermeta`
				WHERE user_id={$userID}
				AND meta_key='GamePropertiesCat_draft'";
	
	            $result = mysqli_query($con,$query);

	            if($result != null)
	        		$resultCount = mysqli_num_rows($result);
	    		if($resultCount > 0)	//Update existing
	    		{

	    				$saveQuery1="UPDATE wp_usermeta
									SET meta_value='{$gameProperties}'
									WHERE meta_key='GamePropertiesCat_draft'
									AND user_id={$userID}";
	    		}
	    		else 					//Insert new
	    		{

	    				$saveQuery1="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
								  	VALUES ('{$userID}', 'GamePropertiesCat_draft','{$gameProperties}')";
	    		}

	    		$saveResult1 = mysqli_query($con,$saveQuery1);


		}

		if(count($genre) > 0)
		{
			$genre = serialize($genre);
			
			$genre=addslashes($genre);

			$query2="SELECT * FROM `wp_usermeta`
					WHERE user_id={$userID}
					AND meta_key='GameUserGeneres_draft'";
	
            $result2 = mysqli_query($con,$query2);

            if($result2 != null)
        		$resultCount2 = mysqli_num_rows($result2);
    		if($resultCount2 > 0)		//update existing
    		{
    				$saveQuery2="UPDATE wp_usermeta
								SET meta_value='{$genre}'
								WHERE meta_key='GameUserGeneres_draft'
								AND user_id={$userID}";

    		}
    		else 						//Insert new
    		{

    				$saveQuery2="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
							  VALUES ('{$userID}', 'GameUserGeneres_draft','{$genre}')";
    		}

    		$saveResult2 = mysqli_query($con,$saveQuery2);


		}

		$jsonResponse->code = 0;
        $jsonResponse->status = "Success";
        $jsonResponse->message = "Survey Details saved !";
        return json_encode($jsonResponse);  

    		
}


function submitSurvey($gameProperties,$genre)
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

	    session_start(); 
		$userID = $_SESSION['userID'];
		
		$gamePropertiesStr = serialize($gameProperties);			
		$gamePropertiesStr=addslashes($gamePropertiesStr);
		
		$genreStr = serialize($genre);		
		$genreStr=addslashes($genreStr);
		
		$retakeQuery="SELECT * FROM `wp_usermeta`
				WHERE user_id={$userID}
				AND meta_key='GamePropertiesCat'";
	
	            $retakeResult = mysqli_query($con,$retakeQuery);

	            if($retakeResult != null)
	        		$retakeResultCount = mysqli_num_rows($retakeResult);
	    		if($retakeResultCount > 0)
				{
						$begin="START TRANSACTION";
						$beginResult = mysqli_query($con,$begin);
							
						if($beginResult == null)
						{
							$jsonResponse->code = 1;
							$jsonResponse->status = "Error";
							$jsonResponse->message = "TRANSACTION Failed";
							return json_encode($jsonResponse);
						}
						else
						{
								$query="UPDATE wp_usermeta
								SET meta_value='{$gamePropertiesStr}'
								WHERE meta_key='GamePropertiesCat'
								AND user_id={$userID}  ";

								$result = mysqli_query($con,$query);
								
							
								$query2="UPDATE wp_usermeta
								SET meta_value='{$genreStr}'
								WHERE meta_key='GameUserGeneres'
								AND user_id={$userID} ";

								$result2 = mysqli_query($con,$query2);
								
								
								$check=true; 
								$check2=true; 

								foreach ($gameProperties as $value) 
								{
									$entryQuery="UPDATE wp_score_data
											SET score_value='{$value["value"]}'
											WHERE categories='{$value["id"]}'
											AND userid={$userID} ";

									$entryResult = mysqli_query($con,$entryQuery);
									
									if(!$entryResult)
									{
										$check=false; 
										break;
									}
								}
								
								foreach ($genre as $value2) 
								{
									$entryQuery2="UPDATE wp_score_data
											SET score_value='{$value2["point"]}'
											WHERE genres_id='{$value2["id"]}'
											AND userid={$userID} ";

									$entryResult2 = mysqli_query($con,$entryQuery2);
									
									if(!$entryResult2)
									{
										$check2=false; 
										break;
									}
								}

								
								if($result && $result2 && $check && $check2)
								{
											// COMMIT
											$COMITTQuery="COMMIT";
											$COMITTresult = mysqli_query($con,$COMITTQuery);

											$jsonResponse->code = 0;
											$jsonResponse->status = "Success";
											$jsonResponse->message = "Survey Details saved !";
											return json_encode($jsonResponse); 
											
											
								}
								else
								{
									// ROLLBACK
							
									$ROLLBACKQuery="ROLLBACK";
									$ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);
									
									$jsonResponse->code = 1;
									$jsonResponse->status = "Error";
									$jsonResponse->message = "TRANSACTION Failed";
									return json_encode($jsonResponse);
									
								}
						}
				}
				else
				{
						$begin="START TRANSACTION";
						$beginResult = mysqli_query($con,$begin);
							
						if($beginResult == null)
						{
							$jsonResponse->code = 1;
							$jsonResponse->status = "Error";
							$jsonResponse->message = "TRANSACTION Failed";
							return json_encode($jsonResponse);
						}
						else
						{
								//**************************GAME Properties

								

								$query="SELECT * FROM `wp_usermeta`
								WHERE user_id={$userID}
								AND meta_key='GamePropertiesCat_draft'";
					
								$result = mysqli_query($con,$query);

								if($result != null)
									$resultCount = mysqli_num_rows($result);
								if($resultCount > 0)	
								{

										$DELquery="DELETE FROM wp_usermeta
													WHERE user_id={$userID}
													AND meta_key='GamePropertiesCat_draft'";
									
										$DELresult = mysqli_query($con,$DELquery);

										
								}

								$saveQuery1="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
											VALUES ('{$userID}', 'GamePropertiesCat','{$gamePropertiesStr}')";
								
								$saveResult1 = mysqli_query($con,$saveQuery1);


								//**************************GENRE

								$query2="SELECT * FROM `wp_usermeta`
									WHERE user_id={$userID}
									AND meta_key='GameUserGeneres_draft'";
					
								$result2 = mysqli_query($con,$query2);

								if($result2 != null)
									$resultCount2 = mysqli_num_rows($result2);
								if($resultCount2 > 0)
								{
									$DELquery2="DELETE FROM wp_usermeta
												WHERE user_id={$userID}
												AND meta_key='GameUserGeneres_draft'";
								
									$DELresult2 = mysqli_query($con,$DELquery2);

								}

								$saveQuery2="INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
											  VALUES ('{$userID}', 'GameUserGeneres','{$genreStr}')";

								$saveResult2 = mysqli_query($con,$saveQuery2);
								
								
								
								$check=true; 
								$check2=true; 
								
								$age='';
								$gender='';
								
								
								$dataQuery="SELECT um1.meta_value as age , um2.meta_value as gender
											FROM wp_usermeta um1 , wp_usermeta um2
											WHERE (um1.meta_key='age' AND um1.user_id={$userID})
											AND (um2.meta_key='gender' AND um2.user_id={$userID})";

								$dataResult = mysqli_query($con,$dataQuery);
								
								if($dataResult)
								{
									$row = mysqli_fetch_array($dataResult);
									
									$age=$row["age"];
									$gender=$row["gender"];
								}
							
								
								foreach ($gameProperties as $value) 
								{
									$entryQuery="INSERT INTO wp_score_data (userid,score_value,type,counter,genres_id,categories,gender,age)
												VALUES ('{$userID}','{$value["value"]}','category',0,0,'{$value["id"]}','{$gender}','{$age}')";

									$entryResult = mysqli_query($con,$entryQuery);
									
									if(!$entryResult)
									{
										$check=false; 
										break;
									}
								}
								
								foreach ($genre as $value2) 
								{
									$entryQuery2="INSERT INTO wp_score_data (userid,score_value,type,counter,genres_id,gender,age)
												VALUES ('{$userID}','{$value2["point"]}','genres',0,'{$value2["id"]}','{$gender}','{$age}')";

									$entryResult2 = mysqli_query($con,$entryQuery2);
									
									if(!$entryResult2)
									{
										$check2=false; 
										break;
									}
								}
								
				
								if($saveResult1 && $saveResult2 && $check && $check2)
								{
											// COMMIT
											$COMITTQuery="COMMIT";
											$COMITTresult = mysqli_query($con,$COMITTQuery);

											$jsonResponse->code = 0;
											$jsonResponse->status = "Success";
											$jsonResponse->message = "Survey Details saved !";
											return json_encode($jsonResponse); 
											
											
								}
								else
								{
									// ROLLBACK
							
									$ROLLBACKQuery="ROLLBACK";
									$ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);
									
									$jsonResponse->code = 1;
									$jsonResponse->status = "Error";
									$jsonResponse->message = "TRANSACTION Failed";
									return json_encode($jsonResponse);
									
								}

						}
				}

}

//*************************************** MOVIES PORTION *********************************************

function getTopMoviesAndShows($limit)
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
	
	$query="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , round(avg(m.Score),1) rating , p2.guid
			FROM wp_posts p, wp_users usr , wp_moviesordering m , wp_posts p2
			WHERE p.post_type = 'movie-show' 
			AND p.post_author = usr.ID 
			AND p.ID = m.GameID 
			AND p.ID = p2.post_parent 
			AND p2.post_type = 'attachment'
			GROUP BY m.GameID
			ORDER BY rating DESC LIMIT 0,{$limit}";
			
			
	$result = mysqli_query($con,$query);
    $resultCount = 0;
    if($result != null)
        $resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
    {
        $jsonResponse->code = 0;
        $jsonResponse->status = "Success";
        $jsonResponse->message = "{$resultCount} Movie(s) found!";
        $jsonResponse->rowCount=$resultCount;

        while($row = mysqli_fetch_array($result))
        {
            $Movie = new stdClass();
            $Movie->postID=$row["ID"];
            $Movie->title=$row["post_title"];
            $Movie->content=$row["post_content"];
            $Movie->postAuthor=$row["display_name"];
            $Movie->rating=$row["rating"];
            $Movie->img=$row["guid"];

            $Movie->content = str_replace("//www.","http://www.",$Movie->content);
			$Movie->content = preg_replace("/\[.*?\]/", "", $Movie->content);
            $Movie->content=cleanString($Movie->content);
			$Movie->content = utf8_encode($Movie->content);
			$Movie->content=	strip_tags($Movie->content);
            
            $dateSrc = $row["post_date"];
            $dateTime = date_create( $dateSrc);
            $Movie->postDate=date_format( $dateTime, 'F d,Y');
            
            array_push($jsonResponse->response,$Movie);
        }
        return json_encode($jsonResponse);
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No Movie found!";
        return json_encode($jsonResponse);
    }
	

}

function getAllMoviesOrShows($platform,$alphabet)
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
         $query="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , round(avg(m.Score),1) rating , p2.guid
				FROM wp_posts p, wp_users usr , wp_moviesordering m , wp_posts p2
				WHERE p.post_type = 'movie-show' 
				AND p.post_title LIKE '{$alphabet}%'
				AND p.post_author = usr.ID 
				AND p.ID = m.GameID 
				AND p.ID = p2.post_parent 
				AND p2.post_type = 'attachment'
				GROUP BY m.GameID
				ORDER BY p.post_title;";
    }
	
    else 
    {
		$query="SELECT p.ID,p.post_title, p.post_content, usr.display_name , p.post_date , round(avg(m.Score),1) rating , p2.guid
				FROM wp_posts p,wp_posts p2 , wp_term_relationships tr , wp_term_taxonomy tt , wp_terms t, wp_users usr, wp_moviesordering m
				WHERE p.ID=tr.object_id
				AND tr.term_taxonomy_id=tt.term_taxonomy_id
				AND tt.term_id=t.term_id
				AND t.slug='{$platform}'
				AND p.post_type = 'movie-show'
				AND p.post_title LIKE '{$alphabet}%'
				AND p.post_author = usr.ID 
				AND p.ID = m.GameID 
				AND p.ID = p2.post_parent
				AND p2.post_type = 'attachment'
				GROUP BY m.GameID
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
        $jsonResponse->message = "{$resultCount} Movie/Show(s) found!";
        $jsonResponse->rowCount=$resultCount;

        while($row = mysqli_fetch_array($result))
        {
            $Movie = new stdClass();
            $Movie->postID=$row["ID"];
            $Movie->title=$row["post_title"];
            $Movie->content=$row["post_content"];
            $Movie->postAuthor=$row["display_name"];
            $Movie->rating=$row["rating"];
            $Movie->img=$row["guid"];

            $Movie->content = str_replace("//www.","http://www.",$Movie->content);
			$Movie->content = preg_replace("/\[.*?\]/", "", $Movie->content);
            $Movie->content=cleanString($Movie->content);
			$Movie->content = utf8_encode($Movie->content);
			$Movie->content=	strip_tags($Movie->content);
            
            $dateSrc = $row["post_date"];
            $dateTime = date_create( $dateSrc);
            $Movie->postDate=date_format( $dateTime, 'F d,Y');
            
            array_push($jsonResponse->response,$Movie);
        }
        return json_encode($jsonResponse);
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No Movie/Show Found!";
        return json_encode($jsonResponse);
    }
}

function checkMovieSurvey()
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


	    $count1=0;
	    $count2=0;

	    session_start(); 
		$userID = $_SESSION['userID'];

	     $query="SELECT * FROM `wp_usermeta`
				WHERE user_id={$userID}
				AND meta_key='GamePropertiesCat_draft_movies'";
	
            $result = mysqli_query($con,$query);

            if($result != null)
        		$resultCount = mysqli_num_rows($result);
    		if($resultCount > 0)
    		{

    			$jsonResponse->code = 0;
		        $jsonResponse->status = "Success";
		        $jsonResponse->message = "Details Found !";
		        


		        $row = mysqli_fetch_array($result);
           		$GamePropertiesArray = unserialize($row['meta_value']);

           		$count1 = count($GamePropertiesArray);

           		$i=0;
           		for($i=0;$i<$count1;$i++)
           		{

           			$obj = new stdClass();
                    $obj->label=$GamePropertiesArray[$i]['lable'];
                    $obj->value=$GamePropertiesArray[$i]['value'];
                    $obj->id=$GamePropertiesArray[$i]['id'];
                    
                    array_push($jsonResponse->response,$obj);

           		}

    		}


    	$query2="SELECT * FROM `wp_usermeta`
				WHERE user_id={$userID}
				AND meta_key='GameUserGeneres_draft_movies'";
	
            $result2 = mysqli_query($con,$query2);

            if($result2 != null)
        		$resultCount2 = mysqli_num_rows($result2);
    		if($resultCount2 > 0)
    		{
    			$jsonResponse->code = 0;
		        $jsonResponse->status = "Success";
		        $jsonResponse->message = "Details Found !";
		        

    			$row2 = mysqli_fetch_array($result2);
           		$GameUserGeneresArray = unserialize($row2['meta_value']);

           		$count2 = count($GameUserGeneresArray);

           		$i=0;
           		for($i=0;$i<$count2;$i++)
           		{

           			$obj = new stdClass();
                    $obj->label=$GameUserGeneresArray[$i]['label'];
                    $obj->value=$GameUserGeneresArray[$i]['point'];
                    $obj->id=$GameUserGeneresArray[$i]['id'];
                    
                    array_push($jsonResponse->response,$obj);

           		}

    		}
			
			
				$query3="SELECT * FROM `wp_usermeta`
				WHERE user_id={$userID}
				AND meta_key='GamePropertiesCat_movies'";
	
				$result3 = mysqli_query($con,$query3);

				if($result3 != null)
					$resultCount3 = mysqli_num_rows($result3);
				if($resultCount3 > 0)
				{
						$jsonResponse->code = 2;
						$jsonResponse->status = "Completed";
						$jsonResponse->message = "Survey Already Completed";
						return json_encode($jsonResponse);
			
				}
			
			
			

    		if($resultCount==0 && $resultCount2==0)
    		{
    			$jsonResponse->code = 1;
        		$jsonResponse->status = "Error";
        		$jsonResponse->message = "No Saved Survey found!";
        		return json_encode($jsonResponse);

    		}


    		$jsonResponse->rowCount=$count1 + $count2;
    		return json_encode($jsonResponse);

}

//********************************************* BUDDY PRESS PORTION *************************************

function getUserProfileData()
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
		
		session_start(); 
		$userID = $_SESSION['userID'];
		
		$count1=0;
		$count2=0;
		
		$obj = new stdClass();
		$obj->aboutMe= array () ;
		$obj->surveyData = array ();
		$obj->userDetails = array ();
		$obj->profiletype='public';
		
		
		$imgQuery="SELECT  um.meta_value as img
							FROM  wp_usermeta um
							WHERE um.user_id={$userID}
							AND um.meta_key='profile_image'";
					
			$imgResult = mysqli_query($con,$imgQuery);
			
			if($imgResult != null)
				$imgResultCount2 = mysqli_num_rows($imgResult);
			if($imgResultCount2 > 0)
			{
				$imgrow = mysqli_fetch_array($imgResult);
				
				$ImgURL=$imgrow["img"];
				$ImgURL=unserialize($ImgURL);

				$obj->img= $ImgURL['url'];
			}
			else
			{
				$obj->img="images/profile.gif";
			}
		

	     $query="SELECT * FROM wp_usermeta
				WHERE user_id={$userID}
				AND meta_key='profiletype'";
	
            $result = mysqli_query($con,$query);

            if($result != null)
        		$resultCount = mysqli_num_rows($result);
    		if($resultCount > 0)
			{
				$row = mysqli_fetch_array($result);
				$obj->profiletype=$row['meta_value'];
			}
			
			$query2="SELECT * FROM wp_usermeta
				WHERE user_id={$userID}
				AND meta_key='member_details'";
	
            $result2 = mysqli_query($con,$query2);

            if($result2 != null)
        		$resultCount2 = mysqli_num_rows($result2);
    		if($resultCount2 > 0)
			{
				$row2 = mysqli_fetch_array($result2);
				$aboutME=unserialize($row2["meta_value"]);
				
				$aboutyourself= $aboutME['aboutme'];
				$currentplaying= $aboutME['currentplaying'];
				$gamertag= $aboutME['gamertag'];
				
				 array_push($obj->aboutMe,$aboutyourself);
				 array_push($obj->aboutMe,$currentplaying);
				 array_push($obj->aboutMe,$gamertag);
				
			}
			
			$query3="SELECT * FROM `wp_usermeta`
					WHERE user_id={$userID}
					AND meta_key='GamePropertiesCat'";

			
            $result3 = mysqli_query($con,$query3);

            if($result3 != null)
        		$resultCount3 = mysqli_num_rows($result3);
    		if($resultCount3 > 0)
			{
				$row3 = mysqli_fetch_array($result3);
           		$GamePropertiesArray = unserialize($row3['meta_value']);

           		$count1 = count($GamePropertiesArray);

           		$i=0;
           		for($i=0;$i<$count1;$i++)
           		{

           			$survey = new stdClass();
                    $survey->label=$GamePropertiesArray[$i]['lable'];
                    $survey->value=$GamePropertiesArray[$i]['value'];
                    $survey->id=$GamePropertiesArray[$i]['id'];
                    
                    array_push($obj->surveyData,$survey);

           		}
			}
			
			
			$query4="SELECT * FROM `wp_usermeta`
					WHERE user_id={$userID}
					AND meta_key='GameUserGeneres'";
	
            $result4 = mysqli_query($con,$query4);

            if($result4 != null)
        		$resultCount4 = mysqli_num_rows($result4);
    		if($resultCount4 > 0)
			{
				
				$row4 = mysqli_fetch_array($result4);
           		$GameUserGeneresArray = unserialize($row4['meta_value']);

           		$count2 = count($GameUserGeneresArray);

           		$i=0;
           		for($i=0;$i<$count2;$i++)
           		{

           			$survey = new stdClass();
                    $survey->label=$GameUserGeneresArray[$i]['label'];
                    $survey->value=$GameUserGeneresArray[$i]['point'];
                    $survey->id=$GameUserGeneresArray[$i]['id'];
                    
                    array_push($obj->surveyData,$survey);

           		}
			}
			
			
			$query5="SELECT u.user_login,u.display_name,u.user_email ,um.meta_value as fname , um2.meta_value as lname , um3.meta_value as dob , um4.meta_value as gender
					FROM wp_users u, wp_usermeta um ,wp_usermeta um2 ,wp_usermeta um3 ,wp_usermeta um4 
					WHERE u.ID={$userID} 
					AND um.meta_key='first_name' AND um.user_id={$userID}
					AND um2.meta_key='last_name' AND um2.user_id={$userID}
					AND um3.meta_key='yearofbirth' AND um3.user_id={$userID}
					AND um4.meta_key='gender' AND um4.user_id={$userID}";
	
            $result5 = mysqli_query($con,$query5);

            if($result5 != null)
        		$resultCount5 = mysqli_num_rows($result5);
    		if($resultCount5 > 0)
			{
				$row5 = mysqli_fetch_array($result5);
				
				$username = $row5['user_login'];
				$displayName= $row5['display_name'];
				$email= $row5['user_email'];
				$fname= $row5['fname'];
				$lname= $row5['lname'];
				$dob= $row5['dob'];
				$gender= $row5['gender'];
				
				$dob=str_replace("-","/",$dob);
				
				array_push($obj->userDetails,$username);
				array_push($obj->userDetails,$fname);
				array_push($obj->userDetails,$lname);
				array_push($obj->userDetails,$displayName);
				array_push($obj->userDetails,$email);
				array_push($obj->userDetails,$dob);
				array_push($obj->userDetails,$gender);
				
				
				
			}
			
			$jsonResponse->code = 0;
			$jsonResponse->status = "Success";
			$jsonResponse->message = "Data found!";
			$jsonResponse->rowCount=$count1 + $count2;
			
			array_push($jsonResponse->response,$obj);
			
    		return json_encode($jsonResponse);
			
}

function updateYourself($me,$playing,$tag)
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
		
		session_start(); 
		$userID = $_SESSION['userID'];
		
		$aboutME["aboutme"] =$me;
		$aboutME["currentplaying"] =$playing  ;
		$aboutME["gamertag"] = $tag;
		
		$aboutMEstr = serialize($aboutME);
		
		$query="SELECT * FROM wp_usermeta
				WHERE user_id={$userID}
				AND meta_key='member_details'";
	
            $result = mysqli_query($con,$query);

            if($result != null)
        		$resultCount = mysqli_num_rows($result);
    		if($resultCount > 0)
			{
				$query2 = "UPDATE wp_usermeta
							SET meta_value='{$aboutMEstr}'
							WHERE meta_key='member_details'
							AND user_id={$userID} ";
			}
			else
			{
					$query2 = " INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
								VALUES ({$userID},'member_details','{$aboutMEstr}');";
			}
			
			
			$result2 = mysqli_query($con,$query2);

            if($result2 != null)
        	{
				$jsonResponse->code = 0;
        		$jsonResponse->status = "Success";
        		$jsonResponse->message = "Updation Successfull !";
        		return json_encode($jsonResponse);
			}
			else
			{
				$jsonResponse->code = 1;
        		$jsonResponse->status = "Error";
        		$jsonResponse->message = "Updation Failed !";
        		return json_encode($jsonResponse);
			}
		
		
}

function editProfile($fname,$lname,$dname,$email,$gender,$dob,$oldPass,$newPass)
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
		
		session_start(); 
		$userID = $_SESSION['userID'];
		
		
			$fnameQuery="UPDATE wp_usermeta
						SET meta_value='{$fname}'
						WHERE meta_key='first_name'
						AND user_id={$userID}  ";
						
			$fnameResult = mysqli_query($con,$fnameQuery); 
		
		
		
			$lnameQuery="UPDATE wp_usermeta
						SET meta_value='{$lname}'
						WHERE meta_key='last_name'
						AND user_id={$userID}  ";
						
			$lnameResult = mysqli_query($con,$lnameQuery); 
		
		
		if($dname != '')
		{
			$dnameQuery="UPDATE wp_users
						SET display_name='{$dname}'
						WHERE ID={$userID}";
						
			$dnameQuery2="UPDATE wp_bp_xprofile_data
						SET `value`='{$dname}'
						WHERE user_id={$userID}";
						
			$dnameResult = mysqli_query($con,$dnameQuery); 
			$dnameResult2 = mysqli_query($con,$dnameQuery2); 
		}
		
		
			$emailQuery="UPDATE wp_users
						SET user_email='{$email}'
						WHERE ID={$userID}";
						
			$emailResult = mysqli_query($con,$emailQuery); 
	
		
		if($gender != '')
		{
			$genderQuery="UPDATE wp_usermeta
						SET meta_value='{$gender}'
						WHERE meta_key='gender'
						AND user_id={$userID}  ";
						
			$genderResult = mysqli_query($con,$genderQuery); 
		}
		
		if($dob != '')
		{
			$dob=str_replace("/","-",$dob);
			
			$dateOfBirth= new DateTime($dob);
				$currDate=new DateTime(date("Y-m-d H:i:s"));
				$interval = $dateOfBirth->diff($currDate);
				$age=$interval->y;
		
			$dobQuery="UPDATE wp_usermeta
						SET meta_value='{$dob}'
						WHERE meta_key='yearofbirth'
						AND user_id={$userID}  ";
						
			$ageQuery="UPDATE wp_usermeta
						SET meta_value='{$age}'
						WHERE meta_key='age'
						AND user_id={$userID}  ";
						
			$dobResult = mysqli_query($con,$dobQuery); 
			$ageResult = mysqli_query($con,$ageQuery); 
								
		}
		
		if( $oldPass != '' && $newPass != '')
		{
				$query="SELECT *
						FROM wp_users
						WHERE ID='{$userID}'";

				
				$result = mysqli_query($con,$query);
				
				if($result != null)
					$resultCount = mysqli_num_rows($result);
				if($resultCount > 0)
				{
						$row = mysqli_fetch_array($result);
					
						$check=wp_check_password($oldPass,$row["user_pass"]);
						if($check)
						{
							$hashedPassword=wp_hash_password( $newPass );
							
							$updatePasswordQuery = "UPDATE wp_users
													SET user_pass='{$hashedPassword}'
													WHERE ID={$userID}";
													
							$updatePasswordResult = mysqli_query($con,$updatePasswordQuery);
							
							$query2="SELECT *
									FROM wp_usermeta
									WHERE user_id={$userID}
									AND meta_key='user_credentials_for_login'";

								$result2 = mysqli_query($con,$query2);
								
								if($result2 != null)
									$resultCount2 = mysqli_num_rows($result2);
								if($resultCount2 > 0)
								{
								
									$row2 = mysqli_fetch_array($result2);
									$userCredentials=unserialize($row2["meta_value"]);
									
									$uname= $userCredentials['user_login'];
									
									$credentialsArray["user_login"] = $uname;
									$credentialsArray["user_password"] = $newPass;
									$credentialsArray["remember"] = (boolean) 1;
									$credentialStr = serialize($credentialsArray);
									
									
									$credentialsQuery="UPDATE wp_usermeta
												SET meta_value='{$credentialStr}'
												WHERE meta_key='user_credentials_for_login'
												AND user_id={$userID}  ";
												
									$credentialsResult = mysqli_query($con,$credentialsQuery);
								}
						
						}
						else
						{
							$jsonResponse->code = 1;
							$jsonResponse->status = "Error";
							$jsonResponse->message = "Old/New Password do not match !";
							return json_encode($jsonResponse);
						}
				}
		}
		
		$jsonResponse->code = 0;
		$jsonResponse->status = "Success";
		$jsonResponse->message = "Profile Updated !";
		return json_encode($jsonResponse);
}

function getSurveyDataRetake()
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


	    $count1=0;
	    $count2=0;

	    session_start(); 
		$userID = $_SESSION['userID'];
		
		$query="SELECT * FROM `wp_usermeta`
				WHERE user_id={$userID}
				AND meta_key='GamePropertiesCat'";
	
            $result = mysqli_query($con,$query);

            if($result != null)
        		$resultCount = mysqli_num_rows($result);
    		if($resultCount > 0)
    		{

		        $row = mysqli_fetch_array($result);
           		$GamePropertiesArray = unserialize($row['meta_value']);

           		$count1 = count($GamePropertiesArray);

           		$i=0;
           		for($i=0;$i<$count1;$i++)
           		{

           			$obj = new stdClass();
                    $obj->label=$GamePropertiesArray[$i]['lable'];
                    $obj->value=$GamePropertiesArray[$i]['value'];
                    $obj->id=$GamePropertiesArray[$i]['id'];
                    
                    array_push($jsonResponse->response,$obj);

           		}

    		}


    	$query2="SELECT * FROM `wp_usermeta`
				WHERE user_id={$userID}
				AND meta_key='GameUserGeneres'";
	
            $result2 = mysqli_query($con,$query2);

            if($result2 != null)
        		$resultCount2 = mysqli_num_rows($result2);
    		if($resultCount2 > 0)
    		{
    			
    			$row2 = mysqli_fetch_array($result2);
           		$GameUserGeneresArray = unserialize($row2['meta_value']);

           		$count2 = count($GameUserGeneresArray);

           		$i=0;
           		for($i=0;$i<$count2;$i++)
           		{

           			$obj = new stdClass();
                    $obj->label=$GameUserGeneresArray[$i]['label'];
                    $obj->value=$GameUserGeneresArray[$i]['point'];
                    $obj->id=$GameUserGeneresArray[$i]['id'];
                    
                    array_push($jsonResponse->response,$obj);

           		}

    		}
			
			$jsonResponse->rowCount=$count1 + $count2;
			
			if($count1 != 0 && $count2 != 0)
			{
				$jsonResponse->code = 0;
		        $jsonResponse->status = "Success";
		        $jsonResponse->message = "Details Found !";
				return json_encode($jsonResponse);
			}
			else
			{
				$jsonResponse->code = 1;
				$jsonResponse->status = "Error";
				$jsonResponse->message = "Completed Survey Not Found !";
				return json_encode($jsonResponse);
			}
}

function updateSurvey($gameProperties,$genre)
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

	    session_start(); 
		$userID = $_SESSION['userID'];
		
		$begin="START TRANSACTION";
		$beginResult = mysqli_query($con,$begin);
			
		if($beginResult == null)
		{
			$jsonResponse->code = 1;
    		$jsonResponse->status = "Error";
    		$jsonResponse->message = "TRANSACTION Failed";
    		return json_encode($jsonResponse);
		}
		else
		{
				$gameProperties = serialize($gameProperties);		
				$gameProperties=addslashes($gameProperties);
				
				
				$query="UPDATE wp_usermeta
				SET meta_value='{$gameProperties}'
				WHERE meta_key='GamePropertiesCat'
				AND user_id={$userID}  ";

				$result = mysqli_query($con,$query);
				
				
				$genre = serialize($genre);		
				$genre=addslashes($genre);
				
				$query2="UPDATE wp_usermeta
				SET meta_value='{$genre}'
				WHERE meta_key='GameUserGeneres'
				AND user_id={$userID} ";

				$result2 = mysqli_query($con,$query2);
				
				if($result != null && $result2 != null)
				{
							// COMMIT
							$COMITTQuery="COMMIT";
							$COMITTresult = mysqli_query($con,$COMITTQuery);

							$jsonResponse->code = 0;
					        $jsonResponse->status = "Success";
					        $jsonResponse->message = "Survey Details saved !";
					        return json_encode($jsonResponse); 
							
							
				}
				else
				{
					// ROLLBACK
			
					$ROLLBACKQuery="ROLLBACK";
					$ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);
					
					$jsonResponse->code = 1;
		    		$jsonResponse->status = "Error";
		    		$jsonResponse->message = "TRANSACTION Failed";
		    		return json_encode($jsonResponse);
					
				}
		}

}

function changePrivacy($profileType)
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

	    session_start(); 
		$userID = $_SESSION['userID'];
		
		$query="SELECT * FROM wp_usermeta
				WHERE user_id={$userID}
				AND meta_key='profiletype'";
	
		$result = mysqli_query($con,$query);

		if($result != null)
			$resultCount = mysqli_num_rows($result);
		if($resultCount > 0)
		{
			$query2 = "UPDATE wp_usermeta
						SET meta_value='{$profileType}'
						WHERE meta_key='profiletype'
						AND user_id={$userID} ";
		}
		else
		{
				$query2 = " INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
							VALUES ({$userID},'profiletype','{$profileType}');";
		}
			
			
		$result2 = mysqli_query($con,$query2);

            if($result2 != null)
        	{
				$jsonResponse->code = 0;
        		$jsonResponse->status = "Success";
        		$jsonResponse->message = "Updation Successfull !";
        		return json_encode($jsonResponse);
			}
			else
			{
				$jsonResponse->code = 1;
        		$jsonResponse->status = "Error";
        		$jsonResponse->message = "Updation Failed !";
        		return json_encode($jsonResponse);
			}
		
}

function updateProfilePicture($uploadPath , $imageURL , $imageType)
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
		
		session_start(); 
		$userID = $_SESSION['userID'];
		
		$profileImg["file"] = $uploadPath;
		$profileImg["url"] = $imageURL;
		$profileImg["type"] = $imageType;
		$profileImgStr = serialize($profileImg);
		
		$query="SELECT  um.meta_value as img
				FROM  wp_usermeta um
				WHERE um.user_id={$userID}
				AND um.meta_key='profile_image'";
	
        $result = mysqli_query($con,$query);

            if($result != null)
        		$resultCount = mysqli_num_rows($result);
    		if($resultCount > 0)
			{
				$query2 = "UPDATE wp_usermeta
							SET meta_value='{$profileImgStr}'
							WHERE meta_key='profile_image'
							AND user_id={$userID} ";
			}
			else
			{
					$query2 = " INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
								VALUES ({$userID},'profile_image','{$profileImgStr}');";
			}
					
		$result2 = mysqli_query($con,$query2);
		
		if($result2 != null)
		{
			$jsonResponse->code = 0;
			$jsonResponse->status = "Success";
			$jsonResponse->message = "Updation Successfull !";
			
			$obj = new stdClass();
			$obj->imageURL=$imageURL;
			array_push($jsonResponse->response,$obj);
			
			return json_encode($jsonResponse);
		}
		else
		{
			$jsonResponse->code = 1;
			$jsonResponse->status = "Error";
			$jsonResponse->message = "Updation Failed !";
			return json_encode($jsonResponse);
		}

}

function getFreiends($userID)
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
		
		
		$friends = array ();
		
		$query="SELECT * FROM `wp_bp_friends`
				WHERE initiator_user_id={$userID}
				AND is_confirmed=1";
	
            $result = mysqli_query($con,$query);

            if($result != null)
        		$resultCount = mysqli_num_rows($result);
    		if($resultCount > 0)
			{
				while($row = mysqli_fetch_array($result))
				{
					$friendID=$row["friend_user_id"];
					
					 array_push($friends,$friendID);
					
				}
			}
			
			$query2="SELECT * FROM `wp_bp_friends`
				WHERE friend_user_id={$userID}
				AND is_confirmed=1";
	
            $result2 = mysqli_query($con,$query2);

            if($result2 != null)
        		$resultCount2 = mysqli_num_rows($result2);
    		if($resultCount2 > 0)
			{
				while($row2 = mysqli_fetch_array($result2))
				{
					$friendID=$row2["initiator_user_id"];
					
					 array_push($friends,$friendID);
					
				}
			}
		
		return $friends ;
}

function checkFriendship($userID,$friendID)
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
		
		if($userID == $friendID)
		{
			return 0;
		}
		
		
		$query="SELECT *
				FROM wp_bp_friends
				WHERE (initiator_user_id={$userID} AND friend_user_id={$friendID})
				OR (initiator_user_id={$friendID} AND friend_user_id={$userID})";
						
		$result = mysqli_query($con,$query);

				if($result != null)
					$resultCount = mysqli_num_rows($result);
				if($resultCount > 0)
				{
					$row = mysqli_fetch_array($result);
					$check = $row["is_confirmed"];
					
					if($check == 1 )
					{
						return 1;
					}
					else
					{
						$check2=$row["initiator_user_id"];
					
						if( $check2 == $userID)
						{
							return 2;
						}
						else
						{
							return 3;
						}
					
						
					}
				}
				else
				{
					return 4;
				}

}

function searchMembers($criteria,$key)
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
		
		session_start(); 
		$userID = $_SESSION['userID'];
		
		if($criteria == 'lastActive' || $criteria == 'newest')
		{
				if($criteria == 'newest')
				{
					$query="SELECT u.ID,u.display_name , um.meta_value as activeTime
						FROM wp_usermeta um , wp_users u
						WHERE um.user_id in (SELECT user_id FROM wp_usermeta WHERE meta_key='current_user_timezone')
						AND u.display_name LIKE '{$key}%'
						AND u.ID = um.user_id
						AND um.meta_key='last_activity'
						ORDER BY u.user_registered DESC";
				
				}
				else
				{
					$query="SELECT u.ID,u.display_name , um.meta_value as activeTime
						FROM wp_usermeta um , wp_users u
						WHERE um.user_id in (SELECT user_id FROM wp_usermeta WHERE meta_key='current_user_timezone')
						AND u.display_name LIKE '{$key}%'
						AND u.ID = um.user_id
						AND um.meta_key='last_activity'
						ORDER BY activeTime DESC";
				}
				
				$result = mysqli_query($con,$query);

				if($result != null)
					$resultCount = mysqli_num_rows($result);
				if($resultCount > 0)
				{
					$jsonResponse->rowCount=$resultCount;
				
					while($row = mysqli_fetch_array($result))
					{
						$jsonResponse->code = 0;
						$jsonResponse->status = "Success";
						$jsonResponse->message = "Members Found !";
					
					
						$member = new stdClass();
						$member->ID=$row["ID"];
						$member->display_name=$row["display_name"];
						$member->friendship_code = checkFriendship($userID,$member->ID);
						
						$dateSrc = $row["activeTime"];
						$retrieveDate = new DateTime($dateSrc);
						$currDate=new DateTime(date("Y-m-d H:i:s"));
						
						$interval = $retrieveDate->diff($currDate);
						
						$member->activeTime= timeFormat($interval);
						
						$query2="SELECT  um.meta_value as img
							FROM  wp_usermeta um
							WHERE um.user_id={$row["ID"]}
							AND um.meta_key='profile_image'";
					
						$result2 = mysqli_query($con,$query2);
						
						if($result2 != null)
							$resultCount2 = mysqli_num_rows($result2);
						if($resultCount2 > 0)
						{
							$row2 = mysqli_fetch_array($result2);
							
							$ImgURL=$row2["img"];
							$ImgURL=unserialize($ImgURL);
				
							$member->img= $ImgURL['url'];
						}
						else
						{
							$member->img="images/profile.gif";
						}
						
						$query3="SELECT  um.meta_value as statusMsg
								FROM  wp_usermeta um
								WHERE um.user_id={$row["ID"]}
								AND um.meta_key='bp_latest_update'";
						
						$result3 = mysqli_query($con,$query3);
			
						if($result3 != null)
							$resultCount3 = mysqli_num_rows($result3);
							
							if($resultCount3 > 0)
							{
								$row3 = mysqli_fetch_array($result3);
								
								$status=$row3["statusMsg"];
								$status=unserialize($status);
					
								$member->statusMsg= $status['content'];
							}
							else
							{
								$member->statusMsg='';
							}
					
						array_push($jsonResponse->response,$member);
					
					}
				}

		}
		
		if($criteria == 'alphabetical' )
		{
			$query="SELECT u.ID,u.display_name
					FROM  wp_users u
					WHERE u.display_name LIKE '{$key}%'
					ORDER BY u.display_name";
			
			$result = mysqli_query($con,$query);

				if($result != null)
					$resultCount = mysqli_num_rows($result);
				if($resultCount > 0)
				{
					$jsonResponse->rowCount=$resultCount;
					
					while($row = mysqli_fetch_array($result))
					{
						$jsonResponse->code = 0;
						$jsonResponse->status = "Success";
						$jsonResponse->message = "Members Found !";
					
						$member = new stdClass();
						$member->ID=$row["ID"];
						$member->display_name=$row["display_name"];
						$member->friendship_code = checkFriendship($userID,$member->ID);
						
						$query2="SELECT  um.meta_value as img
							FROM  wp_usermeta um
							WHERE um.user_id={$row["ID"]}
							AND um.meta_key='profile_image'";
					
						$result2 = mysqli_query($con,$query2);
						
						if($result2 != null)
							$resultCount2 = mysqli_num_rows($result2);
						if($resultCount2 > 0)
						{
							$row2 = mysqli_fetch_array($result2);
							
							$ImgURL=$row2["img"];
							$ImgURL=unserialize($ImgURL);
				
							$member->img= $ImgURL['url'];
						}
						else
						{
							$member->img="images/profile.gif";
						}
						
						$query3="SELECT  um.meta_value as statusMsg
								FROM  wp_usermeta um
								WHERE um.user_id={$row["ID"]}
								AND um.meta_key='bp_latest_update'";
						
						$result3 = mysqli_query($con,$query3);
			
						if($result3 != null)
							$resultCount3 = mysqli_num_rows($result3);
							
							if($resultCount3 > 0)
							{
								$row3 = mysqli_fetch_array($result3);
								
								$status=$row3["statusMsg"];
								$status=unserialize($status);
					
								$member->statusMsg= $status['content'];
							}
							else
							{
								$member->statusMsg='';
							}
							
							$query4="SELECT  um.meta_value as activeTime
									FROM  wp_usermeta um
									WHERE um.user_id={$row["ID"]}
									AND um.meta_key='last_activity'";
						
							$result4 = mysqli_query($con,$query4);
				
							if($result4 != null)
								$resultCount4 = mysqli_num_rows($result4);
								
								if($resultCount4 > 0)
								{
									$row4 = mysqli_fetch_array($result4);
									
									$dateSrc = $row4["activeTime"];
									$retrieveDate = new DateTime($dateSrc);
									$currDate=new DateTime(date("Y-m-d H:i:s"));
									
									$interval = $retrieveDate->diff($currDate);
									
									$member->activeTime= timeFormat($interval);
								
									
								}
								else
								{
									$member->activeTime='Not recently active';
								}
								
							array_push($jsonResponse->response,$member);
					
					}
				
				
				}
		}
		
	return json_encode($jsonResponse);
}

function updateLastActiveTime($userID)
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

			$timeNow = date('Y-m-d H:i:s');
					
			$timeUpdate="UPDATE wp_usermeta
						SET meta_value='{$timeNow}'
						WHERE meta_key='last_activity'
						AND user_id={$userID}";
			
			$timeUpdateResult = mysqli_query($con,$timeUpdate);
		
			$timeUpdate2="UPDATE wp_bp_activity
						SET date_recorded='{$timeNow}'
						WHERE user_id={$userID}
						AND type='last_activity'";
			
			$timeUpdateResult2 = mysqli_query($con,$timeUpdate2);
}

function addFriend($friendID,$code)
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
		
		session_start(); 
		$userID = $_SESSION['userID'];
		
		$timeNow = date('Y-m-d H:i:s');
		
		$begin="START TRANSACTION";
		$beginResult = mysqli_query($con,$begin);
				
		if($beginResult == null)
		{
			
			$jsonResponse->code = 1;
			$jsonResponse->status = "Error";
			$jsonResponse->message = "Friendship Request Not Sent !";
			return json_encode($jsonResponse);	
		}
		else
		{
				$friendshipID='';
		
				// INSERT 1
				$insert1="INSERT INTO wp_bp_friends (initiator_user_id,friend_user_id,is_confirmed,is_limited,date_created)
						VALUES ({$userID},{$friendID},0,0,'{$timeNow}')";
		
						  
				$result1 = mysqli_query($con,$insert1);
				
				
				//Getting Friendship ID
				
				$query="SELECT *
						FROM wp_bp_friends
						WHERE initiator_user_id={$userID}
						AND friend_user_id={$friendID}";
			
				$result = mysqli_query($con,$query);

				if($result != null)
					$resultCount = mysqli_num_rows($result);
				if($resultCount > 0)
				{
						$row = mysqli_fetch_array($result);			
						$friendshipID = $row["id"];
				}
		
				
				// INSERT 2
				$insert2="INSERT INTO wp_bp_notifications (user_id,item_id,secondary_item_id,component_name,component_action,date_notified,is_new)
							VALUES ({$friendID},{$userID},{$friendshipID},'friends','friendship_request','{$timeNow}',1)";
						  
				$result2 = mysqli_query($con,$insert2);
				
				
				if($result != null && $result1 != null && $result2 != null)
				{
							// COMMIT
							$COMITTQuery="COMMIT";
							$COMITTresult = mysqli_query($con,$COMITTQuery);
							
							$jsonResponse->code = 0;
							$jsonResponse->status = "Success";
							$jsonResponse->message = "Friendship Request Sent !";
							
							$obj = new stdClass();
							$obj->code=$code;
							$obj->ID=$friendID;
							array_push($jsonResponse->response,$obj);
							return json_encode($jsonResponse);
						
				}
				else
				{
					// ROLLBACK
			
					$ROLLBACKQuery="ROLLBACK";
					$ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);
					
					$jsonResponse->code = 1;
					$jsonResponse->status = "Error";
					$jsonResponse->message = "Friendship Request Not Sent !";
					return json_encode($jsonResponse);
				}
	
		}

}

function cancelFriendRequest($friendID,$code)
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

		session_start(); 
		$userID = $_SESSION['userID'];
		
		$begin="START TRANSACTION";
		$beginResult = mysqli_query($con,$begin);
				
		if($beginResult == null)
		{
			$jsonResponse->code = 1;
			$jsonResponse->status = "Error";
			$jsonResponse->message = "Friendship Request Not Cancelled !";
			return json_encode($jsonResponse);	
		}
		else
		{
				
				$DELquery="DELETE FROM wp_bp_friends
							WHERE initiator_user_id={$userID}
							AND friend_user_id={$friendID}";
			
				$DELresult = mysqli_query($con,$DELquery);
				
				$DELquery2="DELETE FROM wp_bp_notifications
							WHERE user_id={$friendID}
							AND item_id={$userID}
							AND component_action='friendship_request'";
			
				$DELresult2 = mysqli_query($con,$DELquery2);
				
				if($DELresult != null && $DELresult2 != null)
				{
							// COMMIT
							$COMITTQuery="COMMIT";
							$COMITTresult = mysqli_query($con,$COMITTQuery);
							
							$jsonResponse->code = 0;
							$jsonResponse->status = "Success";
							$jsonResponse->message = "Friendship Request Cancelled !";
							
							$obj = new stdClass();
							$obj->code=$code;
							$obj->ID=$friendID;
							array_push($jsonResponse->response,$obj);
							return json_encode($jsonResponse);
						
				}
				else
				{
					// ROLLBACK
			
					$ROLLBACKQuery="ROLLBACK";
					$ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);
					
					$jsonResponse->code = 1;
					$jsonResponse->status = "Error";
					$jsonResponse->message = "Friendship Request Not Cancelled !";
					return json_encode($jsonResponse);
				}
		
			
		}

	
}

function unfriend($friendID,$code)
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

		session_start(); 
		$userID = $_SESSION['userID'];
		
		$DELquery="DELETE FROM wp_bp_friends
					WHERE (initiator_user_id={$userID} AND friend_user_id={$friendID})
					OR (initiator_user_id={$friendID} AND friend_user_id={$userID})";
			
		$DELresult = mysqli_query($con,$DELquery);
		
		if($DELresult != null)
		{
			$jsonResponse->code = 0;
			$jsonResponse->status = "Success";
			$jsonResponse->message = "Friendship Request Cancelled !";
			
			$obj = new stdClass();
			$obj->code=$code;
			$obj->ID=$friendID;
			array_push($jsonResponse->response,$obj);
			return json_encode($jsonResponse);
						
		}
		else
		{
			$jsonResponse->code = 1;
			$jsonResponse->status = "Error";
			$jsonResponse->message = "Friendship Request Not Cancelled !";
			return json_encode($jsonResponse);
		}
	
}

function acceptFriend($friendID,$code)
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
		
		$friendshipID='';
		$username='';
		$userDisplayName='';
		$friendUsername='';
		$friendDisplayName='';
		
		$timeNow = date('Y-m-d H:i:s');

		session_start(); 
		$userID = $_SESSION['userID'];
		
		$begin="START TRANSACTION";
		$beginResult = mysqli_query($con,$begin);
				
		if($beginResult == null)
		{
			$jsonResponse->code = 1;
			$jsonResponse->status = "Error";
			$jsonResponse->message = "Friendship Request Not Cancelled !";
			return json_encode($jsonResponse);	
		}
		else
		{
		
				$query="SELECT user_login , display_name
						FROM wp_users
						WHERE ID={$userID}";
			
				$result = mysqli_query($con,$query);

				if($result != null)
					$resultCount = mysqli_num_rows($result);
				if($resultCount > 0)
				{
					$row = mysqli_fetch_array($result);
					
					$username=$row["user_login"];
					$userDisplayName=$row["display_name"];
					
					
				}
				
				
				$query2="SELECT user_login , display_name
							FROM wp_users
							WHERE ID={$friendID}";
			
				$result2 = mysqli_query($con,$query2);

				if($result2 != null)
					$resultCount2 = mysqli_num_rows($result2);
				if($resultCount2 > 0)
				{
				
					$row2 = mysqli_fetch_array($result2);
					$friendUsername=$row2["user_login"];
					$friendDisplayName=$row2["display_name"];
				}
				
				
				$query3="SELECT *
						FROM wp_bp_friends
						WHERE initiator_user_id={$friendID}
						AND friend_user_id={$userID}";
			
				$result3 = mysqli_query($con,$query3);

				if($result3 != null)
					$resultCount3 = mysqli_num_rows($result3);
				if($resultCount3 > 0)
				{
					$row3 = mysqli_fetch_array($result3);			
					$friendshipID = $row3["id"];
				}
				
				
			$actionStr1='<a href="http://meterbreak.com/members/'.$username.'/" title="'.$userDisplayName.'">'.$userDisplayName.'</a> and <a href="http://meterbreak.com/members/'.$friendUsername.'/" title="'.$friendDisplayName.'">'.$friendDisplayName.'</a> are now friends';
			$actionStr2='<a href="http://meterbreak.com/members/'.$friendUsername.'/" title="'.$friendDisplayName.'">'.$friendDisplayName.'</a> and <a href="http://meterbreak.com/members/'.$username.'/" title="'.$userDisplayName.'">'.$userDisplayName.'</a> are now friends';
			$linkStr='http://meterbreak.com/members/'.$username.'/';
			
				
				//Updating Friendship Status
				$updateFriendship="UPDATE wp_bp_friends
									SET is_confirmed=1
									WHERE id={$friendshipID}";
			
				$updateResult = mysqli_query($con,$updateFriendship);
				
				//Notification for sender
				
				$insert="INSERT INTO wp_bp_notifications (user_id,item_id,secondary_item_id,component_name,component_action,date_notified,is_new)
							VALUES ({$friendID},{$userID},{$friendshipID},'friends','friendship_accepted','{$timeNow}',1)";
						  
				$insertResult = mysqli_query($con,$insert);
				
				// INSERT 2
				$insert2="INSERT INTO wp_bp_activity (user_id,component,type,action,primary_link,item_id,secondary_item_id,date_recorded,hide_sitewide,mptt_left,mptt_right,is_spam)
							VALUES ({$userID},'friends','friendship_created','{$actionStr1}','{$linkStr}',{$friendshipID},{$friendID},'{$timeNow}',1,0,0,0)";
						  
				$insertResult2 = mysqli_query($con,$insert2);
				
				// INSERT 3
				$insert3="INSERT INTO wp_bp_activity (user_id,component,type,action,primary_link,item_id,secondary_item_id,date_recorded,hide_sitewide,mptt_left,mptt_right,is_spam)
							VALUES ({$friendID},'friends','friendship_created','{$actionStr2}','{$linkStr}',{$friendshipID},{$userID},'{$timeNow}',0,0,0,0)";
						  
				$insertResult3 = mysqli_query($con,$insert3);
				
				if($result && $result2 && $result3 && $updateResult && $insertResult && $insertResult2 && $insertResult3)
				{
					// COMMIT
					$COMITTQuery="COMMIT";
					$COMITTresult = mysqli_query($con,$COMITTQuery);
					
					$jsonResponse->code = 0;
					$jsonResponse->status = "Success";
					$jsonResponse->message = "Friendship Request Cancelled !";
					
					$obj = new stdClass();
					$obj->code=$code;
					$obj->ID=$friendID;
					array_push($jsonResponse->response,$obj);
					return json_encode($jsonResponse);
				}
				else
				{
					// ROLLBACK
			
					$ROLLBACKQuery="ROLLBACK";
					$ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);
					
					$jsonResponse->code = 1;
					$jsonResponse->status = "Error";
					$jsonResponse->message = "Friendship Request Not Cancelled !";
					return json_encode($jsonResponse);
				}
			
		}
}

function getMemberInfo($memberID)
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
	
	session_start(); 
	$userID = $_SESSION['userID'];
	
	$obj = new stdClass();
	$obj->aboutMe= array () ;
	$obj->newsfeed = array();
	$obj->gender='';
	$obj->img='';
	$obj->username='';
	$obj->display_name='';
	$obj->activeTime='';
	$obj->memberID=$memberID;
	$obj->friendship_code = checkFriendship($userID,$memberID);
	
		
		
		$imgQuery="SELECT  um.meta_value as img
							FROM  wp_usermeta um
							WHERE um.user_id={$memberID}
							AND um.meta_key='profile_image'";
					
			$imgResult = mysqli_query($con,$imgQuery);
			
			if($imgResult != null)
				$imgResultCount2 = mysqli_num_rows($imgResult);
			if($imgResultCount2 > 0)
			{
				$imgrow = mysqli_fetch_array($imgResult);
				
				$ImgURL=$imgrow["img"];
				$ImgURL=unserialize($ImgURL);

				$obj->img= $ImgURL['url'];
			}
			else
			{
				$obj->img="images/profile.gif";
			}
		
		
			$query="SELECT * FROM wp_usermeta
					WHERE user_id={$memberID}
					AND meta_key='gender'";
	
            $result = mysqli_query($con,$query);

            if($result != null)
        		$resultCount = mysqli_num_rows($result);
    		if($resultCount > 0)
			{
				$row = mysqli_fetch_array($result);
				$obj->gender=$row["meta_value"];
			
			}
			
			$query2="SELECT * FROM wp_usermeta
				WHERE user_id={$memberID}
				AND meta_key='member_details'";
	
            $result2 = mysqli_query($con,$query2);

            if($result2 != null)
        		$resultCount2 = mysqli_num_rows($result2);
    		if($resultCount2 > 0)
			{
				$row2 = mysqli_fetch_array($result2);
				$aboutME=unserialize($row2["meta_value"]);
				
				$aboutyourself= $aboutME['aboutme'];
				$currentplaying= $aboutME['currentplaying'];
				$gamertag= $aboutME['gamertag'];
				
				 array_push($obj->aboutMe,$aboutyourself);
				 array_push($obj->aboutMe,$currentplaying);
				 array_push($obj->aboutMe,$gamertag);
				
			}
			
			$query3="SELECT user_login,display_name
					FROM wp_users
					WHERE ID={$memberID}";
	
            $result3 = mysqli_query($con,$query3);

            if($result3 != null)
        		$resultCount3 = mysqli_num_rows($result3);
    		if($resultCount3 > 0)
			{
				$row3 = mysqli_fetch_array($result3);
				$obj->username=$row3["user_login"];
				$obj->display_name=$row3["display_name"];
				
			}
			
			$query4="SELECT  um.meta_value as activeTime
									FROM  wp_usermeta um
									WHERE um.user_id={$memberID}
									AND um.meta_key='last_activity'";
						
				$result4 = mysqli_query($con,$query4);
	
				if($result4 != null)
					$resultCount4 = mysqli_num_rows($result4);
					
					if($resultCount4 > 0)
					{
						$row4 = mysqli_fetch_array($result4);
						
						$dateSrc = $row4["activeTime"];
						$retrieveDate = new DateTime($dateSrc);
						$currDate=new DateTime(date("Y-m-d H:i:s"));
						
						$interval = $retrieveDate->diff($currDate);
						
						$obj->activeTime= timeFormat($interval);
					
						
					}
					else
					{
						$member->activeTime='Not recently active';
					}
					
					
					
			$profiletype='public';	
			$check;
					
			$profileQuery="SELECT * FROM wp_usermeta
				WHERE user_id={$memberID}
				AND meta_key='profiletype'";
	
            $profileResult = mysqli_query($con,$profileQuery);

            if($profileResult != null)
        		$resultCount = mysqli_num_rows($profileResult);
    		if($profileResultCount > 0)
			{
				$profilerow = mysqli_fetch_array($profileResult);
				$profiletype=$profilerow['meta_value'];
				
				if($profiletype == 'public')
				{
					$check=1;
				}
				else
				{
					$check=checkFriendship($userID,$memberID);
				}
				
			}
			else
			{
				$check=1;
			}

			if($check)
			{
						$query="SELECT * FROM `wp_bp_activity`
								WHERE ((user_id={$memberID} AND type <> 'activity_comment')
								OR user_id IN (SELECT initiator_user_id FROM `wp_bp_friends`WHERE  friend_user_id={$memberID} AND is_confirmed=1) 
								OR user_id IN (SELECT friend_user_id FROM `wp_bp_friends`WHERE  initiator_user_id={$memberID} AND is_confirmed=1))
								AND type <> 'last_activity'
								AND hide_sitewide=0
								ORDER BY date_recorded DESC";

						$result = mysqli_query($con,$query);

						if($result != null)
							$resultCount = mysqli_num_rows($result);
						if($resultCount > 0)
						{

							while($row = mysqli_fetch_array($result))
							{
								$activity = new stdClass();
								$activity->comments = array ();
								$activity->content='';
								$activity->action=$row["action"];
								$activity->ID=$row["id"];
								$activity->user_id=$row["user_id"];
								$activity->content=$row["content"];
								
								if($row["type"]=='activity_comment' || $row["type"]=='bbp_reply_create')
								{
									$activity->buttonCode=0;
								}
								else
								{
									$activity->buttonCode=1;
									if($row["type"]==$userID)
									{
										$activity->buttonCode=2;
									}
									
								}
								
								$dateSrc = $row["date_recorded"];
								$retrieveDate = new DateTime($dateSrc);
								$currDate=new DateTime(date("Y-m-d H:i:s"));
								$interval = $retrieveDate->diff($currDate);
								
								$activity->actionTime= timeFormat($interval);
								
								$likeQuery="SELECT * 
											FROM wp_thumbs
											WHERE itemid={$activity->ID} AND votetype=1";
									
								$likeResult = mysqli_query($con,$likeQuery);
								
								if($likeResult != null)
									$activity->likes = mysqli_num_rows($likeResult);
									
								$dislikeQuery="SELECT * 
											FROM wp_thumbs
											WHERE itemid={$activity->ID} AND votetype=0";
									
								$dislikeResult = mysqli_query($con,$dislikeQuery);
								
								if($dislikeResult != null)
									$activity->dislikes = mysqli_num_rows($dislikeResult);
								
								
								$imgQuery="SELECT  um.meta_value as img
											FROM  wp_usermeta um
											WHERE um.user_id={$activity->user_id}
											AND um.meta_key='profile_image'";
									
								$imgResult = mysqli_query($con,$imgQuery);
								
								if($imgResult != null)
									$imgResultCount2 = mysqli_num_rows($imgResult);
								if($imgResultCount2 > 0)
								{
									$imgrow = mysqli_fetch_array($imgResult);
									
									$ImgURL=$imgrow["img"];
									$ImgURL=unserialize($ImgURL);

									$activity->img= $ImgURL['url'];
								}
								else
								{
									$activity->img="images/profile.gif";
								}
								
								
								
									$CommentQuery="SELECT * FROM `wp_bp_activity`
													WHERE secondary_item_id={$activity->ID}
													AND item_id={$activity->ID}
													ORDER BY date_recorded ASC";

									$CommentResult = mysqli_query($con,$CommentQuery);

									if($CommentResult != null)
										$CommentResultCount = mysqli_num_rows($CommentResult);
									if($CommentResultCount > 0)
									{
										while($row2 = mysqli_fetch_array($CommentResult))
										{
											
											$comment = new stdClass();
											$comment->subComments = array();
											$comment->content='';
											
											$comment->action=$row2["action"];
											$comment->ID=$row2["id"];
											$comment->user_id=$row2["user_id"];
											$comment->content=$row2["content"];
											
											$dateSrc = $row2["date_recorded"];
											$retrieveDate = new DateTime($dateSrc);
											$currDate=new DateTime(date("Y-m-d H:i:s"));
											$interval = $retrieveDate->diff($currDate);
											
											$comment->actionTime= timeFormat($interval);
											
											$likeQuery="SELECT * 
														FROM wp_thumbs
														WHERE itemid={$comment->ID} AND votetype=1";
												
											$likeResult = mysqli_query($con,$likeQuery);
											
											if($likeResult != null)
												$comment->likes = mysqli_num_rows($likeResult);
												
											$dislikeQuery="SELECT * 
														FROM wp_thumbs
														WHERE itemid={$comment->ID} AND votetype=0";
												
											$dislikeResult = mysqli_query($con,$dislikeQuery);
											
											if($dislikeResult != null)
												$comment->dislikes = mysqli_num_rows($dislikeResult);
											
											
											
											$imgQuery="SELECT  um.meta_value as img
											FROM  wp_usermeta um
											WHERE um.user_id={$comment->user_id}
											AND um.meta_key='profile_image'";
									
											$imgResult = mysqli_query($con,$imgQuery);
											
											if($imgResult != null)
												$imgResultCount2 = mysqli_num_rows($imgResult);
											if($imgResultCount2 > 0)
											{
												$imgrow = mysqli_fetch_array($imgResult);
												
												$ImgURL=$imgrow["img"];
												$ImgURL=unserialize($ImgURL);

												$comment->img= $ImgURL['url'];
											}
											else
											{
												$comment->img="images/profile.gif";
											}
											
												$subCommentQuery="SELECT * FROM `wp_bp_activity`
													WHERE secondary_item_id={$comment->ID}
													AND item_id={$activity->ID}
													ORDER BY date_recorded ASC";

												$subCommentResult = mysqli_query($con,$subCommentQuery);

												if($subCommentResult != null)
													$subCommentResultCount = mysqli_num_rows($subCommentResult);
												if($subCommentResultCount > 0)
												{
													while($row3 = mysqli_fetch_array($subCommentResult))
													{
														$subComment = new stdClass();
														$subComment->content='';
														
														$subComment->action=$row3["action"];
														$subComment->ID=$row3["id"];
														$subComment->user_id=$row3["user_id"];
														$subComment->content=$row3["content"];
														
														$dateSrc = $row3["date_recorded"];
														$retrieveDate = new DateTime($dateSrc);
														$currDate=new DateTime(date("Y-m-d H:i:s"));
														$interval = $retrieveDate->diff($currDate);
														
														$subComment->actionTime= timeFormat($interval);
														
														$likeQuery="SELECT * 
																	FROM wp_thumbs
																	WHERE itemid={$subComment->ID} AND votetype=1";
															
														$likeResult = mysqli_query($con,$likeQuery);
														
														if($likeResult != null)
															$subComment->likes = mysqli_num_rows($likeResult);
															
														$dislikeQuery="SELECT * 
																	FROM wp_thumbs
																	WHERE itemid={$subComment->ID} AND votetype=0";
															
														$dislikeResult = mysqli_query($con,$dislikeQuery);
														
														if($dislikeResult != null)
															$subComment->dislikes = mysqli_num_rows($dislikeResult);
														
														
														
														$imgQuery="SELECT  um.meta_value as img
																	FROM  wp_usermeta um
																	WHERE um.user_id={$subComment->user_id}
																	AND um.meta_key='profile_image'";
															
														$imgResult = mysqli_query($con,$imgQuery);
														
														if($imgResult != null)
															$imgResultCount2 = mysqli_num_rows($imgResult);
														if($imgResultCount2 > 0)
														{
															$imgrow = mysqli_fetch_array($imgResult);
															
															$ImgURL=$imgrow["img"];
															$ImgURL=unserialize($ImgURL);

															$subComment->img= $ImgURL['url'];
														}
														else
														{
															$subComment->img="images/profile.gif";
														}
														
														array_push($comment->subComments,$subComment);
														
													}
												}
											
											array_push($activity->comments,$comment);
										
										}
									}
									
								array_push($obj->newsfeed,$activity);

							}
						
						}
			}
			
			$jsonResponse->code = 0;
			$jsonResponse->status = "Success";
			$jsonResponse->message = "Data found!";
			
			array_push($jsonResponse->response,$obj);
			
			return json_encode($jsonResponse);

}

function getMyNewsfeed()
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
	
	session_start(); 
	$userID = $_SESSION['userID'];
	
		
		$query="SELECT * FROM `wp_bp_activity`
				WHERE ((user_id={$userID} AND type <> 'activity_comment')
				OR user_id IN (SELECT initiator_user_id FROM `wp_bp_friends`WHERE  friend_user_id={$userID} AND is_confirmed=1) 
				OR user_id IN (SELECT friend_user_id FROM `wp_bp_friends`WHERE  initiator_user_id={$userID} AND is_confirmed=1))
				AND type <> 'last_activity'
				AND hide_sitewide=0
				ORDER BY date_recorded DESC";

		$result = mysqli_query($con,$query);

		if($result != null)
			$resultCount = mysqli_num_rows($result);
		if($resultCount > 0)
		{
			$jsonResponse->code = 0;
			$jsonResponse->status = "Success";
			$jsonResponse->message = "{$resultCount} Activities found!";
			$jsonResponse->rowCount=$resultCount;

			while($row = mysqli_fetch_array($result))
			{
				$activity = new stdClass();
				$activity->comments = array ();
				$activity->content='';
				$activity->action=$row["action"];
				$activity->ID=$row["id"];
				$activity->user_id=$row["user_id"];
				$activity->content=$row["content"];
				
				if($row["type"]=='activity_comment' || $row["type"]=='bbp_reply_create')
				{
					$activity->buttonCode=0;
				}
				else
				{
					$activity->buttonCode=1;
					if($row["type"]==$userID)
					{
						$activity->buttonCode=2;
					}
					
				}
				
				$dateSrc = $row["date_recorded"];
				$retrieveDate = new DateTime($dateSrc);
				$currDate=new DateTime(date("Y-m-d H:i:s"));
				$interval = $retrieveDate->diff($currDate);
				
				$activity->actionTime= timeFormat($interval);
				
				$likeQuery="SELECT * 
							FROM wp_thumbs
							WHERE itemid={$activity->ID} AND votetype=1";
					
				$likeResult = mysqli_query($con,$likeQuery);
				
				if($likeResult != null)
					$activity->likes = mysqli_num_rows($likeResult);
					
				$dislikeQuery="SELECT * 
							FROM wp_thumbs
							WHERE itemid={$activity->ID} AND votetype=0";
					
				$dislikeResult = mysqli_query($con,$dislikeQuery);
				
				if($dislikeResult != null)
					$activity->dislikes = mysqli_num_rows($dislikeResult);
				
				
				
				$imgQuery="SELECT  um.meta_value as img
							FROM  wp_usermeta um
							WHERE um.user_id={$activity->user_id}
							AND um.meta_key='profile_image'";
					
				$imgResult = mysqli_query($con,$imgQuery);
				
				if($imgResult != null)
					$imgResultCount2 = mysqli_num_rows($imgResult);
				if($imgResultCount2 > 0)
				{
					$imgrow = mysqli_fetch_array($imgResult);
					
					$ImgURL=$imgrow["img"];
					$ImgURL=unserialize($ImgURL);

					$activity->img= $ImgURL['url'];
				}
				else
				{
					$activity->img="images/profile.gif";
				}
				
				
				
					$CommentQuery="SELECT * FROM `wp_bp_activity`
									WHERE secondary_item_id={$activity->ID}
									AND item_id={$activity->ID}
									ORDER BY date_recorded ASC";

					$CommentResult = mysqli_query($con,$CommentQuery);

					if($CommentResult != null)
						$CommentResultCount = mysqli_num_rows($CommentResult);
					if($CommentResultCount > 0)
					{
						while($row2 = mysqli_fetch_array($CommentResult))
						{
							
							$comment = new stdClass();
							$comment->subComments = array();
							$comment->content='';
							
							$comment->action=$row2["action"];
							$comment->ID=$row2["id"];
							$comment->user_id=$row2["user_id"];
							$comment->content=$row2["content"];
							
							$dateSrc = $row2["date_recorded"];
							$retrieveDate = new DateTime($dateSrc);
							$currDate=new DateTime(date("Y-m-d H:i:s"));
							$interval = $retrieveDate->diff($currDate);
							
							$comment->actionTime= timeFormat($interval);
							
							
							$likeQuery="SELECT * 
										FROM wp_thumbs
										WHERE itemid={$comment->ID} AND votetype=1";
								
							$likeResult = mysqli_query($con,$likeQuery);
							
							if($likeResult != null)
								$comment->likes = mysqli_num_rows($likeResult);
								
							$dislikeQuery="SELECT * 
										FROM wp_thumbs
										WHERE itemid={$comment->ID} AND votetype=0";
								
							$dislikeResult = mysqli_query($con,$dislikeQuery);
							
							if($dislikeResult != null)
								$comment->dislikes = mysqli_num_rows($dislikeResult);
							
							
							$imgQuery="SELECT  um.meta_value as img
							FROM  wp_usermeta um
							WHERE um.user_id={$comment->user_id}
							AND um.meta_key='profile_image'";
					
							$imgResult = mysqli_query($con,$imgQuery);
							
							if($imgResult != null)
								$imgResultCount2 = mysqli_num_rows($imgResult);
							if($imgResultCount2 > 0)
							{
								$imgrow = mysqli_fetch_array($imgResult);
								
								$ImgURL=$imgrow["img"];
								$ImgURL=unserialize($ImgURL);

								$comment->img= $ImgURL['url'];
							}
							else
							{
								$comment->img="images/profile.gif";
							}
							
								$subCommentQuery="SELECT * FROM `wp_bp_activity`
									WHERE secondary_item_id={$comment->ID}
									AND item_id={$activity->ID}
									ORDER BY date_recorded ASC";

								$subCommentResult = mysqli_query($con,$subCommentQuery);

								if($subCommentResult != null)
									$subCommentResultCount = mysqli_num_rows($subCommentResult);
								if($subCommentResultCount > 0)
								{
									while($row3 = mysqli_fetch_array($subCommentResult))
									{
										$subComment = new stdClass();
										$subComment->content='';
										
										$subComment->action=$row3["action"];
										$subComment->ID=$row3["id"];
										$subComment->user_id=$row3["user_id"];
										$subComment->content=$row3["content"];
										
										$dateSrc = $row3["date_recorded"];
										$retrieveDate = new DateTime($dateSrc);
										$currDate=new DateTime(date("Y-m-d H:i:s"));
										$interval = $retrieveDate->diff($currDate);
										
										$subComment->actionTime= timeFormat($interval);
										
										$likeQuery="SELECT * 
													FROM wp_thumbs
													WHERE itemid={$subComment->ID} AND votetype=1";
											
										$likeResult = mysqli_query($con,$likeQuery);
										
										if($likeResult != null)
											$subComment->likes = mysqli_num_rows($likeResult);
											
										$dislikeQuery="SELECT * 
													FROM wp_thumbs
													WHERE itemid={$subComment->ID} AND votetype=0";
											
										$dislikeResult = mysqli_query($con,$dislikeQuery);
										
										if($dislikeResult != null)
											$subComment->dislikes = mysqli_num_rows($dislikeResult);
										
										
										$imgQuery="SELECT  um.meta_value as img
													FROM  wp_usermeta um
													WHERE um.user_id={$subComment->user_id}
													AND um.meta_key='profile_image'";
											
										$imgResult = mysqli_query($con,$imgQuery);
										
										if($imgResult != null)
											$imgResultCount2 = mysqli_num_rows($imgResult);
										if($imgResultCount2 > 0)
										{
											$imgrow = mysqli_fetch_array($imgResult);
											
											$ImgURL=$imgrow["img"];
											$ImgURL=unserialize($ImgURL);

											$subComment->img= $ImgURL['url'];
										}
										else
										{
											$subComment->img="images/profile.gif";
										}
										
										array_push($comment->subComments,$subComment);
										
									}
								}
							
							array_push($activity->comments,$comment);
						
						}
					}
					
				array_push($jsonResponse->response,$activity);

			}
			
			return json_encode($jsonResponse);
		}
		else
		{
			$jsonResponse->code = 1;
			$jsonResponse->status = "Error";
			$jsonResponse->message = "No Activity Found";
			return json_encode($jsonResponse);
		}
		

}

function commentOnActivity($content,$item_id,$secondary_item_id)
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
	
	$username='';
	$userDisplayName='';
	
	session_start(); 
	$userID = $_SESSION['userID'];
	
	$timeNow = date('Y-m-d H:i:s');
	
	
	$query="SELECT user_login , display_name
			FROM wp_users
			WHERE ID={$userID}";

	$result = mysqli_query($con,$query);

	if($result != null)
		$resultCount = mysqli_num_rows($result);
	if($resultCount > 0)
	{
		$row = mysqli_fetch_array($result);
		
		$username=$row["user_login"];
		$userDisplayName=$row["display_name"];
	
	}
	
	$actionStr='<a href="http://meterbreak.com/members/'.$username.'/" title="'.$userDisplayName.'">'.$userDisplayName.'</a> posted a new activity comment';
	$linkStr='http://meterbreak.com/members/'.$username.'/';
	
	// INSERT 1
	$insert1="INSERT INTO wp_bp_activity (user_id,component,type,action,content,primary_link,item_id,secondary_item_id,date_recorded,hide_sitewide,mptt_left,mptt_right,is_spam)
				VALUES ({$userID},'activity','activity_comment','{$actionStr}','{$content}','{$linkStr}',{$item_id},{$secondary_item_id},'{$timeNow}',0,0,0,0)";
			  
	$insertResult1 = mysqli_query($con,$insert1);
	
	if($result && $insertResult1 )
	{
		$jsonResponse->code = 0;
		$jsonResponse->status = "Success";
		$jsonResponse->message = "Comment Posted";
		return json_encode($jsonResponse);
	}
	else
	{
		$jsonResponse->code = 1;
		$jsonResponse->status = "Error";
		$jsonResponse->message = "Comment Not Posted";
		return json_encode($jsonResponse);
	}

}

function thumbsUpDown($postID,$votetype)
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
	
	session_start(); 
	$userID = $_SESSION['userID'];
	
	
	
		$query="SELECT * 
				FROM wp_thumbs
				WHERE itemid={$postID} 
				AND userid={$userID}";

		$result = mysqli_query($con,$query);

		if($result != null)
			$resultCount = mysqli_num_rows($result);
		if($resultCount > 0)
		{
				$row = mysqli_fetch_array($result);
				
				$type=$row["votetype"];
		
				if($votetype == $type)
				{
					//Delete
					
					$DELquery="DELETE FROM wp_thumbs
								WHERE userid={$userID}
								AND itemid={$postID}";
				
					$DELresult = mysqli_query($con,$DELquery);
					
					if($DELresult)
					{
						$jsonResponse->code = 0;
						$jsonResponse->status = "Success";
						$jsonResponse->message = "Updated";
						return json_encode($jsonResponse);
						
					}
					
				}
				else
				{
					//Update
					
					$updateQuery="UPDATE wp_thumbs
							SET votetype='{$votetype}'
							WHERE userid={$userID}
								AND itemid={$postID}";
							  
					$updateResult = mysqli_query($con,$updateQuery);
					
					if($updateResult)
					{
						$jsonResponse->code = 0;
						$jsonResponse->status = "Success";
						$jsonResponse->message = "Updated";
						return json_encode($jsonResponse);
						
					}
				}
		}
		else
		{
				// INSERT 1
				$insert1="INSERT INTO wp_thumbs (userid,itemid,votetype,comments)
						  VALUES ('{$userID}', '{$postID}','{$votetype}',0)";
						  
				$result1 = mysqli_query($con,$insert1); 
				
				if($result1)
				{
					$jsonResponse->code = 0;
					$jsonResponse->status = "Success";
					$jsonResponse->message = "Updated";
					return json_encode($jsonResponse);
					
				}
		}
		
		$jsonResponse->code = 1;
		$jsonResponse->status = "Error";
		$jsonResponse->message = "No Activity Found";
		return json_encode($jsonResponse);

	
}


function statusUpdate($content)
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
	
	$activityID='';
	$username='';
	$userDisplayName='';
	
	session_start(); 
	$userID = $_SESSION['userID'];
	
	$timeNow = date('Y-m-d H:i:s');
	$timestamp = strtotime($timeNow);
	
	
	$query="SELECT user_login , display_name
			FROM wp_users
			WHERE ID={$userID}";

	$result = mysqli_query($con,$query);

	if($result != null)
		$resultCount = mysqli_num_rows($result);
	if($resultCount > 0)
	{
		$row = mysqli_fetch_array($result);
		
		$username=$row["user_login"];
		$userDisplayName=$row["display_name"];
	
	}
	
	$begin="START TRANSACTION";

    $beginResult = mysqli_query($con,$begin);

        

    if($beginResult == null)

    {

        $jsonResponse->code = 1;

        $jsonResponse->status = "Error";

        $jsonResponse->message = "TRANSACTION Failed";

        return json_encode($jsonResponse);

    }

    else

    {
    

        $actionStr='<a href="http://meterbreak.com/members/'.$username.'/" title="'.$userDisplayName.'">'.$userDisplayName.'</a> posted an update';
		$linkStr='http://meterbreak.com/members/'.$username.'/';
	
		// INSERT 1
		$insert1="INSERT INTO wp_bp_activity (user_id,component,type,action,content,primary_link,item_id,secondary_item_id,date_recorded,hide_sitewide,mptt_left,mptt_right,is_spam)
					VALUES ({$userID},'activity','activity_update','{$actionStr}','{$content}','{$linkStr}',0,0,'{$timeNow}',0,0,0,0)";
				  
		$insertResult1 = mysqli_query($con,$insert1);
		
		//Getting activity id
			$activityQuery="SELECT *
					FROM wp_bp_activity
					WHERE user_id={$userID}
					AND date_recorded = '{$timeNow}'";
		
			$activityResult = mysqli_query($con,$activityQuery);

			if($activityResult != null)
				$activityResultCount = mysqli_num_rows($activityResult);
			if($activityResultCount > 0)
			{
			
				$activityRow = mysqli_fetch_array($activityResult);
				$activityID=$activityRow["id"];
				
			}
			
		$postArray["id"] = $activityID;
		$postArray["content"] = $content;
		$postArrayStr = serialize($postArray);

        

        $query="SELECT * FROM wp_usermeta

                WHERE user_id={$userID}

                AND meta_key='bp_latest_update'";

    

        $result = mysqli_query($con,$query);



        if($result != null)

            $resultCount = mysqli_num_rows($result);

        if($resultCount > 0)

        {

            $query2 = "UPDATE wp_usermeta

                        SET meta_value='{$postArrayStr}'

                        WHERE meta_key='bp_latest_update'

                        AND user_id={$userID} ";

                        

            $query3 = "UPDATE wp_usermeta

                        SET meta_value='{$timestamp}'

                        WHERE meta_key='wp__bbp_last_posted'

                        AND user_id={$userID} ";

                        

            $result2 = mysqli_query($con,$query2);

            $result3 = mysqli_query($con,$query3);

        }

        else

        {

            $query2 = " INSERT INTO wp_usermeta (user_id,meta_key,meta_value)

                        VALUES ({$userID},'bp_latest_update','{$postArrayStr}');";

                        

            $query3 = " INSERT INTO wp_usermeta (user_id,meta_key,meta_value)

                            VALUES ({$userID},'wp__bbp_last_posted','{$timestamp}');";

                            

            $result2 = mysqli_query($con,$query2);

            $result3 = mysqli_query($con,$query3);

            

        }

    

        if($insertResult1 && $result2 && $result3)

        {

                    // COMMIT

                    $COMITTQuery="COMMIT";

                    $COMITTresult = mysqli_query($con,$COMITTQuery);



                    $jsonResponse->code = 0;

                    $jsonResponse->status = "Success";

                    $jsonResponse->message = "Status update posted !";

                    return json_encode($jsonResponse); 

                    

                    

        }

        else

        {

            // ROLLBACK

    

            $ROLLBACKQuery="ROLLBACK";

            $ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);

            

            $jsonResponse->code = 1;

            $jsonResponse->status = "Error";

            $jsonResponse->message = "Status update Not posted !";

            return json_encode($jsonResponse);

            

        }

    }
	
}



function postOnFriendWall($friendID,$content)
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
	
		$activityID='';
		$username='';
		$userDisplayName='';
		$friendUsername='';
		$friendDisplayName='';
		
		$timeNow = date('Y-m-d H:i:s');
		$timestamp = strtotime($timeNow);

		session_start(); 
		$userID = $_SESSION['userID'];
		
		$begin="START TRANSACTION";
		$beginResult = mysqli_query($con,$begin);
		
		if($beginResult == null)
		{
			$jsonResponse->code = 1;
			$jsonResponse->status = "Error";
			$jsonResponse->message = "Status Update Not Posted!!";
			return json_encode($jsonResponse);	
		}
		else
		{
		
			$query="SELECT user_login , display_name
					FROM wp_users
					WHERE ID={$userID}";
			
			$result = mysqli_query($con,$query);
			

			if($result != null)
				$resultCount = mysqli_num_rows($result);
			if($resultCount > 0)
			{
				$row = mysqli_fetch_array($result);
				
				$username=$row["user_login"];
				$userDisplayName=$row["display_name"];
			
			}
		
			
			$query2="SELECT user_login , display_name
						FROM wp_users
						WHERE ID={$friendID}";
		
			$result2 = mysqli_query($con,$query2);

			if($result2 != null)
				$resultCount2 = mysqli_num_rows($result2);
			if($resultCount2 > 0)
			{
			
				$row2 = mysqli_fetch_array($result2);
				$friendUsername=$row2["user_login"];
				$friendDisplayName=$row2["display_name"];
			}

			
			$actionStr='<a href="http://meterbreak.com/members/'.$username.'/" title="'.$userDisplayName.'">'.$userDisplayName.'</a> posted an update';
			$linkStr='http://meterbreak.com/members/'.$username.'/';
			$contentStr='<a href="http://meterbreak.com/members/'.$friendUsername.'/" rel="nofollow">@'.$friendUsername.'</a> '.$content.'';
			//$contentStr=addslashes($contentStr);
			
			// INSERT 1
			$insert1="INSERT INTO wp_bp_activity (user_id,component,type,action,content,primary_link,item_id,secondary_item_id,date_recorded,hide_sitewide,mptt_left,mptt_right,is_spam)
						VALUES ({$userID},'activity','activity_update','{$actionStr}','{$contentStr}','{$linkStr}',0,0,'{$timeNow}',0,0,0,0)";
					  
			$insertResult1 = mysqli_query($con,$insert1);
			
					
			//Getting activity id
			$query3="SELECT *
					FROM wp_bp_activity
					WHERE user_id={$userID}
					AND content = '{$contentStr}'";
		
			$result3 = mysqli_query($con,$query3);

			if($result3 != null)
				$resultCount3 = mysqli_num_rows($result3);
			if($resultCount3 > 0)
			{
			
				$row3 = mysqli_fetch_array($result3);
				$activityID=$row3["id"];
				
			}
			

			
			//Setting Notification for friend
			
			$insert2="INSERT INTO wp_bp_notifications (user_id,item_id,secondary_item_id,component_name,component_action,date_notified,is_new)
							VALUES ({$friendID},{$activityID},{$userID},'activity','new_at_mention','{$timeNow}',1)";
						  
			$insertResult2 = mysqli_query($con,$insert2);
			
			
			//Saving data in friends Usermeta
			$postArray[0] =(int) $activityID;
			$postArrayStr = serialize($postArray);
			
			$query4="SELECT *
					FROM wp_usermeta
					WHERE user_id={$friendID}
					AND meta_key='bp_new_mention_count'";
    
			$result4 = mysqli_query($con,$query4);


        if($result4 != null)
            $resultCount4 = mysqli_num_rows($result4);
        if($resultCount4 > 0)
        {
			$query5 = "UPDATE wp_usermeta
                        SET meta_value='{$postArrayStr}'
                        WHERE meta_key='bp_new_mentions'
                        AND user_id={$friendID} ";
		
		
            $query6 = "UPDATE wp_usermeta
                        SET meta_value=meta_value+1
                        WHERE meta_key='bp_new_mention_count'
                        AND user_id={$friendID} ";
                     
                        
            $result5 = mysqli_query($con,$query5);
            $result6 = mysqli_query($con,$query6);
        }
        else
        {
            $query5 = " INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
                        VALUES ({$friendID},'bp_new_mentions','{$postArrayStr}');";
                       
            $query6 = " INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
                            VALUES ({$friendID},'bp_new_mention_count',1);";
                            
            $result5 = mysqli_query($con,$query5);
            $result6 = mysqli_query($con,$query6);
           
        }
		
		//Saving data in own Usermeta
		
		$postArray1["id"] = $activityID;
		$postArray1["content"] = $content;
		$postArrayStr1 = serialize($postArray1);

        

        $query7="SELECT * FROM wp_usermeta
                WHERE user_id={$userID}
                AND meta_key='bp_latest_update'";

        $result7 = mysqli_query($con,$query7);

        if($result7 != null)
            $resultCount7 = mysqli_num_rows($result7);
        if($resultCount7 > 0)
        {
            $query8 = "UPDATE wp_usermeta
                        SET meta_value='{$postArrayStr1}'
                        WHERE meta_key='bp_latest_update'
                        AND user_id={$userID} ";
                        
            $query9 = "UPDATE wp_usermeta
                        SET meta_value='{$timestamp}'
                        WHERE meta_key='wp__bbp_last_posted'
                        AND user_id={$userID} ";
                        
            $result8 = mysqli_query($con,$query8);
            $result9 = mysqli_query($con,$query9);
        }
        else
        {
            $query8 = " INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
                        VALUES ({$userID},'bp_latest_update','{$postArrayStr1}');";
                       
            $query9 = " INSERT INTO wp_usermeta (user_id,meta_key,meta_value)
                            VALUES ({$userID},'wp__bbp_last_posted','{$timestamp}');";
                            
            $result8 = mysqli_query($con,$query8);
            $result9 = mysqli_query($con,$query9);
           
        }
	
			
			if($result && $result2 && $result3 && $result4 && $result5 && $result6 && $result7 && $result8 && $result9 && $insertResult1 && $insertResult2 )
				{
					// COMMIT
					$COMITTQuery="COMMIT";
					$COMITTresult = mysqli_query($con,$COMITTQuery);
					
					$jsonResponse->code = 0;
					$jsonResponse->status = "Success";
					$jsonResponse->message = "Status Update Posted !";
					
					return json_encode($jsonResponse);
				}
				else
				{
					// ROLLBACK
			
					$ROLLBACKQuery="ROLLBACK";
					$ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);
					
					$jsonResponse->code = 1;
					$jsonResponse->status = "Error";
					$jsonResponse->message = "Status Update Not Posted!";
					return json_encode($jsonResponse);
				}
		
		}

}

function sendMsg($username,$content,$subject)
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
	
	$receiverID='';
	$msgID='';
	$thread_id='';
	session_start(); 
	$userID = $_SESSION['userID'];
	
	$timeNow = date('Y-m-d H:i:s');
	
	
	$query="SELECT *
			FROM wp_users
			WHERE user_login='{$username}';";

	$result = mysqli_query($con,$query);

	if($result != null)
		$resultCount = mysqli_num_rows($result);
	if($resultCount > 0)
	{
		$row = mysqli_fetch_array($result);			
		$receiverID=$row["ID"];
	}
	else
	{
		$jsonResponse->code = 2;
		$jsonResponse->status = "Error";
		$jsonResponse->message = "User Not Found !";
		return json_encode($jsonResponse);
	}
	
	
	$begin="START TRANSACTION";
    $beginResult = mysqli_query($con,$begin);
        

    if($beginResult == null)

    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "TRANSACTION Failed";

        return json_encode($jsonResponse);

    }
	else
	{
		// INSERT 1
		$insert1="INSERT INTO wp_bp_messages_messages (thread_id,sender_id,`subject`,message,date_sent)
					VALUES ((SELECT MAX(mm.thread_id) +1 FROM wp_bp_messages_messages mm),{$userID},'{$subject}','{$content}','{$timeNow}')";
				  
		$insertResult1 = mysqli_query($con,$insert1);
		
		
		$query2="SELECT *
				FROM wp_bp_messages_messages
				WHERE sender_id={$userID}
				AND date_sent='{$timeNow}'";

		$result2 = mysqli_query($con,$query2);

		if($result2 != null)
			$resultCount2 = mysqli_num_rows($result2);
		if($resultCount2 > 0)
		{
			$row2 = mysqli_fetch_array($result2);			
			$msgID=$row2["id"];
			$thread_id=$row2["thread_id"];
		}
		
		
		// INSERT 2
		$insert2="INSERT INTO wp_bp_notifications (user_id,item_id,secondary_item_id,component_name,component_action,date_notified,is_new)
							VALUES ({$receiverID},{$msgID},{$userID},'messages','new_message','{$timeNow}',1)";
						  
		$insertResult2 = mysqli_query($con,$insert2);
		
		// INSERT 3
		$insert3="INSERT INTO wp_bp_messages_recipients (user_id,thread_id,unread_count,sender_only,is_deleted)
					VALUES({$receiverID},{$thread_id},1,0,0)";
						  
		$insertResult3 = mysqli_query($con,$insert3);
		
		// INSERT 4
		$insert4="INSERT INTO wp_bp_messages_recipients (user_id,thread_id,unread_count,sender_only,is_deleted)
					VALUES({$userID},{$thread_id},0,1,0)";
						  
		$insertResult4 = mysqli_query($con,$insert4);
		
		
		if($insertResult1 && $result2 && $insertResult2 && $insertResult3 && $insertResult4)
		{
			// COMMIT
			$COMITTQuery="COMMIT";
			$COMITTresult = mysqli_query($con,$COMITTQuery);
		
		
			$jsonResponse->code = 0;
			$jsonResponse->status = "Success";
			$jsonResponse->message = "Message Sent";
			
			$msgDetails = new stdClass();
			$msgDetails->threadID=$thread_id;
			$msgDetails->senderID=$userID;
			$msgDetails->subject=$subject;
			
			array_push($jsonResponse->response,$msgDetails);
			
			return json_encode($jsonResponse);
		}
		else
		{
				// ROLLBACK
			
				$ROLLBACKQuery="ROLLBACK";
				$ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);
		
				$jsonResponse->code = 1;
				$jsonResponse->status = "Error";
				$jsonResponse->message = "Message Not Sent";

				return json_encode($jsonResponse);
		}
		
		
		
	}
	
	

}

function replyMsg($receiverID,$content,$threadID,$subject)
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
	
	$msgID='';
	session_start(); 
	$userID = $_SESSION['userID'];
	
	$timeNow = date('Y-m-d H:i:s');
	
	$begin="START TRANSACTION";
    $beginResult = mysqli_query($con,$begin);
        

    if($beginResult == null)

    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "TRANSACTION Failed";

        return json_encode($jsonResponse);

    }
	else
	{
		$subject='Re: '.$subject;
	
		// INSERT 1
		$insert1="INSERT INTO wp_bp_messages_messages (thread_id,sender_id,`subject`,message,date_sent)
					VALUES ({$threadID},{$userID},'{$subject}','{$content}','{$timeNow}')";
				  
		$insertResult1 = mysqli_query($con,$insert1);
		
		
		$query2="SELECT *
				FROM wp_bp_messages_messages
				WHERE sender_id={$userID}
				AND date_sent='{$timeNow}'";

		$result2 = mysqli_query($con,$query2);

		if($result2 != null)
			$resultCount2 = mysqli_num_rows($result2);
		if($resultCount2 > 0)
		{
			$row2 = mysqli_fetch_array($result2);			
			$msgID=$row2["id"];
		}
		
		
		// INSERT 2
		$insert2="INSERT INTO wp_bp_notifications (user_id,item_id,secondary_item_id,component_name,component_action,date_notified,is_new)
							VALUES ({$receiverID},{$msgID},{$userID},'messages','new_message','{$timeNow}',1)";
						  
		$insertResult2 = mysqli_query($con,$insert2);
		
		// Update 1
		$Update1="UPDATE wp_bp_messages_recipients
					SET sender_only=0 , unread_count=0
					WHERE user_id={$userID}
					AND thread_id={$threadID}";
						  
		$UpdateResult1 = mysqli_query($con,$Update1);
		
		// Update 2
		$Update2="UPDATE wp_bp_messages_recipients
					SET unread_count=unread_count+1
					WHERE user_id={$receiverID}
					AND thread_id={$threadID}";
						  
		$UpdateResult2 = mysqli_query($con,$Update2);
		
		
		
		if($insertResult1 && $result2 && $insertResult2 && $UpdateResult1 && $UpdateResult2)
		{
			// COMMIT
			$COMITTQuery="COMMIT";
			$COMITTresult = mysqli_query($con,$COMITTQuery);
		
		
			$jsonResponse->code = 0;
			$jsonResponse->status = "Success";
			$jsonResponse->message = "Reply Sent";
			
			$msgDetails = new stdClass();
			$msgDetails->threadID=$threadID;
			$msgDetails->senderID=$userID;
			$msgDetails->subject=$subject;
			
			array_push($jsonResponse->response,$msgDetails);
			
			return json_encode($jsonResponse);
		}
		else
		{
				// ROLLBACK
			
				$ROLLBACKQuery="ROLLBACK";
				$ROLLBACKresult = mysqli_query($con,$ROLLBACKQuery);
		
				$jsonResponse->code = 1;
				$jsonResponse->status = "Error";
				$jsonResponse->message = "Reply Not Sent";

				return json_encode($jsonResponse);
		}
		
		
	}

}

function getSentBox()
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
	
	session_start(); 
	$userID = $_SESSION['userID'];
	
	$query="SELECT mm.id, thread_id , sender_id , `subject` , message , date_sent , u.display_name
			FROM wp_bp_messages_messages mm , wp_users u
			WHERE thread_id IN (SELECT thread_id FROM `wp_bp_messages_recipients` WHERE user_id={$userID} AND is_deleted=0)
			AND sender_id={$userID}
			AND sender_id=u.ID
			GROUP BY thread_id
			ORDER BY date_sent DESC";
    
    $result = mysqli_query($con,$query);
    $resultCount = 0;
    if($result != null)
        $resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
    {
        $jsonResponse->code = 0;
        $jsonResponse->status = "Success";
        $jsonResponse->message = "{$resultCount} thread(s) found!";
        $jsonResponse->rowCount=$resultCount;

        while($row = mysqli_fetch_array($result))
        {
            $Thread = new stdClass();
            $Thread->threadID=$row["thread_id"];
            $Thread->senderID=$row["sender_id"];
            $Thread->subject=$row["subject"];
            $Thread->msg=$row["message"];
			$Thread->senderName=$row["display_name"];
			
			$dateSrc = $row["date_sent"];
			$retrieveDate = new DateTime($dateSrc);
			$currDate=new DateTime(date("Y-m-d H:i:s"));
			
			$interval = $retrieveDate->diff($currDate);
			
			$Thread->msgDate= timeFormat($interval);	
			
			$imgQuery="SELECT  um.meta_value as img
						FROM  wp_usermeta um
						WHERE um.user_id={$Thread->senderID}
						AND um.meta_key='profile_image'";
				
			$imgResult = mysqli_query($con,$imgQuery);
			
			if($imgResult != null)
				$imgResultCount2 = mysqli_num_rows($imgResult);
			if($imgResultCount2 > 0)
			{
				$imgrow = mysqli_fetch_array($imgResult);
				
				$ImgURL=$imgrow["img"];
				$ImgURL=unserialize($ImgURL);

				$Thread->img= $ImgURL['url'];
			}
			else
			{
				$Thread->img="images/profile.gif";
			}
			
			$rcvQuery="SELECT u.display_name
						FROM wp_bp_messages_recipients mr , wp_users u
						WHERE mr.thread_id={$Thread->threadID}
						AND mr.user_id <> {$Thread->senderID}
						AND mr.user_id=u.ID";
				
			$rcvResult = mysqli_query($con,$rcvQuery);
			
			if($rcvResult != null)
				$rcvCount2 = mysqli_num_rows($rcvResult);
			if($rcvCount2 > 0)
			{
				$rcvrow = mysqli_fetch_array($rcvResult);
				
				$Thread->receiverName=$rcvrow["display_name"];
			
			}
			
	
            
            array_push($jsonResponse->response,$Thread);
        }
        return json_encode($jsonResponse);
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No thread found!";
        return json_encode($jsonResponse);
    }
	

}

function getThreadDetails($threadID)
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
	
	
	$query="SELECT mm.id,mm.thread_id,mm.sender_id,mm.`subject`,mm.message , mm.date_sent , u.display_name
			FROM wp_bp_messages_messages mm , wp_users u
			WHERE mm.thread_id={$threadID}
			AND mm.sender_id=u.ID
			ORDER BY date_sent";
    
    $result = mysqli_query($con,$query);
    $resultCount = 0;
    if($result != null)
        $resultCount = mysqli_num_rows($result);
    if($resultCount > 0)
    {
        $jsonResponse->code = 0;
        $jsonResponse->status = "Success";
        $jsonResponse->message = "{$resultCount} Message(s) found!";
        $jsonResponse->rowCount=$resultCount;

        while($row = mysqli_fetch_array($result))
        {
            $Message = new stdClass();
            $Message->threadID=$row["thread_id"];
            $Message->senderID=$row["sender_id"];
            $Message->subject=$row["subject"];
            $Message->msg=$row["message"];
			$Message->senderName=$row["display_name"];
			
			$dateSrc = $row["date_sent"];
			$retrieveDate = new DateTime($dateSrc);
			$currDate=new DateTime(date("Y-m-d H:i:s"));
			
			$interval = $retrieveDate->diff($currDate);
			
			$Message->msgDate= timeFormat($interval);	
			
			$imgQuery="SELECT  um.meta_value as img
						FROM  wp_usermeta um
						WHERE um.user_id={$Message->senderID}
						AND um.meta_key='profile_image'";
				
			$imgResult = mysqli_query($con,$imgQuery);
			
			if($imgResult != null)
				$imgResultCount2 = mysqli_num_rows($imgResult);
			if($imgResultCount2 > 0)
			{
				$imgrow = mysqli_fetch_array($imgResult);
				
				$ImgURL=$imgrow["img"];
				$ImgURL=unserialize($ImgURL);

				$Message->img= $ImgURL['url'];
			}
			else
			{
				$Message->img="images/profile.gif";
			}
			
            
            array_push($jsonResponse->response,$Message);
        }
        return json_encode($jsonResponse);
    }
    else
    {
        $jsonResponse->code = 1;
        $jsonResponse->status = "Error";
        $jsonResponse->message = "No Message found!";
        return json_encode($jsonResponse);
    }

}


?>