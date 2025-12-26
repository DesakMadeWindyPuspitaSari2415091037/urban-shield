<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function send(Request $request)
    {
        $message = strtolower($request->input('message'));

        // 1. Deteksi sapaan
        $sapaan = ['hi', 'halo', 'hello', 'hai'];
        if (in_array($message, $sapaan)) {
            return response()->json([
                'answer' => 'Hai! Saya UrbanShield, siap bantu kamu soal kebencanaan dan keselamatan.'
            ]);
        }

        // 2. Deteksi bahasa user
        $language = $this->detectLanguage($message); // 'id' atau 'en'

        // 3. Minta Groq kasih keyword dan kategori
        $aiDecision = $this->callGroqForInstruction($message);
        $parts = explode('|', $aiDecision);
        $keyword = trim($parts[0] ?? '');
        $category = trim($parts[1] ?? 'umum');

        // 4. Cari jawaban dari database sesuai bahasa
        $answer = $this->searchDatabaseSmart($keyword, $category, $language);

        // 5. Fallback kalau nggak ketemu
        if (!$answer) {
            $answer = $language === 'en'
                ? "Sorry, I couldn't find information about '$keyword' ($category)."
                : "Maaf, data mengenai $keyword ($category) tidak ditemukan di referensi kami.";
        }

        return response()->json(['answer' => $answer]);
    }

    private function detectLanguage($text)
    {
        $prompt = "Bahasa apa kalimat ini? Jawab hanya 'id' atau 'en'. Kalimat: \"$text\"";
        return $this->askGroq($prompt);
    }

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

        return strtolower(trim($response->json()['choices'][0]['message']['content'] ?? 'id'));
    }

    private function searchDatabaseSmart($keyword, $category, $language)
    {
        if (!$keyword) return null;

        $isEnglish = $language === 'en';

        if ($category === 'gejala') {
            return DB::table('pertolongan_pertama')
                ->where('nama_kasus', 'like', "%$keyword%")
                ->value($isEnglish ? 'gejala_en' : 'gejala');
        }

        if ($category === 'prosedur') {
            return DB::table('pertolongan_pertama')
                ->where('nama_kasus', 'like', "%$keyword%")
                ->value($isEnglish ? 'langkah_en' : 'langkah') ??
                DB::table('bencana')
                ->where('nama_bencana', 'like', "%$keyword%")
                ->value($isEnglish ? 'prosedur_evakuasi_en' : 'prosedur_evakuasi');
        }

        // Default: definisi
        return DB::table('istilah')
            ->where('istilah', 'like', "%$keyword%")
            ->value($isEnglish ? 'definisi_en' : 'definisi') ??
            DB::table('bencana')
            ->where('nama_bencana', 'like', "%$keyword%")
            ->value($isEnglish ? 'deskripsi_en' : 'deskripsi');
    }
}
