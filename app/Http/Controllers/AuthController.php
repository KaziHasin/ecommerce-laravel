<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use Carbon\Carbon;
use Validator;



class AuthController extends Controller
{
    public function register(Request $request) {
    
     $validator = Validator::make($request->all(), [
            'username' => 'required|min:3',
             'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            're-password' => 'required|same:password',
             'photo' =>'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
           
        ]);
   
        if($validator->fails()){
               return response()->json(['erorr'=> $validator->errors()], 422);
        }
  
  
    
    $input = $request->all();

    $input['photo'] = $request->file('photo')->store('public/user_images');
    $input['password'] = Hash::make($input['password']);
    $input['email_verified_at'] = now();
    $input['remember_token'] = Str::random(100);

    $user = User::create($input);


    $success['token'] =  $user->createToken('MyApp')->plainTextToken; 
    $success['username'] =  $user->username;

    return response()->json(['data'=> $success, 'message'=>'User register successfully.'], 200);
    }


   public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user(); 
            $success['token'] =  $user->createToken('MyApp')->plainTextToken; 

            $success['username'] =  $user->username;
   
            return response()->json(['data'=> $success, 'message'=>'User login successfully.'], 200);
        } 
        else{ 
            
            return response()->json(['message'=>'User credentials are wrong.'], 402);
        } 
    }

    public function changePassword(Request $request) {
  
          $validator = Validator::make($request->all(), [
            
            'password' => 'required|min:6',
            'confirm-password' => 'required|same:password',
            
           
        ]);

          if($validator->fails()){
               return response()->json(['erorr'=> $validator->errors()], 422);
        }

          $user = $request->user();
          $user->password = Hash::make($request->password);
          $user->save();
          $request->user()->tokens()->delete();
          return response()->json(['message' => 'Password changed successfully'], 200);
        }

        public function send_reset_password_email(Request $request) {

            
            $validator = Validator::make($request->all(), [
            
            'email' => 'required|email',
            
        ]);

          if($validator->fails()){
               return response()->json(['erorr'=> $validator->errors()], 422);
            }
                $user = User::where('email', $request->email)->first();

              $reset_email = $user->email;



               if(!$user){

               return response()->json([
        
                 'message'=>'Email does not exist',
                 'status'=>'faild'
                 
               ],404);

               }
                
                // generate token
               $reset_token = Str::random(60);

                
            
            DB::table('password_reset_tokens')->insert([
               
               'email' => $reset_email,
                'token' => $reset_token,
                'created_at' => Carbon::now(),
            ]);


             //send mail

           $mail_send = Mail::send('resetpassword', ["reset_token" =>$reset_token], function(Message $message)use($reset_email){

              $message->subject("Reset your password");
              $message->to($reset_email);

            });
           
           if($mail_send) {

            if(auth()->user()) {
                $request->user()->tokens()->delete();
            }

             return response()->json(["message"=>"Link sent to your email"], 200);

           }else {
            return response()->json(["message"=>"Email not found"], 404);
           }
            
        }
    
    public function reset_password(Request $request, $token){
            
           $validator = Validator::make($request->all(), [
            
            'password' => 'required|min:6',
            'confirmpassword' => 'required|same:password',
            
           
        ]);

          if($validator->fails()){
               return response()->json(['erorr'=> $validator->errors()], 422);
        } 


        $get_email = DB::table('password_reset_tokens')->where('token', $token)->first();
          
          if(!$get_email) {
            return response()->json(["message"=>"Wrong token"], 404);
          }

          $user = User::where("email", $get_email->email)->first();

           $user->password = Hash::make($request->password);
           $user->save();

           DB::table('password_reset_tokens')->where('token', $token)->delete();
          
          return response()->json(["message"=>"Password Reset successfully"], 200);



         
    }

    public function logout(Request $request) {
       
       $request->user()->tokens()->delete();

       return response()->json(["message"=>"logout the user"]);
    }
}
