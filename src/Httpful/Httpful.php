<?php declare(strict_types=1);

namespace Httpful;

use Httpful\Handlers\MimeHandlerAdapter;

class Httpful {
    const VERSION = '0.3.2';

    private static array $mimeRegistrar = [];
    private static MimeHandlerAdapter|null $default = null;

    /**
     * @param string $mimeType
     * @param MimeHandlerAdapter $handler
     */
    public static function register(string $mimeType, MimeHandlerAdapter $handler): void
    {
        self::$mimeRegistrar[$mimeType] = $handler;
    }

    /**
     * @param string|null $mimeType defaults to MimeHandlerAdapter
     * @return MimeHandlerAdapter
     */
    public static function get(string|null $mimeType = null): MimeHandlerAdapter
    {
        if ($mimeType !== null) {
            if (isset(self::$mimeRegistrar[$mimeType])) {
                return self::$mimeRegistrar[$mimeType];
            }
        }

        if (empty(self::$default)) {
            self::$default = new MimeHandlerAdapter();
        }

        return self::$default;
    }

    /**
     * Does this particular Mime Type have a parser registered
     * for it?
     * @param string $mimeType
     * @return bool
     */
    public static function hasParserRegistered(string $mimeType): bool
    {
        return isset(self::$mimeRegistrar[$mimeType]);
    }
}
