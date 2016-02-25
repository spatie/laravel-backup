<?php

namespace Spatie\Backup\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ListCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'backup:list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List all backups';

    public function fire()
    {
        $path = config('laravel-backup.destination.path');

        $collection = collect();

        foreach ($this->getTargetFileSystems() as $filesystem) {
            $disk = Storage::disk($filesystem);

            foreach ($disk->files($path) as $file) {
                $collection->push([
                    'filesystem' => $filesystem,
                    'name' => $file,
                    'lastModified' => $disk->lastModified($file),
                ]);
            }
        }

        $rows = $collection
            ->sortByDesc('lastModified')
            ->filter(function ($value) {
                return ends_with($value['name'], '.zip');
            })
            ->map(function ($value) {
                $lastModified = Carbon::createFromTimestamp($value['lastModified']);

                $value['lastModified'] = $lastModified;
                $value['age'] = $this->getAgeInDays($lastModified);

                return $value;
            });

        $this->table(['Filesystem', 'Filename', 'Created at', 'Age in days'], $rows);
    }

    /**
     * Get the filesystems to where the database should be dumped.
     *
     * @return array
     */
    protected function getTargetFileSystems()
    {
        $fileSystems = config('laravel-backup.destination.filesystem');

        if (is_array($fileSystems)) {
            return $fileSystems;
        }

        return [$fileSystems];
    }

    protected function getAgeInDays(Carbon $date)
    {
        return number_format(round($date->diffInMinutes() / (24 * 60), 2), 2).' ('.$date->diffForHumans().')';
    }
}
