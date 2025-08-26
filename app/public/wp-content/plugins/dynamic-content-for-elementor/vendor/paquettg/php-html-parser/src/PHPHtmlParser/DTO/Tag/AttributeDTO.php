<?php

declare (strict_types=1);
namespace DynamicOOOS\PHPHtmlParser\DTO\Tag;

use DynamicOOOS\stringEncode\Encode;
use DynamicOOOS\stringEncode\Exception;
final class AttributeDTO
{
    /**
     * @var ?string
     */
    private $value;
    /**
     * @var bool
     */
    private $doubleQuote;
    private function __construct(array $values)
    {
        $this->value = $values['value'];
        $this->doubleQuote = $values['doubleQuote'] ?? \true;
    }
    public static function makeFromPrimitives(?string $value, bool $doubleQuote = \true) : AttributeDTO
    {
        return new AttributeDTO(['value' => $value, 'doubleQuote' => $doubleQuote]);
    }
    public function getValue() : ?string
    {
        return $this->value;
    }
    public function isDoubleQuote() : bool
    {
        return $this->doubleQuote;
    }
    public function htmlspecialcharsDecode() : void
    {
        if (!\is_null($this->value)) {
            $this->value = \htmlspecialchars_decode($this->value);
        }
    }
    /**
     * @throws Exception
     */
    public function encodeValue(Encode $encode)
    {
        $this->value = $encode->convert($this->value);
    }
}
