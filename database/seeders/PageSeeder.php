<?php

namespace Database\Seeders;

use Botble\Base\Facades\Html;
use Botble\Base\Supports\BaseSeeder;
use Botble\Page\Models\Page;
use Botble\Slug\Facades\SlugHelper;

class PageSeeder extends BaseSeeder
{
    public function run(): void
    {
        $pages = [
            [
                'name' => 'Homepage',
                'content' =>
                    Html::tag('div', '[simple-slider key="home-slider"][/simple-slider]') .
                    Html::tag(
                        'div',
                        '[featured-product-categories title="Top Categories" subtitle="Lorem ipsum dolor sit amet, consectetur adipiscing elit. Phasellus blandit massa enim Nullam nunc varius."][/featured-product-categories]'
                    ) .
                    Html::tag(
                        'div',
                        '[product-collections title="A change of Season" subtitle="Update your wardrobe with new seasonal trend"][/product-collections]'
                    ) .
                    Html::tag(
                        'div',
                        '[flash-sale title="Deal of the day" subtitle="Best Deals For You" show_popup="yes"][/flash-sale]'
                    ) .
                    Html::tag(
                        'div',
                        '[featured-products title="Our Picks For You" subtitle="Always find the best ways for you" limit="8"][/featured-products]'
                    ) .
                    Html::tag(
                        'div',
                        '[trending-products title="Trending Products" subtitle="Products on trending" limit="4"][/trending-products]'
                    ) .
                    Html::tag('div', '[featured-brands title="Our Brands"][/featured-brands]') .
                    Html::tag(
                        'div',
                        '[news title="Visit Our Blog" subtitle="Our Blog updated the newest trend of the world regularly"][/news]'
                    ) .
                    Html::tag(
                        'div',
                        '[theme-galleries title="@ OUR GALLERIES" subtitle="Our latest fashion galleries images" limit="8"][/theme-galleries]'
                    ) .
                    Html::tag(
                        'div',
                        '[site-features icon1="icon-truck" title1="FREE SHIPPING" subtitle1="Free shipping on all US order or order above $200" icon2="icon-life-buoy" title2="SUPPORT 24/7" subtitle2="Contact us 24 hours a day, 7 days a week" icon3="icon-refresh-ccw" title3="30 DAYS RETURN" subtitle3="Simply return it within 30 days for an exchange" icon4="icon-shield" title4="100% PAYMENT SECURE" subtitle4="We ensure secure payment with PEV"][/site-features]'
                    )
                ,
                'template' => 'homepage',
            ],
            [
                'name' => 'Blog',
                'content' => Html::tag(
                    'p',
                    'We always share fashion tips with the hope you guys will find the best style for yourself.<br />Besides, we update the fashion trend as soon as we can.<br />Enjoy your journey!',
                    ['style' => 'text-align: center']
                ),
            ],
            [
                'name' => 'Contact',
                'content' => Html::tag('p', '[contact-form][/contact-form]'),
            ],
            [
                'name' => 'Cookie Policy',
                'content' => Html::tag('h3', 'EU Cookie Consent') .
                    Html::tag(
                        'p',
                        'To use this website we are using Cookies and collecting some data. To be compliant with the EU GDPR we give you to choose if you allow us to use certain Cookies and to collect some Data.'
                    ) .
                    Html::tag('h4', 'Essential Data') .
                    Html::tag(
                        'p',
                        'The Essential Data is needed to run the Site you are visiting technically. You can not deactivate them.'
                    ) .
                    Html::tag(
                        'p',
                        '- Session Cookie: PHP uses a Cookie to identify user sessions. Without this Cookie the Website is not working.'
                    ) .
                    Html::tag(
                        'p',
                        '- XSRF-Token Cookie: Laravel automatically generates a CSRF "token" for each active user session managed by the application. This token is used to verify that the authenticated user is the one actually making the requests to the application.'
                    ),
            ],
            [
                'name' => 'FAQs',
                'content' => Html::tag('div', '[faqs][/faqs]'),
            ],
        ];

        Page::query()->truncate();

        foreach ($pages as $item) {
            $item['user_id'] = 1;

            if (! isset($item['template'])) {
                $item['template'] = 'default';
            }

            $page = Page::query()->create($item);

            SlugHelper::createSlug($page);
        }
    }
}
