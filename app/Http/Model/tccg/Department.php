<?php

namespace App\Http\Model\tccg;

use Illuminate\Support\Facades\DB;
use JsonSerializable;

class Department implements JsonSerializable {
    private static $departments;
    
    private $tdid;
    private $department;
    private $parent_tdid;
    private $seq;
    private $ou;
    private $tiid;

    public function __construct($department_obj=null) {
        if ($department_obj != null) {
            $this->setTdid($department_obj->tdid);
            $this->setDepartment($department_obj->department);
            $this->setParentTdid($department_obj->parent_tdid);
            $this->setSeq($department_obj->seq);
            $this->setOu($department_obj->ou);
            $this->setTiid($department_obj->tiid);
        }
    }
    
    public static function getDepartments() {
        $result = DB::select("SELECT * 
                              FROM `tccg_department` 
                              ORDER BY `tdid` ASC");
        $departments = array();
        foreach ($result as $value)
            $departments[$value->tdid] = new Department($value);
            
        Department::$departments = $departments;
    }
    
    public static function findDepartmentByOu($ou) {
        if (empty(Department::$departments))
            Department::getDepartments();
            
        $departments = Department::$departments;
        foreach ($departments as $department) {
            if ($department->getOu() == $ou)
                return $department;
        }
        
        return null;
    }
    
    public static function findDepartmentByTdid($tdid) {
        return Department::$departments[$tdid];
    }

    public function setTdid($value) {
        $this->tdid = $value;
    }
    public function getTdid() {
        return $this->tdid;
    }
    
    public function setDepartment($value) {
        $this->department = $value;
    }
    public function getDepartment() {
        return $this->department;
    }
    
    public function setParentTdid($value) {
        $this->parent_tdid = $value;
    }
    public function getParentTdid() {
        return $this->parent_tdid;
    }
    
    public function setSeq($value) {
        $this->seq = $value;
    }
    public function getSeq() {
        return $this->seq;
    }
    
    public function setOu($value) {
        $this->ou = $value;
    }
    public function getOu() {
        return $this->ou;
    }
    
    public function setTiid($value) {
        $this->tiid = $value;
    }
    public function getTiid() {
        return $this->tiid;
    }

    public function jsonSerialize() {
        return [
            'tdid' => $this->tdid, 
            'department' => $this->department, 
            'parent_tdid' => $this->parent_tdid, 
            'seq' => $this->seq, 
            'ou' => $this->ou, 
            'tiid' => $this->tiid
        ];
    }
}
