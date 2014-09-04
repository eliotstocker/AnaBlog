<?php
/**
 * Created by PhpStorm.
 * User: eliotstocker
 * Date: 31/08/2014
 * Time: 17:37
 */

namespace users;


use exceptions\userException;

class userAuth {
    public function verifyUser($accessToken) {
        if(is_file(".access")) {
            $access = json_decode(file_get_contents(".access"));
            if(isset($access->$accessToken)) {
                return $access->$accessToken->id;
            } else {
                throw new userException("Access Token invalid");
            }
        } else {
            throw new userException("Cannot access Access Token storage");
        }
    }

    function login($email, $password) {
        $storageClass = 'storage\\' . STORAGE_CLASS;
        $storage = new $storageClass();
        $u = $storage->getUser($email);
        $salt = $u["salt"];
        $pwdHash = hash('sha256', $salt.md5($password));
        if($pwdHash == $u["hash"]) {
            $access = array();
            if(is_file(".access")) {
                $access = json_decode(file_get_contents(".access"));
            }
            $tokenID = $this->generateRandomString();
            $access->$tokenID = array("id" => $u["_id"]->{"\$id"}, "email" => $u["email"], "accessed" => time());
            file_put_contents(".access", json_encode($access));
            return $tokenID;
        } else {
            throw new userException("User Name or Password Invalid");
        }
    }

    public function logout($at) {
        if(is_file(".access")) {
            $access = json_decode(file_get_contents(".access"));
        } else {
            throw new userException("Cannot access Access Token storage");
        }
        if(isset($access[$at])) {
            unset($access[$at]);
        }
        file_put_contents(".access", json_encode($access));
        return true;
    }

    public function getUser($accessToken) {
        $storageClass = 'storage\\' . STORAGE_CLASS;
        $storage = new $storageClass();

        if(is_file(".access")) {
            $access = json_decode(file_get_contents(".access"));
            if(isset($access[$accessToken])) {
                $email = $access[$accessToken]["email"];
            } else {
                throw new userException("Access Token invalid");
            }
        } else {
            throw new userException("Cannot access Access Token storage");
        }

        $u = $storage->getUser($email);
        unset($u->salt);
        unset($u->hash);
        return $u;
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }
} 