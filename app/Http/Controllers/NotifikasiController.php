<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    public function tandaiDibaca(string $id): RedirectResponse
    {
        $notif = Auth::user()->notifications()->findOrFail($id);
        $notif->markAsRead();

        return back();
    }
}