<?php namespace PhpRestClient;

class PhpRestClient
{
    use Http;

    /** @var bool $return_json_as_array False for object, true for array. */
    public $return_json_as_array = true;

    /** @var string $base_url Base URL to use when calling the REST API. */
    public $base_url = false;

    /**
     * Initializes class with the API Base URL.
     *
     * @param string $base_url Base URL of the REST API.
     *
     * @return void
     */
    public function __construct($base_url)
    {
        $this->base_url = rtrim($base_url, '/') . '/';
    }

    /**
     * Send HTTP request.
     *
     * @param string $path   Request path.
     * @param array  $optons {
     *     @var mixed $CURLOPT_*  Any valid CURLOPT_ setting as a (string) key and 
     *                             associated value. Do not pass the CURLOPTS in with
     *                             array keys set to their constant integer values.
     *     @var bool  $NO_COOKIES Prevent request from using and storing cookies.
     * }
     *
     * @return mixed Boolean false on failure, parsed response on success.
     */
    public function call($path, $options)
    {
        // Base URL has a trailing slash, remove this if passed in.
        $base_url = $this->base_url . ltrim($path, '/');

        $response = $this->http_request($base_url, $options);
        return $this->parse_response($response);
    }

    /**
     * Sets the authentication method for requests.
     *
     * @param string $username Username
     * @param string $password Password
     * @param int    $auth     Authentication Method (CURLAUTH_BASIC or CURLAUTH_DIGEST)
     *
     * @return void
     */
    public function set_authentication($username, $password, $auth=CURLAUTH_BASIC)
    {
        $this->default_curlopts['CURLOPT_HTTPAUTH'] = $auth;
        $this->default_curlopts['CURLOPT_USERPWD']  = "{$username}:{$password}";
    }

    /**
     * Determine if the response is XML or JSON and returns parsed response.
     */
    public function parse_response($response) {
        $first_char = substr(trim($response), 0, 1);

        switch ($first_char) {
            case '<': // XML.
                $response = simplexml_load_string($response);
                if ($response === false) {} // todo: Error.              
                break;

            case '[': case '{': // JSON.
                $response = json_decode($response, $this->return_json_as_array);
                if ($response === false) {} // todo: Error.
                break;
            default: break;
        }
        
        // return parsed results or response string if neither JSON or XML.
        return $response;
    }

    // get
    public function get($path, $query, $headers)
    {
        $query = is_array($query) ? http_build_query($query) : $query;
        $this->set_headers($headers);

        return $this->call($path);
    }

    // put
    public function put($path, $query, $headers)
    {
        if (is_string($query)) {
            $this->curl_headers[] = 'Content-Length: ' . strlen($query);
        }
        $options['CURLOPT_CUSTOMREQUEST'] = 'PUT';        
        $options['CURLOPT_POSTFIELDS'] = $query;

        return $this->call($path, $options);
    }

    // post
    public function post($url, $query, $headers)
    {
        if (is_string($query)) {
            $this->curl_headers[] = 'Content-Length: ' . strlen($query);
        }

        $options['CURLOPT_CUSTOMREQUEST'] = 'POST';
        $options['CURLOPT_POST'] = true;
        $options['CURLOPT_POSTFIELDS'] = $query;

        $this->set_headers($headers);

        return $this->call($path, $options);
    }

    // delete
    public function delete($url, $headers)
    {
        $options['CURLOPT_CUSTOMREQUEST'] = 'DELETE';

        $this->set_headers($headers);

        return $this->call($path, $options);
    }

    // patch
    public function patch($url, $query, $headers)
    {
        $this->curl_headers[] = 'Content-Length: ' . strlen($query);

        $options['CURLOPT_CUSTOMREQUEST'] = 'PATCH';
        $options['CURLOPT_POSTFIELDS'] = $query;
        
        $this->set_headers($headers);

        return $this->call($path, $options);
    }
}
