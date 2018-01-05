<?php

namespace App\Http\Model\sfc;

use JsonSerializable;

use Illuminate\Support\Facades\DB;

class CityAlliance implements JsonSerializable {
    private $cid;
    private $signing_date;
    private $signing_place;
    private $moderator_tccg;
    private $moderator_sfc;

    public function __construct($city_obj=null) {
        if ($city_obj != null) {
            $this->setCid($city_obj->cid);
            $this->setSigningDate($city_obj->signing_date);
            $this->setSigningPlace($city_obj->signing_place);
            $this->setModeratorTccg($city_obj->moderator_tccg);
            $this->setModeratorSfc($city_obj->moderator_sfc);
        }
    }

    public function setCid($value) {
        $this->cid = $value;
    }
    public function getCid() {
        return $this->cid;
    }

    public function setSigningDate($value) {
        $this->signing_date = $value;
    }
    public function getSigningDate() {
        return $this->signing_date;
    }

    public function setSigningPlace($value) {
        $this->signing_place = $value;
    }
    public function getSigningPlace() {
        return $this->signing_place;
    }

    public function setModeratorTccg($value) {
        $this->moderator_tccg = $value;
    }
    public function getModeratorTccg() {
        return $this->moderator_tccg;
    }

    public function setModeratorSfc($value) {
        $this->moderator_sfc = $value;
    }
    public function getModeratorSfc() {
        return $this->moderator_sfc;
    }

    public function jsonSerialize() {
        return [
            'signing_date' => $this->signing_date, 
            'signing_place' => $this->signing_place, 
            'moderator_tccg' => $this->moderator_tccg, 
            'moderator_sfc' => $this->moderator_sfc
        ];
    }
}
