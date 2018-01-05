<?php

namespace App\Http\Model\vote;

use JsonSerializable;

class Session implements JsonSerializable {
    private $vsid;
    private $session;
    private $start_time;
    private $end_time;

    public function __construct($session_obj=null) {
        if ($session_obj != null) {
            $this->setVsid($session_obj->vsid);
            $this->setSession($session_obj->session);
            $this->setStartTime($session_obj->start_time);
            $this->setEndTime($session_obj->end_time);
        }
    }

    public function setVsid($value) {
        $this->vsid = $value;
    }
    public function getVsid() {
        return $this->vsid;
    }

    public function setSession($value) {
        $this->session = $value;
    }
    public function getSession() {
        return $this->session;
    }
    
    public function setStartTime($value) {
        $this->start_time = $value;
    }
    public function getStartTime() {
        return $this->start_time;
    }
    
    public function setEndTime($value) {
        $this->end_time = $value;
    }
    public function getEndTime() {
        return $this->end_time;
    }
    
    public function jsonSerialize() {
        return [
            'vsid' => $this->vsid, 
            'session' => $this->session, 
            'start_time' => $this->start_time, 
            'end_time' => $this->end_time
        ];
    }
}
