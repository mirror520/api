<?php

namespace App\Http\Model\tccg;

use JsonSerializable;

use Lcobucci\JWT;
use Lcobucci\JWT\Signer\Hmac;
use Illuminate\Support\Facades\DB;

class User implements JsonSerializable {
    private $account;
    private $name;
    private $mail;
    private $title;
    private $phone;
    private $sex;
    private $role;
    private $enabled;
    private $dn;
    private $tdid;
    
    private $department;
    private $token;

    public function __construct($user_obj=null, $from_json=false, $from_dn=false) {
        if ($user_obj != null) {
            if ($from_json) {
                $this->setAccount($user_obj['account']);
                $this->setName($user_obj['name']);
                $this->setMail($user_obj['mail']);
                $this->setTitle($user_obj['title']);
                $this->setPhone($user_obj['phone']);
                $this->setSex($user_obj['sex']);
                $this->setRole($user_obj['role']);
                $this->setEnabled($user_obj['enabled']);
                $this->setDn($user_obj['dn']);
                $this->setTdid($user_obj['department']['tdid']);
                
                $department_obj = json_decode(json_encode($user_obj['department']), FALSE);
                $this->setDepartment(new Department($department_obj));
                
                $singer = new Hmac\Sha256();
                $token = (new JWT\Parser())->parse($user_obj['token']['access_token']);               
                $this->setToken($token);
                return;               
            }
            
            if ($from_dn) {
                $this->setAccount((!empty($user_obj['samaccountname'])) ? $user_obj['samaccountname'][0] : "");
                $this->setName((!empty($user_obj['displayname'])) ? $user_obj['displayname'][0] : "");
                $this->setMail((!empty($user_obj['mail'])) ? $user_obj['mail'][0] : "");
                $this->setTitle((!empty($user_obj['title'])) ? $user_obj['title'][0] : "");
                $this->setPhone((!empty($user_obj['ext'])) ? $user_obj['ext'][0] : "");
                $this->setSex((!empty($user_obj['sex'])) ? $user_obj['sex'][0] : "");
                $this->setRole((!empty($user_obj['svuserrole'])) ? $user_obj['svuserrole'][0] : 0);
                $this->setEnabled((!empty($user_obj['logindisabled'])) ? $user_obj['logindisabled'][0] : true);
                $this->setDn($user_obj['dn']);
                $this->setTdid((!empty($user_obj['department'])) ? $user_obj['department'][0] : "000000", true);
                return;
            }           
            $this->setAccount($user_obj[0]);
            $this->setName($user_obj[1]);
            $this->setMail($user_obj[2]);
            $this->setTitle($user_obj[3]);
            $this->setPhone($user_obj[4]);
            $this->setSex($user_obj[5]);
            $this->setRole($user_obj[6]);
            $this->setEnabled($user_obj[7]);
            $this->setTdid($user_obj[8], true);
        }
    }
    
    public static function createToken($user, $secret) {
        $now = time();
        $signer = new Hmac\Sha256();    // HS256

        $token = (new JWT\Builder())->setIssuer('api.secret.taichung.gov.tw')
                                    ->setIssuedAt($now)
                                    ->setExpiration($now + 3600)
                                    ->sign($signer, $secret)
                                    ->getToken();
        return $token;
    }   

    public function refreshToken() {
        $token = User::createToken($this, env('TOKEN_SECRET'));
        $this->setToken($token);
        $result = DB::insert("INSERT INTO `tccg_user` (`time`, `account`, `token`) 
                              VALUES (:time, :account, :token)", 
                             [time(), $this->account, (string) $this->token]);
        return $this->getTokenInfo();
    }
    
    public function setAccount($value) {
        $this->account = $value;
    }
    public function getAccount() {
        return $this->account;
    }
    
    public function setName($value) {
        $this->name = $value;
    }
    public function getName() {
        return $this->name;
    }
    
    public function setMail($value) {
        $this->mail = $value;
    }
    public function getMail() {
        return $this->mail;
    }
    
    public function setTitle($value) {
        $this->title = $value;
    }
    public function getTitle() {
        return $this->title;
    }
    
    public function setPhone($value) {
        $this->phone = $value;
    }
    public function getPhone() {
        return $this->phone;
    }
    
    public function setSex($value) {
        if (($value == 'M') || ($value == 'Male'))
            $this->sex = 'Male';
        else if (($value == 'F') || ($value == 'Female'))
            $this->sex = 'Female';
        else
            $this->sex = 'Unknown';
    }
    public function getSex() {
        return $this->sex;
    }
    
    public function setRole($value) {
        switch ($value) {
            case 1:
            case 'Formal':
                $this->role = 'Formal';
                break;
                
            case 2:
            case 'Contract':
                $this->role = 'Contract';
                break;
                
            case 3:
            case 'Assistant':
                $this->role = 'Assistant';
                break;
                
            default:
                $this->role = 'Unknown';
        }
    }
    public function getRole() {
        return $this->role;
    }
    
    public function setEnabled($value) {
        if ($value == 'FALSE')
            $this->enabled = true;
        else
            $this->enabled = false;
    }
    public function getEnabled() {
        return $this->enabled;
    }
    
    public function setDn($value) {
        $this->dn = $value;
    }
    public function getDn() {
        return $this->dn;
    }
    
    public function setTdid($value, $by_ou=false) {
        if ($by_ou) {
            $department = Department::findDepartmentByOu($value);
            
            if ($department != null) {
                $this->setDepartment($department);
                $this->tdid = $department->getTdid();
            }
            
            $this->tdid = 0;
        }
        
        $this->tdid = $value;
    }
    public function getTdid() {
        return $this->tdid;
    }
    
    public function setToken($value) {
        $this->token = $value;
    }
    public function getToken() {
        return $this->token;
    }
    public function getTokenInfo() {
        $token = $this->getToken();
        
        if ($token != null) {
            $info = array();
            $info['access_token'] = (string) $token;
            $info['token_type'] = 'Bearer';
            $info['expires_in'] = $token->getClaim('exp');
        }
        return $info;
    }       

    public function setDepartment($value) {
        $this->department = $value;
    }
    public function getDepartment() {
        return $this->department;
    }

    public function jsonSerialize($unsetKeys=null) {
        $data = [
            'account' => $this->account, 
            'name' => $this->name, 
            'mail' => $this->mail, 
            'title' => $this->title, 
            'phone' => $this->phone, 
            'sex' => $this->sex, 
            'role' => $this->role, 
            'enabled' => $this->enabled, 
            'dn' => $this->dn, 
            'department' => $this->department,
            'token' => $this->getTokenInfo()
        ];

        if ($unsetKeys != null) {
            foreach ($unsetKeys as $unsetKey)
                unset($data[$unsetKey]);
        }
        
        return $data;
    }
}
