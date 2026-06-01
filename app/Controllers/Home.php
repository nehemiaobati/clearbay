<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        helper('url');

        $data = [
            'page_title'       => 'ClearBay — Real-Time Ambulance Off-Load Management | Nairobi, Kenya',
            'meta_description' => 'Kenya\'s first real-time ambulance off-load management platform — giving hospital emergency departments and ambulance services live visibility to hand over patients faster.',
            'canonical_url'    => base_url(),
            'robots_tag'       => 'noindex, nofollow',
            'metaImage'       => base_url('images/brand.png'),
        ];

        return view('index', $data);
    }
}
