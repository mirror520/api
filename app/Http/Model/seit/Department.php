<?php

namespace App\Http\Model\seit;

use JsonSerializable;

class Department implements JsonSerializable {
    private $did;
    private $department;
    private $seq;
    private $iid;

    private $mails = array();

    public function __construct($mail_obj=null) {
        if ($mail_obj != null) {
            $this->setDid($mail_obj->did);
            $this->setDepartment($mail_obj->department);
            $this->setSeq($mail_obj->seq);
            $this->setIid($mail_obj->iid);
        }
    }

    public function setDid($value) {
        $this->did = $value;
    }
    public function getDid() {
        return $this->did;
    }

    public function setDepartment($value) {
        $this->department = $value;
    }
    public function getDepartment() {
        return $this->department;
    }

    public function setSeq($value) {
        $this->seq = $value;
    }
    public function getSeq() {
        return $this->seq;
    }

    public function setIid($value) {
        $this->iid = $value;
    }
    public function getIid() {
        return $this->iid;
    }

    public function addMail($value, $mid) {
        $this->mails[$mid] = $value;
    }

    public function recursiveRemoveKeys() {
        $this->mails = array_values($this->mails);
    }

    public function jsonSerialize() {
        return [
            'did' => $this->did, 
            'department' => $this->department, 
            'seq' => $this->seq, 
            'mails' => $this->mails
        ];
    }
}
