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
                   ->renderForm()
           !!}
        </section>
    </div>
</main>
