<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get all appointment from user');
$I->setHeader('access-token', 'f70862b6e1347aa07ec841be1f555bb6632061c3f56f27bb9232cb68b7d40209');
$I->sendGET('appoint');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);