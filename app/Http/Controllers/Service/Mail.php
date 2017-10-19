<?php

namespace App\Http\Controllers\Service;

use Illuminate\Support\Facades\DB;

/**
 * 邮件相关服务
 *
 * @author Romeo
 */
class Mail
{
    /**
     * 发送邮件
     * @param string $recipient
     * @param string $subject
     * @param string $content
     * @return mixed
     */
    public function send (string $recipient, string $subject, string $content)
    {
        return Tool::sendRequest(env("MAIL_HOST"), "POST", array(
                    "apiUser"   => env("MAIL_USER"),
                    "apiKey"    => env("MAIL_PASS"),
                    "from"      => "掘金翻译计划<fanyi@juejin.im>",
                    "to"        => $recipient,
                    "subject"   => $subject,
                    "html"      => $content
                ));
    }

    /**
     * 发送译者申请结果通知邮件
     * @param int $id
     * @return mixed
     */
    public function applicant (int $id)
    {
        $applicant = DB::table("applicant")
                        ->select("email", "invitation")
                        ->where("id", $id)
                        ->first();
        
        $subject = "译者申请结果通知 - 掘金翻译计划";
        $recipient = $applicant->email;
        $content = view("mails/applicant", ["invitation" => $applicant->invitation])->render();

        return $this->send($recipient, $subject, $content);
    }

    /**
     * 发送译文推荐结果通知邮件
     * @param int $id
     * @return mixed
     */
    public function recommend (int $id)
    {
        $article = DB::table("recommend")
                    ->join("user", "user.id", "=", "recommend.recommender")
                    ->select("recommend.title", "recommend.comment", "recommend.status", "user.email")
                    ->where("recommend.id", $id)
                    ->first();

        $subject = "文章推荐结果通知 － 掘金翻译计划";
        $recipient = $article->email;
        $content = view("mails/recommend", ["article" => $article])->render();

        return $this->send($recipient, $subject, $content);
    }
}