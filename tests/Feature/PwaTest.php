<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaTest extends TestCase
{
    public function test_manifest_is_standalone_and_references_existing_icons(): void
    {
        $manifest = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('CrossFit Grozny', $manifest['name']);
        $this->assertSame('standalone', $manifest['display']);
        $this->assertSame('#111827', $manifest['theme_color']);
        $this->assertContains('maskable', array_column($manifest['icons'], 'purpose'));

        foreach ($manifest['icons'] as $icon) {
            $path = public_path(ltrim($icon['src'], '/'));

            $this->assertFileExists($path);
            [$width, $height] = getimagesize($path);
            $this->assertSame($icon['sizes'], "{$width}x{$height}");
        }
    }

    public function test_service_worker_exists(): void
    {
        $this->assertFileExists(public_path('sw.js'));
    }

    public function test_pages_link_manifest_and_render_install_banner(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('manifest.webmanifest', false)
            ->assertSee('<meta name="theme-color" content="#111827">', false)
            ->assertSee('sw.js', false)
            ->assertSee(__('app.pwa_install_button'))
            ->assertSee('installBanner()', false);
    }
}
