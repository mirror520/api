<?php

namespace App\Http\Model;

use JsonSerializable;

class Result implements JsonSerializable {
    const SUCCESS = 'success';
    const FAILURE = 'failure';

    private $status;
    private $info = array();
    private $data;
    private $time;

    public function __construct($status=null) {
        $this->setStatus($status);
        $this->setTime(time());
    }

    public function setStatus($value) {
        $this->status = $value;
    }
    public function getStatus() {
        return $this->status;
    }

    public function addInfo($value) {
        $this->info[] = $value;
    }
    public function getInfo() {
        return $this->info;
    }

    public function setData($value) {
        $this->data = $value;
    }
    public function getData() {
        return $this->data;
    }

    public function setTime($value) {
        $this->time = $value;
    }
    public function getTime() {
        return $this->time;
    }

    public function jsonSerialize() {
        return [
            'status' => $this->status, 
            'info' => $this->info, 
            'data' => $this->data, 
            'time' => $this->time
        ];
    }
}