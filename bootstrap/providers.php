<?php

use App\Modules\Admin\AdminServiceProvider;
use App\Modules\Catalog\CatalogServiceProvider;
use App\Modules\Fulfillment\FulfillmentServiceProvider;
use App\Modules\Ordering\OrderingServiceProvider;
use App\Modules\Payments\PaymentsServiceProvider;
use App\Modules\Reporting\ReportingServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    AdminServiceProvider::class,
    CatalogServiceProvider::class,
    PaymentsServiceProvider::class,
    ReportingServiceProvider::class,
    OrderingServiceProvider::class,
    FulfillmentServiceProvider::class,
];
