<?php
$I = new ApiTester($scenario);
$I->wantTo('Show user detail in Profile Tab');
$I->sendGET('user/54d9bc4dda354d757b8b4569');
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'id' => $I->grabDataFromJsonResponse('id'),
    'picture' => $I->grabDataFromJsonResponse('picture'),
    'display_name' => $I->grabDataFromJsonResponse('display_name'),
    'email' => $I->grabDataFromJsonResponse('email'),
    'detail' => $I->grabDataFromJsonResponse('detail')
]);