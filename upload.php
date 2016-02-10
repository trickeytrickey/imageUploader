<?php 
	$uploadsDir = 'uploads/'; 													// folder location for the upload
	$result = array(); 															//create an array used below
	$filename = "";
	
	if($_GET['binary'] == 'false') 												// if binary option is false
	{
		$headers = emu_getallheaders(); 										// get the posted http headers
		$content = base64_decode(file_get_contents('php://input')); 			//read file content in as string
		
		if(!empty($content) && !empty($headers['Up-Filename']))		 			// make sure content and headers are not empty before processing
		{
			$filename = stripslashes($headers['Up-Filename']); 					//get the filename
			$extension = getExtension($filename); 								// get the file extension off of the filename
			if(($extension != "jpg") && ($extension != "jpeg") && ($extension != "png") && ($extension != "gif")) // check for valid file type extension, if not vaid do this
			{
				$result = array('error' 	=> 'Invalid extension.', 			//error array set if it is not successful
								'source' 	=> '');
				echo json_encode($result); 										//error checking, echo the result array as name: value pairs
				exit; 															//exit the php script
			}
			else 																// file type is valid do this
			{
				if(!file_put_contents($uploadsDir . $filename, $content)) 		// upload the file
				{
					$result = array('error'			=> 'Image could not be copied.', // error array set if it is not successful
									'source' 		=>'');
					echo json_encode($result); 									//error chekcing, echo the result array as name: value pairs
					exit; 														//exit the php script
				}
			}
		}
	}
	else 																		//else binary option is set to true
	{
		$image = $_FILES['image']['name']; 										// get the image using php _FILES
		if ($image) 															// check to make sure there is an image before processing
		{
			$filename = stripslashes($_FILES['image']['name']);				 	//get the filename
			$extension = getExtension($filename); 								//get the file extension off of the filename
			if(($extension != "jpg") && ($extension != "jpeg") && ($extension != "png") && ($extension != "gif")) //check for valide file type extension
			{
				$result = array('error' 	=> 'Invalid extension.', 			// error array if it is not successful
								'source' 	=> '');
				echo json_encode($result);										//error checking, echo the result array as name: value pairs
				exit; 															//exit the php script
			}
			else 																//filetype is valid do this
			{
			 $filename = stripslashes($_FILES['image']['name']); 				//get the filename
			 if(!copy($_FILES['image']['tmp_name'], $uploadsDir . $filename)) 	// upload the file
			 {
				 $result = array('error'		=>'Image could not be copied.',  // error array if it is not successful
				 				'source' 		=> '');
				echo json_encode($result); 										//error checking, echo the result array as name: value pairs
				exit; 															//exit the php script
			 }
			}
		}
		else 																	//else there is no image, do this
		{
			$result = array('error'		=>'Image could not be copied.',		 // error array if it is not successful
				 				'source' 		=> '');
			echo json_encode($result); 										//error checking, echo the result array as name: value pairs
			exit; 															//exit the php script
		}
	}
	$result = array('error' 	=> '', 										// it worked correctly, set result aray for AJAX
					'source'	=> $uploadsDir . $filename);
	echo json_encode($result); 												// echo result for AJAX to read on the HTML page
	
	//additional functions
	
	function getExtension($str) 											//funciton to get the extension
	{
		$i = strrpos($str, "."); 											//look for the position of the period, in reverse (Start from the right)
		if(!$i) 															// if the period doesnt exist in the string
		{
			return ""; 														// return nothing
		}
		$l = strlen($str) - $i; 											// get the length of the filename without the extension used as a starting point below
		$ext = strtolower(substr($str, $i + 1, $l));						 //get the file extension, start with the whole string, get . + extension, start at end of filename
		return $ext;														//return the file extension
	}
	function emu_getallheaders() 											// function to get http headers
	{
		foreach($_SERVER as $name => $value) 								// foreach loop to loop through all server variables
		{
			if(substr($name, 0, 5) == 'HTTP_') 								// check to see if the server variable name starts with HTTP_
			$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; //create name, value pairs as a array called headers
		}
		return $headers; 													//return the array of headers, which should contain the filename 
	}
	
	
//////////////////////////////////////////////////////////
/// RESIZE IMAGE
/////////////////////////////////////////////////////////

$dir = 		"./uploads/";
$middir= 	"./mid/";
$thdir= 	"./thumb/";
$img= $filename; //lowercase filename

//might need more logic here to accurately resize according to aspect ratio- portrait v landscate or even an odd size

//create mid image
//resizejpeg($dir, $middir, $img, 320, 240, 320, 240, "mid_");
resizejpeg($dir, $middir, $img, 480, 360, "mid_");

//create thumb image
resizejpeg($dir, $thdir, $img, 160, 120, "th_");


/////////////////////////////////////////////////////////
/// function resizejpeg
//
// = creates a resize image based on the max width
//	specified as well as generages a thumbnail image from
//	a rectangel cut from the middle of the image.
//
//	@dir = directory image is stored in
//	@newdir = directory new image will be stored in
//	@img = the image name
//	@max_W = the max width of the resized image
//	@max_h= the max height of the resized image
//  @th_w= the width of the thumbnail
//  @th_h = the height of the thumbnail
//  @prefix = the prefix of the resized image
//
////////////////////////////////////////////////////////


//function resizejpeg($dir, $newdir, $img, $max_w, $max_h, $th_w, $th_h, $prefix
function resizejpeg($dir, $newdir, $img, $max_w, $max_h, $prefix)
{
	//set destination directory
	if(!$newdir) $newdir = $dir;
	
	//get original images width and height
	list($or_w, $or_h, $or_t) = getImagesize($dir.$img);
	
	//make sure the image is a jpg
	if($or_t == 2) {
		
		//obtain the img ratio
		$ratio = ($or_h / $or_w);
		
		//original image
		$or_image = imagecreatefromjpeg($dir.$img);
		
		//resize image
		if ($or_w > $max_w || $or_h > $max_h) {
			//resize by height then width (H dominant)
			if($max_h < $max_w) {
				$rs_h = $max_h;
				$rs_w = $rs_h/ $ratio;
			}
			//resize by width then height w dominant
			else {
				$rs_w = $max_w;
				$rs_h = $ratio * $rs_W;
			}
			
			//copy old image to new image
			
			$rs_image = imagecreatetruecolor($rs_w, $rs_h);
			imagecopyresampled($rs_image, $or_image, 0, 0, 0, 0, $rs_w, $rs_h, $or_w, $or_h);
			
		}
		//image requires no resizing
		else {
			$rs_w = $or_w;
			$rs_h = $or_h;
			
			$rs_image = $or_image;
		}
		
		//generates resized image
		imagejpeg($rs_image, $newdir.$prefix.$img, 100);
		
		//$th_image = imagecreatetruecolor($th_w, $th_h);
		//cut out a rectange from the resizedimage and store in thumbnail
		//$new_w = (($rs_w / 2) - ($th_w / 2));
		//$new_h = (($rs_h / 2) - ($th_h / 2));
		
		//imagecopyresized($th_image, $rs_image, 0, 0, $new_w, $new_h, $rs_w, $rs_h, $rs_w, $rs_h
		
		//generate thumbnail
		return true;
	}
	
	//imgage type was not jpeg! 
	else {
		return false;
	}

}
?>

				
							
