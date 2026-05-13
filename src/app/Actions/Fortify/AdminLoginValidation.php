<?php

namespace App\Actions\Fortify;

use App\Http\Requests\AdminLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AdminLoginValidation
{

    public function __invoke(Request $request, $next)
    {
        if ($request->is('admin/*')) {

            $formRequest = new AdminLoginRequest();

            $validator = Validator::make(
                $request->all(),
                $formRequest->rules(),
                $formRequest->messages(),
                $formRequest->attributes() // ← ★これ追加
            );

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
        }

        return $next($request);
    }
}
