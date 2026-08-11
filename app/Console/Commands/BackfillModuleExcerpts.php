<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\RencanaPembelajaran;

class BackfillModuleExcerpts extends Command
{
    protected $signature = 'modules:backfill-excerpts {--chunk=100}';
    protected $description = 'Backfill short excerpts for existing RencanaPembelajaran records';

    public function handle()
    {
        $chunk = (int) $this->option('chunk');
        $this->info('Starting backfill of module excerpts...');

        RencanaPembelajaran::chunk($chunk, function ($modules) {
            foreach ($modules as $module) {
                $meta = [];
                if (!empty($module->html_content)) {
                    $decoded = json_decode($module->html_content, true);
                    if (is_array($decoded)) $meta = $decoded;
                }

                $html = $meta['content'] ?? $module->capaian_pembelajaran ?? $module->tujuan ?? '';
                $html = preg_replace('/<img[^>]+src=["\\\'][^>]+>/i', '', $html);
                $text = strip_tags($html);
                $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $text = preg_replace('/\s+/', ' ', $text);
                $excerpt = trim(Str::limit($text, 220, '...'));

                // store excerpt in memory-only payload field to avoid schema changes
                // but also store into `meta_excerpt` JSON key inside html_content if present
                if (is_array($meta)) {
                    $meta['excerpt'] = $excerpt;
                    $module->html_content = json_encode($meta, JSON_UNESCAPED_UNICODE);
                }

                $module->saveQuietly();
                $this->line("Backfilled module {$module->id}");
            }
        });

        $this->info('Backfill complete.');
        return 0;
    }
}
