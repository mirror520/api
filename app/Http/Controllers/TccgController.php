<?php

namespace App\Http\Controllers;

use App\Http\Model\Result;
use App\Http\Model\User as User;
use App\Http\Model\tccg\User as TccgUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use SoapClient;

class TccgController extends Controller
{
    public function login(Request $request) {
        if ($request->has('account') && $request->has('password')) {
            $client = new \SoapClient("http://eip.taichung.gov.tw/ldapService.do?wsdl");
            
            $parameter = array();
            $parameter['in0'] = $request->input('account');
            $parameter['in1'] = $request->input('password');
            $parameter['in2'] = 'sAMAccountName;displayName;mail;title;ext;sex;svuserrole;logindisabled;department';
            
            $handle = $client->getAttr($parameter);
            $result = $handle->out;
            
            try {
                $user = new TccgUser(explode(';', $result));
                $user->refreshToken();
                return response()->json($user);
            } catch (\Exception $e) {
                $result = new Result(Result::FAILURE);
                $result->addInfo('帳號或密碼錯誤');
                return response()->json($result)
                                 ->setStatusCode(401);
            }
        }
        
        $result = new Result(Result::FAILURE);
        $result->addInfo('未輸入帳號或密碼');
        return response()->json($result)
                         ->setStatusCode(401);
    }
    
    public function refreshToken(Request $request, $account) {
        $json = $request->json()->all();
        $user = new TccgUser($json, true);
        $info = $user->getTokenInfo();
        $exp = $info['expires_in'];
        $now = time();
        
        if ($exp - $now > 600) {
            $token = $user->refreshToken();
            return response()->json($token);
        } else {
            $result = new Result(Result::FAILURE);
            $result->addInfo('已超過刷新Token時間');
            return response()->json($result)
                             ->setStatusCode(401);
        }
    }
    
    public function getUserByAccount($account) {
        
    }
    
    public function getUsersByDirectory($dn, $ou) {
        $ds = ldap_connect(env('TCCG_LDAP_ADDRESS'));
        ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);
        $result = ldap_bind($ds, env('TCCG_LDAP_RDN'), env('TCCG_LDAP_PASSWORD'));
        
        $filter = sprintf("(&(ou=%s)(cn=*))", $ou);
        $attributes = array("sAMAccountName", 
                            "displayName", 
                            "mail", 
                            "title", 
                            "ext", 
                            "sex", 
                            "svuserrole", 
                            "logindisabled", 
                            "department");
        
        $result = ldap_search($ds, $dn, $filter, $attributes);
        $data = ldap_get_entries($ds, $result);
        
        $users = array();
        $count = $data['count'];
        for ($i=0; $i<$count; $i++) {
            if (empty($data[$i]['displayname']))
                continue;
                
            $user = new TccgUser($data[$i], false, true);
            $users[] = $user;
        }
        ldap_close($ds);
        
        return $users;
    }
    
    public function updateTccgUsers(Request $request) {
        $result = DB::select("SELECT `tccg_institution`.*, `tccg_department`.* 
                              FROM `tccg_institution` 
                              LEFT JOIN `tccg_department` 
                              ON `tccg_department`.`tiid`=`tccg_institution`.`tiid`
                              ORDER BY `tccg_institution`.`tiid`, 
                                       `tccg_department`.`tdid` ASC");
        
        $unsetKeys = ['tuid', 'department'];       
        foreach ($result as $value) {
            $users = $this->getUsersByDirectory($value->dn, $value->ou);
            
            DB::beginTransaction();   
            foreach ($users as $user) {
                DB::insert("INSERT INTO `tccg_user`(`account`, `name`, `mail`, 
                                                    `title`, `phone`, 
                                                    `sex`, `role`, `enabled`, 
                                                    `dn`, `tdid`)
                            VALUES (:account, :name, :mail, 
                                    :title, :phone, 
                                    :sex, :role, :enabled, 
                                    :dn, :tdid)", $user->jsonSerialize($unsetKeys));
            }
            DB::commit();
        }
        
        $result = new Result(Result::SUCCESS);
        $result->addInfo('TCCG 使用者資料刷新成功');
        return response()->json($result);
    }
    
    public function getDirectories(Request $request) {
        $dn = $request->input('dn');
        $ou = $request->input('ou');
        
        $users = $this->getUsersByDirectory($dn, $ou);
        
        return response()->json($users);
    }
}
