<?php

namespace App\Http\Model\tg;

use JsonSerializable;

class Issue implements JsonSerializable {
    private $giid;
    private $issue;
    private $season;
    private $term;

    public function __construct($gaz_obj=null) {
        if ($gaz_obj != null) {
            $this->setGiid($gaz_obj->giid);
            $this->setIssue($gaz_obj->issue);
            $this->setSeason($gaz_obj->season);
            $this->setTerm($gaz_obj->term);
        }
    }

    public function setGiid($value) {
        $this->giid = $value;
    }
    public function getGiid() {
        return $this->giid;
    }

    public function setIssue($value) {
        $this->issue = $value;
    }
    public function getIssue() {
        return $this->issue;
    }

    public function setSeason($value) {
        $this->season = $value;
    }
    public function getSeason() {
        return $this->season;
    }

    public function setTerm($value) {
        $this->term = $value;
    }
    public function getTerm() {
        return $this->term;
    }

    public function jsonSerialize() {
        return [
            'giid' => $this->giid, 
            'issue' => $this->issue, 
            'season' => $this->season, 
            'term' => $this->term
        ];
    }
}
