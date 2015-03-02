<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get Event Advertise');
$I->setHeader('access-token', '464825681cefa3901678ad49038f941b903bebe8d9174c94251ec3c1c4934c78');
$I->sendGET('event/advertise');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);