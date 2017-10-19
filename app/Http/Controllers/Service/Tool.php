<?php

namespace App\Http\Controllers\Service;

class Tool
{
    /**
     * 创建并发送 HTTP 请求
     * @param string $url
     * @param string $method
     * @param array $params
     * @param array $header
     * @return mixed
     */
    public static function sendRequest(string $url, string $method = "GET", array $params = array(), array $header = array())
    {
        $options = array(
                CURLOPT_USERAGENT       => "fanyi.juejin.im",
                CURLOPT_HTTPHEADER      => $header,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_TIMEOUT         => 10
            );
        
        if (strtoupper($method) === "POST") {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($params);
            $options[CURLOPT_URL] = $url;
        } else {
            $options[CURLOPT_URL] = $url . "?" . http_build_query($params);
        }

        $sr = curl_init();
        curl_setopt_array($sr, $options);
        $ret = curl_exec($sr);
        curl_close($sr);

        return $ret;
    }

    /**
     * 生成 JWT
     * @param int $user
     * @param bool $advance
     * @param bool $admin
     * @return string
     */
    public static function generateToken(int $user, bool $advance = false, bool $admin = false)
    {
        $salt = sha1("^UNo98ER3!0lYj|v3M=-BjVvOdMLP}iD");

        $header = base64_encode(json_encode([
            "type"      => "jwt",
            "algorithm" => "md5"
        ]));

        $payload = base64_encode(json_encode([
            "user"      => $user,
            "admin"     => $admin,
            "advance"   => $advance,
            "expiry"    => time() + 3600
        ]));

        $signature = md5($header . $payload . $salt);

        return $header . "." . $payload . "." . $signature;
    }
}
