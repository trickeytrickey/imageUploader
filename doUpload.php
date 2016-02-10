<?php
//make sre page doesnt time out-- in case of large file
set_time_limit(300);

//make sure the submit button was pressed
if($_POST["uploadFile"] != "")
{
	//get the file extension
	$fileExt = strrchr($_FILES['userfile']['name'], ".");
	
	//if file extension is in this list, do not allow upload
	if( ($fileExt != ".jpg") && ($fileExt != ".gif") && ($fileExt != ".png"))
	{
		//set the session message in case of a bad file
		$_SESSION["badFileType"] = "You cannot upload a file of that type ". $fileExt;
	}
	else
	{
		//get the filename
		$fileName = $_FILES['userfile']['name'];
		
		//make sure the file is uploaded and ready for upload
		if(!is_uploaded_file($_FILES['userfile']['tmp_name']))
		{
			echo "Problem: possible file upload attack";
			exit;
		}
		
		//name the file. This one includes the directory as well
		$upfile = "uploads/".$fileName;
		
		//copy the file into its location on the server
		if (!copy($_FILES['userfile']['tmp_name'], $upfile))
		{
			echo "Problem: could not move file into directory";
			exit;
		}
		echo $fileName;
	}//end filetype check
}

/////////////////////////////////////////////
// resize IMAGE
////////////////////////////////////////////

$dir = 		"./uploads/";
$middir= 	"./mid/";
$thdir= 	"./thumb/";
$img= $fileName; //uppercase filename

//might need more logic here to accurately resize according to aspect ratio- portrait v landscate or even an odd size

//create mid image
//resizejpeg($dir, $middir, $img, 320, 240, 320, 240, "mid_");
resizejpeg($dir, $middir, $img, 480, 360, "mid_");

//create thumb image
//resizejpeg($dir, $thdir, $img, 640, 480, 160, 120, "th_");
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
	list($or_w, $or_h, $or_t) = getimagesize($dir.$img);
	
	//make sure image is a jpeg
	
	if($or_t == 2) {
		//obtain the image ratio 
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
