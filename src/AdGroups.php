<?php
/**
 * Created by E-Softgroup
 * Site: https://e-softgroup.ru
 * All rights reserved, this is app own by E-Softgroup
 * Any use of the application or part thereof without the consent of the copyright owner is prohibited.
 */

namespace Esoftgroup\YandexDirect;

class adgroups extends \Esoftgroup\YandexDirect\Config
{
    public function get_adgroups($filter = array())
    {
        //print_r($filter);

        $campaign_ids_filter = array();

        if(!empty($filter['campaigns_ids']))
            $campaign_ids_filter = (array)$filter['campaigns_ids'];

        //--- Входные данные ----------------------------------------------------//
        // Адрес сервиса Campaigns для отправки JSON-запросов (регистрозависимый)
        $url = $this->api_url.'adgroups';

        // Запрос логина
        $data_connect = $this->sourcedata->get_data_connect(array('account_id' => $filter['account_id'], 'type' => 'direct'));
        //print_r($data_connect);

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


        //print_r($campaign_ids_filter);
        $campaign_ids_chunk = array_chunk($campaign_ids_filter, 10);
        //print_r($campaign_ids_chunk);

        //
        $result_array = array();
        foreach($campaign_ids_chunk as $campaign_ids_chunk_filter) {

            // Параметры запроса к серверу API Директа
            $params = array(
                'method' => 'get',                                 // Используемый метод сервиса Campaigns
                'params' => array(
                    'SelectionCriteria' => (object) array(
                        'CampaignIds' => $campaign_ids_chunk_filter,
                        //'Statuses' => array('DRAFT'),
                    ),  // Критерий отбора кампаний. Для получения всех кампаний должен быть пустым
                    'FieldNames' => array('Id', 'Name', 'CampaignId', 'Status', 'RegionIds', 'NegativeKeywords'),             // Названия параметров, которые требуется получить
                    // Выборка
                    'Page' => array(
                        'Limit' => 10000,
                        'Offset' => 0,
                    ),
                )
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
            //print_r(json_decode($result, true));

            $adgroups = json_decode($result, true);
            //print_r($adgroups);

            if($adgroups['error']) {
                return $adgroups['error'];
                exit;
            }

            foreach($adgroups['result']['AdGroups'] as $adgroup) {
                array_push($result_array, $adgroup);
            }
        }

        return $result_array;
    }
}


