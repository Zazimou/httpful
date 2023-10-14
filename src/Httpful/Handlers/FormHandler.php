<?php declare(strict_types=1);
/**
 * Mime Type: application/x-www-urlencoded
 * @author Nathan Good <me@nategood.com>
 */

namespace Httpful\Handlers;

class FormHandler extends MimeHandlerAdapter 
{
    /**
     * @param string $body
     * @return mixed
     */
    public function parse(string $body): array
    {
        $parsed = array();
        parse_str($body, $parsed);
        return $parsed;
    }
    
    /**
     * @param mixed $payload
     * @return string
     */
    public function serialize(mixed $payload): string
    {
        return http_build_query($payload, '', '&');
    }
}