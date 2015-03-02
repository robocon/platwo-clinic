<?php 

// Random 3 category
$categorys = [
    '54c0ad7410f0ed5e048b4567', '54c0ad7410f0ed5e048b4568',
    '54c0ad7410f0ed5e048b4569', '54c0ad7410f0ed5e048b456a',
    '54c0ad7410f0ed5e048b456b', '54c0ad7410f0ed5e048b456c',
    '54c0ad7410f0ed5e048b456d', '54c0ad7410f0ed5e048b456e',
    '54c0ad7410f0ed5e048b456f', '54c0ad7410f0ed5e048b4570',
    '54c0ad7410f0ed5e048b4571', '54c0ad7410f0ed5e048b4572',
    '54c0ad7410f0ed5e048b4573', '54c0ad7410f0ed5e048b4574',
    '54c0ad7410f0ed5e048b4575', '54c0ad7410f0ed5e048b4576',
    '54c0ad7410f0ed5e048b4577', '54c0ad7410f0ed5e048b4578',
    '54c0ad7410f0ed5e048b4579', '54c0ad7410f0ed5e048b457a',
];
$rand = array_rand($categorys, 3);

// Randdom lat-lng in chiangmai
$lat = (float) rand(18,18).'.'.mt_rand(781000, 795500);
$lng = (float) rand(98,98).'.'.mt_rand(978000, 993500);

// Get test image and convert into base64
$image = base64_encode(file_get_contents(dirname(dirname(__FILE__)).'/test.png'));
$user_id = '54ba29c210f0edb8048b457a';

/**
 * Booking an event
 */
$I = new ApiTester($scenario);
$I->wantTo('Booking an event');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('event/gallery', [
    'picture' => $image,
    'user_id' => $user_id
    ]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$event_id = $I->grabDataFromJsonResponse('event_id');
$picture = $I->grabDataFromJsonResponse('picture');
$I->seeResponseContainsJson([
    'user_id' => $user_id,
    'event_id' => $event_id,
    'picture' => $picture
]);

/**
 * Add more picture into event
 */
$I->wantTo('Add more gallery into event');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$I->sendPOST('event/gallery/'.$event_id, [
    'event_id' => $event_id,
    'picture' => $image,
    'user_id' => $user_id
]);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();
$I->seeResponseContainsJson([
    'user_id' => $user_id,
    'event_id' => $event_id,
]);

/**
 * Update event data
 */
$test_time = time();
//$test_date_start = date('Y-m-d H:i:s');
//$test_date_end = date('Y-m-d H:i:s', strtotime('+2 week'));
$test_date_start = date('Y-m-d H:i:s', strtotime('+1 week'));
$test_date_end = date('Y-m-d H:i:s', strtotime('+2 week'));

$I->wantTo('Update an event data');
$I->haveHttpHeader('Content-Type', 'application/x-www-form-urlencoded');
$put = [
    'name' => 'test add name '.$test_time,
    'detail' => 'test add detail '.$test_time,
    'date_start' => $test_date_start,
    'date_end' => $test_date_end,
    'credit' => 'http://'.$test_time.'.pla2.com/test',
    'user_id' => $user_id,
    'location' => [$lat, $lng],
    'location_name' => 'test location '.$test_time,
    'tags' => [
        $categorys[$rand['0']], $categorys[$rand['1']], $categorys[$rand['2']]
    ],
    'lang' => 'en',
    'country' => '54b8dfa810f0edcf048b4567',
    'city' => '54b8e0e010f0edcf048b4575',
];
$I->sendPUT('event/'.$event_id, $put);
$I->seeResponseCodeIs(200);
$I->seeResponseIsJson();

$tags = $I->grabDataFromJsonResponse('tags');
$time_edit = $I->grabDataFromJsonResponse('time_edit');
$I->seeResponseContainsJson([
    'name' => $put['name'],
    'detail' => $put['detail'],
    'date_start' => $put['date_start'],
    'date_end' => $put['date_end'],
    'credit' => $put['credit'],
    'time_edit' => $time_edit,
    'id' => $event_id,
    'tags' => $tags,
    'location' => [
        'name' => $put['location_name'],
    ],
    'status' => 200
]);