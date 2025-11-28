<?php

namespace PHPAP21;

class CurlResponse
{
    /** @var array */
    private $headers = [];
    /** @var string */
    private $body;

    public function __construct($response)
    {
        $this->parse($response);
    }

    /**
     * @param string $response
     */
    private function parse($response)
    {
        // add limit as html might have additional matching whitespace
        $response = \explode("\r\n\r\n", $response, $limit = 2);
        //Log::debug(__METHOD__, $response);
        if (\count($response) > 1) {
            // We want the last two parts
            $response = \array_slice($response, -2, 2);
            list($headers, $body) = $response;
            foreach (\explode("\r\n", $headers) as $header) {
                $pair = \explode(': ', $header, 2);
                if (isset($pair[1])) {
                    $headerKey = strtolower($pair[0]);
                    $this->headers[$headerKey] = $pair[1];
                }
            }
        }
        else {
            $body = $response[0];
        }
        Log::debug(__METHOD__, [$body]);
        $this->body = $body;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @param string $key
     *
     * @return string
     */
    public function getHeader($key)
    {
        $key = strtolower($key);
        return isset($this->headers[$key]) ? $this->headers[$key] : null;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    public function __toString()
    {
        $body = $this->getBody();
        $body = $body ? : '';
        return $body;
    }
}
