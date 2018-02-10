<?php

declare(strict_types=1);

namespace Api\Application\Controller;

use Api\Application\Form\Type\AddItemFromType;
use Api\Application\Response\ItemResponse;
use Api\Application\Response\ValidationErrorResponse;
use Api\Application\Item\ItemQueryParameters;
use Api\Application\Response\ErrorResponse;
use Api\Service\ItemService;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiController
{
    private $itemService;
    private $formFactory;

    public function __construct(ItemService $itemService, FormFactoryInterface $formFactory)
    {
        $this->itemService = $itemService;
        $this->formFactory = $formFactory;
    }

    public function getItems(Request $request): Response
    {
        try {
            $itemQueryParameters = new ItemQueryParameters($request->query->get('amount', []));
        } catch (\InvalidArgumentException $exception) {
            $errorResponse = new ErrorResponse($exception->getMessage());
            return new JsonResponse($errorResponse->respond(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse($this->itemService->findByCriteria($itemQueryParameters));
    }

    public function addItem(Request $request): Response
    {
        $form = $this->formFactory->create(AddItemFromType::class);
        $form->submit($request->request->all());
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $newRecordId = $this->itemService->addItem($data['name'], $data['amount']);
            $itemResponse = new ItemResponse($newRecordId, $data['name'], $data['amount']);
            return new JsonResponse($itemResponse->respond(), Response::HTTP_CREATED);
        }

        return new JsonResponse(
            (new ValidationErrorResponse($form->getErrors(true)))->respond(),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
