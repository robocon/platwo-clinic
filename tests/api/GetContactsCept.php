<?php
$I = new ApiTester($scenario);
$I->wantTo('Show contact');
$I->sendGET('contact');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$phone = $I->grabDataFromJsonResponse('phone');
$I->seeResponseContainsJson([
    'phone' => $phone
]);