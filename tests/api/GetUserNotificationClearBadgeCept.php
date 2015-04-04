<?php 
$I = new ApiTester($scenario);
$I->wantTo('perform actions and see result');
$I->setHeader('access-token', 'c69a4407edefecf237cd616a773c0194f89b15bae581ac0dc7dc36ce74c6f6f8');
$I->sendGET('user/notify/clear_badge');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$data = $I->grabDataFromJsonResponse('data');
$I->seeResponseContainsJson([
    'data' => $data
]);