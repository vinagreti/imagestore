<?php

    function resize_img($image, $format, $image_type, $newfilename = null) {
        $raw_img_x = imagesx($image);
        $raw_img_y = imagesy($image);
        $formatParts = explode('x', $format);
        if (isset($formatParts[1])) {
            $new_img_x = is_numeric($formatParts[0]) ? $formatParts[0] : 50;
            $new_img_y = is_numeric($formatParts[1]) ? $formatParts[1] : 50;
        } else {
            $new_size = is_numeric($formatParts[0]) ? $formatParts[0] : 50;
            $ratio = $raw_img_y / $raw_img_x;
            #### Maximum Either Dimension - maxD_100
            if ($raw_img_x > $raw_img_y) {
                $new_img_x = (int) $new_size;
                $new_img_y = (int) ($new_size * $ratio);
            } else {
                $new_img_y = (int) $new_size;
                $new_img_x = (int) ($new_size * $ratio);
            }
        }
        $width = imagesx($image);
        $height = imagesy($image);
        $newImg = imagecreatetruecolor($new_img_x, $new_img_y);
		/* Check if this image is PNG or GIF, then set if Transparent*/  
		if(in_array( strtolower($image_type) , array('image/png', 'image/gif'))){
			imagealphablending($newImg, false);
			imagesavealpha($newImg,true);
			$transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
			imagefilledrectangle($newImg, 0, 0, $new_img_x, $new_img_y, $transparent);
		}
		imagecopyresampled($newImg, $image, 0, 0, 0, 0, $new_img_x, $new_img_y, $width, $height);
		//Generate the file, and rename it to $newfilename
		switch (strtolower($image_type)) {
			case 'image/gif': imagegif($newImg,$newfilename); break;
			case 'image/jpeg': imagejpeg($newImg,$newfilename);  break;
			case 'image/png': imagepng($newImg,$newfilename); break;
			default:  trigger_error('Failed resize image!', E_USER_WARNING);  break;
		}
		return $newfilename;
    }
	
	// parametros da funcao
    $path = $_GET['path']; // from .htaccess
    $format = $_GET['format']; // from .htaccess

	// pega a imagem da url fornecida
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $path); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // good edit, thanks!
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); // also, this seems wise considering output is image.
    $data = curl_exec($ch);
	$image_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    curl_close($ch);

	// cria uma nova imagem com os dados retornados na chamada CURL
    if ($data) {
        $image = imagecreatefromstring($data);
    } else { // se não encontrar a imagem cria uma
        $image = @imagecreatetruecolor(1,1)
              or die("Cannot Initialize new GD image stream");
        $text_color = imagecolorallocate($image, 220, 220, 220);
        imagefill($image, 0, 0, $text_color);
    }

	// seta o header
    header('Content-Type: '.$image_type);
    header('Cache-control: Public');
    header ("Expires: " . gmdate ("D, d M Y H:i:s", time() + 2592000) . " GMT");

	// redimensiona a imagem
    $resized_img = resize_img($image, $format, $image_type);

?>
