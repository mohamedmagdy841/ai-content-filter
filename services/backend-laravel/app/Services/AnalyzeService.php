<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnalyzeService
{
    public function analyzeContent(string $content = "", string $aiModel = "toxic-bert", string $title = "")
    {
        try {
            return Http::post("http://localhost:8080/analyze", [
                'title' => $title,
                'content' => $content,
                'ai_model' => $aiModel,
            ])->json();
        } catch (\Exception $e) {
            Log::warning("FastAPI service is currently unavailable. " . $e->getMessage());
            return null;
        }
    }
}
