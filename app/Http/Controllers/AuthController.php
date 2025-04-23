<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailer;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class AuthController extends Controller{

 /*      public function __construct()
    {
        $this->middleware('auth:sanctum',
         ['except' => ['connectFacebook', 
         'connectFacebookPage', 'login', 
         'register', 'forgot_password',
         'reset_password', 'verify_email',
          'resend_verification_email', 
          'socialLogin',
           'handleProviderCallback',
           'get_user_from_token']]);
    }
 */


public function register(Request $request){
  $validator = Validator::make($request->all(),[
    'name'=> 'required|string|max:255',
    'email'=> 'required|string|email|max:255|unique:users',
    'password'=> 'required|string|min:8|confirmed',
    'username'=> 'required|string|max:255|unique:users',
    'user_type'=> 'required|string|max:255',
  ]);

  if ($validator->fails()) {
    return response()->json([
        'message' => 'Validation Error',
        'errors' => $validator->errors()
    ], 422); 
}

$input = $request->all();
$user =User::create($input);



$success['token']= $user->createToken('MyApp')->accessToken;
$success['name']= $user->name;
$success['email']= $user->email;
$success['user_type']= $user->user_type;
$success['username']= $user->username;
$success['message'] = "User Registered Successfully";
return response()->json($success, 200);










  /* public function Registeration(Request $request){
    $data = $request->validate(
      [
        'name'=> 'required|string|max:255',
        'email'=> 'required|string|email|max:255|unique:users',
        'password'=> 'required|string|min:8|confirmed',
        'username'=> 'required|string|max:255|unique:users',
        'user_type'=> 'required|string|max:255',


      ]
      );
      $user = User::create($data);
      $token = $user->createToken('api-auth-token')->plainTextToken;
      return [
        'user'=> $user,
        'token'=>$token,
      ];
  }
  public function login(Request $request){
    $data = $request->validate([
        "password"=> ["required","min:8","string"],
        "email"=> ["required","email"],
    ]);

 $user = User::where("email", $data["email"])->first();
    if(!$user||!Hash::check($data["password"],$user->password)){
      return response()->json([
        "message"=>"Invalid credentials"
      ],401);

    }
    $token = $user->createToken('api-auth-token')->plainTextToken;
    return response()->json ([
        "user" => $user,
        "token" => $token,
    ]);
  } */
}

public function login(Request $request){



  $user = User::where("email", $request->email)->first();
  if(!$user || !Hash::check($request->password,$user->password)){
    return response()->json([
      "message"=>"Invalid credentials"
    ],401);
  }
  $token = $user->createToken('MyApp')->plainTextToken;
  return response()->json ([
    "user" => $user,
    "token" => $token,
]);

}
public function logout(Request $request){
  if($request->user() && method_exists($request->user(), 'currentAccessToken')){
    $request->user()->currentAccessToken()->delete();
  }
  return response()->json([
    "message"=>"Logged out successfully"
  ],200);
}

public function forget(Request $request)  
{
    $request->validate([
        'email' => 'required|email|exists:users,email'
    ]);

    $token = Str::random(64);

    DB::table('password_reset_tokens')->updateOrInsert(
        ['email' => $request->email],
        [
            'token' => $token,
            'created_at' => Carbon::now()
        ]
    );

    $resetUrl = env('FRONTEND_URL') . '/reset-password?token=' . $token . '&email=' . urlencode($request->email);

    try {
        Mail::send([], [], function($message) use ($request, $resetUrl) {
            $message->to($request->email)
                   ->subject('Reset Password Notification')
                   ->html("Please click the following link to reset your password: <a href='{$resetUrl}'>Reset Password</a>");
        });
        return response()->json([
            'message' => 'Password reset link sent successfully'
        ], 200);

    } catch (Exception $e) {
        Log::error('Password reset failed: ' . $e->getMessage());
        return response()->json([
            'message' => 'Failed to send password reset email',
            'error' => $e->getMessage() 
        ], 500);
    }
}
public function reset(Request $request){
  $request->validate(
    [
      'email'=>'email|required|exists:users',
      'password'=>'required|min:6|confirmed',
      'password_confirmation'=>'required'
    ],
    ['token'=>'required|min:6',]
  );
  $updatepassword = DB::table('password_reset_tokens')->where(
    ['email'=>$request->email,'token'=>$request->token]
  )->first();
  if(!$updatepassword){
    $request['error'] = 'Invalid Request';
    $request['message'] = 'This request to reset password is invalid.';
    $statusCode = 400;
    return response()->json($request,$statusCode);
  }
  $user = User::where('email',$request->email)->update(['password'=>Hash::make($request->password)]);
  DB::table('password_reset_tokens')->where(['email'=> $request->email])->delete();


  
}


}