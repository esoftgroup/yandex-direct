<?php

namespace Esoftgroup\YandexDirect;

class AdGroups extends Config
{
    public function getAdGroups($filter = array())
    {
        $campaign_ids_filter = array();

        if(!empty($filter['campaignsIds']))
            $campaign_ids_filter = (array)$filter['campaignsIds'];

        //--- Входные данные ----------------------------------------------------//
        // Адрес сервиса Campaigns для отправки JSON-запросов (регистрозависимый)
        $url = $this->api_url.'adgroups';

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

        $campaign_ids_chunk = array_chunk($campaign_ids_filter, 10);

        //
        $result_array = array();
        foreach($campaign_ids_chunk as $campaign_ids_chunk_filter) {

            // Параметры запроса к серверу API Директа
            $params = array(
                'method' => 'get',                              // Используемый метод сервиса Campaigns
                'params' => array(
                    'SelectionCriteria' => (object) array(
                        'CampaignIds' => $campaign_ids_chunk_filter,
                    ), // Критерий отбора кампаний. Для получения всех кампаний должен быть пустым
                    'FieldNames' => array('Id', 'Name', 'CampaignId', 'Status', 'RegionIds', 'NegativeKeywords'),             // Названия параметров, которые требуется получить
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
            $adgroups = json_decode($result, true);

            if(isset($adgroups['error'])) {
                return $adgroups['error'];
            } else {
                foreach($adgroups['result']['AdGroups'] as $adgroup) {
                    array_push($result_array, $adgroup);
                }
            }
        }

        return $result_array;
    }
}


