<?php

namespace App\Exceptions;

use RuntimeException;

class WarrantyProductRestrictedException extends RuntimeException
{
    public function __construct(bool $requiresInstallation)
    {
        parent::__construct($requiresInstallation
            ? 'الزامی است نصب این محصول توسط سرویسکار شرکت بروات انجام شود. لطفاً جهت ثبت درخواست نصب رایگان به شماره 09337039438 از روبیکا پیام دهید و یا در غیر این صورت با واحد خدمات مرکزی بروات به شماره تماس 02191693797 تماس حاصل فرمایید.'
            : 'فعال‌سازی آنلاین گارانتی این کالا غیرفعال است. برای پیگیری با واحد خدمات مرکزی بروات تماس بگیرید.');
    }

    /**
     * @return array{reason: string, message: string, rubika_phone: string, phone: string, phone_href: string}
     */
    public function supportDetails(): array
    {
        return [
            'reason' => 'warranty_product_restricted',
            'message' => $this->getMessage(),
            'rubika_phone' => '09337039438',
            'phone' => '02191693797',
            'phone_href' => 'tel:02191693797',
        ];
    }
}
