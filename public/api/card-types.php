<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\CardTypeModel;

use Portalbox\Transform\CardTypeTransformer;

//switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
    case 'GET':     // List
        Session::require_authorization(Permission::LIST_CARD_TYPES);

        try {
            $model = new CardTypeModel(Config::config());
            $card_types = $model->search();
            $transformer = new CardTypeTransformer();
            ResponseHandler::render($card_types, $transformer);
        } catch(Exception $e) {
            http_response_code(500);
            die('We experienced issues communicating with the database');
        }
    break;
}