<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobPhoto;

class JobPhotoController extends Controller
{
    public function index()
    {
        $photos = JobPhoto::with(['booking', 'provider'])
            ->latest()
            ->paginate(24);

        return view('admin.photos.index', compact('photos'));
    }
}
