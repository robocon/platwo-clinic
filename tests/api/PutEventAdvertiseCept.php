<?php 
$event_id = '54ee0b7efc5067a3208b4567';

$I = new ApiTester($scenario);
$I->wantTo('Update Event Advertise');
$I->setHeader('access-token', '464825681cefa3901678ad49038f941b903bebe8d9174c94251ec3c1c4934c78');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPUT('event/advertise/'.$event_id, [
    'enable' => 1,
    'cities' => [
        '54b8e0e010f0edcf048b4569', '54b8e0e010f0edcf048b4575', '54b8e0e010f0edcf048b459e'
    ]
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
//$id = $I->grabDataFromJsonResponse('id');
$I->seeResponseContainsJson([
    'id' => $event_id
]);