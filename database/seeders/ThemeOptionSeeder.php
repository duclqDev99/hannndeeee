<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Setting\Models\Setting;
use Botble\Theme\Facades\Theme;

class ThemeOptionSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('general');

        $theme = Theme::getThemeName();

        Setting::query()->where('key', 'LIKE', 'theme-' . $theme . '-%')->delete();

        Setting::query()->insertOrIgnore([
            [
                'key' => 'theme',
                'value' => $theme,
            ],
            [
                'key' => 'theme-' . $theme . '-site_title',
                'value' => 'HASA',
            ],
            [
                'key' => 'theme-' . $theme . '-seo_title',
                'value' => 'HASA - Multipurpose Laravel Fashion Shop',
            ],
            [
                'key' => 'theme-' . $theme . '-seo_description',
                'value' => 'HASA is made specifically for a Fashion store, but also well suited for an electronics store, furniture store, etc. It’s clean and minimal. Every page is fully responsive, making your creation look good on any device.',
            ],
            [
                'key' => 'theme-' . $theme . '-copyright',
                'value' => '© ' . now()->format('Y') . ' Botble Technologies. All Rights Reserved.',
            ],
            [
                'key' => 'theme-' . $theme . '-favicon',
                'value' => 'general/favicon.png',
            ],
            [
                'key' => 'theme-' . $theme . '-logo',
                'value' => 'general/logo.png',
            ],
            [
                'key' => 'admin_favicon',
                'value' => 'general/favicon.png',
            ],
            [
                'key' => 'admin_logo',
                'value' => 'general/logo-light.png',
            ],
            [
                'key' => 'theme-' . $theme . '-seo_og_image',
                'value' => 'general/open-graph-image.png',
            ],
            [
                'key' => 'theme-' . $theme . '-address',
                'value' => 'North Link Building, 10 Admiralty Street, 757695 Singapore',
            ],
            [
                'key' => 'theme-' . $theme . '-hotline',
                'value' => '18006268',
            ],
            [
                'key' => 'theme-' . $theme . '-email',
                'value' => 'sales@botble.com',
            ],
            [
                'key' => 'theme-' . $theme . '-facebook',
                'value' => 'https://facebook.com',
            ],
            [
                'key' => 'theme-' . $theme . '-twitter',
                'value' => 'https://twitter.com',
            ],
            [
                'key' => 'theme-' . $theme . '-youtube',
                'value' => 'https://youtube.com',
            ],
            [
                'key' => 'theme-' . $theme . '-linkedin',
                'value' => 'https://linkedin.com',
            ],
            [
                'key' => 'theme-' . $theme . '-pinterest',
                'value' => 'https://pinterest.com',
            ],
            [
                'key' => 'theme-' . $theme . '-instagram',
                'value' => 'https://instagram.com',
            ],
            [
                'key' => 'theme-' . $theme . '-homepage_id',
                'value' => '1',
            ],
            [
                'key' => 'theme-' . $theme . '-blog_page_id',
                'value' => '2',
            ],
            [
                'key' => 'theme-' . $theme . '-cookie_consent_message',
                'value' => 'Your experience on this site will be improved by allowing cookies ',
            ],
            [
                'key' => 'theme-' . $theme . '-cookie_consent_learn_more_url',
                'value' => url('cookie-policy'),
            ],
            [
                'key' => 'theme-' . $theme . '-cookie_consent_learn_more_text',
                'value' => 'Cookie Policy',
            ],
            [
                'key' => 'theme-' . $theme . '-enabled_sticky_header',
                'value' => 'yes',
            ],
            [
                'key' => 'theme-' . $theme . '-product_page_banner_title',
                'value' => 'Enjoy Shopping with us',
            ],
        ]);

        $socialLinks = [
            [
                [
                    'key' => 'social-name',
                    'value' => 'Facebook',
                ],
                [
                    'key' => 'social-icon',
                    'value' => 'fa fa-facebook',
                ],
                [
                    'key' => 'social-url',
                    'value' => 'https://facebook.com.com',
                ],
            ],
            [
                [
                    'key' => 'social-name',
                    'value' => 'Twitter',
                ],
                [
                    'key' => 'social-icon',
                    'value' => 'fa fa-twitter',
                ],
                [
                    'key' => 'social-url',
                    'value' => 'https://twitter.com',
                ],
            ],
            [
                [
                    'key' => 'social-name',
                    'value' => 'Instagram',
                ],
                [
                    'key' => 'social-icon',
                    'value' => 'fa fa-instagram',
                ],
                [
                    'key' => 'social-url',
                    'value' => 'https://instagram.com',
                ],
            ],
            [
                [
                    'key' => 'social-name',
                    'value' => 'Pinterest',
                ],
                [
                    'key' => 'social-icon',
                    'value' => 'fa fa-pinterest',
                ],
                [
                    'key' => 'social-url',
                    'value' => 'https://pinterest.com',
                ],
            ],
            [
                [
                    'key' => 'social-name',
                    'value' => 'Linkedin',
                ],
                [
                    'key' => 'social-icon',
                    'value' => 'fa fa-linkedin',
                ],
                [
                    'key' => 'social-url',
                    'value' => 'https://linkedin.com',
                ],
            ],
            [
                [
                    'key' => 'social-name',
                    'value' => 'Youtube',
                ],
                [
                    'key' => 'social-icon',
                    'value' => 'fa fa-youtube',
                ],
                [
                    'key' => 'social-url',
                    'value' => 'https://youtube.com',
                ],
            ],
        ];

        Setting::query()->insertOrIgnore([
            'key' => 'theme-' . $theme . '-social_links',
            'value' => json_encode($socialLinks),
        ]);
    }
}
