<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TokenController extends Controller
{
    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getToken(Request $request) : JsonResponse
    {
        $user = User::where([
            'name' => $request->getUser(),
            'password' => $request->getPassword()
        ])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'code' => Response::HTTP_UNAUTHORIZED
            ]);
        }
        $token = $user->createToken($user->name . $user->password);

        return response()->json([
            'success' => true,
            'code' => Response::HTTP_OK,
            'token' => $token->plainTextToken
        ]);
    }
}
