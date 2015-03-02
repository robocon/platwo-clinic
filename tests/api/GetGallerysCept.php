<?php
$I = new ApiTester($scenario);
$I->wantTo('Show pictures from event_id');
$I->sendGET('gallery/54c85e2610f0ed1e048b4568');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);