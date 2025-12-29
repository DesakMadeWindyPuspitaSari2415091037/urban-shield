<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ContributorController extends Controller
{
    public function store(Request $request) {
    $request->validate([
        'judul' => 'required',
        'file_pdf' => 'nullable|mimes:pdf|max:2048', // Batas 2MB [cite: 20]
    ]);

    $path = $request->file('file_pdf') ? $request->file('file_pdf')->store('knowledge_pdfs') : null;

    KnowledgeBase::create([
        'judul' => $request->judul,
        'isi_teks' => $request->isi_teks,
        'file_path' => $path,
        'tipe' => $path ? 'pdf' : 'teks',
        'user_id' => auth()->id(),
        'is_approved' => false // Default belum di-approve 
    ]);

    return back()->with('success', 'Data berhasil dikirim, menunggu persetujuan Admin.');
}
}
