<?php
if(isset($_POST) && isset($_POST["w"]) && isset($_POST["h"]) && isset($_POST["id"]) && isset($_POST["ImageName"]))
{
	############ Edit settings ##############
	$imageName				= $_POST["ImageName"];
	$ThumbSquareSize 		= 200; //Thumbnail will be 200x200
	$container_width 		= $_POST["w"];
	$container_height 		= $_POST["h"];
	$ThumbPrefix			= "thumb_"; //Normal thumb Prefix
	$DestinationDirectory	= getcwd().DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR; //specify upload directory ends with / (slash)
	$Quality 				= 90; //jpeg quality
	##########################################
	
	//check if this is an ajax request
	if (!isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
		die();
	}
	
	// check $_FILES['ImageFile'] not empty
	if(!isset($_FILES[$imageName]) || !is_uploaded_file($_FILES[$imageName]['tmp_name']))
	{
			die('Something wrong with uploaded file, something missing!'); // output error when above checks fail.
	}
	
	// Random number will be added after image name
	$RandomNumber 	= rand(0, 9999999999); 

	$ImageName 		= str_replace(' ','-',strtolower($_FILES[$imageName]['name'])); //get image name
	$ImageSize 		= $_FILES[$imageName]['size']; // get original image size
	$TempSrc	 	= $_FILES[$imageName]['tmp_name']; // Temp name of image file stored in PHP tmp folder
	$ImageType	 	= $_FILES[$imageName]['type']; //get file type, returns "image/png", image/jpeg, text/plain etc.

	//Let's check allowed $ImageType, we use PHP SWITCH statement here
	switch(strtolower($ImageType))
	{
		case 'image/png':
			//Create a new image from file 
			$CreatedImage =  imagecreatefrompng($_FILES[$imageName]['tmp_name']);
			break;
		case 'image/gif':
			$CreatedImage =  imagecreatefromgif($_FILES[$imageName]['tmp_name']);
			break;			
		case 'image/jpeg':
		case 'image/pjpeg':
			$CreatedImage = imagecreatefromjpeg($_FILES[$imageName]['tmp_name']);
			break;
		default:
			die('Unsupported File!'); //output error and exit
	}
	
	//PHP getimagesize() function returns height/width from image file stored in PHP tmp folder.
	//Get first two values from image, width and height. 
	//list assign svalues to $CurWidth,$CurHeight
	list($CurWidth,$CurHeight)=getimagesize($TempSrc);
	$image_ratio = $CurWidth / $CurHeight;
	
	$src_width = $CurWidth;
	$src_height = $CurHeight;
	// Resize image proportionally according to the size of container
	if($CurWidth > $container_width)
	{
		$CurWidth = $container_width;
		$CurHeight = $CurWidth / $image_ratio;
	}
	if($CurHeight > $container_height)
	{
		$CurHeight = $container_height;
		$CurWidth = $CurHeight / $image_ratio;
	}
	
	if($CurWidth < $container_width)
	{
		$CurWidth = $container_width;
		$CurHeight = $CurWidth / $image_ratio;
	}
	if($CurHeight < $container_height){
		$CurHeight = $container_height;
		$CurWidth = $CurHeight * $image_ratio;
	}
	
	//Get file extension from Image name, this will be added after random name
	$ImageExt = substr($ImageName, strrpos($ImageName, '.'));
  	$ImageExt = str_replace('.','',$ImageExt);
	
	//remove extension from filename
	$ImageName 		= preg_replace("/\\.[^.\\s]{3,4}$/", "", $ImageName); 
	
	//Construct a new name with random number and extension.
	$NewImageName = $ImageName.'-'.$RandomNumber.'.'.$ImageExt;
	
	//set the Destination Image
	$thumb_DestRandImageName 	= $DestinationDirectory.$ThumbPrefix.$NewImageName; //Thumbnail name with destination directory
	$DestRandImageName 			= $DestinationDirectory.$NewImageName; // Image with destination directory
	
	//Resize image to Specified Size by calling resizeImage function.
	if(resizeImage($CurWidth,$CurHeight,$DestRandImageName,$CreatedImage,$Quality,$ImageType, $src_width, $src_height))
	{
		//Create a square Thumbnail right after, this time we are using cropImage() function
		if(!cropImage($CurWidth,$CurHeight,$ThumbSquareSize,$thumb_DestRandImageName,$CreatedImage,$Quality,$ImageType))
		{
			echo 'Error Creating thumbnail';
		}
		/*
		We have succesfully resized and created thumbnail image
		We can now output image to user's browser or store information in the database
		*/
		$json = array("imgSrc"=> ("uploads/".$NewImageName),"thumbSrc"=> ("uploads/".$ThumbPrefix.$NewImageName));

		echo json_encode($json);
		
		/****************************************************/
		/****************************************************/
		/*
		// Insert info into database table!
		mysql_query("INSERT INTO myImageTable (ImageName, ThumbName, ImgPath)
		VALUES ($DestRandImageName, $thumb_DestRandImageName, 'uploads/')");
		/****************************************************/
		/****************************************************/

	}else{
		die('Resize Error'); //output error
	}
}


