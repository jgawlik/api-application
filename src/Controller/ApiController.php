<?php

declare(strict_types=1);

namespace Api\Application\Controller;

use Api\Application\Form\Type\ItemFromType;
use Api\Application\Response\ItemResponse;
use Api\Application\Response\ValidationErrorResponse;
use Api\Application\Item\ItemQueryParameters;
use Api\Application\Response\ErrorResponse;
use Api\Exception\ItemNotFoundException;
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
            $itemQueryParameters = new ItemQueryParameters($request->query->all());
        } catch (\InvalidArgumentException $exception) {
            $errorResponse = new ErrorResponse($exception->getMessage());

            return new JsonResponse($errorResponse->respond(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse($this->itemService->findByCriteria($itemQueryParameters));
    }

    public function getItem(int $itemId): Response
    {
        try {
            $item = $this->itemService->getItem($itemId);
        } catch (ItemNotFoundException $exception) {
            $errorResponse = new ErrorResponse($exception->getMessage());

            return new JsonResponse($errorResponse->respond(), Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($item);
    }

    public function addItem(Request $request): Response
    {
        $form = $this->formFactory->create(ItemFromType::class);
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

    public function updateItem(Request $request, int $itemId): Response
    {
        $form = $this->formFactory->create(ItemFromType::class);
        $form->submit($request->request->all());
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->itemService->updateItem($data['name'], $data['amount'], $itemId);

            return new JsonResponse([], Response::HTTP_NO_CONTENT);
        }

        return new JsonResponse(
            (new ValidationErrorResponse($form->getErrors(true)))->respond(),
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    public function removeItem(int $itemId): Response
    {
        $this->itemService->removeItem($itemId);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
