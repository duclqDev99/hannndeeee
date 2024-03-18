 <main class="page--inner">
     <div class="container">
         <section class="section--auth">
             {!!
                $form
                    ->formClass('ps-form--account')
                    ->modify('submit', 'submit', [
                            'attr' => [
                                'class' => 'mt-4 btn--custom btn--rounded btn--outline btn-block',
                            ],
                        ])
                    ->modify('remember', 'html', ['html' => sprintf('<div class="col-6"><div class="ps-checkbox">
                                <input class="form-control" type="checkbox" name="remember" id="remember-me">
                                <label for="remember-me" class="control-label">%s</label>
                            </div></div>', __('Remember me'))], true)
                    ->renderForm()
            !!}
         </section>
     </div>
 </main>
