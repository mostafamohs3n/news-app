<?php

namespace App\Helpers;

class ResponseUtilities
{

    /**
     * @param $response
     * @return array|string[]
     */
    public static function getResponseErrorData($response): array
    {
        return [
            'code' => $response['code'] ?? $response['fault']['faultstring'] ?? 'Error',
            'message' => $response['message'] ?? $response['fault']['detail']['errorcode'] ?? 'Something went wrong.',
        ];
    }

    /**
     * @param $response
     * @return bool
     */
    public static function isResponseError($response): bool
    {
        return
            ($response['status'] ?? '') == 'error'
            || !empty($response['message'])
            || !empty($response['fault']);
    }
}
