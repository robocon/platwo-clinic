<?php
$I = new ApiTester($scenario);
$I->wantTo('Show events');
$I->sendGET('event');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);