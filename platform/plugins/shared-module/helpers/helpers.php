<?php

use Botble\Agent\Models\AgentIssue;
use Botble\Agent\Models\AgentReceipt;
use Botble\Agent\Models\AngentProposalIssue;
use Botble\Agent\Models\ProposalAgentReceipt;
use Botble\Base\Enums\BaseStatusEnum;
use Botble\Base\Facades\Assets;
use Botble\Base\Supports\SortItemsWithChildrenHelper;
use Botble\HubWarehouse\Models\HubReceipt;
use Botble\HubWarehouse\Models\ProposalHubIssue;
use Botble\SaleWarehouse\Models\SaleIssue;
use Botble\SaleWarehouse\Models\SaleProposalIssue;
use Botble\SaleWarehouse\Models\SaleReceipt;
use Botble\Showroom\Models\ShowroomIssue;
use Botble\Showroom\Models\ShowroomProposalIssue;
use Botble\Showroom\Models\ShowroomProposalReceipt;
use Botble\Showroom\Models\ShowRoomReceipt;
use Botble\Showroom\Repositories\Interfaces\UserShowRoomInterface;
use Botble\WarehouseFinishedProducts\Repositories\Interfaces\UserWarehouseInterface;
use Botble\Base\Forms\Fields\TextField;
use Botble\HubWarehouse\Repositories\Interfaces\UserAgentInterface;
use Botble\HubWarehouse\Repositories\Interfaces\UserHubInterface;
use Botble\WarehouseFinishedProducts\Enums\HubStatusEnum;
use Botble\WarehouseFinishedProducts\Models\ActualReceipt as ModelsActualReceipt;
use Botble\WarehouseFinishedProducts\Models\ProposalReceiptProducts;
use Botble\SharedModule\Supports\MyEcommerceNotification;
use Botble\WarehouseFinishedProducts\Models\ReceiptProduct;
use Botble\Base\Models\BaseModel;
use Botble\Base\Supports\AdminNotificationItem;
use Botble\NotiAdminPusher\Events\NotiAdminPusherEvent;
use Botble\Sales\Models\Order;
use Botble\Warehouse\Forms\PurchaseGoodsForm;
use Botble\Warehouse\Models\ActualReceipt;
use Botble\HubWarehouse\Models\ActualReceipt as HubWarehouseActualReceipt;
use Botble\Warehouse\Models\MaterialOut;
use Botble\Warehouse\Models\MaterialProposalPurchase;
use Botble\Warehouse\Models\MaterialReceiptConfirm;
use Botble\Warehouse\Models\ProposalPurchaseGoods;
use Botble\Warehouse\Models\ReceiptPurchaseGoods;
use Botble\WarehouseFinishedProducts\Models\ProductIssue;
use Botble\WarehouseFinishedProducts\Models\ProposalProductIssue;
use Botble\HubWarehouse\Models\ProposalHubReceipt;
use Botble\Warehouse\Models\MaterialOutConfirm;

use Botble\Base\Events\AdminNotificationEvent;
use ArchiElite\EcommerceNotification\Supports\EcommerceNotification;
use ArchiElite\NotificationPlus\Drivers\Telegram;
use ArchiElite\NotificationPlus\Facades\NotificationPlus;
use Botble\Agent\Models\AgentOrder;
use Botble\Base\Forms\FieldOptions\OnOffFieldOption;
use Botble\Base\Forms\Fields\OnOffField;
use Botble\Ecommerce\Models\Order as ModelsOrder;
use Botble\Ecommerce\Models\Product;
use Botble\HubWarehouse\Models\HubIssue;
use Botble\OrderRetail\Models\OrderProduction;
use Botble\OrderHgf\Noti\HGFNoti;
use Botble\OrderRetail\Noti\RetailNoti;
use Botble\OrderStepSetting\Models\Action;
use Botble\SaleWarehouse\Enums\SaleWarehouseStatusEnum;
use Botble\SaleWarehouse\Repositories\Interfaces\SaleUserInterface;
use Botble\Showroom\Models\ExchangeGoods;
use Botble\Showroom\Models\ShowroomOrder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

