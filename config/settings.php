<?php

/*
|--------------------------------------------------------------------------
| Platform Settings
|--------------------------------------------------------------------------
|
| Every setting administrators can change on Admin › Platform settings.
| Each group is a tab; each field is stored under "{group}.{field}".
| "default" is used until an administrator saves a value.
|
| Field types: text, textarea, email, url, number, boolean, select, multiselect.
| Module on/off switches are added automatically from App\Enums\PlatformModule.
|
*/

return [

    'groups' => [

        'general' => [
            'label' => 'General',
            'description' => 'Website identity and contact details shown across the platform.',
            'icon' => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008z',
            'fields' => [
                'site_name' => ['label' => 'Website name', 'type' => 'text', 'default' => 'Cambo Hub', 'rules' => ['required', 'string', 'max:100']],
                'tagline' => ['label' => 'Tagline', 'type' => 'text', 'default' => 'Cambodia all-in-one platform', 'rules' => ['nullable', 'string', 'max:150']],
                'contact_email' => ['label' => 'Contact email', 'type' => 'email', 'default' => 'hello@cambohub.com', 'rules' => ['nullable', 'email', 'max:255']],
                'support_phone' => ['label' => 'Support phone', 'type' => 'text', 'default' => '', 'rules' => ['nullable', 'string', 'max:50'], 'placeholder' => '+855 12 345 678'],
                'address' => ['label' => 'Office address', 'type' => 'textarea', 'default' => 'Phnom Penh, Cambodia', 'rules' => ['nullable', 'string', 'max:500']],
            ],
        ],

        'localization' => [
            'label' => 'Languages & currency',
            'description' => 'Languages, currencies and time zone used by the public website.',
            'icon' => 'M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802',
            'fields' => [
                'default_locale' => ['label' => 'Default language', 'type' => 'select', 'default' => 'en', 'options' => ['en' => 'English', 'km' => 'ខ្មែរ (Khmer)'], 'rules' => ['required', 'in:en,km']],
                'supported_locales' => ['label' => 'Available languages', 'type' => 'multiselect', 'default' => ['en', 'km'], 'options' => ['en' => 'English', 'km' => 'ខ្មែរ (Khmer)'], 'rules' => ['required', 'array', 'min:1'], 'item_rules' => ['in:en,km']],
                'default_currency' => ['label' => 'Default currency', 'type' => 'select', 'default' => 'USD', 'options' => ['USD' => 'US dollar (USD)', 'KHR' => 'Cambodian riel (KHR)'], 'rules' => ['required', 'in:USD,KHR']],
                'supported_currencies' => ['label' => 'Accepted currencies', 'type' => 'multiselect', 'default' => ['USD', 'KHR'], 'options' => ['USD' => 'US dollar (USD)', 'KHR' => 'Cambodian riel (KHR)'], 'rules' => ['required', 'array', 'min:1'], 'item_rules' => ['in:USD,KHR']],
                'khr_per_usd' => ['label' => 'Exchange rate (KHR per 1 USD)', 'type' => 'number', 'default' => 4100, 'rules' => ['required', 'integer', 'min:1', 'max:100000']],
                'timezone' => ['label' => 'Time zone', 'type' => 'select', 'default' => 'Asia/Phnom_Penh', 'options' => ['Asia/Phnom_Penh' => 'Asia/Phnom_Penh (UTC+7)', 'UTC' => 'UTC'], 'rules' => ['required', 'in:Asia/Phnom_Penh,UTC']],
            ],
        ],

        'email' => [
            'label' => 'Email',
            'description' => 'Sender details for emails sent by the platform.',
            'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75',
            'fields' => [
                'from_name' => ['label' => 'Sender name', 'type' => 'text', 'default' => 'Cambo Hub', 'rules' => ['required', 'string', 'max:100']],
                'from_address' => ['label' => 'Sender email', 'type' => 'email', 'default' => 'no-reply@cambohub.com', 'rules' => ['required', 'email', 'max:255']],
                'reply_to' => ['label' => 'Reply-to email', 'type' => 'email', 'default' => '', 'rules' => ['nullable', 'email', 'max:255']],
                'admin_alerts_address' => ['label' => 'Admin alerts email', 'type' => 'email', 'default' => '', 'rules' => ['nullable', 'email', 'max:255'], 'help' => 'Where platform alerts for administrators are sent.'],
            ],
        ],

        'notifications' => [
            'label' => 'Notifications',
            'description' => 'Which automatic notifications the platform sends.',
            'icon' => 'M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0',
            'fields' => [
                'login_alerts' => ['label' => 'New sign-in alerts', 'type' => 'boolean', 'default' => true, 'rules' => ['boolean'], 'help' => 'Email users when their account is signed in to from a new IP address.'],
                'new_user_alerts' => ['label' => 'New registration alerts', 'type' => 'boolean', 'default' => false, 'rules' => ['boolean'], 'help' => 'Email the admin alerts address when someone registers.'],
                'submission_alerts' => ['label' => 'Pending approval alerts', 'type' => 'boolean', 'default' => true, 'rules' => ['boolean'], 'help' => 'Email reviewers when new content is waiting for approval.'],
            ],
        ],

        'registration' => [
            'label' => 'Registration',
            'description' => 'Who can create an account on the public website.',
            'icon' => 'M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z',
            'fields' => [
                'enabled' => ['label' => 'Allow new registrations', 'type' => 'boolean', 'default' => true, 'rules' => ['boolean'], 'help' => 'When off, the sign-up page is closed. Existing users can still sign in.'],
                'closed_message' => ['label' => 'Message when registration is closed', 'type' => 'textarea', 'default' => 'New registrations are temporarily closed. Please check back soon.', 'rules' => ['nullable', 'string', 'max:500']],
            ],
        ],

        'uploads' => [
            'label' => 'Uploads',
            'description' => 'Limits for files people upload to the platform.',
            'icon' => 'M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5',
            'fields' => [
                'max_size_mb' => ['label' => 'Maximum file size (MB)', 'type' => 'number', 'default' => 10, 'rules' => ['required', 'integer', 'min:1', 'max:100']],
                'image_types' => ['label' => 'Allowed image types', 'type' => 'multiselect', 'default' => ['jpg', 'jpeg', 'png', 'webp'], 'options' => ['jpg' => 'JPG', 'jpeg' => 'JPEG', 'png' => 'PNG', 'webp' => 'WebP', 'gif' => 'GIF', 'svg' => 'SVG'], 'rules' => ['required', 'array', 'min:1'], 'item_rules' => ['in:jpg,jpeg,png,webp,gif,svg']],
                'document_types' => ['label' => 'Allowed document types', 'type' => 'multiselect', 'default' => ['pdf'], 'options' => ['pdf' => 'PDF', 'doc' => 'DOC', 'docx' => 'DOCX', 'xls' => 'XLS', 'xlsx' => 'XLSX', 'csv' => 'CSV'], 'rules' => ['required', 'array', 'min:1'], 'item_rules' => ['in:pdf,doc,docx,xls,xlsx,csv']],
            ],
        ],

        'seo' => [
            'label' => 'SEO',
            'description' => 'Default search engine details, used when a page does not set its own.',
            'icon' => 'M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z',
            'fields' => [
                'meta_title' => ['label' => 'Default page title', 'type' => 'text', 'default' => 'Cambo Hub — Cambodia all-in-one platform', 'rules' => ['required', 'string', 'max:70'], 'help' => 'Up to 70 characters.'],
                'meta_description' => ['label' => 'Default description', 'type' => 'textarea', 'default' => 'News, jobs, marketplace, businesses and more for Cambodia — all in one place.', 'rules' => ['nullable', 'string', 'max:160'], 'help' => 'Up to 160 characters.'],
                'meta_keywords' => ['label' => 'Keywords', 'type' => 'text', 'default' => 'Cambodia, news, jobs, marketplace', 'rules' => ['nullable', 'string', 'max:255']],
                'og_image_url' => ['label' => 'Social sharing image URL', 'type' => 'url', 'default' => '', 'rules' => ['nullable', 'url', 'max:500']],
                'allow_indexing' => ['label' => 'Allow search engines to index the site', 'type' => 'boolean', 'default' => true, 'rules' => ['boolean']],
            ],
        ],

        'search' => [
            'label' => 'Search',
            'description' => 'How the public website search behaves.',
            'icon' => 'M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75',
            'fields' => [
                'enabled' => ['label' => 'Enable site search', 'type' => 'boolean', 'default' => true, 'rules' => ['boolean']],
                'results_per_page' => ['label' => 'Results per page', 'type' => 'number', 'default' => 20, 'rules' => ['required', 'integer', 'min:5', 'max:100']],
                'min_query_length' => ['label' => 'Minimum search length (characters)', 'type' => 'number', 'default' => 2, 'rules' => ['required', 'integer', 'min:1', 'max:10']],
            ],
        ],

        'footer' => [
            'label' => 'Footer',
            'description' => 'Text and social links at the bottom of every public page. Footer links are managed in Website › Menus.',
            'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h12A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6zM3.75 15.75h16.5',
            'fields' => [
                'about_en' => ['label' => 'About text (English)', 'type' => 'textarea', 'default' => 'Cambo Hub brings news, jobs, shopping, businesses and services for Cambodia together in one place.', 'rules' => ['nullable', 'string', 'max:500']],
                'about_km' => ['label' => 'About text (Khmer)', 'type' => 'textarea', 'default' => '', 'rules' => ['nullable', 'string', 'max:500'], 'help' => 'Optional — the English text is shown when empty.'],
                'copyright' => ['label' => 'Copyright line', 'type' => 'text', 'default' => '© {year} Cambo Hub. All rights reserved.', 'rules' => ['required', 'string', 'max:150'], 'help' => '{year} is replaced with the current year.'],
                'facebook_url' => ['label' => 'Facebook page', 'type' => 'url', 'default' => '', 'rules' => ['nullable', 'url', 'max:255']],
                'telegram_url' => ['label' => 'Telegram channel', 'type' => 'url', 'default' => '', 'rules' => ['nullable', 'url', 'max:255']],
                'youtube_url' => ['label' => 'YouTube channel', 'type' => 'url', 'default' => '', 'rules' => ['nullable', 'url', 'max:255']],
                'tiktok_url' => ['label' => 'TikTok', 'type' => 'url', 'default' => '', 'rules' => ['nullable', 'url', 'max:255']],
                'linkedin_url' => ['label' => 'LinkedIn', 'type' => 'url', 'default' => '', 'rules' => ['nullable', 'url', 'max:255']],
            ],
        ],

        'maintenance' => [
            'label' => 'Maintenance',
            'description' => 'Temporarily close the public website. Administrators keep full access.',
            'icon' => 'M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z',
            'fields' => [
                'enabled' => ['label' => 'Maintenance mode', 'type' => 'boolean', 'default' => false, 'rules' => ['boolean'], 'help' => 'Visitors see the maintenance page. The admin panel keeps working.'],
                'message' => ['label' => 'Maintenance message', 'type' => 'textarea', 'default' => "We're making improvements to Cambo Hub and will be back shortly. Thank you for your patience.", 'rules' => ['required', 'string', 'max:500']],
            ],
        ],

    ],

    /*
    | Tab for the module on/off switches. Its fields are generated from PlatformModule.
    */
    'modules' => [
        'label' => 'Modules',
        'description' => 'Turn platform services on or off. Turned-off modules disappear from the public website.',
        'icon' => 'M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875s-2.25.84-2.25 1.875c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 01-.657.643 48.39 48.39 0 01-4.163-.3c.186 1.613.293 3.25.315 4.907a.656.656 0 01-.658.663v0c-.355 0-.676-.186-.959-.401a1.647 1.647 0 00-1.003-.349c-1.036 0-1.875 1.007-1.875 2.25s.84 2.25 1.875 2.25c.369 0 .713-.128 1.003-.349.283-.215.604-.401.959-.401v0c.31 0 .555.26.532.57a48.039 48.039 0 01-.642 5.056c1.518.19 3.058.309 4.616.354a.64.64 0 00.657-.643v0c0-.355-.186-.676-.401-.959a1.647 1.647 0 01-.349-1.003c0-1.035 1.008-1.875 2.25-1.875 1.243 0 2.25.84 2.25 1.875 0 .369-.128.713-.349 1.003-.215.283-.4.604-.4.959v0c0 .333.277.599.61.58a48.1 48.1 0 005.427-.63 48.05 48.05 0 00.582-4.717.532.532 0 00-.533-.57v0c-.355 0-.676.186-.959.401-.29.221-.634.349-1.003.349-1.035 0-1.875-1.007-1.875-2.25s.84-2.25 1.875-2.25c.37 0 .713.128 1.003.349.283.215.604.401.96.401v0a.656.656 0 00.658-.663 48.422 48.422 0 00-.37-5.36c-1.886.342-3.81.574-5.766.689a.578.578 0 01-.61-.58v0z',
    ],

    /*
    | Cache key for resolved settings. Settings are cached until an administrator saves a change.
    */
    'cache_key' => 'platform-settings',

];
