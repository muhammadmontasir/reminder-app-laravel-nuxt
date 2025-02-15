<?php

namespace App\Services;

use Illuminate\Support\Str;

class IdGenerationService
{
    public function generate(): string
    {
        return 'EVENT-' . time() . '-' . Str::random(8);
    }
}