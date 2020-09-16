<?php
namespace App\Http\Controllers\Api\Traits;
use Validator;

trait ApiResponse
{

    public function apiResponse($data = array() , $message = '', $code = 200)
    {
        $array = [
            'status'    => [
                        "code"      => $code,
                        "success"   => in_array($code, $this->successCode())? true : false,
                        "message"   => $message
                    ],
            'results'   => $data
        ];
        return response($array, $code);
    }//end of api function

    public function successCode()
    {
        return [
            200 , 201 , 202
        ];
    }//end of code success function

    public function unknowError()
    {
        return $this->apiResponse($data = array(), 'Unknow Error', 520);
    }//end of function return unknow error

    public function createdResponse($data,$message)
    {
        return $this->apiResponse($data, $message, 201);
    }//end of function return createdResponse error


    public function apiValidation($request,$array)
    {
        $validate=validator::make($request->all(), $array);
        if($validate->fails())
        {
            return $this->apiResponse($data = array(), $validate->errors()->first(),422);
        }
    }//end of apiVallidation function

}