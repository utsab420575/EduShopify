<?php

namespace App\Console\Commands;

use App\Models\Listing;
use Illuminate\Console\Command;
use Spatie\MediaLibrary\Conversions\FileManipulator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * One-time / maintenance task for product images:
 *  - generates the product_banner / card / thumb copies for gallery images
 *    that don't have them yet (images uploaded before cropping existed get a
 *    centred 4:3 crop; --regenerate rebuilds every size, e.g. after the
 *    size definitions changed);
 *  - reports image/video rows whose file is missing on disk, and with
 *    --prune-missing deletes those rows (clearing a cover that pointed at one).
 *
 *   php artisan product-media:refresh
 *   php artisan product-media:refresh --regenerate
 *   php artisan product-media:refresh --prune-missing
 */
class RefreshProductMedia extends Command
{
    protected $signature = 'product-media:refresh
        {--regenerate : Rebuild every image size, not only missing ones}
        {--prune-missing : Delete media rows whose file no longer exists}';

    protected $description = 'Generate missing product image sizes and report/prune media rows with missing files';

    public function handle(FileManipulator $manipulator): int
    {
        $media = Media::where('model_type', (new Listing)->getMorphClass())
            ->whereIn('collection_name', ['gallery', 'videos', 'video_posters'])
            ->get();

        $generated = 0;
        $missing = collect();

        foreach ($media as $item) {
            if (! is_file($item->getPath())) {
                $missing->push($item);

                continue;
            }

            if ($item->collection_name === 'gallery') {
                $before = count(array_filter($item->generated_conversions ?? []));
                if ($this->option('regenerate')) {
                    $manipulator->createDerivedFiles($item);
                    $item->touch(); // new ?v= so browsers drop the old copies
                    $generated += count(config('product_media.images.conversions')) + 1;

                    continue;
                }
                $manipulator->createDerivedFiles($item, onlyMissing: true);
                $generated += max(0, count(array_filter($item->refresh()->generated_conversions ?? [])) - $before);
            }
        }

        $this->info("Product media checked: {$media->count()} — image sizes generated: {$generated}.");

        if ($missing->isEmpty()) {
            $this->info('No media rows with missing files.');

            return self::SUCCESS;
        }

        $this->warn("Media rows whose file is missing: {$missing->count()}");
        $this->table(['media id', 'listing id', 'collection', 'file'], $missing->map(fn ($m) => [$m->id, $m->model_id, $m->collection_name, $m->file_name])->all());

        if (! $this->option('prune-missing')) {
            $this->line('Run again with --prune-missing to delete these rows.');

            return self::SUCCESS;
        }

        foreach ($missing as $item) {
            Listing::where('primary_image_media_id', $item->id)->update(['primary_image_media_id' => null]);
            $item->forceDelete();
        }
        $this->info("Deleted {$missing->count()} media rows with missing files.");

        return self::SUCCESS;
    }
}
