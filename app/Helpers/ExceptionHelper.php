<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Validator;

class ExceptionHelper
{
    private static $messages = [
        'unique' => ':attribute :input has already been used!',
        'required' => ':attribute is required!'
    ];

    public static function validate($params, $validate)
    {
        $responseArr['status'] = 'success';
        $responseArr['message'] = '';

        $validator = Validator::make($params, $validate, self::$messages);

        if ($validator->fails()) {
            $responseArr['status'] = 'error';
            $responseArr['message'] = $validator->errors();
            return $responseArr;
        }

        return $responseArr;
    }
}
