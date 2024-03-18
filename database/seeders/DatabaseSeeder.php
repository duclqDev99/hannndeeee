<?php

namespace Database\Seeders;

use Botble\ACL\Database\Seeders\UserSeeder;
use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Database\Seeders\CurrencySeeder;
use Botble\Ecommerce\Database\Seeders\ReviewSeeder;
use Botble\Ecommerce\Database\Seeders\ShippingSeeder;
use Botble\Ecommerce\Database\Seeders\TaxSeeder;
use Botble\Language\Database\Seeders\LanguageSeeder;

class DatabaseSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->prepareRun();

        $this->call([
            LanguageSeeder::class,
            BrandSeeder::class,
            CurrencySeeder::class,
            ProductCategorySeeder::class,
            ProductCollectionSeeder::class,
            ProductLabelSeeder::class,
            ProductTagSeeder::class,
            ProductAttributeSeeder::class,
            ProductSeeder::class,
            TaxSeeder::class,
            CustomerSeeder::class,
            ReviewSeeder::class,
            OrderEcommerceSeeder::class,
            ShippingSeeder::class,
            FlashSaleSeeder::class,
            SimpleSliderSeeder::class,
            UserSeeder::class,
            BlogSeeder::class,
            PageSeeder::class,
            SettingSeeder::class,
            StoreLocatorSeeder::class,
            ProductOptionSeeder::class,
            GallerySeeder::class,
            FaqSeeder::class,
            MenuSeeder::class,
            WidgetSeeder::class,
            ThemeOptionSeeder::class,
            CustomerOrderSeeder::class,
            PurchaseOrderSeeder::class,
        ]);

        $this->finished();
    }
}
