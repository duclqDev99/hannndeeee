<?php

namespace Botble\CustomLogin\Forms\Fronts\Auth;

use Botble\Base\Facades\Html;
use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\FieldOptions\HtmlFieldOption;
use Botble\Base\Forms\Fields\EmailField;
use Botble\Base\Forms\Fields\HtmlField;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Base\Forms\Fields\PasswordField;
use Botble\Base\Forms\Fields\PhoneNumberField;
use Botble\Captcha\Facades\Captcha;
use Botble\Ecommerce\Forms\Fronts\Auth\FieldOptions\TextFieldOption;
use Botble\Ecommerce\Http\Requests\LoginRequest;
use Botble\Ecommerce\Models\Customer;

class CustomersLoginForm extends AuthForm
{
    public function setup(): void
    {
        parent::setup();

        $this
            ->setUrl(route('customer.send-otp'))
            // ->setValidatorClass(LoginRequest::class)
            // ->icon('ti ti-lock')
            ->heading(__('Login to your account'))
            // ->description(__('Your personal data will be used to support your experience throughout this website, to manage access to your account.'))
            ->when(
                theme_option('login_background'),
                fn (AuthForm $form, string $background) => $form->banner($background)
            )
            ->add(
                'phonenumber',
                PhoneNumberField::class,
                TextFieldOption::make()
                    ->label(__('Phone'))
                    ->placeholder(__('Phone number'))
                    ->icon('ti ti-phone')
                    ->toArray()
            )
            ->add('g-recaptcha', HtmlField::class, [
                'html' => Captcha::display(),
            ])
            ->add('openRow', HtmlField::class, [
                'html' => '<div class="row g-0 mb-3">',
            ])
            ->add(
                'remember',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(__('Remember me'))
                    ->wrapperAttributes(['class' => 'col-6'])
                    ->toArray()
            )
            
            ->add('closeRow', HtmlField::class, [
                'html' => '</div>',
            ])
            ->submitButton(__('Continue'), 'ti ti-arrow-narrow-right')
            ->add(
                'register',
                HtmlField::class,
                HtmlFieldOption::make()
                    ->view('plugins/ecommerce::customers.includes.register-link')
                    ->toArray()
            )
            ->add('filters', HtmlField::class, [
                'html' => apply_filters(BASE_FILTER_AFTER_LOGIN_OR_REGISTER_FORM, null, Customer::class),
            ]);
    }
}
