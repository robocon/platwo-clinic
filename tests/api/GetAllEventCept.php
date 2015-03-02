<?php 
$I = new ApiTester($scenario);
$I->wantTo('Show all event for mobile');
$I->sendGET('event/all');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);