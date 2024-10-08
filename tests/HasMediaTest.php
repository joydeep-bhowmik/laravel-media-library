<?php

namespace JoydeepBhowmik\LaravelMediaLibary\Tests;

use Mockery as m;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use JoydeepBhowmik\LaravelMediaLibary\Traits\HasMedia;


beforeEach(function () {
    // Mock the storage disk using Laravel's storage facade
    Storage::fake('public');

    // Create a dummy class that uses the trait for testing
    $this->model = new class {
        use HasMedia;

        public $primaryKey = 'id';
        public $id = 1;

        public function getKey()
        {
            return $this->id;
        }
    };
});

it('can add a single media file', function () {
    // Create a fake file upload
    $file = UploadedFile::fake()->image('test-image.jpg');

    // Add the file to the model and save it to the 'uploads' collection
    $this->model->addMedia($file)->toCollection('uploads', 'public');

    // Check that the file was added correctly to the internal mediaFiles array
    expect($this->model->__mediaFiles)->toHaveCount(1);
    expect($this->model->__mediaFiles[0]->getClientOriginalName())->toBe('test-image.jpg');
});

// it('can handle multiple media files', function () {
//     // Create multiple fake file uploads
//     $files = [
//         UploadedFile::fake()->image('test-image1.jpg'),
//         UploadedFile::fake()->image('test-image2.jpg')
//     ];

//     // Add multiple files and save them to the 'uploads' collection
//     $this->model->addMedia($files)->toCollection('uploads', 'public');

//     // Check that all files were added correctly
//     expect($this->model->__mediaFiles)->toHaveCount(2);
// });



// it('can retrieve media from a collection', function () {
//     // Add a file to the model
//     $file = UploadedFile::fake()->image('test-image.jpg');
//     $this->model->addMedia($file)->toCollection('uploads', 'public');

//     // Mock the Media model retrieval
//     $mediaMock = m::mock('alias:YourNamespace\Media');
//     $mediaMock->shouldReceive('where')->andReturnSelf();
//     $mediaMock->shouldReceive('get')->andReturn(collect([
//         (object) ['file_name' => 'test-image.jpg']
//     ]));

//     // Retrieve media from the model
//     $media = $this->model->getMedia('uploads');

//     // Assert that the media was retrieved correctly
//     expect($media)->toHaveCount(1);
//     expect($media->first()->file_name)->toBe('test-image.jpg');
// });

// it('can delete a media collection', function () {
//     // Add a file to the model
//     $file = UploadedFile::fake()->image('test-image.jpg');
//     $this->model->addMedia($file)->toCollection('uploads', 'public');

//     // Mock the Media model and delete operation
//     $mediaMock = m::mock('alias:YourNamespace\Media');
//     $mediaMock->shouldReceive('where')->andReturnSelf();
//     $mediaMock->shouldReceive('get')->andReturn(collect([
//         (object) ['id' => 1, 'file_name' => 'test-image.jpg', 'directory' => null]
//     ]));
//     $mediaMock->shouldReceive('delete')->andReturn(true);

//     // Act: Delete the media collection
//     $this->model->deleteMediaCollection('uploads');

//     // Assert: Check if the file and the media collection were deleted
//     Storage::disk('public')->assertMissing('uploads/test-image.jpg');
//     expect($this->model->getMedia('uploads'))->toHaveCount(0);
// });
