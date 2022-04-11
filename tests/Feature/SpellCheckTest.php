<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;

class SpellCheckTest extends TestCase
{
    /**
     * @return void
     */
    public function testMakeSpellCheckApiRequest(): void
    {
        $response = Http::asForm()
            ->retry(5)
            ->post('https://api.languagetoolplus.com/v2/check', [
                'text' => 'Random string',
                'language' => 'en-GB'
            ]);
        $this->assertEquals(200, $response->status());
    }
}
