<?php

namespace App\Exceptions;

use RuntimeException;

class WarrantyProductRestrictedException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('ثبت گارانتی این محصول از طریق سایت امکان‌پذیر نیست. لطفاً در نرم‌افزار روبیکا به شماره ۰۹۳۳۷۰۳۹۴۳۸ پیام ارسال کنید یا با واحد مرکزی خدمات به شماره ۰۲۱۹۱۶۹۳۷۹۷ تماس بگیرید.');
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
