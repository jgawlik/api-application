<?php

namespace Api\Application\Item;

use Api\Repository\ItemQueryInterface;

class ItemQueryParameters implements ItemQueryInterface
{
    private $greater;
    private $equals;

    public function __construct(array $amountOptions)
    {
        $this->validateParameters($amountOptions);
        $this->equals = $this->getPropertyValueToSet('amount_equals', $amountOptions);
        $this->greater = $this->getPropertyValueToSet('amount_greater', $amountOptions);
    }

    public function getGreater(): ?int
    {
        return $this->greater;
    }

    public function getEquals(): ?int
    {
        return $this->equals;
    }

    private function getPropertyValueToSet(string $name, array $amountOptions)
    {
        return isset($amountOptions[$name]) ? (int)$amountOptions[$name] : null;
    }

    private function validateParameters(array $amountOptions)
    {
        if (isset($amountOptions['amount_greater']) && !ctype_digit($amountOptions['amount_greater'])) {
            throw new \InvalidArgumentException('Parametr greater musi być integerem!');
        }
        if (isset($amountOptions['amount_equals']) && !ctype_digit($amountOptions['amount_equals'])) {
            throw new \InvalidArgumentException('Parametr equals musi być integerem!');
        }
    }
}
