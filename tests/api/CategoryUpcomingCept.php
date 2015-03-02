<?php 
$I = new ApiTester($scenario);
$I->wantTo('Show upcoming event');
$I->sendGET('event/category/upcoming/54c0ad7410f0ed5e048b4572');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);
