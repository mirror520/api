<?php

namespace App\Http\Model\seit;

use JsonSerializable;

class Institution implements JsonSerializable {
    private $iid;
    private $institution;

    private $departments = array();

    public function __construct($mail_obj=null) {
        if ($mail_obj != null) {
            $this->setIid($mail_obj->iid);
            $this->setInstitution($mail_obj->institution);
        }
    }

    public function setIid($value) {
        $this->iid = $value;
    }
    public function getIid() {
        return $this->iid;
    }

    public function setInstitution($value) {
        $this->institution = $value;
    }
    public function getInstitution() {
        return $this->institution;
    }

    public function addDepartment($value, $did) {
        $this->departments[$did] = $value;
    }
    public function getDepartments() {
        return $this->departments;
    }
    public function getDepartment($did) {
        return $this->departments[$did];
    }

    public function recursiveRemoveKeys() {
        $this->departments = array_values($this->departments);
        foreach ($this->departments as $department)
            $department->recursiveRemoveKeys();
    }

    public function jsonSerialize() {
        return [
            'iid' => $this->iid, 
            'institution' => $this->institution, 
            'departments' => $this->departments
        ];
    }
}