if (!function_exists('get_warehouse')) {
    function get_warehouse(): array
    {
        $warehouse = app(UserWarehouseInterface::class)
            ->getAllUserWarehouse(['status' => BaseStatusEnum::PUBLISHED], [], ['id', 'name']);
        return app(SortItemsWithChildrenHelper::class)
            ->setChildrenProperty('child_cats')
            ->setItems($warehouse)
            ->sort();
    }
}
if (!function_exists('get_showRoom')) {
    function get_showRoom(): array
    {
        $showroom = app(UserShowRoomInterface::class)
            ->getAllUserShowRoom([], [], ['id', 'name']);
        return app(SortItemsWithChildrenHelper::class)
            ->setChildrenProperty('child_cats')
            ->setItems($showroom)
            ->sort();
    }
}


add_filter(BASE_FILTER_BEFORE_RENDER_FORM, function ($form, $data) {
    if ($data instanceof \Botble\ACL\Models\User) {
        if ((get_class($form) == \Botble\ACL\Forms\ProfileForm::class || get_class($form) == \Botble\ACL\Forms\UserForm::class)) {
            $model = $form->getModel();
            Assets::addScriptsDirectly([
                'vendor/core/plugins/hub-warehouse/js/department-hub.js',
                'vendor/core/plugins/warehouse-finished-products/js/add-warehouse-user.js',
                'vendor/core/plugins/showroom/js/showroom-user.js ',
                'https://unpkg.com/vue-multiselect@2.1.0',
            ])->addStylesDirectly([
                'https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css',
            ])->removeItemDirectly([
                'vendor/core/core/media/css/media.css'
            ]);
            Assets::usingVueJS();
            if (isset($model->warehouse_finished)) {
                $checkSelectSearch = $model->warehouse_finished->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'name' => $detail->name,
                    ];
                });
            }
            if (isset($model->showroom)) {
                $checkSelectedShowRoom = $model->showroom->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'name' => $detail->name,
                    ];
                });
            }
            if (!$form->getformHelper()->hasCustomField('WarehouseUser')) {
                $form->getformHelper()->addCustomField('WarehouseUser', \Botble\WarehouseFinishedProducts\Forms\Fields\WarehouseUser::class);
            }
            if (!$form->getformHelper()->hasCustomField('ShowRoomUser')) {
                $form->getformHelper()->addCustomField('ShowRoomUser', \Botble\Showroom\Forms\Fields\ShowRoomUserField::class);
            }
            if (get_class($form) == \Botble\ACL\Forms\ProfileForm::class) {
                $form
                    ->add('warehouse_id[]', 'WarehouseUser', [
                        'label' => trans('Danh sách kho thành phẩm'),
                        'label_attr' => [
                            'class' => 'control-label required',
                            'id' => 'warehouse',
                        ],
                        'choices' => get_warehouse(),
                        'value' => isset($checkSelectSearch) ? $checkSelectSearch : []
                    ])
                    ->add('showRoom[]', 'ShowRoomUser', [
                        'label' => trans('Danh sách ShowRoom'),
                        'label_attr' => [
                            'class' => 'control-label required',
                            'id' => 'showRoom',
                        ],
                        'choices' => get_showRoom(),
                        'value' => isset($checkSelectedShowRoom) ? $checkSelectedShowRoom : []
                    ]);
            } else {
                $form
                    ->addAfter('agent_id[]', 'warehouse_id[]', 'WarehouseUser', [
                        'label' => trans('Danh sách kho thành phẩm'),
                        'label_attr' => [
                            'class' => 'control-label required',
                            'id' => 'warehouse',
                        ],
                        'choices' => get_warehouse(),
                    ])
                    ->addAfter('agent_id[]', 'showRoom[]', 'ShowRoomUser', [
                        'label' => trans('Danh sách ShowRoom'),
                        'label_attr' => [
                            'class' => 'control-label required',
                            'id' => 'showRoom',
                        ],
                        'choices' => get_showRoom(),
                    ]);;
            }
        }
    }
    if ($data instanceof \Botble\Showroom\Models\Showroom) {
        if (get_class($form) == \Botble\Showroom\Forms\ShowroomForm::class) {
            if (Auth::user()->super_user) {
                $form->addAfter('status', 'provider_banking', 'text', [
                    'label' => 'Provider Banking',
                    'label_attr' => ['class' => 'control-label required'],
                    'attr' => [
                        'placeholder' => 'Provider Banking',
                        'data-counter' => 120,
                        'readonly' => true
                    ],
                ]);
            }
        }
    }
    if (get_class($data) === \Botble\Ecommerce\Models\Product::class) {
        $form
            ->addBefore('description', 'item_diameter', 'text', [
                'label' => trans('plugins/ecommerce::products.form.item_diameter'),
                'values' => $data->getModel()?->item_diameter,
            ])
            ->addBefore('description', 'production_time', 'datePicker', [
                'label' => trans('plugins/ecommerce::products.form.production_time'),
                'label_attr' => ['class' => 'control-label'],
                'values' => $data->getModel()?->production_time,
                'attr' => [
                    'class' => 'form-control datepicker',
                    'data-date-format' => 'Y/m/d',
                ],
            ])
            ->addBefore(
                'is_featured','is_show_home',
                OnOffField::class,
                OnOffFieldOption::make()
                    ->label(trans('plugins/ecommerce::products.form.use_home_name'))
                    ->defaultValue(false)
                    ->toArray()
            )
            ->addBefore('name','home_display_name', 'text', [
                'label' => trans('plugins/ecommerce::products.form.home_display_name'),
                'label_attr' => ['class' => 'required'],
                'attr' => [
                    'data-counter' => 250,
                ],
                'values' => $data->getModel()?->home_display_name,
            ]);
    }
    return $form;
}, 130, 2);

