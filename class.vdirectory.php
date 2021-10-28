<?php


class VcloudAPI {

    //private $curlOBJ;
    private $sessionToken;
    private $user;
    private $org;
    private $passwd;
    private $url;
    private $apiVersion;

    function __construct($url, $user, $org, $passwd, $apiVersion) {

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

    private function getResponse($ch, String $urlExtension, $headers = null, $method = "get", $bodyOnly = false) {
        
        if (!$ch) $ch = curl_init();
        
        if ($urlExtension[0] != "/") $urlExtension = "/" . $urlExtension;
        
        if (!$headers) {
            $headers = Array("Accept: application/*;version=".$this->apiVersion, 
                             "Authorization: ".$this->sessionToken
                            );
        }
        curl_setopt($ch, CURLOPT_URL, $this->url.$urlExtension);
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

    private function getJWTToken() {

        // Authentication        
        $headers = Array(
            "Accept: application/*;version=".VCloudAPI::$apiVersion,
            "Authorization: Basic " . base64_encode("$this->user@$this->org:$this->passwd")
        );

        $response = $this->getResponse(null, "/cloudapi/1.0.0/sessions", $headers, "post");
        
        if (!$response) {
            echo json_encode(Array("Error" => "Failed to send authentication request"));
        }

        preg_match("/(?<=ACCESS\-TOKEN\:\s).*?(?=\s)/", $response , $m);
        $token = $m[0];
        preg_match("/(?<=VCLOUD-TOKEN-TYPE\:\s).*?(?=\s)/", $response, $m);
        $auth_token = $m[0] . " " . $token;
        
        return $auth_token;
    }

    public function getCurrentSession() {
        $response = $this->getResponse(null, "/cloudapi/1.0.0/sessions/current", null, "get", true);
        
        var_dump($response);
    }

    public function getApps() {
        
    }

    function __destruct() {
        
    }

}


?>