<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get all appointment from user');
$I->setHeader('access-token', '2cba37f7c3a7815f8a380a4f51fbc5c8766d2fbf7b96d94e3b85b970a0ff0cc2');
$I->sendGET('appoint');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);