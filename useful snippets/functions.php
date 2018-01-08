function getRandomString($length) {
	$salt = array_merge(range('a', 'z'), range(0, 9));
	$maxIndex = count($salt) - 1;

	$result = '';
	for ($i = 0; $i < $length; $i++) {
		$index = mt_rand(0, $maxIndex);
		$result .= $salt[$index];
	}
	return $result;
}

function rrmdir($dir) {
	if (is_dir($dir)) {
		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (filetype($dir."/".$object) == "dir") rmdir($dir."/".$object); else unlink($dir."/".$object);
			}
		}
		reset($objects);
		rmdir($dir);
	}
}

function generate_token($length = 32){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function slash_path($path, $beginWithSlash = true){
	// Clean up the given path (check slashes etc...)
	if(substr($path, -1, 1) == '/'){
		$path = substr($path, 0, -1);
	}
	if($beginWithSlash){
		if(substr($path, 0, 1) != '/'){
			$path = '/' . $path;
		}
	}
	return $path;
}

function create_dir_if_needed($path){
	// Create upload dir if it doesn't exist
	if(!is_dir($path)){
		mkdir($path, NEW_DIR_PERMS, true);
	}
}

function get_unique_filename_for_dir($path){
	$usedNames = glob($path . '*');
	foreach($usedNames as $key => $usedName){
		$usedNames[$key] = pathinfo($usedName, PATHINFO_FILENAME);
	}
	$newFilename = substr(md5(uniqid()), 0, 16);
	while(in_array($newFilename, $usedNames)){
		$newFilename = substr(md5(uniqid()), 0, 16);
	}
	return $newFilename;
}

/**
 * Wrapper for wideimage
 * if othersizes is set, it will return all image file names created.
 * @param $file
 * @param $path
 * @param null $width
 * @param null $height
 * @param string $resize_method
 * @param array $otherSizes
 * @param bool $relativeToUploadPath
 * @return array|string (array if otherSizes is set)
 */
function upload_image($file, $path, $width = null, $height = null, $resize_method = 'AspectFill', $otherSizes = array(), $relativeToUploadPath = false){
	$path = slash_path($path);

	// Append UPLOADS_PATH if needed
	if($relativeToUploadPath){
		$path = UPLOADS_PATH . $path;
	}

	file_put_contents("log.txt", "UPLOADING TO {$path}\n", FILE_APPEND);

	create_dir_if_needed($path);
	$filename  = get_unique_filename_for_dir($path) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
	$tmp_image = $file['tmp_name'];
	file_put_contents("log.txt", "PRE WIDEIMAGE LOAD\n", FILE_APPEND);
	$img       = \WideImage\WideImage::load($tmp_image);
	file_put_contents("log.txt", "WIDEIMAGE LOADED IMG\n", FILE_APPEND);
	if($width || $height){
		if($resize_method == 'AspectFill'){
			$img = $img->resize($width, $height, 'outside')->crop('center', 'middle', $width, $height);
		}else if($resize_method == 'AspectFit'){
			$img = $img->resize($width, $height, 'inside');
		}
	}
	file_put_contents("log.txt", "GOT HERE\n", FILE_APPEND);
	$img->saveToFile($path . '/' . $filename);
	file_put_contents("log.txt", "IMAGE SAVED\n", FILE_APPEND);

	if(!empty($otherSizes)){
	    $allNames = array($filename);
		foreach($otherSizes as $otherSize){
			$resize_method = (trim($otherSize['resize_method']) != '') ? $otherSize['resize_method'] : 'AspectFill';
			$width         = ((int)$otherSize['width'] > 0)  ? $otherSize['width']  : null;
			$height        = ((int)$otherSize['height'] > 0) ? $otherSize['height'] : null;
			$img           = \WideImage\WideImage::load($tmp_image);
			if($width || $height){
				if($resize_method == 'AspectFill'){
					$img = $img->resize($width, $height, 'outside')->crop('center', 'middle', $width, $height);
				}else if($resize_method == 'AspectFit'){
					$img = $img->resize($width, $height, 'inside');
				}
			}
			$resizedFilename = pathinfo($filename, PATHINFO_FILENAME) . '-' . $width . 'x' . $height . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
			$img->saveToFile($path . '/' . $resizedFilename);
			$allNames[] = $resizedFilename;
		}
		return $allNames;
	}
	file_put_contents("log.txt", "FILENAME: {$filename}\n", FILE_APPEND);
	return $filename;
}

function upload_file($file, $path, $relativeToUploadPath = true){
	$path = slash_path($path);
	// Append UPLOADS_PATH if needed
	if($relativeToUploadPath){
		$path = UPLOADS_PATH . $path;
	}
	create_dir_if_needed($path);
	$filename  = get_unique_filename_for_dir($path) . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
	move_uploaded_file($file['tmp_name'], $path . '/' . $filename);
	return $filename;
}

/**
 * Just a wrapper for unlink to catch errors, if you set errors it will return them, else na
 * @param $file
 * @param bool $errors
 * @return Exception
 */
function unlink_file($file, $errors = false) {
    try {
        unlink($file);
        return true;
    } catch (Exception $e) {
        if($errors)
            return $e;
        return false;
    }
}

/**
 *
 */
function checkExtension($filename , $allowed = array('gif','jpg', 'jpeg','png'))
{
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if (!in_array($ext, $allowed)) {
        return false;
    }
    return true;
}

function download_csv($filename, $rows){
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header('Content-Description: File Transfer');
	header("Content-type: text/csv");
	header("Content-Disposition: attachment; filename={$filename}");
	header("Expires: 0");
	header("Pragma: public");
	$fh = @fopen( 'php://output', 'w' );
	foreach($rows as $row){
		fputcsv($fh, $row);
	}
	fclose($fh);
	exit;
}


/**
 * Organises an array of files to format more freindly
 * turns each file into its own array.
 * @param unknown $file_post
 * @return unknown
 */
function reArrayFiles(&$file_post) {
	foreach( $file_post as $key => $all ){
		foreach( $all as $i => $val ){
			$return[$i][$key] = $val;
		}
	}
	return $return;
}