// This function will proportionally resize image 
function resizeImage($CurWidth,$CurHeight,$DestFolder,$SrcImage,$Quality,$ImageType, $src_width, $src_height)
{
	//Check Image size is not 0
	if($CurWidth <= 0 || $CurHeight <= 0) 
	{
		return false;
	}
	
	$NewWidth  	= ceil($CurWidth);
	$NewHeight 	= ceil($CurHeight);
	
	//Construct a proportional size of new image
	$NewCanves 	= imagecreatetruecolor($NewWidth, $NewHeight);
	
	// Resize Image
	if(imagecopyresized($NewCanves, $SrcImage,0, 0, 0, 0, $NewWidth, $NewHeight, $src_width, $src_height))
	{
		switch(strtolower($ImageType))
		{
			case 'image/png':
				imagepng($NewCanves,$DestFolder);
				break;
			case 'image/gif':
				imagegif($NewCanves,$DestFolder);
				break;			
			case 'image/jpeg':
			case 'image/pjpeg':
				imagejpeg($NewCanves,$DestFolder,$Quality);
				break;
			default:
				return false;
		}
	//Destroy image, frees memory	
	if(is_resource($NewCanves)) {imagedestroy($NewCanves);} 
	return true;
	}

}

//This function corps image to create exact square images, no matter what its original size!
function cropImage($CurWidth,$CurHeight,$iSize,$DestFolder,$SrcImage,$Quality,$ImageType)
{	 
	//Check Image size is not 0
	if($CurWidth <= 0 || $CurHeight <= 0) 
	{
		return false;
	}
	
	//abeautifulsite.net has excellent article about "Cropping an Image to Make Square bit.ly/1gTwXW9
	if($CurWidth>$CurHeight)
	{
		$y_offset = 0;
		$x_offset = ($CurWidth - $CurHeight) / 2;
		$square_size 	= $CurWidth - ($x_offset * 2);
	}else{
		$x_offset = 0;
		$y_offset = ($CurHeight - $CurWidth) / 2;
		$square_size = $CurHeight - ($y_offset * 2);
	}
	
	$NewCanves 	= imagecreatetruecolor($iSize, $iSize);	
	if(imagecopyresampled($NewCanves, $SrcImage,0, 0, $x_offset, $y_offset, $iSize, $iSize, $square_size, $square_size))
	{
		switch(strtolower($ImageType))
		{
			case 'image/png':
				imagepng($NewCanves,$DestFolder);
				break;
			case 'image/gif':
				imagegif($NewCanves,$DestFolder);
				break;			
			case 'image/jpeg':
			case 'image/pjpeg':
				imagejpeg($NewCanves,$DestFolder,$Quality);
				break;
			default:
				return false;
		}
	//Destroy image, frees memory	
	if(is_resource($NewCanves)) {imagedestroy($NewCanves);} 
	return true;

	}
}

if(isset($_POST["offsetX"]) || isset($_POST["offsetY"]))
{
	// When somebody saves a picture you can read offsetY and offsetX and save them, so it become stored
	// offsetX / offsetY
	echo "Success";
}