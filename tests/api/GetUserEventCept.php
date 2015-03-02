<?php 
$I = new ApiTester($scenario);
$I->wantTo('Show event on user');
$I->sendGET('user/event/54ba29c210f0edb8048b457a');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);