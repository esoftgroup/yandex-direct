<?php

namespace Esoftgroup\YandexDirect;

class Ads extends Config
{
    public function getAds($filter = array())
    {
        $campaign_ids_filter = array();
        $adgroup_ids_filter = array();

        if(!empty($filter['campaignsIds']))
            $campaign_ids_filter = (array)$filter['campaignsIds'];

        if(!empty($filter['adgroupsIds']))
            $adgroup_ids_filter = (array)$filter['adgroupsIds'];

        //--- Входные данные ----------------------------------------------------//
        // Адрес сервиса Campaigns для отправки JSON-запросов (регистрозависимый)
        $url = $this->api_url.'ads';

        // OAuth-токен пользователя, от имени которого будут выполняться запросы
        $token = $this->access_token;
        // Логин клиента рекламного агентства
        // Обязательный параметр, если запросы выполняются от имени рекламного агентства
        $clientLogin = $this->client_login;

        //--- Подготовка и выполнение запроса -----------------------------------//
        // Установка HTTP-заголовков запроса
        $headers = array(
            "Authorization: Bearer $token",                    // OAuth-токен. Использование слова Bearer обязательно
            "Client-Login: $clientLogin",                      // Логин клиента рекламного агентства
            "Accept-Language: ru",                             // Язык ответных сообщений
            "Content-Type: application/json; charset=utf-8"    // Тип данных и кодировка запроса
        );

        //print_r($headers);

        // Параметры запроса к серверу API Директа
        $params = array(
            'method' => 'get',                                  // Используемый метод сервиса Campaigns
            'params' => array(
                'SelectionCriteria' => (object) array(
                    'CampaignIds' => $campaign_ids_filter,
                    'AdGroupIds' => $adgroup_ids_filter,
                ), // Критерий отбора кампаний. Для получения всех кампаний должен быть пустым
                'FieldNames' => array("AdCategories", "AgeLabel", "AdGroupId", "CampaignId", "Id", "State", "Status", "StatusClarification", "Type", "Subtype"),             // Названия параметров, которые требуется получить
                'TextAdFieldNames' => array("Title", "Title2", "Text", "Href", "Mobile", "DisplayDomain", "DisplayUrlPath", "DisplayUrlPathModeration", "VCardId", "VCardModeration", "SitelinkSetId", "SitelinksModeration", "AdImageHash", "AdImageModeration", "AdExtensions", "VideoExtension"),
                'MobileAppAdFieldNames' => array("Title", "Text", "Features", "Action", "AdImageHash", "AdImageModeration", "TrackingUrl"),
                // Выборка
                'Page' => array(
                    'Limit' => 10000,
                    'Offset' => 0,
                ),
            )
        );

        $ads = $this->request($headers, $url, $params);
        return $ads['result']['Ads'];
    }

    private function request($headers, $url, $params) {

        // Преобразование входных параметров запроса в формат JSON
        $body = json_encode($params);

        // Создание контекста потока: установка HTTP-заголовков и тела запроса
        $streamOptions = stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                'header' => $headers,
                'content' => $body
            ),
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
            ),
        ));

        // Выполнение запроса, получение результата
        $result = file_get_contents($url, 0, $streamOptions);
        $ads = json_decode($result, true);

        return $ads;
    }
}


