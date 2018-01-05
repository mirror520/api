<?php

namespace App\Http\Model\seit;

use JsonSerializable;

class MailUserAgent implements JsonSerializable {
    private $mid;
    private $fail_time;
    private $user_agent;
    private $platform;
    private $device_type;
    private $browser_name_pattern;

    public function __construct($mail_obj=null) {
        if ($mail_obj != null) {
            $this->setMid($mail_obj->mid);
            $this->setFailTime($mail_obj->fail_time);
            $this->setUserAgent($mail_obj->user_agent);
            $this->setPlatform($mail_obj->platform);
            $this->setDeviceType($mail_obj->device_type);
            $this->setBrowserNamePattern($mail_obj->browser_name_pattern);
        }
    }

    public function setMid($value) {
        $this->mid = $value;
    }
    public function getMid() {
        return $this->mid;
    }

    public function setFailTime($value) {
        $this->fail_time = $value;
    }
    public function getFailTime() {
        return $this->fail_time;
    }

    public function setUserAgent($value) {
        $this->user_agent = $value;
    }
    public function getUserAgent() {
        return $this->user_agent;
    }

    public function setPlatform($value) {
        $this->platform = $value;
    }
    public function getPlatform() {
        return $this->platform;
    }

    public function setDeviceType($value) {
        $this->device_type = $value;
    }
    public function getDeviceType() {
        return $this->device_type;
    }

    public function setBrowserNamePattern($value) {
        $this->browser_name_pattern = $value;
    }
    public function getBrowserNamePattern() {
        return $this->browser_name_pattern;
    }

    public function jsonSerialize() {
        return [
            'mid' => $this->mid, 
            'fail_time' => $this->fail_time, 
            'user_agent' => $this->user_agent, 
            'platform' => $this->platform, 
            'device_type' => $this->device_type, 
            'browser_name_pattern' => $this->browser_name_pattern
        ];
    }
}
