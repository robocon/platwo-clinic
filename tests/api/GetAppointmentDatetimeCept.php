<?php 
$I = new ApiTester($scenario);
$I->wantTo('Get appointment datetime');
$I->sendGET('appoint/datetime');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$date = $I->grabDataFromJsonResponse('date');
$I->seeResponseContainsJson([
    'date' => $date
]);