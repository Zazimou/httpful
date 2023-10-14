<?php declare(strict_types=1);

namespace Httpful;

use Exception;

/**
 * Models an HTTP response
 *
 * @author Nate Good <me@nategood.com>
 */
class Response
{

    public string|null $content_type = null;
    public string|null $parent_type = null;
    public string $charset = 'utf-8';
    public array $meta_data;
    public string $raw_headers;
    public string $raw_body;
    public Response\Headers $headers;
    public string|array|object|null $body;
    public bool $is_mime_vendor_specific = false;
    public bool $is_mime_personal = false;
    public int $code = 0;
    public Request $request;

    /**
     * @param string  $body
     * @param string  $headers
     * @param Request $request
     * @param array   $meta_data
     * @throws Exception
     */
    public function __construct(string $body, string $headers, Request $request, array $meta_data = [])
    {
        $this->request = $request;
        $this->raw_headers = $headers;
        $this->raw_body = $body;
        $this->meta_data = $meta_data;

        $this->code = $this->_parseCode($headers);
        $this->headers = Response\Headers::fromString($headers);

        $this->_interpretHeaders();

        $this->body = $this->_parse($body);
    }

    /**
     * @param $headers
     * @return int
     * @throws Exception
     */
    public function _parseCode($headers): int
    {
        $end = strpos($headers, "\r\n");
        if ($end === false) $end = strlen($headers);
        $parts = explode(' ', substr($headers, 0, $end));
        if (count($parts) < 2 || !is_numeric($parts[1])) {
            throw new Exception("Unable to parse response code from HTTP response due to malformed response");
        }
        return intval($parts[1]);
    }

    /**
     * After we've parse the headers, let's clean things
     * up a bit and treat some headers specially
     */
    public function _interpretHeaders(): void
    {
        // Parse the Content-Type and charset
        $content_type = isset($this->headers['Content-Type']) ? $this->headers['Content-Type'] : '';
        $content_type = explode(';', $content_type);

        $this->content_type = $content_type[0];
        if (count($content_type) == 2 && str_contains($content_type[1], '=')) {
            list(, $this->charset) = explode('=', $content_type[1]);
        }

        // RFC 2616 states "text/*" Content-Types should have a default
        // charset of ISO-8859-1. "application/*" and other Content-Types
        // are assumed to have UTF-8 unless otherwise specified.
        // http://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.7.1
        // http://www.w3.org/International/O-HTTP-charset.en.php
        if (!isset($this->charset)) {
            $this->charset = substr($this->content_type, 5) === 'text/' ? 'iso-8859-1' : 'utf-8';
        }

        // Is vendor type? Is personal type?
        if (str_contains($this->content_type, '/')) {
            list(, $sub_type) = explode('/', $this->content_type);
            $this->is_mime_vendor_specific = str_starts_with($sub_type, 'vnd.');
            $this->is_mime_personal = str_starts_with($sub_type, 'prs.');
        }

        // Parent type (e.g. xml for application/vnd.github.message+xml)
        $this->parent_type = $this->content_type;
        if (str_contains($this->content_type, '+')) {
            list(, $this->parent_type) = explode('+', $this->content_type, 2);
            $this->parent_type = Mime::getFullMime($this->parent_type);
        }
    }

    /**
     * Parse the response into a clean data structure
     * (most often an associative array) based on the expected
     * Mime type.
     * @param string $body Http response body
     * @return object|array|string|null the response parse accordingly
     */
    public function _parse(string $body): object|array|string|null
    {
        // If the user decided to forgo the automatic
        // smart parsing, short circuit.
        if (!$this->request->auto_parse) {
            return $body;
        }

        // If provided, use custom parsing callback
        if (isset($this->request->parse_callback)) {
            return call_user_func($this->request->parse_callback, $body);
        }

        // Decide how to parse the body of the response in the following order
        //  1. If provided, use the mime type specifically set as part of the `Request`
        //  2. If a MimeHandler is registered for the content type, use it
        //  3. If provided, use the "parent type" of the mime type from the response
        //  4. Default to the content-type provided in the response
        $parse_with = $this->request->expected_type;
        if (empty($this->request->expected_type)) {
            $parse_with = Httpful::hasParserRegistered($this->content_type)
                ? $this->content_type
                : $this->parent_type;
        }

        return Httpful::get($parse_with)->parse($body);
    }

    /**
     * Status Code Definitions
     *
     * Informational 1xx
     * Successful    2xx
     * Redirection   3xx
     * Client Error  4xx
     * Server Error  5xx
     *
     * http://pretty-rfc.herokuapp.com/RFC2616#status.codes
     *
     * @return bool Did we receive a 4xx or 5xx?
     */
    public function hasErrors(): bool
    {
        return $this->code >= 400;
    }

    /**
     * @return bool
     */
    public function hasBody(): bool
    {
        return isset($this->body);
    }

    /**
     * Parse text headers from response into
     * array of key value pairs
     * @param string $headers raw headers
     * @return array parse headers
     */
    public function _parseHeaders(string $headers): array
    {
        return Response\Headers::fromString($headers)->toArray();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->raw_body;
    }
}
