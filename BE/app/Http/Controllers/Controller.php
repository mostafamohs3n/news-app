<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public function returnSuccess($data, $message = '', $code = Response::HTTP_OK): JsonResponse
    {
        return $this->returnResponse($data, true, $message, $code);
    }

    public function returnResponse($data, $success, $message, $code = Response::HTTP_OK): JsonResponse
    {
        $response = [
            'success' => $success,
            'data' => $data,
            'message' => $message
        ];
        return response()->json($response, $code);
    }

    /**
     * @param $data
     * @param $message
     * @param $code
     * @return JsonResponse
     */
    public function returnError($data, $message, $code = Response::HTTP_INTERNAL_SERVER_ERROR): JsonResponse {
        return $this->returnResponse($data, false, $message, $code);
    }
}
