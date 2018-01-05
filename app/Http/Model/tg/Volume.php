<?php

namespace App\Http\Model\tg;

use JsonSerializable;

class Volume implements JsonSerializable {
    private $gvid;
    private $volume;
    private $year_roc;
    private $year;

    public function __construct($gaz_obj=null) {
        if ($gaz_obj != null) {
            $this->setGvid($gaz_obj->gvid);
            $this->setVolume($gaz_obj->volume);
            $this->setYearRoc($gaz_obj->year_roc);
            $this->setYear($gaz_obj->year);
        }
    }

    public function setGvid($value) {
        $this->gvid = $value;
    }
    public function getGvid() {
        return $this->gvid;
    }

    public function setVolume($value) {
        $this->volume = $value;
    }
    public function getVolume() {
        return $this->volume;
    }

    public function setYearRoc($value) {
        $this->year_roc = $value;
    }
    public function getYearRoc() {
        return $this->year_roc;
    }

    public function setYear($value) {
        $this->year = $value;
    }
    public function getYear() {
        return $this->year;
    }

    public function jsonSerialize() {
        return [
            'gvid' => $this->gvid, 
            'volume' => $this->volume, 
            'year_roc' => $this->year_roc, 
            'year' => $this->year
        ];
    }
}
