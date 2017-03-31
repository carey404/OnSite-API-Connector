<?php


class RESTConnector
{

    // Replace the below variables with the credentials for your integration
    // User-Agent and X-PAPPID can be found on http://my.lightspeedpos.com under 'My Apps'
    // Username and Password are the credentials you would use to log into Lightspeed OnSite
    // The Host is the IP address of the Lightspeed server
    // The Port is 9630 by default. Verify under System Preferences > Lightspeed Server

    private $user_agent = 'com.carey.testapp/1.0';
    private $privateID = 'X-PAPPID: 2b537d57-e7fc-4141-a3c2-0cd37c9db658';
    private $username = 'lightspeed';
    private $password = 'lightspeed3';
    // private $user_agent = 'com.acme.basicwidget/1.0';
    // private $privateID = 'X-PAPPID: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx';
    // private $username = 'lightspeed_username';
    // private $password = 'lightspeed_password';
    private $host = 'localhost';
    private $port = '9630';
    private $endpoint;
    private $domain;
    private $method;
    private $requestXml;
    private $httperror = "";
    private $exception = "";
    private $responseBody = "";
    private $headers = "";
    private $cookieJar = "";
    private $req = null;
    private $res = null;
    private $arrCurl;


    public function __construct($root_url = "")
    {
        $this->domain = sprintf("https://%s:%d/api/", $this->host, $this->port);

        $this->arrCurl = array(
            'CURLOPT_HTTPAUTH' => CURLAUTH_BASIC,
            'CURLOPT_SSL_VERIFYPEER' => false,
            'CURLOPT_SSL_VERIFYHOST' => false,
            'CURLOPT_USERAGENT' => $this->user_agent,
            'CURLOPT_HTTPHEADER' => array($this->privateID),
            'CURLOPT_ENCODING' => 'gzip',
            'CURLOPT_USERPWD' => $this->username . ':' . $this->password,
            'CURLOPT_RETURNTRANSFER' => true,
            'CURLOPT_HEADER' => 1,
            'CURLINFO_HEADER_OUT' => true,
            'CURLOPT_FOLLOWLOCATION' => true
        );
        return true;
    }

    public function createRequest($endpoint, $method = 'GET', $body = null, $mycookies = null)
    {
        $this->endpoint = $this->domain . $endpoint;
        $this->requestXml = $body;
        $this->cookieJar = $mycookies;

        switch ($method)
        {
            case "GET":
            case "POST":
            case "PUT":
            case "LOCK":
            case "UNLOCK":
            case "DELETE":
                $this->method = $method;
                $this->arrCurl['CURLOPT_CUSTOMREQUEST'] = $this->method;
                break;

            default:
                throw new Exception ($method . ' method not supported');
                break;
        }
    }

    public function sendRequest()
    {
        $ch = curl_init($this->endpoint);

        if (!$ch)
            throw new Exception('Failed to initialize curl resource');

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Ignore certificate check errors
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // Ignore host check errors
        curl_setopt($ch, CURLOPT_USERAGENT, $this->user_agent);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($this->privateID));
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip');
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->method);

        $fp = fopen(dirname(__FILE__).'/errorlog.txt', 'w');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_STDERR, $fp);
        if (!is_null($this->cookieJar))
            curl_setopt($ch, CURLOPT_COOKIE, $this->cookieJar);

        $result = curl_exec($ch);
        $arrInfo = curl_getinfo($ch);

        if (!$result)
            throw new Exception(sprintf("cURL call failed\n%s\n%s", curl_error($ch), curl_errno($ch)));

        if (!isset($arrInfo['http_code']))
            throw new Exception(sprintf("An error occurred\n %s", $result."\n".print_r($arrInfo, true)));

        if ((integer)$arrInfo['http_code'] < 200 || (integer)$arrInfo['http_code'] > 206)
        {
            $this->setError($result);
            echo sprintf("Unexpected HTTP Status: %s\n", $this->httperror);
            var_dump($result);
            exit();
        }

        $this->setResponse($result);
        $this->setCookies($result);
    }

    public function getResponse()
    {
        return $this->responseBody;
    }

    public function setResponse($result)
    {
        if (is_null($result))
            $this->responseBody = null;
        else
            $this->responseBody = substr($result, strpos($result, '<?xml'));
    }

    public function getError()
    {
        return $this->httperror;
    }

    public function setError($result)
    {
        $endPos = strpos($result, 'content-type');
        $startPos = strpos($result, 'HTTP/1.1 ') + strlen('HTTP/1.1 ');
        $lengthError = $endPos - $startPos;
        $this->httperror = substr($result, $startPos, $lengthError);
    }

    public function getException()
    {
        return $this->exception;
    }

    public function setCookies($result)
    {
        $endPos = strpos($result, '; Path');
        $startPos = strpos($result, 'LS_SERVER_SESSION_ID=');
        $lengthCookie = $endPos - $startPos;
        $this->cookieJar = substr($result, $startPos, $lengthCookie);
    }

    public function getCookies()
    {
        return $this->cookieJar;
    }

}

?>
