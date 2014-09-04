<?php

    $path = $_GET['path']; // from .htaccess

    $format = $_GET['format']; // from .htaccess

    file_put_contents('log.txt', $path . " em " . $format);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $path); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // good edit, thanks!
    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1); // also, this seems wise considering output is image.
    $data = curl_exec($ch);
    curl_close($ch);

    if ($data) {

        $image = imagecreatefromstring($data);

    } else { // se não encontrar a imagem cria uma

        $image = @imagecreatetruecolor(1,1)
              or die("Cannot Initialize new GD image stream");
        $text_color = imagecolorallocate($image, 220, 220, 220);
        imagefill($image, 0, 0, $text_color);

    }

    header('Content-Type: image/jpeg');

    header('Cache-control: Public');

    header ("Expires: " . gmdate ("D, d M Y H:i:s", time() + 2592000) . " GMT");

    $resized_img = formatImage($format, $image);

    imagejpeg($resized_img, Null, 100);

    imagedestroy($resized_img);

    function resizeImage($originalImage,$toWidth,$toHeight) {

        $width = imagesx($originalImage);

        $height = imagesy($originalImage);

        $imageResized = imagecreatetruecolor($toWidth, $toHeight);

        imagecopyresampled($imageResized, $originalImage, 0, 0, 0, 0, $toWidth, $toHeight, $width, $height);

        return $imageResized;

    }

    function maxProtect($number) {

        return $number;

    }

    function formatImage($format, $image) {

        $raw_img_x = imagesx($image);

        $raw_img_y = imagesy($image);

        $formatParts = explode('x', $format);

        if (isset($formatParts[1])) {

            $new_img_x = is_int($formatParts[0]) ? $formatParts[0] : 100;

            $new_img_y = is_int($formatParts[1]) ? $formatParts[1] : 100;

        } else {

            $new_size = is_int($formatParts[0]) ? $formatParts[0] : 100;

            $ratio = $raw_img_y / $raw_img_x;

            #### Maximum Either Dimension - maxD_100
            if ($raw_img_x > $raw_img_y) {

                $new_img_x = (int) maxProtect($new_size);

                $new_img_y = (int) (maxProtect($new_size) * $ratio);

            } else {

                $new_img_y = (int) maxProtect($new_size);

                $new_img_x = (int) (maxProtect($new_size) * $ratio);

            }

        }

        $image = resizeImage($image, $new_img_x, $new_img_y);

        return $image;

    }

?>