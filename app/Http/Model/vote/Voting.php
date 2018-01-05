<?php

namespace App\Http\Model\vote;

use JsonSerializable;

class Voting implements JsonSerializable {
    private $vvid;
    private $tccg_account;
    private $vcid;
    private $create_time;

    public function __construct($vote_obj=null) {
        if ($vote_obj != null) {
            $this->setVvid($vote_obj->vvid);
            $this->setTccgAccount($vote_obj->tccg_account);
            $this->setVcid($vote_obj->vcid);
            $this->setCreateTime($vote_obj->create_time);
        }
    }

    public function setVvid($value) {
        $this->vvid = $value;
    }
    public function getVvid() {
        return $this->vvid;
    }

    public function setTccgAccount($value) {
        $this->tccg_account = $value;
    }
    public function getTccgAccount() {
        return $this->tccg_account;
    }
    
    public function setVcid($value) {
        $this->vcid = $value;
    }
    public function getVcid($value) {
        return $this->vcid;
    }
    
    public function setCreateTime($value) {
        $this->create_time = $value;
    }
    public function getCreateTime() {
        return $this->create_time;
    }
    
    public function jsonSerialize() {
        return [
            'vvid' => $this->vvid, 
            'tccg_account' => $this->tccg_account, 
            'vcid' => $this->vcid, 
            'create_time' => $this->create_time
        ];
    }
}
