<?php

namespace Esoftgroup\YandexDirect;

class Keywords extends Config
{
    public function get_keywords($filter = array())
    {
        //print_r($filter);

        $campaign_ids_filter = array();
        $adgroup_ids_filter = array();

        if(!empty($filter['campaigns_ids']))
            $campaign_ids_filter = (array)$filter['campaigns_ids'];

        if(!empty($filter['adgroups_ids']))
            $adgroup_ids_filter = (array)$filter['adgroups_ids'];

        //--- Входные данные ----------------------------------------------------//
        // Адрес сервиса Campaigns для отправки JSON-запросов (регистрозависимый)
        $url = $this->api_url.'keywords';


        // OAuth-токен пользователя, от имени которого будут выполняться запросы
        $token = $data_connect['access_token'];
        // Логин клиента рекламного агентства
        // Обязательный параметр, если запросы выполняются от имени рекламного агентства
        $clientLogin = $data_connect['client_login'];

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
                    //'Ids' => array(),
                    'CampaignIds' => $campaign_ids_filter,
                    'AdGroupIds' => $adgroup_ids_filter,
                ), // Критерий отбора кампаний. Для получения всех кампаний должен быть пустым
                // Названия параметров, которые требуется получить
                'FieldNames' => array("Id", "Keyword", "State", "Status", "ServingStatus", "AdGroupId", "CampaignId", "Bid", "ContextBid", "StrategyPriority", "UserParam1", "UserParam2", "Productivity", "StatisticsSearch", "StatisticsNetwork"),
                // Выборка
                'Page' => array(
                    'Limit' => 10000,
                    'Offset' => 0,
                ),
            ),
        );
        //print_r($params);

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
            /*
            // Для полноценного использования протокола HTTPS можно включить проверку SSL-сертификата сервера API Директа
            'ssl' => array(
               'verify_peer' => true,
               'cafile' => getcwd().DIRECTORY_SEPARATOR.'CA.pem' // Путь к локальной копии корневого SSL-сертификата
            )
            */
        ));

        // Выполнение запроса, получение результата
        $result = file_get_contents($url, 0, $streamOptions);

        $keywords = json_decode($result, true);
        //print_r(json_decode($result, true));

        return $keywords['result']['Keywords'];
    }
}