if (!function_exists('get_hub')) {
    function get_hub(): array
    {
        $hubs = app(UserHubInterface::class)
            ->getAllUserHub(['status' => HubStatusEnum::ACTIVE], [], ['id', 'name']);
        return app(SortItemsWithChildrenHelper::class)
            ->setChildrenProperty('child_cats')
            ->setItems($hubs)
            ->sort();
    }
}
if (!function_exists('get_agent')) {
    function get_agent(): array
    {
        $agent = app(UserAgentInterface::class)
            ->getAllUserAgent(['status' => BaseStatusEnum::PUBLISHED], [], ['id', 'name']);
        return app(SortItemsWithChildrenHelper::class)
            ->setChildrenProperty('child_cats')
            ->setItems($agent)
            ->sort();
    }
}


if (!function_exists('get_sale')) {
    function get_sale(): array
    {
        $sales = app(SaleUserInterface::class)
            ->getAllSaleUser(['status' => SaleWarehouseStatusEnum::ACTIVE], [], ['id', 'name']);
        return app(SortItemsWithChildrenHelper::class)
            ->setChildrenProperty('child_cats')
            ->setItems($sales)
            ->sort();
    }
}


add_filter(BASE_FILTER_BEFORE_RENDER_FORM, function ($form, $data) {
    if ($data instanceof \Botble\ACL\Models\User) {
        if (get_class($form) == \Botble\ACL\Forms\ProfileForm::class || get_class($form) == \Botble\ACL\Forms\UserForm::class) {
            $model = $form->getModel();
            if (isset($model->userHub)) {
                $checkSelectSearch = $model->userHub->map(function ($detail) {
                    return [
                        'id' => $detail->hub->id,
                        'name' => $detail->hub ? $detail->hub->name : null,
                    ];
                });
            };

            if (isset($model->agent)) {
                $checkSelectAgent = $model->agent->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'name' => $detail ? $detail->name : null,
                    ];
                });
            };
            if (isset($model->userSale)) {
                $checkSelectSale = $model->userSale->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'name' => $detail->saleWarehouse ? $detail->saleWarehouse->name : null,
                    ];
                });
            };
            $departSelect = [];

            if (get_class($form) == \Botble\ACL\Forms\ProfileForm::class) {
                if (isset($model->department)) {
                    $checkSelectDepartment = $model->department->map(function ($detail) use (&$departSelect) {
                        $departSelect[] = $detail->department_code;
                    });
                }
            }

            Assets::addScriptsDirectly([
                'vendor/core/plugins/hub-warehouse/js/add-hub-user.js',
                'vendor/core/plugins/hub-warehouse/js/add-agent-user.js',
                'vendor/core/plugins/hub-warehouse/js/department-hub.js',
                'vendor/core/plugins/sale-warehouse/js/sale-user.js',
                'https://unpkg.com/vue-multiselect@2.1.0',
            ])->addStylesDirectly([
                'https://unpkg.com/vue-multiselect@2.1.0/dist/vue-multiselect.min.css',
            ])->removeItemDirectly([
                'vendor/core/core/media/css/media.css'
            ]);
            Assets::usingVueJS();
            if (!$form->getformHelper()->hasCustomField('UserHub')) {
                $form->getformHelper()->addCustomField('UserHub', \Botble\HubWarehouse\Forms\Fields\HubUser::class);
            }
            if (!$form->getformHelper()->hasCustomField('agentUser')) {
                $form->getformHelper()->addCustomField('agentUser', \Botble\HubWarehouse\Forms\Fields\AgentUser::class);
            }
            if (!$form->getformHelper()->hasCustomField('saleUser')) {
                $form->getformHelper()->addCustomField('saleUser', \Botble\SaleWarehouse\Forms\Fields\SaleUserFormField::class);
            }

            $form
                ->addAfter('email', 'phone', TextField::class, [
                    'label' => 'Số điện thoại',
                    'required' => true,
                    'attr' => [
                        'placeholder' => '+84...'
                    ],
                    'wrapper' => [
                        'class' => $form->getFormHelper()->getConfig('defaults.wrapper_class') . ' col-md-6',
                    ],
                ]);
            if (\Auth::user()->hasPermission('users.edit-department')) {
                if (get_class($form) == \Botble\ACL\Forms\ProfileForm::class) {
                    $form->add('department_id[]', 'customSelect', [
                        'label' => 'Phòng ban/ Bộ phận',
                        'label_attr' => ['class' => 'control-label required'],
                        'attr' => [
                            'class' => 'form-control select-full',
                            'multiple' => true,
                        ],
                        'choices' => get_departments()->pluck('name', 'code')->toArray(),
                        'selected' => $departSelect,
                    ])
                        ->add('hub_id[]', 'UserHub', [
                            'label' => trans('Danh sách hub'),
                            'label_attr' => [
                                'class' => 'control-label required',
                                'id' => 'hub',
                            ],
                            'choices' => get_hub(),
                            'value' => isset($checkSelectSearch) ? $checkSelectSearch : [],
                        ])
                        ->add('sale_warehouse_id[]', 'saleUser', [
                            'label' => trans('Danh sách Kho sale'),
                            'label_attr' => [
                                'class' => 'control-label required',
                                'id' => 'sale',
                            ],
                            'choices' => get_sale(),
                            'value' => isset($checkSelectSale) ? $checkSelectSale : [],
                        ])
                        ->add('agent_id[]', 'agentUser', [
                            'label' => trans('Danh sách đai lý'),
                            'label_attr' => [
                                'class' => 'control-label required',
                                'id' => 'agent',
                            ],
                            'choices' => get_agent(),
                            'value' => isset($checkSelectAgent) ? $checkSelectAgent : [],

                        ]);
                } else {
                    $form->addAfter('password_confirmation', 'department_id[]', 'customSelect', [
                        'label' => 'Phòng ban/ Bộ phận',
                        'label_attr' => ['class' => 'control-label required'],
                        'attr' => [
                            'class' => 'form-control select-full',
                            'multiple' => true,
                        ],
                        'choices' => get_departments()->pluck('name', 'code')->toArray(),
                        'selected' => $departSelect,
                    ])
                        ->addAfter('department_id[]', 'hub_id[]', 'UserHub', [
                            'label' => trans('Danh sách hub'),
                            'label_attr' => [
                                'class' => 'control-label required',
                                'id' => 'hub',
                            ],
                            'choices' => get_hub(),
                            'value' => isset($checkSelectSearch) ? $checkSelectSearch : [],
                        ])
                        ->addAfter('hub_id[]', 'agent_id[]', 'agentUser', [
                            'label' => trans('Danh sách đai lý'),
                            'label_attr' => [
                                'class' => 'control-label required',
                                'id' => 'agent',
                            ],
                            'choices' => get_agent(),
                            'value' => isset($checkSelectAgent) ? $checkSelectAgent : [],

                        ])
                        ->addAfter('agent_id[]', 'sale_warehouse_id[]', 'saleUser', [
                            'label' => trans('Danh sách Kho sale'),
                            'label_attr' => [
                                'class' => 'control-label required',
                                'id' => 'sale',
                            ],
                            'choices' => get_sale(),
                            'value' => isset($checkSelectSale) ? $checkSelectSale : [],
                        ]);
                }
            }
        }
    }
    return $form;
}, 120, 2);






