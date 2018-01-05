<?php

namespace App\Http\Model\tg;

use JsonSerializable;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class Gazette implements JsonSerializable {
    private $gid;
    private $gazette;
    private $issue_date;
    private $create_time;
    private $uri;
    private $enabled;
    
    private $gvid;
    private $giid;

    private $volume;
    private $issue;

    public function __construct($gaz_obj=null) {
        if ($gaz_obj != null) {
            $this->setGid($gaz_obj->gid);
            $this->setGazette($gaz_obj->gazette);
            $this->setIssueDate($gaz_obj->issue_date);
            $this->setCreateTime($gaz_obj->create_time);
            $this->setUri($gaz_obj->uri);
            $this->setEnabled( ($gaz_obj->enabled == 'true') ? true : false );

            $this->setGvid($gaz_obj->gvid);
            $this->setGiid($gaz_obj->giid);

            $this->setVolume(new Volume($gaz_obj));
            $this->setIssue(new Issue($gaz_obj));
        }
    }

    public static function getGazettes() {
        $result = DB::select("SELECT `gazette`.*, 
                                     `volume`.*, 
                                     `issue`.* 
                              FROM `tg_gazette` AS `gazette` 
                              LEFT JOIN `tg_volume` AS `volume` ON `gazette`.`gvid` = `volume`.`gvid` 
                              LEFT JOIN `tg_issue` AS `issue` ON `gazette`.`giid` = `issue`.`giid` 
                              ORDER BY `gazette`.`gid` ASC");

        if (empty($result))
            return null;
        else {
            $gazettes = array();
            foreach ($result as $gazette)
                $gazettes[] = new Gazette($gazette);
            
            return $gazettes;
        }
    }

    public static function find($gid=null) {
        if ($gid != null) {
            $result = DB::select("SELECT `gazette`.*, 
                                         `volume`.*, 
                                         `issue`.* 
                                  FROM `tg_gazette` AS `gazette` 
                                  LEFT JOIN `tg_volume` AS `volume` ON `gazette`.`gvid` = `volume`.`gvid` 
                                  LEFT JOIN `tg_issue` AS `issue` ON `gazette`.`giid` = `issue`.`giid` 
                                  WHERE `gid`=:gid LIMIT 1", ['gid' => $gid]);
        }

        if (empty($result))
            return null;
        else
            return new Gazette($result[0]);
    }

    public function setGid($value) {
        $this->gid = $value;
    }
    public function getGid() {
        return $this->gid;
    }

    public function setGazette($value) {
        $this->gazette = $value;
    }
    public function getGazette() {
        return $this->gazette;
    }

    public function setIssueDate($value) {
        $this->issue_date = $value;
    }
    public function getIssueDate() {
        return $this->issue_date;
    }

    public function setCreateTime($value) {
        $this->create_time = $value;
    }
    public function getCreateTime() {
        return $this->create_time;
    }

    public function setUri($value) {
        $this->uri = $value;
    }
    public function getUri() {
        return $this->uri;
    }

    public function setEnabled($value) {
        $this->enabled = $value;
    }
    public function getEnabled() {
        return $this->enabled;
    }


    public function setGvid($value) {
        $this->gvid = $value;
    }
    public function getGvid() {
        return $this->gvid;
    }


    public function setGiid($value) {
        $this->giid = $value;
    }
    public function getGiid() {
        return $this->giid;
    }

    public function setVolume($value) {
        $this->volume = $value;
    }
    public function getVolume() {
        return $this->volume;
    }

    public function setIssue($value) {
        $this->issue = $value;
    }
    public function getIssue() {
        return $this->issue;
    }

    public function jsonSerialize() {
        return [
            'gid' => $this->gid, 
            'gazette' => $this->gazette, 
            'issue_date' => $this->issue_date, 
            'create_time' => $this->create_time, 
            'uri' => ($this->uri != '') ? URL::to('/v1.0/tg/gazettes/'. $this->gid . '/file') : '', 
            'enabled' => $this->enabled, 

            'gvid' => $this->gvid, 
            'giid' => $this->giid, 
            
            'volume' => $this->volume, 
            'issue' => $this->issue
        ];
    }
}