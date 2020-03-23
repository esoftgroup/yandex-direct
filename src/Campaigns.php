<?php

namespace Esoftgroup\YandexDirect;

class Campaigns extends Config
{
    public function getCampaigns()
    {
        //--- Входные данные ----------------------------------------------------//
        // Адрес сервиса Campaigns для отправки JSON-запросов (регистрозависимый)
        $url = $this->api_url.'campaigns';

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
                'SelectionCriteria' => (object) array(), // Критерий отбора кампаний. Для получения всех кампаний должен быть пустым
                'FieldNames' => array("BlockedIps", "ExcludedSites", "Currency", "DailyBudget", "Notification", "EndDate", "Funds", "ClientInfo", "Id", "Name", "NegativeKeywords", "RepresentedBy", "StartDate", "Statistics", "State", "Status", "StatusPayment", "StatusClarification", "SourceId", "TimeTargeting", "TimeZone", "Type"),             // Названия параметров, которые требуется получить
                'TextCampaignFieldNames' => array("CounterIds", "RelevantKeywords", "Settings", "BiddingStrategy", "PriorityGoals"),
                // Выборка
                'Page' => array(
                    'Limit' => 10000,
                    'Offset' => 0,
                ),
            )
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
        $campaigns = json_decode($result, true);

        if($campaigns['error']) {
            return $campaigns['error']['error_code']." - ".$campaigns['error']['error_detail'].": ".$campaigns['error']['error_string'];
        } else {
            return $campaigns['result']['Campaigns'];
        }
    }
}
