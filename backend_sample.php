<?php
require 'RecommendedOrders.php';

$recOrders = new RecommendedOrders('MyCookieName');
$event = !empty($_REQUEST['event']) ? strtolower($_REQUEST['event']) : null;
$param = !empty($_REQUEST['param']) ? $_REQUEST['param'] : null;

switch ($event) {
    case 'add':
        $result = $recOrders->addID($param);
        break;
    case 'remove':
        $result = $recOrders->removeID($param);
        break;
    case 'check':
        $result = $recOrders->isRecommended(false);
        break;
    case 'check_last':
        $result = $recOrders->isRecommended();
        break;
    default:
        $result = false;
}
echo $result ? json_encode(['status' => 'success']) : json_encode(['status' => 'unsuccess']);
