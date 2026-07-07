<?php

return [
    'groups' => [
        [
            'key' => 'dashboard',
            'label' => 'داشبورد',
            'permissions' => [
                [
                    'name' => 'dashboard.view',
                    'label' => 'مشاهده داشبورد',
                    'description' => 'دسترسی به صفحه اصلی پنل خدمات پس از فروش.',
                ],
            ],
        ],
        [
            'key' => 'warranty',
            'label' => 'گارانتی',
            'permissions' => [
                [
                    'name' => 'warranties.activate',
                    'label' => 'فعال سازی گارانتی',
                    'description' => 'ثبت درخواست فعال سازی گارانتی برای مشتری.',
                ],
                [
                    'name' => 'warranties.view_own',
                    'label' => 'مشاهده گارانتی های خود',
                    'description' => 'مشاهده گارانتی های ثبت شده برای کاربر جاری.',
                ],
                [
                    'name' => 'warranties.view_any',
                    'label' => 'مشاهده همه گارانتی ها',
                    'description' => 'مشاهده لیست مدیریتی گارانتی های همه کاربران.',
                ],
            ],
        ],
        [
            'key' => 'users',
            'label' => 'کاربران',
            'permissions' => [
                [
                    'name' => 'users.view_any',
                    'label' => 'مشاهده کاربران',
                    'description' => 'مشاهده لیست کاربران پنل.',
                ],
                [
                    'name' => 'customers.view_any',
                    'label' => 'مشاهده مشتری ها',
                    'description' => 'مشاهده و جست و جوی لیست مشتری های ثبت شده در سامانه.',
                ],
            ],
        ],
        [
            'key' => 'roles',
            'label' => 'نقش ها و دسترسی ها',
            'permissions' => [
                [
                    'name' => 'roles.manage',
                    'label' => 'مدیریت نقش ها',
                    'description' => 'ایجاد و ویرایش نقش ها و دسترسی های آن ها.',
                ],
            ],
        ],
        [
            'key' => 'settings',
            'label' => 'تنظیمات',
            'permissions' => [
                [
                    'name' => 'settings.manage',
                    'label' => 'مدیریت تنظیمات',
                    'description' => 'دسترسی به صفحه تنظیمات مدیریتی سامانه.',
                ],
            ],
        ],
    ],
];
