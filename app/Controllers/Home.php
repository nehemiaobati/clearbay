<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        helper('url');

        $data = [
            'pageTitle'       => 'ClearBay — Real-Time Ambulance Off-Load Management | Nairobi, Kenya',
            'metaDescription' => 'Kenya\'s first real-time ambulance off-load management platform — giving hospital emergency departments and ambulance services live visibility to hand over patients faster.',
            'canonicalUrl'    => base_url(),
            'robotsTag'       => 'noindex, nofollow',
            'metaImage'       => base_url('assets/images/brand.png'),
        ];

        return view('index', $data);
    }
}
