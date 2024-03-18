<?php

namespace Database\Seeders;

use Botble\Base\Facades\MetaBox;
use Botble\Base\Supports\BaseSeeder;
use Botble\Language\Models\LanguageMeta;
use Botble\SimpleSlider\Models\SimpleSlider;
use Botble\SimpleSlider\Models\SimpleSliderItem;
use Illuminate\Support\Arr;

class SimpleSliderSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('sliders');

        SimpleSlider::query()->truncate();
        SimpleSliderItem::query()->truncate();

        $sliders = [
            [
                'name' => 'Home slider',
                'key' => 'home-slider',
            ],
        ];

        $sliderItems = [
            [
                'title' => 'New Collection',
                'link' => '/products',
                'description' => 'Save more with coupons & up to 70% off',
                'button_text' => 'Shop now',
            ],
            [
                'title' => 'Big Deals',
                'link' => '/products',
                'description' => 'Headphone, Gaming Laptop, PC and more...',
                'button_text' => 'Discover now',
            ],
            [
                'title' => 'Trending Collection',
                'link' => '/products',
                'description' => 'Save more with coupons & up to 20% off',
                'button_text' => 'Shop now',
            ],
        ];

        foreach ($sliders as $value) {
            $slider = SimpleSlider::query()->create(Arr::only($value, ['name', 'key']));

            LanguageMeta::saveMetaData($slider);

            foreach ($sliderItems as $key => $item) {
                $item['image'] = 'sliders/' . ($key + 1) . '.jpg';
                $item['order'] = $key + 1;
                $item['simple_slider_id'] = $slider->id;

                $sliderItem = SimpleSliderItem::query()->create(Arr::except($item, ['button_text']));

                MetaBox::saveMetaBoxData($sliderItem, 'button_text', $item['button_text']);
            }
        }
    }
}
