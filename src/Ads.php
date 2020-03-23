<?php

namespace Esoftgroup\YandexDirect;

class ads extends Config
{
    public function get_ads($filter = array())
    {
        $campaign_ids_filter = array();
        $adgroup_ids_filter = array();

        if(!empty($filter['campaigns_ids']))
            $campaign_ids_filter = (array)$filter['campaigns_ids'];

        if(!empty($filter['adgroups_ids']))
            $adgroup_ids_filter = (array)$filter['adgroups_ids'];

        //--- Входные данные ----------------------------------------------------//
        // Адрес сервиса Campaigns для отправки JSON-запросов (регистрозависимый)
        $url = $this->api_url.'ads';

        // Запрос логина
        $data_connect = $this->sourcedata->get_data_connect(array('account_id' => $filter['account_id'], 'type' => 'direct'));
        //print_r($data_connect);

        // OAuth-токен пользователя, от имени которого будут выполняться запросы
        $token = $this->access_token;
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

        //print_r($headers);

        // Параметры запроса к серверу API Директа
        $params = array(
            'method' => 'get',                                 // Используемый метод сервиса Campaigns
            'params' => array(
                'SelectionCriteria' => (object) array(
                    'CampaignIds' => $campaign_ids_filter,
                    'AdGroupIds' => $adgroup_ids_filter,
                    //'Statuses' => array('PREACCEPTED', 'ACCEPTED'),
                ),        // Критерий отбора кампаний. Для получения всех кампаний должен быть пустым
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

        //print_r($params);

        $ads = $this->request($headers, $url, $params);

        //print_r($ads);

        return $ads['result']['Ads'];
    }

    private function request($headers, $url, $params) {

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
        //print_r(json_decode($result, true));

        $ads = json_decode($result, true);

        return $ads;
    }
}


