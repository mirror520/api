<?php

namespace App\Http\Model\sfc;

use JsonSerializable;

use App\Http\Model\sfc\CityAlliance;
use Illuminate\Support\Facades\DB;

class City implements JsonSerializable {
    private $cid;
    private $city;
    private $city_en;
    private $introduction;
    private $website;
    private $latitude;
    private $longitude;
    private $emblem_url;
    
    private $coid;
    private $country;
    private $country_en;

    private $caid;
    private $category;
    private $category_en;

    private $alliances = array();

    public function __construct($city_obj=null) {
        if ($city_obj != null) {
            $this->setCid($city_obj->cid);
            $this->setCity($city_obj->city);
            $this->setCityEn($city_obj->city_en);
            $this->setIntroduction($city_obj->introduction);
            $this->setWebsite($city_obj->website);
            $this->setLatitude($city_obj->latitude);
            $this->setLongitude($city_obj->longitude);
            $this->setEmblemUrl($city_obj->emblem_url);

            $this->setCoid($city_obj->coid);
            $this->setCountry($city_obj->country);
            $this->setCountryEn($city_obj->country_en);

            $this->setCaid($city_obj->caid);
            $this->setCategory($city_obj->category);
            $this->setCategoryEn($city_obj->category_en);

            $this->addAlliance(new CityAlliance($city_obj));
        }
    }

    public static function find() {
        $result = DB::select("SELECT `city`.*, 
                                     `X`(`city`.`location`) AS `latitude`, `Y`(`city`.`location`) AS `longitude`, 
                                     `alliance`.*, 
                                     `category`.*, 
                                     `country`.* 
                              FROM `sfc_city` AS `city` 
                              LEFT JOIN `sfc_country` AS `country` ON `city`.`coid` = `country`.`coid` 
                              LEFT JOIN `sfc_category` AS `category` ON `city`.`caid` = `category`.`caid` 
                              LEFT JOIN `sfc_city_alliance` AS `alliance` ON `city`.`cid` = `alliance`.`cid` 
                              ORDER BY `city`.`cid` ASC");

        if (empty($result))
            return null;
        else {
            $cities = array();
            foreach ($result as $city) {
                if (!key_exists($city->cid, $cities))
                    $cities[$city->cid] = new City($city);
                else
                    $cities[$city->cid]->addAlliance(new CityAlliance($city));
            }
            
            return array_values($cities);
        }
    }

    public function setCid($value) {
        $this->cid = $value;
    }
    public function getCid() {
        return $this->cid;
    }

    public function setCity($value) {
        $this->city = $value;
    }
    public function getCity() {
        return $this->city;
    }

    public function setCityEn($value) {
        $this->city_en = $value;
    }
    public function getCityEn() {
        return $this->city_en;
    }

    public function setIntroduction($value) {
        $this->introduction = $value;
    }
    public function getIntroduction() {
        return $this->introduction;
    }

    public function setWebsite($value) {
        $this->website = $value;
    }
    public function getWebsite() {
        return $this->website;
    }

    public function setLatitude($value) {
        $this->latitude = $value;
    }
    public function getLatitude() {
        return $this->latitude;
    }

    public function setLongitude($value) {
        $this->longitude = $value;
    }
    public function getLongitude() {
        return $this->longitude;
    }

    public function setEmblemUrl($value) {
        $this->emblem_url = $value;
    }
    public function getEmblemUrl() {
        return $this->emblem_url;
    }

    public function setCoid($value) {
        $this->coid = $value;
    }
    public function getCoid() {
        return $this->coid;
    }

    public function setCountry($value) {
        $this->country = $value;
    }
    public function getCountry() {
        return $this->country;
    }

    public function setCountryEn($value) {
        $this->country_en = $value;
    }
    public function getCountryEn() {
        return $this->country_en;
    }

    public function setCaid($value) {
        $this->caid = $value;
    }
    public function getCaid() {
        return $this->caid;
    }

    public function setCategory($value) {
        $this->category = $value;
    }
    public function getCategory() {
        return $this->category;
    }

    public function setCategoryEn($value) {
        $this->category_en = $value;
    }
    public function getCategoryEn() {
        return $this->category_en;
    }

    public function addAlliance($value) {
        $this->alliances[] = $value;
    }

    public function jsonSerialize() {
        return [
            'cid' => $this->cid, 
            'city' => $this->city, 
            'city_en' => $this->city_en, 
            'introduction' => $this->introduction, 
            'website' => $this->website, 
//            'latitude' => $this->latitude, 
//            'longitude' => $this->longitude, 
            'emblem_url' => $this->emblem_url, 

            'alliances' => $this->alliances, 

            'coid' => $this->coid, 
            'country' => $this->country, 
            'country_en' => $this->country_en, 

            'caid' => $this->caid, 
            'category' => $this->category, 
            'category_en' => $this->category_en
        ];
    }
}
