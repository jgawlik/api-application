<?php

declare(strict_types=1);

namespace Api\Application\Response;

interface ResponseInterface
{
    public function respond(): array;
}
