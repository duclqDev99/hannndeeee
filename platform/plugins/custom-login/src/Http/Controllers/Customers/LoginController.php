<?php

namespace Botble\CustomLogin\Http\Controllers\Customers;

use Botble\ACL\Traits\LogoutGuardTrait;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\CustomLogin\Forms\Fronts\Auth\CustomersLoginForm;
use Botble\CustomLogin\Http\Requests\CustomerOtpRequest;
use Botble\CustomLogin\Http\Requests\CustomLoginRequest;
use Botble\CustomLogin\Http\Requests\UserOtpRequest;
use Botble\CustomLogin\Traits\AuthenticatesUsers;
use Botble\CustomLogin\Traits\RedirectsUsers;
use Botble\CustomLogin\Traits\ThrottlesLogins;
use Botble\Ecommerce\Enums\CustomerStatusEnum;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Forms\Fronts\Auth\LoginForm;
use Botble\Ecommerce\Http\Requests\LoginRequest;
use Botble\Ecommerce\Models\Customer;
use Botble\JsValidation\Facades\JsValidator;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\SharedModule\Trait\LoginWithAppTrait;
use Botble\Theme\Facades\Theme;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Illuminate\Pipeline\Pipeline;
use Closure;

class LoginController extends BaseController
{
    use AuthenticatesUsers, LogoutGuardTrait {
        AuthenticatesUsers::attemptLogin as baseAttemptLogin;
    }
    use LoginWithAppTrait,RedirectsUsers,ThrottlesLogins;

    public string $redirectTo = '/';

    public function __construct(protected BaseHttpResponse $response)
    {
        $this->middleware('customer.guest', ['except' => 'logout']);
    }

    public function showLoginForm()
    {
        SeoHelper::setTitle(__('Login'));

        Theme::breadcrumb()->add(__('Login'), route('customer.login'));

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

        $model = Customer::class;

        return view('plugins/custom-login::auth.customer.login', compact('jsValidator', 'model', 'countryCode', 'phonenumber'));

    }

    protected function guard()
    {
        return auth('customer');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, (new LoginRequest())->rules());
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            $this->sendLockoutResponse($request);
        }
        $data = $request->only(["phone", "otp"]);
        $phone = str_replace('+84', '0', $request->input('phone'));
        $rs = $this->loginWithOTP($data,1);
        if ($rs) {
            if (isset($rs->error_code) && $rs->error_code === 0) {
                $user = Customer::query()->where(['phone' => $phone])->first();
                if (!empty($user)) {

                    if (!$user->status->getValue() === CustomerStatusEnum::ACTIVATED) {
                        return $this->response
                            ->setError()
                            ->setMessage(trans('core/acl::auth.login.not_active'));
                    }
                    return app(Pipeline::class)->send($request)
                        ->through(apply_filters('customer_login_pipeline', [
                            function (Request $request, Closure $next) use ($user) {
                                $this->guard()->login($user, true);
                                return $next($request);

                            },
                        ]))
                        ->then(function (Request $request) {
                            $this->guard()->user()->update(['last_login' => Carbon::now()]);

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
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to log in and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        $this->sendFailedLoginResponse();
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();

        $this->loggedOut($request);

        return redirect()->to(route('public.index'));
    }

    protected function attemptLogin(Request $request)
    {
        if ($this->guard()->validate($this->credentials($request))) {
            $customer = $this->guard()->getLastAttempted();

            // if (EcommerceHelper::isEnableEmailVerification() && empty($customer->confirmed_at)) {
            //     throw ValidationException::withMessages([
            //         'confirmation' => [
            //             __(
            //                 'The given email address has not been confirmed. <a href=":resend_link">Resend confirmation link.</a>',
            //                 [
            //                     'resend_link' => route('customer.resend_confirmation', ['email' => $customer->email]),
            //                 ]
            //             ),
            //         ],
            //     ]);
            // }

            if ($customer->status->getValue() !== CustomerStatusEnum::ACTIVATED) {
                throw ValidationException::withMessages([
                    'email' => [
                        __('Your account has been locked, please contact the administrator.'),
                    ],
                ]);
            }

            return $this->baseAttemptLogin($request);
        }

        return false;
    }

    public function username(): string
    {
        return 'phone';
        return EcommerceHelper::isLoginUsingPhone() ? 'phone' : 'email';
    }

    function sendOTP(CustomerOtpRequest $rq)
    {
        $countryCode = $rq->input('countrycode');
        $phone = $rq->input('phonenumber');
        $phoneSendToApp = '+' . $countryCode . $phone;
        $res = $this->sendOTPTrait($phoneSendToApp,1);
        $resJson = json_decode($res->getBody()->getContents());
        if ($resJson->error_code === 0) {
            return view('plugins/custom-login::auth.customer.otp', compact('phone', 'countryCode', 'phoneSendToApp'));
        }

        return response($res->getBody())->header('Content-Type', 'application/json');
    }
}
