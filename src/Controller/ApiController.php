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
use Swagger\Annotations as SWG;

class ApiController
{
    private $itemService;
    private $formFactory;

    public function __construct(ItemService $itemService, FormFactoryInterface $formFactory)
    {
        $this->itemService = $itemService;
        $this->formFactory = $formFactory;
    }

    /**
     *
     * @SWG\Get(
     *      summary="Pobierz kolekcję artykułów filtrowaną przez opcjonalne kryteria",
     *      tags={"Items"},
     *      @SWG\Parameter(name="amount_greater", in="query", type="integer", description="Stan magazynowy artykułów większy od"),
     *      @SWG\Parameter(name="amount_equals", in="query", type="integer", description="Stan magazynowy artykułów równy"),
     *      @SWG\Response(response="200", description="Pobierz kolekcję artykułów"),
     *      @SWG\Response(response="500", description="Błąd serwera")
     * )
     *
     * @param Request $request
     * @return Response
     */
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

    /**
     *
     * @SWG\Get(
     *      summary="Pobierz pojedyńczy artykuł",
     *      tags={"Items"},
     *      @SWG\Parameter(name="itemId", in="path", type="integer", description="Id artykułu"),
     *      @SWG\Response(response="200", description="Pobrano pojedyńczy artykuł"),
     *      @SWG\Response(response="404", description="Nie znaleziono artykułu"),
     *      @SWG\Response(response="500", description="Błąd serwera")
     * )
     *
     * @param int $itemId
     * @return Response
     */
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

    /**
     *
     * @SWG\Post(
     *      summary="Dodaj nowy artykuł",
     *      tags={"Items"},
     *      consumes={"application/x-www-form-urlencoded"},
     *      @SWG\Parameter(name="name", in="formData", type="string", description="Nazwa artykułu"),
     *      @SWG\Parameter(name="amount", in="formData", type="integer", description="Stan magazynowy artykułu"),
     *      @SWG\Response(response="201", description="Dodano nowy artykuł"),
     *      @SWG\Response(response="422", description="Błąd walidacji przesłanych danych"),
     *      @SWG\Response(response="500", description="Błąd serwera")
     * )
     *
     * @param Request $request
     * @return Response
     */
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

    /**
     *
     * @SWG\Patch(
     *      summary="Aktualizuj istniejący artykuł",
     *      tags={"Items"},
     *      consumes={"application/x-www-form-urlencoded"},
     *      @SWG\Parameter(name="itemId", in="path", type="integer", description="Id istniejącego artykułu"),
     *      @SWG\Parameter(name="name", in="formData", type="string", description="Nowa nazwa artykułu"),
     *      @SWG\Parameter(name="amount", in="formData", type="integer", description="Nowy stan magazynowy artykułu"),
     *      @SWG\Response(response="201", description="Zaktualizowano artykuł"),
     *      @SWG\Response(response="422", description="Błąd walidacji przesłanych danych"),
     *      @SWG\Response(response="500", description="Błąd serwera")
     * )
     * @param int $itemId
     * @param Request $request
     * @return Response
     */
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

    /**
     *
     * @SWG\Delete(
     *      summary="Usunięcie artykułu",
     *      tags={"Items"},
     *      @SWG\Parameter(name="itemId", in="path", type="integer", description="Id artykułu"),
     *      @SWG\Response(response="201", description="Usunięto artykuł"),
     *      @SWG\Response(response="500", description="Błąd serwera")
     * )
     * @param int $itemId
     * @return Response
     */
    public function removeItem(int $itemId): Response
    {
        $this->itemService->removeItem($itemId);

        return new JsonResponse([], Response::HTTP_NO_CONTENT);
    }
}
