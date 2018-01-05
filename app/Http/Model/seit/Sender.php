<?php

namespace App\Http\Model\seit;

use JsonSerializable;

class Sender implements JsonSerializable {
    private $sid;
    private $sender;
    private $addressor;
    private $host;
    private $port;
    private $username;
    private $password;
    private $reply_name;
    private $reply_address;
    private $confirm_reading;

    public function __construct($mail_obj=null) {
        if ($mail_obj != null) {
            $this->setSid($mail_obj->sid);
            $this->setSender($mail_obj->sender);
            $this->setAddressor($mail_obj->addressor);
            $this->setHost($mail_obj->host);
            $this->setPort($mail_obj->port);
            $this->setUsername($mail_obj->username);
            $this->setPassword($mail_obj->password);
            $this->setReplyName($mail_obj->reply_name);
            $this->setReplyAddress($mail_obj->reply_address);
            $this->setConfirmReading($mail_obj->confirm_reading);
        }
    }

    public function setSid($value) {
        $this->sid = $value;
    }
    public function getSid() {
        return $this->sid;
    }

    public function setSender($value) {
        $this->sender = $value;
    }
    public function getSender() {
        return $this->sender;
    }

    public function setAddressor($value) {
        $this->addressor = $value;
    }
    public function getAddressor() {
        return $this->addressor;
    }
    
    public function setHost($value) {
        $this->host = $value;
    }
    public function getHost() {
        return $this->host;
    }
    
    public function setPort($value) {
        $this->port = $value;
    }
    public function getPort() {
        return $this->port;
    }
    
    public function setUsername($value) {
        $this->username = $value;
    }
    public function getUsername() {
        return $this->username;
    }
    
    public function setPassword($value) {
        $this->password = $value;
    }
    public function getPassword() {
        return $this->password;
    }

    public function setReplyName($value) {
        $this->reply_name = $value;
    }
    public function getReplyName() {
        return $this->reply_name;
    }
    
    public function setReplyAddress($value) {
        $this->reply_address = $value;
    }
    public function getReplyAddress() {
        return $this->reply_address;
    }

    public function setConfirmReading($value) {
        $this->confirm_reading = $value;
    }
    public function getConfirmReading() {
        return $this->confirm_reading;
    }

    public function jsonSerialize() {
        return [
            'sid' => $this->sid, 
            'sender' => $this->sender, 
            'addressor' => $this->addressor, 
            'host' => $this->host, 
            'port' => $this->port, 
            'username' => $this->username, 
            'password' => $this->password, 
            'reply_name' => $this->reply_name, 
            'reply_address' => $this->reply_address, 
            'confirm_reading' => $this->confirm_reading
        ];
    }
}
