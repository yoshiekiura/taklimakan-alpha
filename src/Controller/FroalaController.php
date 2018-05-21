<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class FroalaController extends Controller
{
    /**
    * @Route("/froala/image", name="froala_image")
    */

	public function image(Request $request) {

        // Allowed extentions.
        $allowedExts = array("gif", "jpeg", "jpg", "png", "webp", "svg");

        // Get filename.
        $temp = explode(".", $_FILES["file"]["name"]);

        // Get extension.
        $extension = end($temp);

        // An image check is being done in the editor but it is best to
        // check that again on the server side.
        // Do not use $_FILES["file"]["type"] as it can be easily forged.
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $_FILES["file"]["tmp_name"]);

        if (
            (($mime == "image/gif") ||
            ($mime == "image/jpeg") ||
            ($mime == "image/pjpeg") ||
            ($mime == "image/x-png") ||
            ($mime == "image/png") ||
            ($mime == "image/webp") ||
            ($mime == "image/svg"))
        && in_array(strtolower($extension), $allowedExts)) {

            // Generate new random name.
            $name = sha1(microtime()) . "." . $extension;

            // Save file in the uploads folder.
            move_uploaded_file($_FILES["file"]["tmp_name"], getcwd() . "/images/all/" . $name);

            // Generate response.
            //$response = new \StdClass;
            // $response->link = "http://localhost/uploads/images/" . $name;
            //$response->link = "//images/all/" . $name;
            $link = "/images/all/" . $name;
            //echo stripslashes(json_encode($response));

            return new JsonResponse(["link" => $link]);
        }

    }

}