# Lightspeed OnSite API - Sample Integration
 
 This is a sample application to demonstrate the process for connecting to the Lightspeed OnSite API. The code was based on a [custom reporting tool](https://github.com/ottaz/custom-reorder-reporter) created by [Kevin Ottley](https://github.com/ottaz).
 
# API Reference
 
 Please consult our [documentation](http://developers.lightspeedhq.com/onsite) for a complete list of API endpoints and available methods.
 
# Usage

Before you begin, be sure to update the variables in the rest_connector.php file.

```
private $user_agent = 'com.acme.basicwidget/1.0';
private $privateID = 'X-PAPPID: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
private $username = 'lightspeed_username';
private $password = 'lightspeed_password';
```

* The Username and Password are the credentials you would use to log into Lightspeed OnSite
* The Host is the IP address of the Lightspeed server
* The Port is 9630 by default. Verify under System Preferences > Lightspeed Server

The first step is to create a request using the createRequest method. The createRequest method accepts four parameters.

* URL
* Method (GET, POST, PUT, LOCK, UNLOCK, or DELETE)
* Request body (XML body for POST and PUT requests)
* Session cookie

```
$rest = new RESTConnector();
$rest->createRequest('products/', 'GET', null, $_SESSION['cookies']);
```

The request is sent to the server using the sendRequest method.

```
$rest->sendRequest();
```

Retrieve the response using the getResponse method.

```
$response = $rest->getResponse();
```

Finally, check for errors with the getError and getException methods.
```
$error = $rest->getError();
$exception = $rest->getException();
```
    