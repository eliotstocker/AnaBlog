<?php
namespace storage;

use entries\entryRegistration;
use exceptions\updateException;

class mongodb {
    private $_con;
    private $_db;

    function __construct($username, $password, $host, $database, $collection) {
        $this->_con = new \MongoClient("mongodb://{$username}:{$password}@{$host}");
        $this->_db = $this->_con->$database;
        $this->_col = $this->_db->$collection;
    }

    public function createEntry(entryRegistration $data) {
        $this->_col->insert($data->entryToArray());
        return true;
    }

    public function returnEntry($id) {
        $entry = $this->_col->findOne(array("_id" => $id));
        return $entry;
    }

    public function updateEntry($id, entryRegistration $data) {
        if($data->getUpdate()) {
            $this->_col->update(array("_id" => $id), $data->entryToArray());
        } else {
            throw new updateException("update request for non update data");
        }
    }

    public function getLatestEntries($page = 1, $count = 10) {
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
        $user = $this->_col->findOne(array("email" => $email));
        return $user;
    }
} 