<?php

declare(strict_types=1);

namespace Api\Application\Item;

use Api\Repository\ItemQueryInterface;

class ItemQueryParameters implements ItemQueryInterface
{
    private $greater;
    private $equals;

    public function __construct(array $amountOptions)
    {
        $this->validateParameters($amountOptions);
        $this->equals = $this->getPropertyValueToSet('equals', $amountOptions);
        $this->greater = $this->getPropertyValueToSet('greater', $amountOptions);
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
        if (isset($amountOptions['greater']) && !is_int($amountOptions['greater'])) {
            throw new \InvalidArgumentException('Parametr greater musi być integerem!');
        }
        if (isset($amountOptions['equals']) && !is_int($amountOptions['equals'])) {
            throw new \InvalidArgumentException('Parametr equals musi być integerem!');
        }
    }
}
