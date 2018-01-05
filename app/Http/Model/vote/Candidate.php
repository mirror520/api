<?php

namespace App\Http\Model\vote;

use JsonSerializable;

class Candidate implements JsonSerializable {
    private $vcid;
    private $candidate;

    public function __construct($cand_obj=null) {
        if ($cand_obj != null) {
            $this->setVcid($cand_obj->vcid);
            $this->setCandidate($cand_obj->candidate);
        }
    }

    public function setVcid($value) {
        $this->vcid = $value;
    }
    public function getVcid() {
        return $this->vcid;
    }

    public function setCandidate($value) {
        $this->candidate = $value;
    }
    public function getCandidate() {
        return $this->candidate;
    }
    
    public function jsonSerialize() {
        return [
            'vcid' => $this->vcid, 
            'candidate' => $this->candidate 
        ];
    }
}
