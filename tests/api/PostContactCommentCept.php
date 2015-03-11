<?php 
$I = new ApiTester($scenario);
$I->wantTo('Add Comment into Contact');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', '10d96485dd0a326cee8bd159689c9b8a36d29365cee7b0e8185d34841acfbdbf');
$message = 'Lorem ipsum dolor sit amet '.time();
$I->sendPOST('contact/comment', [
    'message' => $message
    ]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'message' => $message
]);