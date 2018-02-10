<?php

declare(strict_types=1);

namespace Api\Application\Response;

class ItemResponse implements ResponseInterface
{
    private $data;

    public function __construct(int $id, string $name, int $amount)
    {
        $this->data = [
            'id' => $id,
            'name' => $name,
            'amount' => $amount,

        ];
    }

    public function respond(): array
    {
        return $this->data;
    }
}
