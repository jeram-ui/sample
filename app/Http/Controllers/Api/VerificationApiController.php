<?php
namespace App\Http\Controllers\Api;
use App\User;
use Illuminate\Foundation\Auth\VerifiesEmails;
use Illuminate\Http\Request;
class VerificationApiController extends BaseController
{
use VerifiesEmails;
// protected $redirectTo = 'http://localhost:8080/lgu_Front/public/';
protected $redirectTo = 'https://cityofnagacebu.com/boss';
public function verify(Request $request) {
	$userID = $request['id'];
	$user = User::findOrFail($userID);
	$date = date("Y-m-d g:i:s");
	$user->email_verified_at = $date; 
	$user->save();
	return redirect($this->redirectPath())->with('verified', true);
}

public function resend(Request $request)
{
	if ($request->user()->hasVerifiedEmail()) {
		return response()->json('User already have verified email!', 422);
// return redirect($this->redirectPath());
	}
	$request->user()->sendEmailVerificationNotification();
	return response()->json('The notification has been resubmitted');
// return back()->with(‘resent’, true);
}
}