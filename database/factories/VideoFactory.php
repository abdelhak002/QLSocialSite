<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Morphs\Postable;
use App\Models\Post;
use App\Models\Video;
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VideoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Video::class;
    /**
     * @var string
     */
    private static $temp;
    private static $original;
    private static $files;
    private static $loadedFiles = [];
    private static $defaultExtension = 'mp4';

    public function __construct(...$items)
    {
        parent::__construct(...$items);
        if(is_null(self::$files))
        {
            self::$files = collect(($this->model::disk('faker_')->files()));
            foreach(self::$files as $key => $video)
            {
                self::$files[$key] = $this->model::disk('faker_')->path($video);
            }
        }
    }
    /**
     * Define the model's default state.
     *
     * @return array
     * @throws \Exception
     */
    public function definition()
    {
        $name = Str::random().'.'.self::$defaultExtension;
        $temp = Storage::disk('temp')->path($name);
        copy((self::$original = self::$files->random()), $temp);
        self::$temp = $temp;
        return [
            'origin_name' => $name
        ];
    }

    public function configure()
    {
        return $this->afterMaking(function($model){
            if( ! isset(self::$loadedFiles[self::$original]))
            {
                $attributes = self::$loadedFiles[self::$original] = $this->model::extractAttributesFromFile(self::$temp);
            }else{
                $attributes = self::$loadedFiles[self::$original];
            }
            $model->fill($attributes);
            $model->temporary_file_location = self::$temp;
        });
    }
}
