<?php

require_once 'rest_connector.php';
require_once 'session.php';

// check to see if we start a new session or maintain current one
checksession();

$rest = new RESTConnector();

// Get all products
// Remember to include the trailing forward slash after the endpoint or the request will fail
$rest->createRequest('products/', 'GET', null, $_SESSION['cookies']);
$rest->sendRequest();
$response = $rest->getResponse();
$error = $rest->getError();
$exception = $rest->getException();

// Parse the XML response and print to screen
$xml = simplexml_load_string($response);
echo '<pre>';
print_r($xml);
echo '</pre>';

// Save our session cookies to avoid consuming more than one user seat
if ($_SESSION['cookies']==null)
    $_SESSION['cookies'] = $rest->getCookies();

// Display any error message
if ($error!=null)
    die($error);

if ($exception!=null)
    die($exception);

//
// Additional requests go here
//

// POST to session/current/logout/ to logout when we are finished
$rest->createRequest('sessions/current/logout/', 'POST', null, $_SESSION['cookies']);
$rest->sendRequest();
$error = $rest->getError();
$exception = $rest->getException();

// Display any error message
if ($error!=null)
    die($error);

if ($exception!=null)
    die($exception);

?>

<html>
<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
</head>

<body>

</body>
</html>
