<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get feed overview');
$I->sendGET('feed/overview');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$details = $I->grabDataFromJsonResponse('details');
$pictures = $I->grabDataFromJsonResponse('pictures');
$I->seeResponseContainsJson([
    'details' => $details,
    'pictures' => $pictures
]);