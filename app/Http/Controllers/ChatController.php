<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    private $intents = [
        'definisi' => [
            'apa itu', 'itu apa', 'itu apaan', 'itu apasih', 'apa maksudnya', 'apa artinya', 'apa maknanya',
            'apa pengertiannya', 'apa definisinya', 'maksudnya apa', 'artinya apa', 'maknanya apa',
            'jelasin dong', 'tolong jelaskan', 'bisa dijelasin gak', 'itu tuh apa', 'itu tuh maksudnya apa',
            'itu tuh gimana', 'itu tuh kayak gimana', 'itu tuh tentang apa', 'itu tuh penjelasannya gimana',
            'itu tuh bisa dijelasin ga', 'apa arti kata', 'apa yang dimaksud dengan', 'apa yang disebut dengan',
            'apa yang dinamakan', 'apa pengertian dari', 'apa itu yang dimaksud', 'apa sih yang dimaksud dengan',
            'apa sih arti dari'
        ],
        'gejala' => [
            'apa gejalanya', 'gejalanya apa', 'ciri-cirinya apa', 'tanda-tandanya apa', 'bagaimana gejalanya',
            'apa saja gejalanya', 'gimana tahu orang itu kenapa', 'tanda orang yang mengalami',
            'apa yang dirasakan', 'apa yang terjadi pada tubuh', 'apa yang dialami',
            'apa ciri-ciri kondisi ini', 'apa tanda-tanda awalnya', 'apa yang muncul saat kejadian',
            'apa yang dirasakan korban'
        ],
        'prosedur' => [
            'apa yang harus dilakukan', 'langkah-langkahnya apa', 'gimana cara menolong',
            'apa pertolongan pertamanya', 'cara mengatasi', 'tindakan darurat untuk',
            'apa yang dilakukan saat kejadian', 'bagaimana cara evakuasi',
            'apa yang dilakukan saat kondisi ini', 'apa yang harus dilakukan jika terjadi',
            'apa yang dilakukan setelah kejadian', 'bagaimana penanganannya',
            'apa saja yang perlu disiapkan', 'apa yang dilakukan saat darurat',
            'apa yang dilakukan saat bencana'
        ],
        'kategori' => [
            'termasuk kategori apa', 'jenis kasus apa', 'ini termasuk medis atau trauma',
            'ini masuk kategori apa', 'ini tipe kasus apa', 'ini termasuk bencana apa',
            'ini termasuk jenis apa', 'ini termasuk klasifikasi apa', 'ini termasuk kelompok apa',
            'ini termasuk tipe kejadian apa'
        ]
    ];

    public function index()
    {
        return view('chat');
    }

    public function send(Request $request)
    {
        $userMessage = strtolower($request->input('message'));

        // Deteksi sapaan
        $sapaan = ['hi', 'halo', 'hai', 'hello', 'selamat pagi', 'selamat siang', 'selamat sore', 'selamat malam'];
        foreach ($sapaan as $salam) {
            if (Str::contains($userMessage, $salam)) {
                return response()->json([
                    'answer' => 'Hai! Saya UrbanShield, siap bantu kamu soal kebencanaan dan keselamatan.'
                ]);
            }
        }

        // Deteksi kategori berdasarkan intent
        $detectedKategori = null;
        foreach ($this->intents as $kategori => $pola) {
            foreach ($pola as $frasa) {
                if (Str::contains($userMessage, $frasa)) {
                    $detectedKategori = $kategori;
                    break 2;
                }
            }
        }

        // Fallback ke 'definisi' jika tidak terdeteksi
        $detectedKategori = $detectedKategori ?? 'definisi';

        // Ekstraksi keyword sederhana
        $keyword = $this->extractKeyword($userMessage);

        // Cari jawaban dari database
        $answer = $this->searchDatabaseSmart($keyword, $detectedKategori);

        if (!$answer) {
            $answer = "Maaf, saya tidak menemukan informasi tentang \"$keyword\" dalam kategori \"$detectedKategori\".";
        }

        return response()->json(['answer' => $answer]);
    }

    private function extractKeyword($text)
    {
        $stopwords = ['apa', 'itu', 'yang', 'adalah', 'dan', 'dengan', 'bagaimana', 'cara', 'untuk', 'siapa', 'dimana', 'kapan'];
        $words = explode(' ', strtolower($text));
        $filtered = array_diff($words, $stopwords);
        return trim(array_values($filtered)[0] ?? '');
    }

    private function searchDatabaseSmart($keyword, $category)
    {
        if (!$keyword) return null;

        if ($category === 'gejala') {
            return DB::table('pertolongan_pertama')
                ->where('nama_kasus', 'like', "%$keyword%")
                ->value('gejala');
        }

        if ($category === 'prosedur') {
            return DB::table('pertolongan_pertama')
                ->where('nama_kasus', 'like', "%$keyword%")
                ->value('langkah') ??
                DB::table('bencana')
                ->where('nama_bencana', 'like', "%$keyword%")
                ->value('prosedur_evakuasi');
        }

        if ($category === 'kategori') {
            return DB::table('pertolongan_pertama')
                ->where('nama_kasus', 'like', "%$keyword%")
                ->value('kategori') ??
                DB::table('bencana')
                ->where('nama_bencana', 'like', "%$keyword%")
                ->value('kategori');
        }

        // Default: definisi
        return DB::table('istilah')
            ->where('istilah', 'like', "%$keyword%")
            ->value('definisi') ??
            DB::table('bencana')
            ->where('nama_bencana', 'like', "%$keyword%")
            ->value('deskripsi');
    }

    // Opsional: kalau kamu mau tetap pakai Groq
    private function callGroqForInstruction($message)
    {
        $prompt = "Tugasmu adalah menganalisis pertanyaan user: \"$message\". 
        Tentukan SATU kata kunci utama dan SATU kategori informasi (gejala, prosedur, atau definisi).
        Contoh:
        - 'gejala pingsan' -> pingsan|gejala
        - 'cara evakuasi gempa' -> gempa|prosedur
        - 'apa itu evakuasi' -> evakuasi|definisi
        Jawab HANYA dengan format: keyword|kategori";

        return $this->askGroq($prompt);
    }

    private function askGroq($prompt)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('GROQ_API_KEY'),
            'Content-Type' => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.1-8b-instant',
            'messages' => [['role' => 'user', 'content' => $prompt]],
            'temperature' => 0.1,
            'max_tokens' => 30,
        ]);

        return strtolower(trim($response->json()['choices'][0]['message']['content'] ?? ''));
    }
}
