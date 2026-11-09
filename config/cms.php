<?php

/*
|--------------------------------------------------------------------------
| Central CMS defaults
|--------------------------------------------------------------------------
|
| Starting values for the central CMS. The CmsSeeder writes them to the
| database, where administrators manage them. Stored values always take
| precedence; these defaults only fill keys that have not been saved yet.
|
| Links accept either a named route ("route" + optional "parameters") or a
| site-relative / absolute "url".
|
*/

return [
    'settings' => [
        'branding' => [
            'site_name' => 'Cambo Hub',
            'name' => 'Cambo',
            'name_accent' => 'Hub',
            'tagline' => 'វេទិកាសេវាកម្មចម្រុះសម្រាប់កម្ពុជា',
            'tagline_locale' => 'km',
            'logo' => 'images/branding/cambo-hub-mark.svg',
        ],
        'navigation' => [
            ['label' => 'Home', 'route' => 'home', 'icon' => 'home'],
            ['label' => 'Our mission', 'route' => 'mission', 'icon' => 'community'],
            ['label' => 'Our vision', 'route' => 'vision', 'icon' => 'education'],
            ['label' => 'Contact', 'route' => 'contact', 'icon' => 'messages'],
        ],
        'hero' => [
            'eyebrow' => 'WELCOME TO CAMBO HUB',
            'title' => 'Your everyday Cambodia.',
            'highlight' => 'One connected place.',
            'intro' => 'News, jobs, shopping, businesses, property, events, and community — discover everything Cambodia has to offer with one account.',
            'image' => 'images/backgrounds/cambodia-platform.webp',
            'image_alt' => 'Angkor Wat and a Cambodian city connected by blue and red light trails',
            'caption_title' => 'Cambodia, connected.',
            'caption_label' => 'YOUR ALL-IN-ONE PLATFORM',
        ],
        'search' => [
            'placeholder' => 'Search jobs, products, businesses, property, events…',
            'suggestions' => ['Accountant', 'Apartment', 'Coffee', 'Smartphone', 'Workshop'],
        ],
        'membership' => [
            'eyebrow' => 'ONE ACCOUNT. EVERY SERVICE.',
            'heading' => 'Join Cambo Hub today.',
            'body' => 'Apply for jobs, save products, follow businesses, and book events with a single secure account.',
            'register_label' => 'Create your account',
            'login_label' => 'I already have an account',
        ],
        'footer' => [
            'tagline' => ['Cambodia, connected.', 'Opportunity, closer.'],
            'columns' => [
                [
                    'heading' => 'Explore',
                    'links' => [
                        ['label' => 'News', 'route' => 'search', 'parameters' => ['type' => 'news']],
                        ['label' => 'Jobs', 'route' => 'search', 'parameters' => ['type' => 'job']],
                        ['label' => 'Products', 'route' => 'search', 'parameters' => ['type' => 'product']],
                        ['label' => 'Events', 'route' => 'search', 'parameters' => ['type' => 'event']],
                    ],
                ],
                [
                    'heading' => 'Local',
                    'links' => [
                        ['label' => 'Businesses', 'route' => 'search', 'parameters' => ['type' => 'business']],
                        ['label' => 'Properties', 'route' => 'search', 'parameters' => ['type' => 'property']],
                        ['label' => 'Community', 'route' => 'search', 'parameters' => ['type' => 'social-post']],
                    ],
                ],
                [
                    'heading' => 'Cambo Hub',
                    'links' => [
                        ['label' => 'Our mission', 'route' => 'mission'],
                        ['label' => 'Our vision', 'route' => 'vision'],
                        ['label' => 'Contact', 'route' => 'contact'],
                    ],
                ],
            ],
            'copyright' => 'Cambo Hub. All rights reserved.',
            'note' => 'Made for Cambodia. Built around you.',
        ],
    ],

    /*
    | Homepage sections in their default display order.
    */
    'homepage_sections' => [
        'service-category' => ['eyebrow' => 'EVERYTHING IN ONE PLACE', 'heading' => 'Explore our services', 'subheading' => 'Every Cambo Hub service, one tap away.', 'item_limit' => 12],
        'story' => ['eyebrow' => 'HAPPENING NOW', 'heading' => 'Stories', 'subheading' => null, 'item_limit' => 10],
        'promotional-banner' => ['eyebrow' => null, 'heading' => 'Promotions', 'subheading' => null, 'item_limit' => 2],
        'news' => ['eyebrow' => 'STAY INFORMED', 'heading' => 'Latest news', 'subheading' => 'Headlines from across Cambodia.', 'item_limit' => 4, 'link_label' => 'All news', 'link_url' => '/search?type=news'],
        'job' => ['eyebrow' => 'YOUR NEXT STEP', 'heading' => 'Featured jobs', 'subheading' => 'Opportunities from trusted Cambodian employers.', 'item_limit' => 6, 'link_label' => 'All jobs', 'link_url' => '/search?type=job'],
        'product' => ['eyebrow' => 'SHOP LOCAL', 'heading' => 'Popular products', 'subheading' => 'What people across Cambodia are buying this week.', 'item_limit' => 8, 'link_label' => 'All products', 'link_url' => '/search?type=product'],
        'advertisement' => ['eyebrow' => 'SPONSORED', 'heading' => 'Advertisements', 'subheading' => null, 'item_limit' => 2],
        'business' => ['eyebrow' => 'SUPPORT LOCAL', 'heading' => 'Business listings', 'subheading' => 'Discover trusted shops, services, and companies near you.', 'item_limit' => 6, 'link_label' => 'All businesses', 'link_url' => '/search?type=business'],
        'property' => ['eyebrow' => 'FIND YOUR PLACE', 'heading' => 'Property advertisements', 'subheading' => 'Homes, apartments, and commercial spaces for rent or sale.', 'item_limit' => 4, 'link_label' => 'All properties', 'link_url' => '/search?type=property'],
        'social-post' => ['eyebrow' => 'FROM THE COMMUNITY', 'heading' => 'Social highlights', 'subheading' => 'What people are sharing on Cambo Hub.', 'item_limit' => 3, 'link_label' => 'Community', 'link_url' => '/search?type=social-post'],
        'event' => ['eyebrow' => 'DON’T MISS OUT', 'heading' => 'Featured events', 'subheading' => 'Workshops, festivals, and meetups coming up soon.', 'item_limit' => 4, 'link_label' => 'All events', 'link_url' => '/search?type=event'],
    ],
];
