<?php namespace PhpRestClient;

trait Http
{
    public $default_curlopts = array(
        'CURLOPT_CONNECTTIMEOUT' => 5,
        'CURLOPT_RETURNTRANSFER' => true,
        'CURLOPT_AUTOREFERER'    => true,
        'CURLOPT_FOLLOWLOCATION' => true,
        'CURLOPT_SSL_VERIFYPEER' => false,
    );

    public $curl_headers = array();

    public function setDefaultCurlopts(array $options)
    {
        array_merge($this->default_curlopts, $options);

        // Remove default options set to null.
        foreach ($this->default_curlopts as $option=>$value) {
            if (null == $value) {
                unset($this->default_curlopts[$option]);
            }
        }
    }

    // set an associative array of headers
    public function setHeaders($headers)
    {
        foreach ($headers as $header=>$value) {
            $this->curl_headers[] = "{$header}: {$value}";
        }
    }

    public function httpRequest($url, $options=array())
    {
        // Validate URL.
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $options['CURLOPT_URL'] = $url;
        } else return false;

        $ch = curl_init();

        // Default to use cookies.
        if (!$options['NO_COOKIES']) {
            $this->cookie_file = $options['CURLOPT_COOKIEFILE'] ?: false;
            // If no cookie file passed in, create a cookie in the temp directory.
            $this->cookie_file = $this->cookie_file  ?: tempnam(sys_get_temp_dir(), 'Http_Cookie_');
            if (is_writeable($this->cookie_file)) {
                $options['CURLOPT_COOKIEFILE'] = $this->cookie_file;
                $options['CURLOPT_COOKIEJAR']  = $this->cookie_file;
            }
        }

        // Set custom headers.
        if (!empty($this->curl_headers)) {
            $options['CURLOPT_HTTPHEADER'] = $this->curl_headers;
        }

        // Create array of cURL options.
        foreach ($options as $option=>$value) {
            if (strpos($option, 'CURLOPT_') !== false) {
                $curlopts[constant($option)] = $value;
            }
        }

        // Set default values not passed in.
        foreach ($this->default_curlopts as $option=>$value) {
            if (!isset($curlopts[$option])) {
                $curlopts[constant($option)] = $value;
            }
        }

        curl_setopt_array($ch, $curlopts);
        $this->curl_response = curl_exec($ch);

        // Save last request.
        $this->curl_info = curl_getinfo($ch);

        // Save last error.
        if (false === $this->curl_repsponse) {
            $this->curl_error = curl_error($ch);
        } else $this->curl_error = false;

        curl_close($ch);

        // Reset headers.
        $this->curl_headers = array();

        return $this->curl_response;
    }
}
