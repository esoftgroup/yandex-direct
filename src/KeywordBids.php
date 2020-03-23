<?php

namespace Esoftgroup\YandexDirect;

class KeywordBids extends Config
{
    public function getKeywordBids($filter = array())
    {
        //print_r($filter);
        $campaign_ids_filter = array();
        $adgroup_ids_filter = array();

        if(!empty($filter['campaignsIds']))
            $campaign_ids_filter = (array)$filter['campaignsIds'];

        if(!empty($filter['adgroupsIds']))
            $adgroup_ids_filter = (array)$filter['adgroupsIds'];

        //--- Входные данные ----------------------------------------------------//
        // Адрес сервиса Campaigns для отправки JSON-запросов (регистрозависимый)
        $url = $this->api_url.'keywordbids';

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

        // Параметры запроса к серверу API Директа
        $params = array(
            'method' => 'get',                                 // Используемый метод сервиса Campaigns
            'params' => array(
                'SelectionCriteria' => (object) array(
                    'CampaignIds' => $campaign_ids_filter,
                    'AdGroupIds' => $adgroup_ids_filter,
                ), // Критерий отбора кампаний. Для получения всех кампаний должен быть пустым
                //'FieldNames' => array("KeywordId", "AdGroupId", "CampaignId", "ServingStatus", "Bid", "ContextBid", "StrategyPriority", "CompetitorsBids", "SearchPrices", "ContextCoverage", "MinSearchPrice", "CurrentSearchPrice", "AuctionBids")             // Названия параметров, которые требуется получить
                'FieldNames' => array("KeywordId", "AdGroupId", "CampaignId", "ServingStatus", "StrategyPriority"),             // Названия параметров, которые требуется получить
                'SearchFieldNames' => array("Bid", "AuctionBids"),             // Названия параметров, которые требуется получить
                'NetworkFieldNames' => array("Bid", "Coverage"),             // Названия параметров, которые требуется получить
                // Выборка
                'Page' => array(
                    'Limit' => 10000,
                    'Offset' => 0,
                ),
            ),
        );

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
        $bids = json_decode($result, true);

        return $bids['result']['KeywordBids'];
    }
}