if (!function_exists('send_notify_cms_and_tele')) {
    function send_notify_cms_and_tele($object, $arrNoti)
    {
        $subTitle = '';
        $billCode = null;
        $isPayment = false;
        $formSend = 'normal';
        //Biến cancel để hiện status huỷ vào thông báo đến admin
        $class = get_class($object);
        switch ($class) {
            case MaterialProposalPurchase::class:
                $typeName = "đơn đề xuất nhập kho nguyên phụ liệu";
                $key = 'proposal';
                $subTitle = 'Kho nguyên phụ liệu';
                break;
            case MaterialReceiptConfirm::class:
                $typeName = "đơn nhập kho nguyên phụ liệu";
                $key = 'receipt';
                $subTitle = 'Kho nguyên phụ liệu';
                break;
            case ProposalPurchaseGoods::class:
                $typeName = "đơn đề xuất mua hàng nguyên phụ liệu";
                $key = 'proposal';
                $subTitle = 'Kho nguyên phụ liệu';
                break;
            case ReceiptPurchaseGoods::class:
                $typeName = "phiếu mua hàng nguyên phụ liệu";
                $key = 'receipt';
                $subTitle = 'Kho nguyên phụ liệu';
                break;
            case MaterialOut::class:
                $typeName = "đơn đề xuất xuất kho nguyên phụ liệu";
                $key = 'proposal';
                $subTitle = 'Kho nguyên phụ liệu';
                break;
            case ActualReceipt::class:
                $typeName = "phiếu thực nhận nhập kho nguyên phụ liệu";
                $key = 'receipt';
                $subTitle = 'Kho nguyên phụ liệu';
                break;
            case MaterialOutConfirm::class:
                $typeName = "phiếu xác nhận xuất kho nguyên phụ liệu";
                $key = 'receipt';
                $subTitle = 'Kho nguyên phụ liệu';
                break;
            case ProposalReceiptProducts::class:
                $typeName = "phiếu đề xuất nhập kho thành phẩm";
                $key = 'proposal';
                $subTitle = 'Kho thành phẩm';
                break;
            case ReceiptProduct::class:
                $typeName = "phiếu nhập kho thành phẩm";
                $key = 'receipt';
                $subTitle = 'Kho thành phẩm';
                break;
            case ModelsActualReceipt::class:
                $typeName = "phiếu thực nhận nhập kho thành phẩm";
                $key = 'receipt';
                $subTitle = 'Kho thành phẩm';
                break;
            case ProposalProductIssue::class:
                $typeName = "phiếu đề xuất xuất kho thành phẩm";
                $key = 'proposal';
                $subTitle = 'Kho thành phẩm';
                break;
            case ProductIssue::class:
                $typeName = "phiếu xuất kho thành phẩm";
                $key = 'receipt';
                $subTitle = 'Kho thành phẩm';
                break;
            case ProposalHubReceipt::class:
                $typeName = "phiếu đề xuất nhập kho hub";
                $key = 'proposal';
                $subTitle = 'Kho HUB';
                break;
            case HubWarehouseActualReceipt::class:
                $typeName = "phiếu thực nhận nhập kho hub";
                $key = 'receipt';
                $subTitle = 'Kho HUB';
                break;
            case HubReceipt::class:
                $typeName = "phiếu nhập kho hub";
                $key = 'receipt';
                $subTitle = 'Kho HUB';
                $billCode = get_proposal_receipt_product_code($object->receipt_code);
                break;
            case ProposalHubIssue::class:
                $typeName = "phiếu đề xuất xuất kho hub";
                $key = 'proposal';
                $subTitle = 'Kho HUB';
                $billCode = get_proposal_issue_product_code($object->proposal_code);
                break;
            case HubIssue::class:
                $typeName = "phiếu xuất kho hub";
                $key = 'receipt';
                $subTitle = 'Kho HUB';
                $billCode = get_proposal_issue_product_code($object->issue_code);
                break;

            case ProposalAgentReceipt::class:
                $typeName = "phiếu đề xuất nhập kho đại lý";
                $key = 'proposal';
                $subTitle = 'Kho đại lý';
                $billCode = get_proposal_receipt_product_code($object->proposal_code);
                break;
            case AgentReceipt::class:
                $typeName = "phiếu nhập kho đại lý";
                $key = 'receipt';
                $subTitle = 'Kho đại lý';
                $billCode = get_proposal_receipt_product_code($object->receipt_code);
                break;
            case AngentProposalIssue::class:
                $typeName = "phiếu yêu cầu trả hàng kho đại lý";
                $key = 'proposal';
                $subTitle = 'Kho đại lý';
                $billCode = get_proposal_issue_product_code($object->proposal_code);
                break;
            case AgentIssue::class:
                $typeName = "phiếu xuất kho đại lý";
                $key = 'receipt';
                $subTitle = 'Kho đại lý';
                $billCode = get_proposal_issue_product_code($object->issue_code);
                break;

            case ShowroomProposalReceipt::class:
                $typeName = "phiếu đề xuất nhập kho showroom";
                $key = 'proposal';
                $subTitle = 'Kho Showroom';
                $billCode = get_proposal_receipt_product_code($object->proposal_code);
                break;
            case ShowRoomReceipt::class:
                $typeName = "phiếu nhập kho showroom";
                $key = 'receipt';
                $subTitle = 'Kho Showroom';
                $billCode = get_proposal_receipt_product_code($object->receipt_code);
                break;

            case ShowroomProposalIssue::class:
                $typeName = "phiếu yêu cầu trả hàng showroom";
                $key = 'proposal';
                $subTitle = 'Kho Showroom';
                $billCode = get_proposal_issue_product_code($object->proposal_code);
                break;
            case ShowroomIssue::class:
                $typeName = "phiếu xuất kho showroom";
                $key = 'receipt';
                $subTitle = 'Kho Showroom';
                $billCode = get_proposal_issue_product_code($object->issue_code);
                break;
            case AgentOrder::class:
                $typeName = "đơn hàng cho đại lý";
                $key = 'order';
                $subTitle = 'Đại lý';
                $isPayment = true;
                break;
            case ShowroomOrder::class:
                $typeName = "đơn hàng cho " . ($object->location?->name ?? 'showroom');
                $key = 'order';
                $subTitle = 'Showroom';
                $isPayment = true;
                break;
            case ExchangeGoods::class:
                $typeName = "đơn đổi trả hàng tại " . ($object->showroom?->showroom?->name ?? 'showroom');
                $key = 'exchange';
                $subTitle = 'Showroom';
                $formSend = 'exchange';
                break;
            case SaleReceipt::class:
                $typeName = "phiếu nhập kho sale";
                $key = 'receipt';
                $subTitle = 'Kho sale';
                $billCode = get_proposal_receipt_product_code($object->receipt_code);
                break;
            case SaleProposalIssue::class:
                $typeName = "phiếu đề xuất xuất kho Sale";
                $key = 'proposal';
                $subTitle = 'Kho sale';
                $billCode = get_proposal_issue_product_code($object->proposal_code);
                break;
            case SaleIssue::class:
                $typeName = "phiếu xuất kho Sale";
                $key = 'receipt';
                $subTitle = 'Kho sale';
                $billCode = get_proposal_issue_product_code($object->issue_code);
                break;
            case Product::class:
                $typeName = "sản phẩm ";
                $key = 'product';
                $subTitle = 'Product';
                $formSend = 'build_product';
                break;
            case RetailNoti::class:
                $typeName = "";
                $key = 'order_retail';
                $subTitle = 'Thay đổi đơn hàng sale retail';
                $formSend = 'retail_hgf';
                $object->title = Arr::get($arrNoti, 'note' , '');
            case HGFNoti::class:
                $typeName = "";
                $key = 'order_retail';
                $subTitle = 'Thay đổi đơn hàng sale retail';
                $billCode = '';
                $formSend = 'retail_hgf';
                $object->title = Arr::get($arrNoti, 'note' , '');
            default:
                break;
        }

        if ($isPayment) {
            $action = Auth::user()->name . $arrNoti['action'] . $typeName;
        }else{
            $action = 'Người ' . $arrNoti['action'] . ": " . Auth::user()->name . ". Tiêu đề: ";
        }
        //Create event for admin
        event(
            new AdminNotificationEvent(
                AdminNotificationItem::make()
                    ->title(ucfirst($arrNoti['action']) . ' ' . $typeName)
                    ->description($action)
                    ->action(trans('plugins/ecommerce::order.new_order_notifications.view'), $arrNoti['route'])
                    ->permission($arrNoti['permission'])
            )
        );
        // event(new NotiAdminPusherEvent($notiItem));

        //Create register
        if (in_array(Telegram::class, NotificationPlus::getAvailableDrivers())) {
            $user = request()->user();
            $chatId = '';
            foreach (LIST_TELEGRAM_CHAT_ID as $keyArr => $classArray) {
                foreach ($classArray as $keyChat => $classItem) {
                    if (in_array($class, $classItem)) {
                        $chatId = $keyChat;
                        break;
                    }
                }
            }
            if ($formSend == 'normal') {
                if ($isPayment) {
                    MyEcommerceNotification::make()
                        ->sendNotifyToDriversUsing($key, '{{ user_name }} đã {{ action }}.', [
                            'sub_title' => $object->where->name,
                            'note' => $object->order->description,
                            'order_code' => $object->order->code,
                            'order_url' => $arrNoti['route'],
                            'order' => $object,
                            'amount' => $object->order->amount,
                            'status' => $arrNoti['status'],
                            'created_at' => $object->created_at,
                            'user_name' => Auth::user()->name,
                            'user_role_name' => $user->roles()->first()?->name,
                            'warehouse_name' => $object->where->name,
                            'action' => $arrNoti['action'] . ' ' . $typeName,
                            'chat_id' => $chatId,
                        ]);
                } else {
                    MyEcommerceNotification::make()
                        ->sendNotifyToDriversUsing($key, '{{ user_name }} đã {{ action }}.', [
                            'sub_title' => $object->warehouse_name,
                            'title' => $object->title,
                            'note' => $object->description,
                            'proposal_id' => $object->code,
                            'proposal_url' => $arrNoti['route'],
                            'proposal' => $object,
                            'status' => $arrNoti['status'],
                            'expected_date' => $object->expected_date,
                            'user_name' => Auth::user()->name,
                            'user_role_name' => $user->roles()->first()?->name,
                            'warehouse_name' => $object->warehouse_name,
                            'action' => $arrNoti['action'] . ' ' . $typeName,
                            'chat_id' => $chatId,
                            'quantity' => $object->quantity,
                            'bill_code' => $billCode,
                        ]);
                }
            } else if ($formSend == 'exchange') {
                MyEcommerceNotification::make()
                    ->sendNotifyToDriversUsing($key, '{{ user_name }} đã {{ action }}.', [
                        'note' => $object->description,
                        'url' => $arrNoti['route'],
                        'exchange' => $object,
                        'status' => $arrNoti['status'],
                        'created_at' => $object->created_at,
                        'user_name' => Auth::user()->name,
                        'showroom_name' => $object->showroom?->showroom?->name,
                        'action' => $arrNoti['action'] . ' ' . $typeName,
                        'chat_id' => $chatId,
                        'quantity' => $object->total_quantity,
                    ]);
            }else if($formSend == 'retail_hgf'){
                MyEcommerceNotification::make()
                ->sendNotifyToDriversUsing($key, '{{ user_name }} đã {{ action }}.', [
                    'title' => $arrNoti['title'],
                    'type_name' => $arrNoti['type_name'],
                    'type_value' => $arrNoti['type_value'],
                    'customer_name' => $arrNoti['customer_name'],
                    'customer_phone' =>  $arrNoti['customer_phone'],
                    'url' => $arrNoti['route'],
                    'user_name' => Auth::user()->name,
                    'note' => Arr::get($arrNoti, 'note' , ''),
                    'chat_id' => $chatId,
                ]);
            }
            else if($formSend == 'build_product'){
                MyEcommerceNotification::make()
                ->sendNotifyToDriversUsing($key, '{{ user_name }} đã {{ action }}.', [
                    // 'note' => $object->description,
                    'url' => $arrNoti['route'],
                    'exchange' => $object,
                    'product_sku' => $object->sku,
                    'product_id' => $object->id,
                    'product_name' => $object->name,
                    'status' => $arrNoti['status'],
                    'updated_at' => $object->updated_at,
                    'user_name' => Auth::user()->name,
                    // 'showroom_name' => $object->showroom?->showroom?->name,
                    'action' => $arrNoti['action'] . ' ' . $typeName,
                    'chat_id' => $chatId,
                    // 'quantity' => $object->total_quantity,
                ]);
            }
        }
    }
}
if (!function_exists('get_provinces_vn')) {
    function get_provinces_vn()
    {
        $data = DB::table('viettel_province')
            ->orderBy('viettel_name')
            ->get();
        $transformedData = $data->mapWithKeys(function ($item) {
            return [$item->viettel_id => $item->viettel_name];
        });
        return $transformedData;
    }
}
if (!function_exists('get_districts_by_province_id')) {
    function get_districts_by_province_id($stateID)
    {
        $data = DB::table('viettel_district')
            ->orderBy('viettel_name')
            ->where('viettel_provice_id', $stateID)
            ->get();
        $transformedData = $data->mapWithKeys(function ($item) {
            return [$item->viettel_id => $item->viettel_name];
        });
        return $transformedData;
    }
}
if (!function_exists('get_ward_by_district_id')) {
    function get_ward_by_district_id($districtID)
    {
        $data = DB::table('viettel_wards')
            ->orderBy('viettel_name')
            ->where('viettel_district_id', $districtID)
            ->get();
        $transformedData = $data->mapWithKeys(function ($item) {
            return [$item->viettel_id => $item->viettel_name];
        });
        return $transformedData;
    }
}
