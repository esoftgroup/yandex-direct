<?php

/*
 * (c) YOUR NAME <your@email.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

// If you don't to add a custom vendor folder, then use the simple class
// namespace HelloComposer;
namespace Esoftgroup\YandexDirect;

class Campaigns extends Config
{
    public function get_campaigns($connect = array())
    {
        //--- Входные данные ----------------------------------------------------//
        // Адрес сервиса Campaigns для отправки JSON-запросов (регистрозависимый)
        $url = $this->api_url.'campaigns';
        
        // OAuth-токен пользователя, от имени которого будут выполняться запросы
        $token = $connect['access_token'];
        // Логин клиента рекламного агентства
        // Обязательный параметр, если запросы выполняются от имени рекламного агентства
        $clientLogin = $connect['client_login'];

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
                    //'States' => array('ON'),
                ),        // Критерий отбора кампаний. Для получения всех кампаний должен быть пустым
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
        //$body = json_encode($params, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
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
            // Для полноценного использования протокола HTTPS можно включить проверку SSL-сертификата сервера API Директа
            /*'ssl' => array(
               'verify_peer' => true,
               'cafile' => getcwd().DIRECTORY_SEPARATOR.'CA.pem' // Путь к локальной копии корневого SSL-сертификата
            )*/
        ));

        // Выполнение запроса, получение результата
        $result = file_get_contents($url, 0, $streamOptions);
        //print_r(json_decode($result, true));

        $campaigns = json_decode($result, true);
        //print_r($campaigns);

        if($campaigns['error']) {
            return $campaigns['error']['error_code']." - ".$campaigns['error']['error_detail'].": ".$campaigns['error']['error_string'];
        } else {
            return $campaigns['result']['Campaigns'];
        }
    }
}
