<?php 

$image = base64_encode(file_get_contents(dirname(dirname(__FILE__)).'/test.png'));
$access_token = '10d96485dd0a326cee8bd159689c9b8a36d29365cee7b0e8185d34841acfbdbf';
$I = new ApiTester($scenario);
$I->wantTo('Add feed overview pictures');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', $access_token);
$I->sendPOST('feed/overview/picture', [
    'picture' => $image,
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$picture = $I->grabDataFromJsonResponse('picture');
$I->seeResponseContainsJson([
    'picture' => $picture,
]);

$I->wantTo('Add feed overview detail');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->setHeader('access-token', $access_token);
$I->sendPUT('feed/overview', [
    'details' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse interdum euismod tellus, ut consectetur felis laoreet ut. Aenean vel feugiat mauris. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Proin dictum neque ornare dolor imperdiet blandit. Donec commodo felis libero, consequat semper nibh congue eu. Nullam auctor lacinia nunc, a condimentum eros semper vel. Nulla erat est, volutpat laoreet felis malesuada, iaculis dapibus massa. Etiam in turpis quis arcu tincidunt porttitor sit amet at felis. Praesent rhoncus tellus vitae ex pharetra eleifend. Nullam lobortis nulla et efficitur convallis. Nam laoreet ut turpis non tincidunt. Donec eget elit eget ipsum porttitor porttitor. Vestibulum fringilla lorem ac ex mollis, nec fringilla nulla varius. ',
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$detail = $I->grabDataFromJsonResponse('detail');
$I->seeResponseContainsJson([
    'detail' => $detail,
]);
