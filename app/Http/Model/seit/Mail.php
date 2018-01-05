<?php

namespace App\Http\Model\seit;

use JsonSerializable;

class Mail implements JsonSerializable {
    private $mid;
    private $create_time;
    private $delivery_time;
    private $fail_time;
    private $mail_status;
    private $test_status;

    private $mtid;
    private $sid;
    private $rid;

    private $mail_template;
    private $sender;
    private $recipient;

    public function __construct($mail_obj=null) {
        if ($mail_obj != null) {
            $this->setMid($mail_obj->mid);
            $this->setCreateTime($mail_obj->create_time);
            $this->setDeliveryTime($mail_obj->delivery_time);
            $this->setFailTime($mail_obj->fail_time);
            $this->setMailStatus($mail_obj->mail_status);
            $this->setTestStatus($mail_obj->test_status);

            $this->setMtid($mail_obj->mtid);
            $this->setSid($mail_obj->sid);
            $this->setRid($mail_obj->rid);

            $this->setMailTemplate(new MailTemplate($mail_obj));
            $this->setSender(new Sender($mail_obj));
            $this->setRecipient(new Recipient($mail_obj));
        }
    }

    public function setMid($value) {
        $this->mid = $value;
    }
    public function getMid() {
        return $this->mid;
    }

    public function setCreateTime($value) {
        $this->create_time = $value;
    }
    public function getCreateTime() {
        return $this->create_time;
    }

    public function setDeliveryTime($value) {
        $this->delivery_time = $value;
    }
    public function getDeliveryTime() {
        return $this->delivery_time;
    }

    public function setFailTime($value) {
        $this->fail_time = $value;
    }
    public function getFailTime() {
        return $this->fail_time;
    }

    public function setMailStatus($value) {
        $this->mail_status = $value;
    }
    public function getMailStatus() {
        return $this->mail_status;
    }

    public function setTestStatus($value) {
        $this->test_status = $value;
    }
    public function getTestStatus() {
        return $this->test_status;
    }

    public function setMtid($value) {
        $this->mtid = $value;
    }
    public function getMtid() {
        return $this->mtid;
    }

    public function setSid($value) {
        $this->sid = $value;
    }
    public function getSid() {
        return $this->sid;
    }

    public function setRid($value) {
        $this->rid = $value;
    }
    public function getRid() {
        return $this->rid;
    }

    public function setMailTemplate($value) {
        $this->mail_template = $value;
    }
    public function getMailTemplate() {
        return $this->mail_template;
    }

    public function setSender($value) {
        $this->sender = $value;
    }
    public function getSender() {
        return $this->sender;
    }

    public function setRecipient($value) {
        $this->recipient = $value;
    }
    public function getRecipient() {
        return $this->recipient;
    }

    public function jsonSerialize($unsetKeys=null) {
        $data = [
            'mid' => $this->mid, 
            'create_time' => $this->create_time, 
            'delivery_time' => $this->delivery_time, 
            'fail_time' => $this->fail_time, 
            'mail_status' => $this->mail_status, 
            'test_status' => $this->test_status, 

            'mtid' => $this->mtid, 
            'sid' => $this->sid, 
            'rid' => $this->rid, 

            'mail_template' => $this->mail_template, 
            'sender' => $this->sender, 
            'recipient' => $this->recipient
        ];

        if ($unsetKeys != null) {
            foreach ($unsetKeys as $unsetKey)
                unset($data[$unsetKey]);
        }

        return $data;
    }
}
