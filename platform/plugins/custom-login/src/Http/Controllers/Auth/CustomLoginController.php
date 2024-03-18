<?php

namespace Botble\CustomLogin\Http\Controllers\Auth;

use Botble\ACL\Models\User;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\BaseHelper;
use Botble\Base\Facades\PageTitle;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\CustomLogin\Http\Requests\CustomLoginRequest;
use Botble\CustomLogin\Http\Requests\UserOtpRequest;
use Botble\CustomLogin\Traits\AuthenticatesUsers;
use Botble\JsValidation\Facades\JsValidator;
use Botble\SharedModule\Trait\LoginWithAppTrait;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Validation\ValidationException;

class CustomLoginController extends BaseController
{
    use AuthenticatesUsers, LoginWithAppTrait;

    protected string $redirectTo = '/';

    public function __construct(protected BaseHttpResponse $response)
    {
        $this->middleware('guest', ['except' => 'logout']);

        $this->redirectTo = BaseHelper::getAdminPrefix();
    }

    public function showLoginForm()
    {
        PageTitle::setTitle(trans('core/acl::auth.login_title'));

        Assets::addScripts(['jquery-validation', 'form-validation'])

            ->addStylesDirectly('vendor/core/plugins/custom-login/css/login.css')
            ->removeStyles([
                'select2',
                'fancybox',
                'spectrum',
                'simple-line-icons',
                'custom-scrollbar',
                'datepicker',
            ])
            ->removeScripts([
                'select2',
                'fancybox',
                'cookie',
            ]);
        $countryCode = request()->input('countryCode');
        $phonenumber = request()->input('phonenumber');
        $jsValidator = JsValidator::formRequest(CustomLoginRequest::class);

        $model = User::class;

        return view('plugins/custom-login::auth.login', compact('jsValidator', 'model', 'countryCode', 'phonenumber'));
    }

    public function login(Request $request)
    {

        $this->validateLogin($request);
        $data = $request->only(["phone", "otp"]);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $this->sendLockoutResponse($request);
        }
        $rs = $this->loginWithOTP($data);

        if ($rs) {
            if (isset($rs->error_code) && $rs->error_code === 0) {
                $user = User::query()->where(['phone' => $request->input('phone')])->first();
                if (!empty($user)) {
                    if (!$user->activated) {
                        return $this->response
                            ->setError()
                            ->setMessage(trans('core/acl::auth.login.not_active'));
                    }
                    return app(Pipeline::class)->send($request)
                        ->through(apply_filters('core_acl_login_pipeline', [
                            function (Request $request, Closure $next) use ($user) {
                                Auth::login($user, true);
                                return $next($request);

                            },
                        ]))
                        ->then(function (Request $request) {
                            Auth::guard()->user()->update(['last_login' => Carbon::now()]);

                            if (!session()->has('url.intended')) {
                                session()->flash('url.intended', url()->current());
                            }

                            return $this->sendLoginResponse($request);
                        });
                }
            } else {
                
                $this->incrementLoginAttempts($request);
                throw ValidationException::withMessages([
                    $this->username() => [$rs->error_msg],
                ]);
            }
        } else {
            
            $this->incrementLoginAttempts($request);

            return $this->sendFailedLoginResponse("Tài khoản chưa đăng ký trên hệ thống WGHN.");
        }
    }
    function sendOTP(UserOtpRequest $rq)
    {
        $countryCode = $rq->input('countrycode');
        $phone = $rq->input('phonenumber');
        $phoneSendToApp = '+' . $countryCode . $phone;
        $res = $this->sendOTPTrait($phoneSendToApp);
        $resJson = json_decode($res->getBody()->getContents());
        if ($resJson->error_code === 0) {
            // if(1){
            return view('plugins/custom-login::auth.otp', compact('phone', 'countryCode', 'phoneSendToApp'));
        }

        return response($res->getBody())->header('Content-Type', 'application/json');
    }
    public function username()
    {
        return filter_var(request()->input('username'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    }

    public function logout(Request $request)
    {
        do_action(AUTH_ACTION_AFTER_LOGOUT_SYSTEM, $request, $request->user());

        $this->guard()->logout();

        $request->session()->invalidate();

        return $this->response
            ->setNextUrl(route('access.login'))
            ->setMessage(trans('core/acl::auth.login.logout_success'));
    }

}
