<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\SupabaseService;
use Mockery\MockInterface;

class AgriScraperTest extends TestCase
{
    /**
     * Test tips listing endpoint and structured field parsing.
     */
    public function test_get_tips_endpoint_parses_markdown_correctly()
    {
        $mockArticles = [
            [
                'id' => 1,
                'title' => 'Cara Menanam Padi Organik',
                'category' => 'Tips Tani',
                'content' => "### ⚡ Solusi Cepat\nLakukan pengairan berselang dan pemupukan organik teratur.\n\n### 🛠️ Alat & Bahan\n- Benih Padi Unggul\n- Pupuk Kompos\n- Cangkul\n\n### 📖 Panduan Lengkap\nBerikut adalah langkah-langkah lengkap menanam padi organik...\n\n### 🌐 Sumber\n[IPB Digitani](https://digitani.ipb.ac.id/artikel-padi)",
                'image_url' => 'https://example.com/padi.jpg',
                'published_at' => '2026-08-30T12:00:00Z',
                'status' => 'Publikasi'
            ]
        ];

        // Mock SupabaseService
        $this->mock(SupabaseService::class, function (MockInterface $mock) use ($mockArticles) {
            $mock->shouldReceive('select')
                ->once()
                ->with('tips', [
                    'select' => '*',
                    'status' => 'eq.Publikasi',
                    'order'  => 'published_at.desc'
                ])
                ->andReturn($mockArticles);
        });

        $response = $this->getJson('/api/tips');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Daftar tips pertanian berhasil diambil.',
            ]);

        // Check if parsed fields are present
        $data = $response->json('data.0');
        $this->assertEquals('Lakukan pengairan berselang dan pemupukan organik teratur.', $data['parsed']['solusi_cepat']);
        $this->assertEquals(['Benih Padi Unggul', 'Pupuk Kompos', 'Cangkul'], $data['parsed']['alat_bahan']);
        $this->assertStringContainsString('Berikut adalah langkah-langkah lengkap menanam padi organik...', $data['parsed']['panduan_lengkap']);
        $this->assertEquals('https://digitani.ipb.ac.id/artikel-padi', $data['source']);
        $this->assertEquals('IPB Digitani', $data['source_label']);
        $this->assertEquals('https://digitani.ipb.ac.id/artikel-padi', $data['parsed']['sumber']);
    }

    /**
     * Test single tip endpoint returns 404 if not found.
     */
    public function test_get_single_tip_returns_404_if_not_exists()
    {
        $this->mock(SupabaseService::class, function (MockInterface $mock) {
            $mock->shouldReceive('selectSingle')
                ->once()
                ->with('tips', 999)
                ->andReturn(null);
        });

        $response = $this->getJson('/api/tips/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Tips pertanian tidak ditemukan.'
            ]);
    }

    /**
     * Test hama listing endpoint.
     */
    public function test_get_hama_endpoint()
    {
        $mockHama = [
            [
                'id' => 10,
                'nama' => 'Wereng Cokelat',
                'kategori' => 'Padi',
                'deskripsi' => 'Serangga kecil penghisap cairan tanaman.',
                'ciri_ciri' => '- Daun menguning\n- Tanaman mengering',
                'cara_mengatasi' => 'Gunakan musuh alami atau insektisida organik.',
                'gambar_url' => 'https://example.com/wereng.jpg'
            ]
        ];

        $this->mock(SupabaseService::class, function (MockInterface $mock) use ($mockHama) {
            $mock->shouldReceive('select')
                ->once()
                ->with('hama', [
                    'select' => '*',
                    'order'  => 'nama.asc'
                ])
                ->andReturn($mockHama);
        });

        $response = $this->getJson('/api/hama');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Daftar hama & penyakit berhasil diambil.',
                'data' => $mockHama
            ]);
    }
}
