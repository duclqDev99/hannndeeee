<?php

use Botble\Base\Facades\MetaBox;
use Botble\Ecommerce\Models\FlashSale;
use Botble\LanguageAdvanced\Supports\LanguageAdvancedManager;
use Botble\Media\Facades\RvMedia;
use Botble\SimpleSlider\Models\SimpleSliderItem;

register_page_template([
    'homepage' => __('Homepage'),
    'full-width' => __('Full width'),
]);

register_sidebar([
    'id' => 'footer_sidebar',
    'name' => __('Footer sidebar'),
    'description' => __('Footer sidebar'),
]);

app()->booted(function () {
    remove_sidebar('primary_sidebar');
});

RvMedia::setUploadPathAndURLToPublic()
    ->addSize('medium', 570, 570)
    ->addSize('small', 570, 268);

if (is_plugin_active('simple-slider')) {
    add_filter(BASE_FILTER_BEFORE_RENDER_FORM, function ($form, $data) {
        switch (get_class($data)) {
            case SimpleSliderItem::class:
                $value = MetaBox::getMetaData($data, 'button_text', true);

                $form
                    ->addAfter('link', 'button_text', 'text', [
                        'label' => __('Button text'),
                        'label_attr' => ['class' => 'control-label'],
                        'value' => $value,
                        'attr' => [
                            'placeholder' => __('Ex: Shop now'),
                        ],
                    ]);

                break;

            case FlashSale::class:
                $image = MetaBox::getMetaData($data, 'image', true);

                $form
                    ->addAfter('end_date', 'image', 'mediaImage', [
                        'label' => __('Image'),
                        'label_attr' => ['class' => 'control-label'],
                        'value' => $image,
                    ]);

                break;
        }

        return $form;
    }, 124, 3);

    add_action([BASE_ACTION_AFTER_CREATE_CONTENT, BASE_ACTION_AFTER_UPDATE_CONTENT], function ($screen, $request, $data) {
        switch (get_class($data)) {
            case SimpleSliderItem::class:
                if ($request->has('button_text')) {
                    MetaBox::saveMetaBoxData($data, 'button_text', $request->input('button_text'));
                }

                break;
            case FlashSale::class:
                if ($request->has('image')) {
                    MetaBox::saveMetaBoxData($data, 'image', $request->input('image'));
                }

                break;
        }
    }, 120, 3);
}

app()->booted(function () {
    if (is_plugin_active('language-advanced')) {
        LanguageAdvancedManager::registerModule(FlashSale::class, ['name', 'image']);
    }

    if (is_plugin_active('ecommerce')) {
        add_filter('ecommerce_product_eager_loading_relations', function (array $with) {
            return array_merge($with, ['variationAttributeSwatchesForProductList']);
        }, 120);
    }
});
