<?php
/**
 * Created by PhpStorm.
 * User: eliotstocker
 * Date: 31/08/2014
 * Time: 17:28
 */

namespace entries;


use exceptions\unauthorizedException;
use exceptions\userException;
use users\userAuth;

class operations {
    private $_storage;
    private $_users;

    function __construct() {
        $storageClass = 'storage\\' . STORAGE_CLASS;
        $this->_storage = new $storageClass();
        $this->_users = new userAuth();
    }

    public function getEntries($page) {
        return $this->_storage->getLatestEntries($page);
    }

    public function getEntry($id) {
        return $this->_storage->returnEntry($id);
    }

    public function createEntry($at, $data) {
        try {
            $userID = $this->_users->verifyUser($at);
            $entry = entryRegistration::fromArray((array) $data);
            $entry->setUser($userID);
            $this->_storage->createEntry($entry);
        } catch(userException $e) {
            throw new unauthorizedException("Access Token Invalid");
        } catch(\Exception $e) {
            throw new unauthorizedException($e->getMessage());
        }
    }

    public function saveEntry($at, $id, $data) {
        try {
            $userID = $this->_users->verifyUser($at);
            $entry = entryRegistration::fromArray($data);
            $entry->setUpdate($id);
            $entry->setUser($userID);
            $this->_storage->updateEntry($entry);
        } catch(userException $e) {
            throw new unauthorizedException("Access Token Invalid");
        } catch(\Exception $e) {
            throw new unauthorizedException($e->getMessage());
        }
    }
}