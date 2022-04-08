<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Client\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SpellCheckController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function spellCheck(Request $request): JsonResponse
    {
        $xmlObject = simplexml_load_string($request->getContent());
        $json = json_encode($xmlObject);
        $phpArray = json_decode($json, true);

        $response = $this->callSpellCheckApi($phpArray['error_message']['message']);
        $mistakes = json_decode($response->body(), true);
        // TODO Create method to parse mistakes and build new sentence

        return response()->json([
            'error_messages' =>  [
                'title' => $phpArray['error_message']['title'],
                'module' => $phpArray['error_message']['module'],
                'language' => [
                    'code' => 'en-GB'
                ],
                'message' => $phpArray['error_message']['message'], // Here will be result of fixed message
                'original_message' => $phpArray['error_message']['message']
            ]
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDifferences (Request $request): JsonResponse
    {
        $phpArray = json_decode($request->getContent(), true);

        $originalMessage = $phpArray['error_messages'][0]['original_message'];
        $message = $phpArray['error_messages'][0]['message'];

        $distance = levenshtein($originalMessage, $message);
        $similarity = similar_text($originalMessage, $message);

        return response()->json([
            'comparisaon' => [
                [
                    'message' => $message,
                    'original_message' => $originalMessage,
                    'distance' => $distance,
                    'similarity' => $similarity
                ]
            ]
        ]);
    }

    /**
     * @param string $data
     * @return Response
     */
    private function callSpellCheckApi(string $data): Response
    {
        $params = [
            'text' => $data,
            'language' => 'en-GB'
        ];
        return Http::withHeaders([
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Accept' => 'application/json'
            ])
            ->asForm()
            ->post('https://api.languagetoolplus.com/v2/check', $params);
    }
}
