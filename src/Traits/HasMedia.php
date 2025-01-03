<?php

namespace JoydeepBhowmik\LaravelMediaLibary\Traits;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use JoydeepBhowmik\LaravelMediaLibary\Models\Media;

trait HasMedia
{
    private $__mediaFiles = [];

    private object $__currentMediaCollection;

    public function findMediaByUploaderId(string $id)
    {
        return Media::where('user_id', $id);
    }

    public function findMediaByUploader(User $user)
    {
        return $this->findMediaByUploaderId($user->id);
    }

    protected static function bootHasMedia()
    {
        static::deleting(function ($model) {
            Media::where('model_type', class_basename($model))
                ->where('model_id', $model->{$model->primaryKey})
                ->delete();
        });
    }

    public function hasMedia(string $collectionName = ''): bool
    {
        return $this->media($collectionName)->get() ? true : false;
    }

    public function addMedia(UploadedFile|array $files)
    {
        if (is_array($files)) {
            foreach ($files as $file) {
                $this->__mediaFiles[] = $file; // Add each file to the array
            }
        } else {
            $this->__mediaFiles[] = $files; // Add the single file to the array
        }

        return $this;
    }


    public function toCollection(string $name = 'uploads', string $disk = null, $folder = null)
    {
        $user = auth()->user();
        $the_disk = $disk ?? config('media.disk');
        $default_directory = config('media.default_directory') ?? 'uploads';

        foreach ($this->__mediaFiles as $file) {
            $media = new Media();

            $file_name = time() . $file->getClientOriginalName();

            $media->file_name = $file_name;
            $media->original_file_name = $file->getClientOriginalName();
            $media->mime_type = $file->getMimeType();
            $media->collection = $name;
            $media->model_id = $this->{$this->primaryKey};
            $media->model_type = class_basename($this);
            $media->user_id = $user?->id;
            $media->disk = $the_disk;
            $media->directory = trim($folder, '/');

            if ($file->storeAs(trim($default_directory, '/') . '/' . $folder, $file_name, $the_disk)) {
                $media->save();
            }
        }

        // Clear the media files array to avoid duplication in future calls
        $this->__mediaFiles = [];
    }


    public function deleteMediaCollection(string $collection, string $disk = null)
    {
        $media = $this->media($collection);

        $the_disk = $disk ?? config('media.disk');

        $default_directory = config('media.default_directory') ?? 'uploads';

        foreach ($media->get() as $m) {
            $filepath = trim($default_directory, '/') . '/' . ($m->directory ? $m->directory . '/' : '') . $m->file_name;
            Storage::disk($the_disk)->exists($filepath) && Storage::disk($the_disk)->delete($filepath);
        }

        return $media->delete();
    }

    public function deleteAllMedia()
    {
        return $this->deleteMediaCollection('*');
    }

    public function media(string $collection = null)
    {
        $media = null;

        if ($collection === '*' or !$collection) {
            $media = Media::where('model_type', class_basename($this))
                ->where('model_id', $this->{$this->primaryKey});
        }

        if ($collection !== '*' and $collection) {
            $media = Media::where('model_type', class_basename($this))
                ->where('model_id', $this->{$this->primaryKey})
                ->where('collection', $collection);
        }

        $media = $media->orderBy('ordering');

        $this->__currentMediaCollection = $media;

        return $media;
    }

    public function getMedia(string $collection = null)
    {
        return $this->media($collection)->get();
    }

    public function getFirstMedia(string $collection = '*')
    {
        return $this->media($collection)?->first();
    }
    public function getFirstMediaUrl(string $collection = '*')
    {
        return $this->media($collection)?->first()?->getUrl();
    }

    public function updateMediaOrdering($items, string $collection = "*")
    {
        // Extract the photo IDs from the input array
        $ids = collect($items)->pluck('value')->toArray();

        // Fetch all photos that match the given IDs
        $this->media($collection)->whereIn('id', $ids)
            ->get()
            ->each(function ($item) use ($ids) {
                // Update the 'ordering' field based on the position of the photo ID in the $ids array
                $item->update(['ordering' => array_search($item->id, $ids) + 1]); // Adding 1 to start ordering from 1
            });
    }
}
