<?php

namespace App\Http\Model\seit;

use JsonSerializable;

class MailTemplate implements JsonSerializable {
    private $mtid;
    private $subject;
    private $body;

    public function __construct($mail_obj=null) {
        if ($mail_obj != null) {
            $this->setMtid($mail_obj->mtid);
            $this->setSubject($mail_obj->subject);
            $this->setBody($mail_obj->body);
        }
    }

    public function setMtid($value) {
        $this->mtid = $value;
    }
    public function getMtid() {
        return $this->mtid;
    }

    public function setSubject($value) {
        $this->subject = $value;
    }
    public function getSubject() {
        return $this->subject;
    }

    public function setBody($value) {
        $this->body = $value;
    }
    public function getBody() {
        return $this->body;
    }

    public function jsonSerialize() {
        return [
            'mtid' => $this->mtid, 
            'subject' => $this->subject, 
            'body' => $this->body
        ];
    }
}
