<?php

namespace App\Http\Controllers;

use App\Http\Model\Result;
use App\Http\Model\vote\Candidate;
use App\Http\Model\vote\Voting;
use App\Http\Model\vote\Session;
use Illuminate\Support\Facades\DB;

use Illuminate\Http\Request;

class VoteController extends Controller
{
    public function getVotings(Request $request, $tccg_account) {
        $result = DB::select("SELECT * FROM `vote_voting` 
                              WHERE `tccg_account` LIKE :tccg_account", 
                              ['tccg_account' => $tccg_account]);
        return $result;
    }
    
    public function getResult(Result $request) {
        $result = DB::select("SELECT COUNT(*) AS `result`, 
                                     `vote_candidate`.`vcid`,
                                     `vote_candidate`.`candidate`
                              FROM `vote_voting` LEFT JOIN `vote_candidate` 
                              ON `vote_candidate`.`vcid`=`vote_voting`.`vcid` 
                              GROUP BY `vote_voting`.`vcid` 
                              ORDER BY `result` DESC, `vcid` ASC");
        return $result;
    }
    
    public function getSessions(Request $request, $vsid) {
        $result = DB::select("SELECT * FROM `vote_session` 
                              WHERE `vsid`=:vsid ", 
                              ['vsid' => $vsid]);
        return $result;
    }
    
    public function setSessionTime(Request $request, $vsid, $type) {
        $year   = $request->input('year');
        $month  = $request->input('month');
        $day    = $request->input('day');
        $hour   = $request->input('hour');
        $minute = $request->input('minute');
        $second = $request->input('second');
        
        $time = mktime($hour, $minute, $second, $month, $day, $year);
        
        if ($type == 'start_time') {
            $result = DB::update("UPDATE `vote_session` 
                                  SET `start_time`=:time 
                                  WHERE `vsid`=:vsid", 
                                  [
                                      'time' => $time, 
                                      'vsid' => $vsid
                                  ]);               
            
            $result = new Result(Result::SUCCESS);
            $result->addInfo("設定投票開始時間完成！");
            $result->setData(date(DATE_ATOM, $time));
        } else if ($type == 'end_time') {
            $result = DB::update("UPDATE `vote_session` 
                                  SET `end_time`=:time 
                                  WHERE `vsid`=:vsid", 
                                  [
                                      'time' => $time, 
                                      'vsid' => $vsid
                                  ]);               
            $result = new Result(Result::SUCCESS);
            $result->addInfo("設定投票結束時間完成！");
            $result->setData(date(DATE_ATOM, $time));
        }
        
        return $result;
    }
    
    public function getCandidates(Request $request) {
        $result = DB::select("SELECT * FROM `vote_candidate` ORDER BY `vcid` ASC");
        return $result;
    }
    
    public function insertVoting(Request $request, $vcid) {
        if (($vcid > 0) && ($request->has('tccg_account'))) {
            $tccg_account = $request->input('tccg_account'); 
            
            $result = $this->getVotings($request, $tccg_account);
            if (count($result) > 0) {
                $voting = new Voting($result[0]);
                if ($voting->getTccgAccount() == $tccg_account) {
                    $result = new Result(Result::FAILURE);
                    $result->addInfo("您已經投過票了！");
                    return $result;
                }
            }
            
            $result = $this->getSessions($request, 1);
            if (count($result) > 0) {
                $session = new Session($result[0]);
                
                $now = time();

                if ($now < $session->getStartTime()) {
                    $result = new Result(Result::FAILURE);
                    $result->addInfo("投票尚未開始！");               
                    return $result;
                }
            
                if ($now > $session->getEndTime()) {
                    $result = new Result(Result::FAILURE);
                    $result->addInfo("您已經超過投票時間！");
                    return $result;
                }
            }
          
            DB::insert("INSERT INTO `vote_voting`(`tccg_account`, `vcid`, `create_time`)
                        VALUES (:tccg_account, :vcid, :create_time)", 
                        [
                            'tccg_account' => $tccg_account, 
                            'vcid' => $vcid, 
                            'create_time' => $now
                        ]);
                
            $result = new Result(Result::SUCCESS);
            $result->addInfo("您已經投票完成！");
        } else {
            $result = new Result(Result::FAILURE);
            $result->addInfo("您輸入的參數有錯誤！");
        }
        
        return $result;
    }
}
