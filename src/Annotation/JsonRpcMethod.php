<?php

namespace Yoanm\SymfonyJsonRpcHttpServer\Annotation;

/**
 * Annotation class for @JsonRpcMethod().
 *
 * @Annotation
 * @Target({"CLASS"})
 */
class JsonRpcMethod
{
    private $name;

    /**
     * @param array $data An array of key/value parameters
     *
     * @throws \BadMethodCallException
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException(sprintf('Unknown property "%s" on annotation "%s".', $key, static::class));
            }
            $this->$method($value);
        }
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getName(): string {
        return $this->name;
    }
}
