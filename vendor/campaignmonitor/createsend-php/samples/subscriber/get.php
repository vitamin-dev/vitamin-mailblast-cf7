<?php

require_once '../../csrest_subscribers.php';

$auth = array(
    'access_token' => 'your access token',
    'refresh_token' => 'your refresh token');
$wrap = new CS_REST_Subscribers('Your list ID', $auth);

//The 2nd argument will return the tracking preference of the subscriber - 'ConsentToTrack'
$result = $wrap->get('Email address', true);

echo "Result of GET /api/v3.3/subscribers/{list id}.{format}?email={email}\n<br />";
if($result->was_successful()) {
    echo "Got subscriber <pre>";
    var_dump($result->response);
} else {
    echo 'Failed with code '.$result->http_status_code."\n<br /><pre>";
    var_dump($result->response);
}
echo '</pre>';