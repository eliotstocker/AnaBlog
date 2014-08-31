<?php
namespace storage;

use entries\entryRegistration;
use exceptions\updateException;

class mongodb {
    private $_con;
    private $_db;

    function __construct() {
        $this->_con = new \MongoClient("mongodb://".DB_USER.":".DB_PASSWORD."@".DB_HOST);
        $this->_db = $this->_con->{DB_DATABASE};
        $this->_col = $this->_db->{DB_BLOG_COLLECTION};
    }

    public function createEntry(entryRegistration $data) {
        $this->_col->insert($data->entryToArray());
        return true;
    }

    public function returnEntry($id) {
        $entry = $this->_col->findOne(array("_id" => new \MongoId($id)));
        unset($entry["_id"]);
        return $entry;
    }

    public function updateEntry($id, entryRegistration $data) {
        if($data->getUpdate()) {
            $this->_col->update(array("_id" => new \MongoId($id)), $data->entryToArray());
        } else {
            throw new updateException("update request for non update data");
        }
    }

    public function getLatestEntries($page = 1, $count = 10, $auth = false) {
        if($page < 1) {
            $page = 1;
        }
        $entries = $this->_col->find()->limit($count)->skip(($page - 1) * $count)->sort(array("created" => true));
        $return = new \stdClass();
        $return->count = $entries->count();
        $return->page = $page;
        $return->pages = ceil($return->count / $count);
        $return->results = array();
        foreach($entries as $entry) {
            $entry["id"] = $entry["_id"]->{"\$id"};
            unset($entry["_id"]);
            if(isset($entry["editor"])) {
                $editor = $this->getUserByID($entry["editor"]);
                $info = array("first_name" => $editor["first_name"], "last_name" => $editor["last_name"]);
                if($auth) {
                    $info["id"] = $editor["_id"];
                    $info["email"] = $editor["email"];
                }
                $entry["editor"] = $info;
            }
            $author = $this->getUserByID($entry["author"]);
            $info = array("first_name" => $author["first_name"], "last_name" => $author["last_name"]);
            if($auth) {
                $info["id"] = $author["_id"];
                $info["email"] = $author["email"];
            }
            $entry["author"] = $info;
            $return->results[] = $entry;
        }
        return $return;
    }

    public function listEntries($page = 1, $count = 100) {
        if($page < 1) {
            $page = 1;
        }
    }

    public function getUser($email) {
        $user = $this->_db->{DB_USER_COLLECTION}->findOne(array("email" => $email));
        return $user;
    }

    public function getUserByID($id) {
        $user = $this->_db->{DB_USER_COLLECTION}->findOne(array("_id" => new \MongoId($id)));
        return $user;
    }
}