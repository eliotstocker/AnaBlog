<?php
require_once "../classes/ClassLoader.php";

$entries = new \entries\operations();

$headers = apache_request_headers();

try {
    switch($_SERVER["REQUEST_METHOD"]) {
        case "GET":
            GETRequest($_GET);
            break;
        case "POST":

            break;
        case "PUT":

            break;
    }
} catch(\exceptions\unauthorizedException $e) {
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(array("error", $e->getMessage()));
}

function GETRequest($get) {
    global $entries;
    if(isset($get["post"])) {
        json_encode($entries->getEntry($get["post"]));
    } else {
        $page = 1;
        if(isset($get["page"])) {
            $page = $get["page"];
        }
        json_encode($entries->getEntries($page));
    }
}

function POSTRequest($post) {
    global $entries;
    if(isset($post["email"])) {

    } else {
        if(isset($headers["Authorization"])) {
            $entries->createEntry($headers["Authorization"], $post["data"]);
        } elseif(isset($headers["authorization"])) {
            $entries->createEntry($headers["authorization"], $post["data"]);
        } else {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(array("error", "no authorization provided"));
        }
    }
}

function PUTRequest($put) {
    global $entries;

}