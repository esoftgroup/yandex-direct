<?php

require_once __DIR__ . '/vendor/autoload.php';



$config = new \Esoftgroup\YandexDirect\Config();
echo $config->setAccessToken(1111);
echo $config->getAccessToken();