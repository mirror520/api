<?php

namespace App\Http\Controllers;

use App\Http\Model\User;
use App\Http\Model\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function login(Request $request) {
        if ($request->has('username') && $request->has('password')) {
            $user = User::find(null, $request->input('username'));
            if ($user == null) {
                $result = new Result(Result::FAILURE);
                $result->addInfo("沒有此使用者");
                return $result;           
            } else {
                if (password_verify($request->input('password'), $user->getPassword())) {
                    $info = $user->refreshToken();
                    return response()->json($info);
                } else {
                    $result = new Result(Result::FAILURE);
                    $result->addInfo("帳號或密碼錯誤");
                    return $result;
                }
            }
        }
        
        $result = new Result(Result::FAILURE);
        $result->addInfo("請輸入使用者帳號密碼");
        return $result;
    }

    public function register(Request $request) {
        if ($request->has('username') && $request->has('password') && $request->has('name')) {
            $user = new User();
            $unsetKeys = ['uid'];
            $user->setUsername($request->input('username'));
            $user->setPassword(password_hash($request->input('password'), PASSWORD_DEFAULT));
            $user->setName($request->input('name'));
            $user->setToken('');

            $user_arr = $user->jsonSerialize($unsetKeys);
            $result = User::insert($user_arr);
            if ($result) {
                $result = new Result(Result::SUCCESS);
                $result->addInfo("使用者註冊成功");
            } else {
                $result = new Result(Result::FAILURE);
                $result->addInfo("使用者註冊失敗");
            }
        }
        
        $result = new Result(Result::FAILURE);
        $result->addInfo("請輸入完整使用者訊息");       
        
        return $result;
    }

    public function getUsers(Request $request, $uid=null) {
        if ($uid != null) {
            $user = User::find($uid);
            return response()->json($user);
        } else {
            $users = User::getUsers();
            return response()->json($users);
        }
    }

    public function delete(Request $request, $uid) {
        $user = User::find($uid);
        if (!empty($user)) {
            DB::delete("DELETE FROM `user` WHERE `uid`=:uid", ['uid' => $uid]);
            return response()->json($user);
        }
    }
}