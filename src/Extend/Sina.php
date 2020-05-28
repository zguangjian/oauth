<?php
/**
 * Created by PhpStorm.
 * User: Tjian
 * Date: 2019/5/9
 * Time: 21:31
 */

namespace zguangjian\Extend;

use zguangjian\Config;
use zguangjian\Events\OAuthInterface;
use zguangjian\Http;

class Sina extends OAuthInterface
{
    protected $payload;
    /**
     * 获取requestCode的api接口
     * @var string
     */
    protected $GetRequestCodeURL = 'https://api.weibo.com/oauth2/authorize';
    /**
     * 获取access_token的api接口
     * @var string
     */
    protected $GetAccessTokenURL = 'https://api.weibo.com/oauth2/access_token';
    /**
     * 获取用户基本信息api接口
     */
    protected $GetAccessUserInfo = 'https://api.weibo.com/2/users/show.json';

    public function __construct(Config $config)
    {
        $this->payload = [
            'AppKey' => $config->config['AppKey'],
            'AppSecret' => $config->config['AppSecret'],
            'Callback' => $config->config['Callback'],
            'Version' => $config->Version,
            'ResponseType' => $config->ResponseType,
            'GrantType' => $config->GrantType,
        ];

    }

    /**
     * @return mixed|string|void
     */
    public function getCode()
    {
        $params = [
            'client_id' => $this->payload['AppKey'],
            'redirect_uri' => $this->payload['Callback'],
            'response_type' => $this->payload['ResponseType'],
            'state' => md5(rand(1, 100))
        ];
        return Http::urlSplit($this->GetRequestCodeURL, $params);
    }

    public function getOpenId()
    {
        parent::getOpenId(); // TODO: Change the autogenerated stub
    }

    public function getToken(string $code)
    {
        $param = [
            'client_id' => $this->payload['AppKey'],
            'client_secret' => $this->payload['AppSecret'],
            'grant_type' => $this->payload['GrantType'],
            'redirect_uri' => $this->payload['Callback'],
            'code' => $code,
        ];
        $res = Http::request($this->GetAccessTokenURL, $param, 'POST');
        $res = json_decode($res);
        $this->payload['openId'] = $res->uid;
        $this->payload['accessToken'] = $res->access_token;
        return $this;
    }

    public function getUserInfo(string $code)
    {
        $this->getToken($code);
        $param = [
            'access_token' => $this->payload['accessToken'],
            'uid' => $this->payload['openId']
        ];
        $res = Http::request($this->GetAccessUserInfo, $param);
        $res = json_decode($res);
        $this->payload['userInfo'] = $res;
        return $this;

    }
}