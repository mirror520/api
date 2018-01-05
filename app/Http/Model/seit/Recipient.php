<?php

namespace App\Http\Model\seit;

use JsonSerializable;

class Recipient implements JsonSerializable {
    private $rid;
    private $recipient;
    private $account;
    private $addressee;
    private $did;

    public function __construct($mail_obj=null) {
        if ($mail_obj != null) {
            $this->setRid($mail_obj->rid);
            $this->setRecipient($mail_obj->recipient);
            $this->setAccount($mail_obj->account);
            $this->setAddressee($mail_obj->addressee);
            $this->setDid($mail_obj->did);
        }
    }

    public function setRid($value) {
        $this->rid = $value;
    }
    public function getRid() {
        return $this->rid;
    }

    public function setRecipient($value) {
        $this->recipient = $value;
    }
    public function getRecipient() {
        return $this->recipient;
    }

    public function setAccount($value) {
        $this->account = $value;
    }
    public function getAccount() {
        return $this->account;
    }

    public function setAddressee($value) {
        $this->addressee = $value;
    }
    public function getAddressee() {
        return $this->addressee;
    }

    public function setDid($value) {
        $this->did = $value;
    }
    public function getDid() {
        return $this->did;
    }

    public function jsonSerialize() {
        return [
            'rid' => $this->rid, 
            'recipient' => $this->recipient, 
            'account' => $this->account, 
            'addressee' => $this->addressee
        ];
    }
}
