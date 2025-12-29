<?php

namespace App\Http\Controllers;

class AdminController extends Controller
{
public function approve($id) {
    $data = KnowledgeBase::findOrFail($id);
    $data->update(['is_approved' => true]);
    return back();
}
}
