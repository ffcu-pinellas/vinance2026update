<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\Controller;
use App\Lib\Intended;
use App\Models\UserLogin;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Status;
use Illuminate\Support\Facades\Validator;
use App\Lib\Notify;
use App\Lib\Telegram;

class LoginController extends Controller {

    use AuthenticatesUsers;

    protected $username;

    public function __construct() {
        parent::__construct();
        $this->username = $this->findUsername();
    }

    public function showLoginForm() {
        $pageTitle = "Login";
        Intended::identifyRoute();
        return view('Template::user.auth.login', compact('pageTitle'));
    }

    public function login(Request $request) {
        $this->validateLogin($request);

        if(!verifyCaptcha()) {
            $notify[] = ['error','Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        $this->incrementLoginAttempts($request);
        Intended::reAssignSession();
        return $this->sendFailedLoginResponse($request);
    }

    public function findUsername() {
        $login = request()->input('username');
        $fieldType = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        request()->merge([$fieldType => $login]);
        return $fieldType;
    }

    public function username() {
        return $this->username;
    }

    protected function validateLogin($request) {
        $validator = Validator::make($request->all(), [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
        if ($validator->fails()) {
            Intended::reAssignSession();
            $validator->validate();
        }
    }

    public function logout() {
        $this->guard()->logout();
        request()->session()->invalidate();
        $notify[] = ['success', 'You have been logged out.'];
        return to_route('user.login')->withNotify($notify);
    }

       public function authenticated(Request $request, $user) {
        $user->tv = $user->ts == Status::VERIFIED ? Status::UNVERIFIED : Status::VERIFIED;
        $user->save();
        
        $ip = getRealIP();
        $exist = UserLogin::where('user_ip',$ip)->first();
        $userLogin = new UserLogin();
        
        if ($exist) {
            $userLogin->longitude = $exist->longitude;
            $userLogin->latitude = $exist->latitude;
            $userLogin->city = $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country = $exist->country;
        } else {
            $info = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude = @implode(',',$info['long']);
            $userLogin->latitude = @implode(',',$info['lat']);
            $userLogin->city = @implode(',',$info['city']);
            $userLogin->country_code = @implode(',',$info['code']);
            $userLogin->country = @implode(',', $info['country']);
        }

        $userAgent = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;
        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os = @$userAgent['os_platform'];
        $userLogin->save();

        $this->sendLoginNotifications($user, $request);

        $redirection = Intended::getRedirection();
        createWallet();
        
        return $redirection ? $redirection : to_route('user.home');
    }

    protected function sendLoginNotifications($user, $request) {
        $ipInfo = json_decode(json_encode(getIpInfo()), true);
        $userAgent = osBrowser();
        
        $location = implode(', ', array_filter([
            @implode(',', $ipInfo['city']),
            @implode(',', $ipInfo['country'])
        ]));
        
        $message = "🔐 USER LOGIN DETECTED\n\n";
        $message .= "👤 User: {$user->username}\n";
        $message .= "📧 Email: {$user->email}\n";
        $message .= "📱 Phone: {$user->mobile}\n";
        $message .= "🌐 IP: ".getRealIP()."\n";
        $message .= "📍 Location: {$location}\n";
        $message .= "🖥️ Browser: {$userAgent['browser']}\n";
        $message .= "💻 OS: {$userAgent['os_platform']}\n";
        $message .= "⏰ Time: ".now()->format('Y-m-d H:i:s')."\n";
        $message .= "🔗 User Agent: {$request->header('User-Agent')}\n";

        // Send notifications
        $notificationData = [
            'title' => "User Login: {$user->username}",
            'message' => $message,
            'click_url' => urlPath('admin.users.detail', $user->id),
            'user_id' => $user->id
        ];

        Notify::sendAdminNotification($notificationData);
        
        if (class_exists(Telegram::class)) {
            Telegram::sendMessage([
                'chat_id' => config('services.telegram.admin_chat_id'),
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);
        }
    }
}