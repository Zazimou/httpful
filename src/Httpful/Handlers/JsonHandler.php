<?php declare(strict_types=1);
/**
 * Mime Type: application/json
 * @author Nathan Good <me@nategood.com>
 */

namespace Httpful\Handlers;

use Httpful\Exception\JsonParseException;
use stdClass;

class JsonHandler extends MimeHandlerAdapter
{
    private bool $decodeAsArray = false;

    public function init(array $args): void
    {
        $this->decodeAsArray = !!(array_key_exists('decode_as_array', $args) ? $args['decode_as_array'] : false);
    }

    /**
     * @param string $body
     * @return stdClass|array|string|null
     * @throws JsonParseException
     */
    public function parse(string $body): stdClass|array|string|null
    {
        $body = $this->stripBom($body);
        if (empty($body))
            return null;
        $parsed = json_decode($body, $this->decodeAsArray);
        if (is_null($parsed) && 'null' !== strtolower($body))
            throw new JsonParseException('Unable to parse response as JSON: ' . json_last_error_msg());
        return $parsed;
    }

    /**
     * @param mixed $payload
     * @return string
     */
    public function serialize(mixed $payload): string
    {
        return json_encode($payload);
    }
}
