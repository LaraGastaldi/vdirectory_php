<?php

/*
VcloudAPI class
Usage:

$myvcloud = new VcloudAPI("https://vcloud.url.example", "username", "password", 36.0);
$currentSession = $myvcloud->getCurrentSession();


TO-DO:
- automate the process of collecting the apiversion, 
by sending the request to the vcloud url with the extension "/api/versions",
with the basic authentication
(https://kb.vmware.com/s/article/56948)

- organize the extensions in an object
*/

$ext_array = array(
    "GET" => array(
        "session" => array()
    ),
    "POST" => array(),
    "DELETE" => array(),
    "PUT" => array()
);

class VcloudAPI
{

    //private $curlOBJ;
    private $sessionToken;
    private $user;
    private $org;
    private $passwd;
    private $url;
    private $apiVersion;

    function __construct($url, $user, $org, $passwd, $apiVersion)
    {

        $this->url = $url;
        $this->user = $user;
        $this->org = $org;
        $this->passwd = $passwd;
        $this->apiVersion = $apiVersion;

        // Authenticate
        $this->sessionToken = $this->getJWTToken();
        var_dump($this->sessionToken);
        $this->getCurrentSession();
    }

    private function validateURL($url)
    {
        #TODO validation and correction of url used in the constructor 
    }

    private function getAPIVersion()
    {
        #TODO automatic validation of the api version
        # 
    }

    private function getResponse($ch, String $urlExtension, $headers = null, $method = "get", $bodyOnly = false)
    {

        if (!$ch) $ch = curl_init();

        if ($urlExtension[0] != "/") $urlExtension = "/" . $urlExtension;

        if (!$headers) {
            $headers = array(
                "Accept: application/*;version=" . $this->apiVersion,
                "Authorization: " . $this->sessionToken
            );
        }
        curl_setopt($ch, CURLOPT_URL, $this->url . "/cloudapi/1.0.0" . $urlExtension);
        if (!$bodyOnly) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        if ($method == "post") {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    private function getJWTToken(): string
    {

        // Authentication        
        $headers = array(
            "Accept: application/*;version=" . $this->apiVersion,
            "Authorization: Basic " . base64_encode("$this->user@$this->org:$this->passwd")
        );

        $response = $this->getResponse(null, "/sessions", $headers, "post");

        if (!$response) {
            echo json_encode(array("Error" => "Failed to send authentication request"));
        }

        preg_match("/(?<=ACCESS-TOKEN:\s).*?(?=\s)/", $response, $m);
        $token = $m[0];
        preg_match("/(?<=VCLOUD-TOKEN-TYPE:\s).*?(?=\s)/", $response, $m);
        return $m[0] . " " . $token;
    }

    public function getCurrentSession()
    {
        $response = $this->getResponse(null, "/sessions/current", null, "get", true);

        var_dump($response);
        return $response;
    }

    public function getApps()
    {
    }

    function __destruct()
    {
    }
}
