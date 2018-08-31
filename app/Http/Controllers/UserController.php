<?php

namespace App\Http\Controllers;

use App\University;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use JWTAuth;
use Illuminate\Support\Facades\Input;
use Tymon\JWTAuth\Exceptions\JWTException;

class UserController extends Controller
{
    
   
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'error' => 'invalid_credentials'
                ], 400);
            }
        } catch (JWTException $e) {
            return response()->json([
                'error' => 'could_not_create_token'
            ], 500);
        }
        
        return response()->json(compact('token'));
    }
    
    public function register(Request $request)
    {
        $rules = [
            'name' => 'required',
            'cpf' => 'required|max:14|min:11|unique:tb_user',
            'rg' => 'required|max:14|min:5|unique:tb_user',
            'email' => 'required|email|unique:tb_user',
            'password' => 'required|max:32|min:8',
        ];
        $message =[
        
        ];
    
        $validator = Validator::make(Input::all(), $rules);
        
        if ($validator->fails()){
            return response()
                    ->json($validator->errors()->toJson(), 400);
        }
        
        $university = new University();
        $university->name = 'asd';
        $university->course = 'asd';
        $university->save();
        
        
        $user = new User();
        $user->name = $request->input('name');
        $user->cpf = $request->input('cpf');
        $user->rg = $request->input('rg');
        $user->email = $request->input('email');
        $user->password = $request->input('password');
        $user->cd_university = $university->cd_university;
        $user->email = $request->input('email');
        $user->password = Hash::make($request->input('password'));
        $user->save();
        
        
        $token = JWTAuth::fromUser($user);
        
       
        return response()->json([
            $token
        ], 201);
        
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = auth()->user();
        
        $dataUser = User::with('universities')->
            where('cd_user' ,'=', $user->cd_user)
            ->paginate();
        return $dataUser;
        
        
    }
    
    public function getAuthenticatedUser()
    {
        try {
            
            if (! $user = JWTAuth::parseToken()->authenticate()) {
                return response()->json([
                    'user_not_found'
                ], 404);
            }
            
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            
            return response()->json([
                'token_expired'
            ], $e->getStatusCode());
            
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            
            return response()->json([
                'token_invalid'
            ], $e->getStatusCode());
            
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            
            return response()->json([
                'token_absent'
            ], $e->getStatusCode());
            
        }
        
        return response()->json([
            $user
        ]);
    }
}
