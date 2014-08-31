<?php
/**
 * Created by PhpStorm.
 * User: eliotstocker
 * Date: 31/08/2014
 * Time: 16:59
 */

namespace entries;


use exceptions\entryException;

class entryRegistration {
    private $_entry;

    public function __construct($content, $title)
    {
        $this->_entry = array();
        $this->setContent($content);
        $this->setTitle($title);
        $this->setCreated(time());
    }

    public static function fromArray(array $a)
    {
        $requiredFields = array("content", "title");
        foreach ($requiredFields as $r) {
            if (!array_key_exists($r, $a)) {
                throw new entryException("not a valid entry, '" . $r . "' not set");
            }
        }
        $c = new static($a['content'], $a["title"]);

        return $c;
    }

    private function setCreated($time) {
        if(!is_int($time)) {
            throw new entryException("time must be integer");
        }
        $this->_entry['created'] = $time;
    }

    public function setUpdate($i) {
        if (empty($i)) {
            throw new entryException("id cannot be empty");
        }
        if(isset($this->_entry["author"])) {
            $this->_entry["editor"] = $this->_entry["author"];
            unset($this->_entry["author"]);
        }
        if(isset($this->_entry["created"])) {
            $this->_entry["updated"] = $this->_entry["created"];
            unset($this->_entry["created"]);
        }
        $this->_entry['_id'] = $i;
    }

    public function getUpdate() {
        if(isset($this->_entry["_id"])) {
            return true;
        } else {
            return false;
        }
    }

    public function setUser($u) {
        if($this->getUpdate()) {
            $this->_entry["author"] = $u;
        } else {
            $this->_entry["editor"] = $u;
        }
    }

    public function getUser() {

    }

    public function setTitle($t)
    {
        if (empty($n)) {
            throw new ClientRegistrationException("name cannot be empty");
        }
        $this->_entry['title'] = $t;
    }

    public function getTitle()
    {
        return $this->_entry['title'];
    }

    public function setContent($c)
    {
        if (empty($n)) {
            throw new ClientRegistrationException("name cannot be empty");
        }
        $this->_entry['content'] = $c;
    }

    public function getContent()
    {
        return $this->_entry['content'];
    }

    public function entryToArray()
    {
        if(!isset($this->_entry["author"]) && !isset($this->_entry["editor"])) {
            throw new \Exception("an author or editor ust be specified");
        }
        return $this->_entry;
    }
}
