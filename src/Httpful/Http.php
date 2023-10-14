<?php declare(strict_types=1);

namespace Httpful;

/**
 * @author Nate Good <me@nategood.com>
 */
class Http
{
    const HEAD      = 'HEAD';
    const GET       = 'GET';
    const POST      = 'POST';
    const PUT       = 'PUT';
    const DELETE    = 'DELETE';
    const PATCH     = 'PATCH';
    const OPTIONS   = 'OPTIONS';
    const TRACE     = 'TRACE';

    /**
     * @return array of HTTP method strings
     */
    public static function safeMethods(): array
    {
        return array(self::HEAD, self::GET, self::OPTIONS, self::TRACE);
    }

    /**
     * @param string $method HTTP method
     * @return bool
     */
    public static function isSafeMethod(string $method): bool
    {
        return in_array($method, self::safeMethods());
    }

    /**
     * @param string $method HTTP method
     * @return bool
     */
    public static function isUnsafeMethod(string $method): bool
    {
        return !in_array($method, self::safeMethods());
    }

    /**
     * @return array list of (always) idempotent HTTP methods
     */
    public static function idempotentMethods(): array
    {
        // Though it is possible to be idempotent, POST
        // is not guarunteed to be, and more often than
        // not, it is not.
        return array(self::HEAD, self::GET, self::PUT, self::DELETE, self::OPTIONS, self::TRACE, self::PATCH);
    }

    /**
     * @param string $method HTTP method
     * @return bool
     */
    public static function isIdempotent(string $method): bool
    {
        return in_array($method, self::idempotentMethods());
    }

    /**
     * @param string $method HTTP method
     * @return bool
     */
    public static function isNotIdempotent(string $method): bool
    {
        return !in_array($method, self::idempotentMethods());
    }

}