<?php
	include 'DBLayer.php';
	$error = "";
	$msg = "";
	$imageURL = "";
	$fileElementName = 'profilePicture';

	if(!empty($_FILES[$fileElementName]['error']))
	{
		switch($_FILES[$fileElementName]['error'])
		{

			case '1':
				$error = 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
				break;
			case '2':
				$error = 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
				break;
			case '3':
				$error = 'The uploaded file was only partially uploaded';
				break;
			case '4':
				$error = 'No file was uploaded.';
				break;

			case '6':
				$error = 'Missing a temporary folder';
				break;
			case '7':
				$error = 'Failed to write file to disk';
				break;
			case '8':
				$error = 'File upload stopped by extension';
				break;
			case '999':
			default:
				$error = 'No error code avaiable';
		}	}elseif(empty($_FILES['profilePicture']['tmp_name']) || $_FILES['profilePicture']['tmp_name'] == 'none')
	{
		$error = 'No file was uploaded..';
	}else 
	{
			 $msg .= " File Name: " . $_FILES['profilePicture']['name'] . ", ";
			 $msg .= " File Size: " . @filesize($_FILES['profilePicture']['tmp_name']);
			// for security reason, we force to remove all uploaded file
			// @unlink($_FILES['profilePicture']);		

			$ext = substr($_FILES['profilePicture']['name'], -3);
			$uploadPath = "/home/meter/public_html/wp-content/uploads/". date("Y") . "/" . date("m") . "/" . $_FILES['profilePicture']['name'];
			$imageURL = "http://meterbreak.com/wp-content/uploads/" . date("Y") . "/" . date("m") . "/" . $_FILES['profilePicture']['name'];
			$imageType = "image/" . $ext ;
			
			move_uploaded_file($_FILES['profilePicture']['tmp_name'], $uploadPath );

			echo updateProfilePicture($uploadPath , $imageURL , $imageType);
	}		
	echo "{";
	echo				"error: '" . $error . "',\n";
	echo				"msg: '" . $msg . "',\n";
	echo				"imageURL: '" . $imageURL. "'\n";

	echo "}";
?>