<?php

namespace App\Http\Model;

use JsonSerializable;

use Lcobucci\JWT;
use Lcobucci\JWT\Signer\Hmac;
use Illuminate\Support\Facades\DB;

class User implements JsonSerializable {
    private $uid;
    private $username;
    private $password;
    private $name;
    private $token;     // JWT Bearer Token

    public function __construct($user_obj=null) {
        if ($user_obj != null) {
            $this->setUid($user_obj->uid);
            $this->setUsername($user_obj->username);
            $this->setPassword($user_obj->password);
            $this->setName($user_obj->name);

            if ($user_obj->token != null) {
                $token = (new JWT\Parser())->parse($user_obj->token);
                $this->setToken($token);
            }
        }
    }

    public function refreshToken() {
        $token = User::createToken($this, env('TOKEN_SECRET'));
        $this->setToken($token);
        $result = DB::update("UPDATE `user` SET `token` = ? WHERE `uid`=?", 
                             [(string) $this->token, $this->uid]);
        return $this->getTokenInfo();
    }

    public static function insert($user_arr) {
        $result = DB::insert("INSERT INTO `user` (`username`, `password`, `name`, `token`) 
                              VALUES (:username, :password, :name, :token)", $user_arr);
        return $result;
    }

    public static function getUsers() {
        $result = DB::select("SELECT * FROM `user` ORDER BY `uid` ASC");

        if (empty($result))
            return null;
        else {
            $users = array();
            foreach ($result as $user)
                $users[] = new User($user);

            return $users;
        }
    }

    public static function find($uid=null, $username=null) {
        if ($uid != null && $username == null) {
            $result = DB::select("SELECT * FROM `user` WHERE `uid`=:uid LIMIT 1", 
                                ['uid' => $uid]);
        }
        else if ($uid == null && $username != null) {
            $result = DB::select("SELECT * FROM `user` WHERE `username` LIKE :username LIMIT 1", 
                                ['username' => $username]);
        }

        if (empty($result))
            return null;
        else
            return new User($result[0]);
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

    public function setUid($value) {
        $this->uid = $value;
    }
    public function getUid() {
        return $this->uid;
    }

    public function setUsername($value) {
        $this->username = $value;
    }
    public function getUsername() {
        return $this->username;
    }

    public function setPassword($value) {
        $this->password = $value;
    }
    public function getPassword() {
        return $this->password;
    }

    public function setName($value) {
        $this->name = $value;
    }
    public function getName() {
        return $this->name;
    }

    public function setToken($value) {
        $this->token = $value;
    }
    public function getToken() {
        return $this->token;
    }
    public function getTokenInfo() {
        $token = $this->getToken();

        $info = array();
        $info['access_token'] = (string) $token;
        $info['token_type'] = 'Bearer';
        $info['expires_in'] = $token->getClaim('exp');

        return $info;
    }

    public function jsonSerialize($unsetKeys=null) {
        $data = [
            'uid' => $this->uid, 
            'username' => $this->username, 
            'password' => $this->password, 
            'name' => $this->name, 
            'token' => (string) $this->token
        ];

        if ($unsetKeys != null) {
            foreach ($unsetKeys as $unsetKey)
                unset($data[$unsetKey]);
        }

        return $data;
    }
}
