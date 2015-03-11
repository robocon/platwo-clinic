<?php
$I = new ApiTester($scenario);
$I->wantTo('Test get feeds');
$I->sendGET('feed');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);