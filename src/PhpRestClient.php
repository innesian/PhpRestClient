<?php namespace PhpRestClient;

class PhpRestClient
{
    use Http;

    public $return_json_as_array = true;

    public $base_url = false;

    public function __construct($base_url)
    {
        $this->base_url = rtrim($base_url, '/') . '/';
    }

    /**
     * Build URL.
     */
    public function build_url($path)
    {
        $path = ltrim($path, '/');

        return $this->base_url . $path;
    }

    /**
     * Sets the authentication method for requests.
     *
     * @param string $username - Username
     * @param string $password - Password
     * @param int $auth - Authentication Method (CURLAUTH_BASIC or CURLAUTH_DIGEST
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
        $url = $this->build_url($path);

        $query = is_array($query) ? http_build_query($query) : $query;
        $this->set_headers($headers);

        $response = $this->http_request($url);
        
        return $this->parse_response($response);
    }

    // put
    public function put($url, $query, $headers)
    {
        if (is_string($query)) {
            $this->curl_headers[] = 'Content-Length: ' . strlen($query);
        }
        $options['CURLOPT_CUSTOMREQUEST'] = 'PUT';        
        $options['CURLOPT_POSTFIELDS'] = $query;

        $response = $this->http_request($url, $options);

        return $this->parse_response($response);
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

        $response = $this->http_request($url, $options);

        return $this->parse_response($response);
    }

    // delete
    public function delete($url, $headers)
    {
        $options['CURLOPT_CUSTOMREQUEST'] = 'DELETE';

        $this->set_headers($headers);

        $response = $this->http_request($url);

        return $this->parse_response($response);
    }

    // patch
    public function patch($url, $query, $headers)
    {
        $this->curl_headers[] = 'Content-Length: ' . strlen($query);

        $options['CURLOPT_CUSTOMREQUEST'] = 'PATCH';
        $options['CURLOPT_POSTFIELDS'] = $query;
        
        $this->set_headers($headers);

        $response = $this->http_request($url, $options);

        return $this->parse_response($response);
    }
}
