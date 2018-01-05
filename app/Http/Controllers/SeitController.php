<?php

namespace App\Http\Controllers;

use App\Http\Model\Result;
use App\Http\Model\seit\Mail;
use App\Http\Model\seit\MailUserAgent;
use App\Http\Model\seit\Institution;
use App\Http\Model\seit\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Lcobucci\JWT;
use Lcobucci\JWT\Signer\Hmac;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class SeitController extends Controller
{
    public function getRecipients($did) {
        $result = DB::select("SELECT * FROM `seit_recipient` 
                              WHERE `did`=:did ORDER BY `rid`", ['did' => $did]);
        return $result;
    }

    public function getMails(Request $request) {
        $result = DB::select("SELECT * FROM `seit_mail` 
                              LEFT JOIN `seit_mail_template` ON `seit_mail_template`.`mtid`=`seit_mail`.`mtid` 
                              LEFT JOIN `seit_sender` ON `seit_sender`.`sid`=`seit_mail`.`sid` 
                              LEFT JOIN (`seit_recipient` 
                                LEFT JOIN (`seit_department` 
                                    LEFT JOIN `seit_institution` 
                                    ON `seit_institution`.`iid`=`seit_department`.`iid`
                                ) ON `seit_department`.`did`=`seit_recipient`.`did`
                              ) ON `seit_recipient`.`rid`=`seit_mail`.`rid` 
                              ORDER BY `mid`");
        
        $showTree = $request->input('showTree') == 'true';
        if (!$showTree) 
            return response()->json($result);
        else {
            $mails = array();
            foreach ($result as $mail) {
                if (!key_exists($mail->iid, $mails))
                    $mails[$mail->iid] = new Institution($mail);
                $institution = $mails[$mail->iid];

                if (!key_exists($mail->did, $institution->getDepartments()))
                    $institution->addDepartment(new Department($mail), $mail->did);
                $department = $institution->getDepartment($mail->did);

                $department->addMail(new Mail($mail), $mail->mid);
            }

            $mails = array_values($mails);
            foreach ($mails as $institution)
                $institution->recursiveRemoveKeys();

            return response()->json($mails);
        }
    }

    public function getMail($mid) {
        $result = DB::select("SELECT * FROM `seit_mail` 
                              LEFT JOIN `seit_mail_template` ON `seit_mail_template`.`mtid`=`seit_mail`.`mtid` 
                              LEFT JOIN `seit_sender` ON `seit_sender`.`sid`=`seit_mail`.`sid` 
                              LEFT JOIN `seit_recipient` ON `seit_recipient`.`rid`=`seit_mail`.`rid` 
                              WHERE `mid`=:mid 
                              ORDER BY `mid`", ['mid' => $mid]);
        return new Mail($result[0]);
    }

    public function insertMails(Request $request, $did) {
        if ($request->has('mtid') && $request->has('sid')) {
            $mtid = $request->input('mtid');
            $sid = $request->input('sid');
        }

        $recipients = $this->getRecipients($did);

        $mail = new Mail();
        $unsetKeys = ['mid', 'mail_template', 'sender', 'recipient'];
        $mail->setCreateTime(time());
        $mail->setDeliveryTime(0);
        $mail->setFailTime(0);
        $mail->setMailStatus('ready');
        $mail->setTestStatus('unknown');
        $mail->setMtid($mtid);
        $mail->setSid($sid);
        //$mail->setRid(0);

        DB::beginTransaction();
        foreach ($recipients as $recipient) {
            $mail->setRid($recipient->rid);
            
            DB::insert("INSERT INTO `seit_mail`(`create_time`, `delivery_time`, `fail_time`, 
                                                `mail_status`, `test_status`, 
                                                `mtid`, `sid`, `rid`)
                        VALUES (:create_time, :delivery_time, :fail_time, 
                                :mail_status, :test_status, 
                                :mtid, :sid, :rid)", $mail->jsonSerialize($unsetKeys));
        }
        DB::commit();

        return '郵件創造成功';
    }

    public function updateMail(Request $request, $mid) {
        if ($request->has('mail_status')) {
            $delivery_time = $request->input('delivery_time');
            $mail_status = $request->input('mail_status');

            DB::update("UPDATE `seit_mail` SET `delivery_time`=:delivery_time, 
                                               `mail_status`=:mail_status 
                        WHERE `mid`=:mid", [
                            'delivery_time' => $delivery_time, 
                            'mail_status' => $mail_status, 
                            'mid' => $mid
                        ]);
        } else if ($request->has('test_status')) {
            $test_status = $request->input('test_status');

            if (($test_status == 'fail') && ($request->has('fail_time'))) {
                $fail_time = $request->input('fail_time');
                DB::update("UPDATE `seit_mail` SET `fail_time`=:fail_time, 
                                                   `test_status`=:test_status 
                            WHERE `mid`=:mid", [
                                'fail_time' => $fail_time, 
                                'test_status' => $test_status, 
                                'mid' => $mid
                            ]);
            } else if ($test_status == 'correct') {
                DB::update("UPDATE `seit_mail` SET `test_status`=:test_status 
                            WHERE `mid`=:mid", [
                                'test_status' => $test_status, 
                                'mid' => $mid
                            ]);
            }
        }

        return $this->getMail($mid);
    }

    public function insertMailUserAgent($mid, $fail_time, $agent) {
        DB::insert("INSERT INTO `seit_mail_user_agent`(`mid`, `fail_time`, `user_agent`, `platform`, `device_type`, `browser_name_pattern`)
                    VALUES (:mid, :fail_time, :user_agent, :platform, :device_type, :browser_name_pattern)", 
                    [
                        'mid' => $mid, 
                        'fail_time' => $fail_time, 
                        'user_agent' => $agent['parent'], 
                        'platform' => $agent['platform'], 
                        'device_type' => $agent['device_type'], 
                        'browser_name_pattern' => $agent['browser_name_pattern']
                    ]);
    }

    public function getMailUserAgents(Request $request, $mid) {
        $result = DB::select("SELECT * FROM `seit_mail_user_agent` WHERE `mid`=:mid ORDER BY `fail_time`", 
                            ['mid' => $mid]);
        $mail_user_agents = array();
        foreach ($result as $mail_user_agent) 
            $mail_user_agents[] = new MailUserAgent($mail_user_agent);

        return response()->json($mail_user_agents);
    }

    public function sendMail(Request $request, $mid) {
        $mail = $this->getMail($mid)->jsonSerialize();
        $sender = $mail['sender']->jsonSerialize();
        $recipient = $mail['recipient']->jsonSerialize();
        $mail_template = $mail['mail_template']->jsonSerialize();
        
        $mailer = new PHPMailer(true);
        try {
            $mailer->CharSet = "utf-8";
            $mailer->Encoding = "base64";

            $mailer->isSMTP();
            //$mailer->SMTPDebug = 3;

            $mailer->Host = $sender['host'];
            $mailer->Port = $sender['port'];

            $mailer->SMTPAuth = true;
            $mailer->Username = $sender['username'];
            $mailer->Password = $sender['password'];

            $mailer->isHTML(true);
            $mailer->setFrom($sender['addressor'], $sender['sender']);
            $mailer->addReplyTo($sender['reply_address'], $sender['reply_name']);
            if ($sender['confirm_reading'] != '')
                $mailer->ConfirmReadingTo = $sender['confirm_reading'];
            
            $mailer->addAddress($recipient['addressee'], $recipient['recipient']);

            $signer = new Hmac\Sha256();    // HS256
            $token = (new JWT\Builder())->set('mid', $mail['mid'])
                                        ->set('account', $recipient['account'])
                                        ->set('time', time())
                                        ->sign($signer, env('TOKEN_SECRET'))
                                        ->getToken();

            $mailer->Subject = $mail_template['subject'];
            $mailer->Body = str_replace("{jwt}", $token, $mail_template['body']);

            $mailer->send();

            $request->replace([
                'mail_status' => 'delivered', 
                'delivery_time' => time()
            ]);
            $newData = $this->updateMail($request, $mid);

            $result = new Result(Result::SUCCESS);
            $result->addInfo("Message Delivery to " . $recipient['addressee']);
            $result->setData($newData);
        } catch (Exception $e) {
            $request->replace([
                'mail_status' => 'failure', 
                'delivery_time' => time()
            ]);
            $newData = $this->updateMail($request, $mid);

            $result = new Result(Result::FAILURE);
            $result->addInfo("Message cound not be send.");
            $result->addInfo("Mailer Error: " . $mailer->ErrorInfo);
            $result->setData($newData);
        }

        return response()->json($result);
    }

    public function showImage(Request $request, $jwt) {
        $singer = new Hmac\Sha256();    // HS256
        $token = (new JWT\Parser())->parse($jwt);
        if (!$token->verify($singer, env('TOKEN_SECRET')))
            return response('Unauthorized.', 401);
        else {
            $account = $token->getClaim('account');
            $mid = $token->getClaim('mid');
            $fail_time = time();

            $agent = get_browser(null, true);

            $request->replace([
                'test_status' => 'fail', 
                'fail_time' => $fail_time
            ]);
            $this->updateMail($request, $mid);
            $this->insertMailUserAgent($mid, $fail_time, $agent);
        }

        $width          = 600;
        $height         = 200;

        $use_font       = true;

        $image          = imagecreate($width, $height);
        $background     = imagecolorallocate($image, 51, 51, 51);

        $text_color     = imagecolorallocate($image, 255, 255, 255);
        $word           = $account . "，您好！\n若您有看到此訊息，則表示郵件軟體設定尚未正確，\n我們將儘速前去為您設定，謝謝！\n您的郵件軟體偵測為： " . $agent['parent'] . " (" . $agent['platform'] . ")";
        $font_size      = 17;
        $font_path      = '../resources/fonts/msjh.ttc';
        $angle           = 0;

        if (!$use_font) {
            $font_width      = imagefontwidth($font_size) * strlen($word) / 2;
            $font_height     = imagefontheight($font_size) / 2;
        } else {
            $bbox            = imagettfbbox($font_size, $angle, $font_path, $word);
            $font_width      = $bbox[4] / 2;
            $font_height     = $bbox[5] / 2;
        }
        $x  = $width / 2 - $font_width;
        $y  = $height / 2 - $font_height - 50;

        if (!$use_font)
            imagestring($image, $font_size, $x, $y, $word, $text_color);
        else
            imagettftext($image, $font_size, $angle, $x, $y, $text_color, $font_path, $word);

        ob_start();
        imagepng($image);
        $content = (string) $image;
        imagedestroy($image);

        return response($content, 200)->header('Content-Type', 'image/png');
    }
}