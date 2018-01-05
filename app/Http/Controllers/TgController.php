<?php

namespace App\Http\Controllers;

use App\Http\Model\Result;
use App\Http\Model\tg\Gazette;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class TgController extends Controller
{
    public function getGazettes(Request $request) {
        if (!Redis::command('EXISTS', ['gazettes']))
            $this->refresh($request);

        $gazettes = Redis::get('gazettes');
        return response($gazettes)->header('Content-Type', 'application/json');
    }
    
    public function refresh(Request $request) {
        Redis::command('DEL', ['gazettes']);
        
        $gazettes = Gazette::getGazettes();
        $data = json_encode($gazettes);
        Redis::set('gazettes', $data);

        $result = new Result(Result::SUCCESS);
        $result->addInfo('公報資料刷新成功');
        return response()->json($result);
    }

    public function uploadFile(Request $request, $gid) {
        $url = sprintf('/tg/gazettes/%d.pdf', $gid);

        $gazette = Gazette::find($gid);
        if ($gazette == null) {
            $result = new Result(Result::FAILURE);
            $result->addInfo('無此公報資料');
        } else {
            // 驗證檔案及移動檔案
            if ($request->hasFile('gazette')) {
                $file = $request->file('gazette');
                if ($file->getMimeType() == 'application/pdf') {
                    if (Storage::exists($url)) {
                        $result = new Result(Result::FAILURE);
                        $result->addInfo('目標檔案已經存在');
                    } else {
                        $file->move(storage_path('app'). '/tg/gazettes/', $gid. '.pdf');

                        $result = new Result(Result::SUCCESS);
                        $result->addInfo('檔案: '. $file->getClientOriginalName() .', 已經成功上傳');
                    }
                } else {
                    $result = new Result(Result::FAILURE);
                    $result->addInfo('目標檔案格式有錯誤');
                }
            }
        }

        // 更新資料庫
        if ($result->getStatus() == Result::SUCCESS) {
            DB::update("UPDATE `tg_gazette` SET `uri`=:uri
                        WHERE `gid`=:gid", [
                            'uri' => $gid. '.pdf', 
                            'gid' => $gid
                        ]);

            $this->refresh($request);
        }

        return response()->json($result);
    }

    public function downloadFile(Request $request, $gid) {
        $url = sprintf('/tg/gazettes/%d.pdf', $gid);

        $gazette = Gazette::find($gid);
        if ($gazette == null) {
            $result = new Result(Result::FAILURE);
            $result->addInfo('無此公報資料');
        } else if (!$gazette->getEnabled()) {
            $result = new Result(Result::FAILURE);
            $result->addInfo('此公報沒有開放');
            $result->addInfo('為符合「個人資料保護法」之規範，已關閉自101年10月1日前資料，敬請見諒！');
        } else {
            if (Storage::exists($url)) {
                // 更新下載次數

                return response()->download(storage_path('app'). $url, $gazette->getGazette() .'.pdf');
            } else {
                $result = new Result(Result::FAILURE);
                $result->addInfo('存取公報檔案失敗');
                $result->addInfo('請聯繫本系統管理人員(分機11121)');
            }
        }

        return response()->json($result);
    }

    public function deleteFile(Request $request, $gid) {
        $url = sprintf('/tg/gazettes/%d.pdf', $gid);
        
        $gazette = Gazette::find($gid);
        if ($gazette == null) {
            $result = new Result(Result::FAILURE);
            $result->addInfo('無此公報資料');
        } else {
            if (Storage::exists($url)) {
                Storage::delete($url);

                DB::update("UPDATE `tg_gazette` SET `uri`=:uri
                            WHERE `gid`=:gid", [
                                'uri' => '', 
                                'gid' => $gid
                            ]);

                $this->refresh($request);
            }

            $result = new Result(Result::SUCCESS);
            $result->addInfo('檔案已經刪除');
            return response()->json($result);
        }
    }
}