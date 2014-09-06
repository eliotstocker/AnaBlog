<?php
require_once "ClassLoader.php";
require_once "../config.php";

$entries = new \entries\operations();
$users = new \users\userAuth();

$headers = apache_request_headers();

try {
    switch($_SERVER["REQUEST_METHOD"]) {
        case "GET":
            GETRequest($_GET);
            break;
        case "POST":
            $POST = file_get_contents("php://input");
            POSTRequest($POST, $_GET);
            break;
        case "PUT":
            $PUT = file_get_contents("php://input");
            PUTRequest($PUT, $_GET);
            break;
    }
} catch(\exceptions\unauthorizedException $e) {
    header('HTTP/1.0 401 Unauthorized');
    echo json_encode(array("error" => $e->getMessage()));
}

function GETRequest($get) {
    global $entries;
    global $users;
    if(isset($get["post"])) {
        $auth = false;
        if(isset($headers["Authorization"])) {
            $users->verifyUser($headers["Authorization"]);
            $auth = true;
        } elseif(isset($headers["authorization"])) {
            $users->verifyUser($headers["authorization"]);
            $auth = true;
        }
        echo json_encode($entries->getEntry($get["post"], $auth));
    } elseif(isset($get["logout"]) && $get["logout"] == "true") {
        if(isset($headers["Authorization"])) {
            $users->logout($headers["Authorization"]);
        } elseif(isset($headers["authorization"])) {
            $users->logout($headers["authorization"]);
        } else {
            throw new \exceptions\unauthorizedException("Access Token not Found");
        }
        echo json_encode(array("success" => true));
    } else {
        $page = 1;
        if(isset($get["page"])) {
            $page = $get["page"];
        }
        if(isset($headers["Authorization"])) {
            $users->verifyUser($headers["Authorization"]);
            echo json_encode($entries->listEntries($page));
        } elseif(isset($headers["authorization"])) {
            $users->verifyUser($headers["authorization"]);
            echo json_encode($entries->listEntries($page));
        } else {
            echo json_encode($entries->getEntries($page));
        }
    }
}

function POSTRequest($post, $get) {
    global $entries;
    global $users;
    global $headers;
    $data = json_decode($post);
    if(isset($data->email)) {
        if(!isset($data->password)) {
            throw new \exceptions\unauthorizedException("Password must be supplied");
        }
        echo json_encode(array("access_token" => $users->login($data->email, $data->password), "type" => "Authorization"));
    } else {
        if(isset($headers["Authorization"])) {
            $entries->createEntry($headers["Authorization"], $data);
        } elseif(isset($headers["authorization"])) {
            $entries->createEntry($headers["authorization"], $data);
        } else {
            throw new \exceptions\unauthorizedException("Access Token not Found");
        }
        echo json_encode(array("success" => true));
    }
}

function PUTRequest($put, $get) {
    global $entries;
    $data = json_decode($put);
    if(isset($headers["Authorization"])) {
        $entries->saveEntry($headers["Authorization"], $get["id"], $data);
    } elseif(isset($headers["authorization"])) {
        $entries->saveEntry($headers["authorization"], $get["id"], $data);
    } else {
        throw new \exceptions\unauthorizedException("Access Token not Found");
    }
    echo json_encode(array("success" => true));
}