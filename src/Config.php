<?php

namespace Esoftgroup\YandexDirect;

class Config
{
    public $api_url = 'https://api.direct.yandex.com/json/v5/';
    public $api_sandbox_url = 'https://api-sandbox.direct.yandex.com/json/v5/';
    public $access_token = '';
    public $client_login = '';

    public function getAccessToken() {
        return $this->access_token;
    }

    public function getClientLogin() {
        return $this->client_login;
    }

    public function setApiUrl($api_url = 'https://api.direct.yandex.com/json/v5/') {
        return $this->api_url = $api_url;
    }

    public function setAccessToken($access_token = '') {
        return $this->access_token = $access_token;
    }

    public function setClientLogin($client_login = '') {
        return $this->client_login = $client_login;
    }
}
