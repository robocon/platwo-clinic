<?php 
$I = new ApiTester($scenario);
$I->wantTo('Show upcoming event');
$I->sendGET('event/upcoming');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);
