<?php

declare(strict_types=1);

namespace Api\Application\Response;

use Symfony\Component\Form\FormErrorIterator;

class ValidationErrorResponse implements ResponseInterface
{
    private $formErrors;

    public function __construct(FormErrorIterator $formErrors)
    {
        $this->formErrors = $formErrors;
    }

    public function respond(): array
    {
        return ['errors' => ['validation' => $this->getPreparedFormErrors()]];
    }


    private function getPreparedFormErrors(): array
    {
        $response = [];
        foreach ($this->formErrors as $error) {
            $response[$error->getOrigin()->getName()][] = $error->getMessage();
        }

        return $response;
    }
}
