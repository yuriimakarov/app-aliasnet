<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
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
        $errorMessagesArr = json_decode($json, true);

        if (isset($errorMessagesArr['error_message'][0])) { // Check if $errorMessagesArr has multiple error_message
            $response = $this->getMessagesSpellCheck($errorMessagesArr['error_message']);
        } else {
            $response = $this->getMessagesSpellCheck($errorMessagesArr);
        }

        return response()->json($response);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getDifferences(Request $request): JsonResponse
    {
        $messagesArray = json_decode($request->getContent(), true);
        $response = [];

        foreach ($messagesArray['error_messages'] as $k => $messageItem) {
            $response['comparison'][$k] = [
                'message' => $messageItem['error_message']['message'],
                'original_message' => $messageItem['error_message']['original_message'],
                'distance' => levenshtein(
                    $messageItem['error_message']['original_message'],
                    $messageItem['error_message']['message']
                ),
                'similarity' => similar_text(
                    $messageItem['error_message']['original_message'],
                    $messageItem['error_message']['message']
                )
            ];
        }

        return response()->json($response);
    }

    /**
     * @param array $data
     * @return array
     */
    private function getMessagesSpellCheck(array $data): array
    {
        $response = [];

        foreach ($data as $errorMessage) {
            $correctedData = $this->callSpellCheckApi($errorMessage['message']);

            if ($correctedData !== false && !empty($correctedData['matches'])) {
                $message = $this->correctSpellingMistakes($correctedData['matches'], $errorMessage['message']);
            } else {
                $message = $errorMessage['message'];
            }

            $response['error_messages'][]['error_message'] = [
                'title' => $errorMessage['title'],
                'module' => $errorMessage['module'],
                'language' => [
                    'code' => 'en-GB'
                ],
                'message' => $message, // Here will be result of corrected message
                'original_message' => $errorMessage['message']
            ];
        }

        return $response;
    }
    /**
     * @param string $data
     * @return array|bool
     */
    private function callSpellCheckApi(string $data): array|bool
    {
        $response = Http::asForm()
            ->retry(5)
            ->post('https://api.languagetoolplus.com/v2/check', [
                'text' => $data,
                'language' => 'en-GB'
            ]);

        if ($response->status() == 200) {
            return json_decode($response->body(), true);
        }
        return false;
    }

    /**
     * @param array $data
     * @param string $originalMessage
     * @return string
     */
    private function correctSpellingMistakes(array $data, string $originalMessage): string
    {
        $correctWords = [];
        $incorrectWords = [];

        foreach ($data as $message) {
            $correctWords[] = $message['replacements'][0]['value'];
            $incorrectWords[] = substr($originalMessage, $message['offset'], $message['length']);
        }

        return str_replace($incorrectWords, $correctWords, $originalMessage);
    }
}
