<?php

use App\Models\Comment;
use App\Models\Morphs\Postable;
use App\Models\Post;
use App\Models\Video;
use Database\Seeders\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CreateVideosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** @var Blueprint $schema */
        $schema = null;
        Schema::create(Video::tablename(), function (Blueprint $table) use(&$schema){
            $schema=$table;
            $table->id();
            $table->morphs('videoable');
            $table->char('sha256', 64)->index('videos_by_sha256');
            
            $table->smallInteger('width', unsigned:true);
            $table->smallInteger('height', unsigned:true);
            $table->float('duration',total:9, places:3, unsigned:true);
            $table->unsignedBigInteger('size');
            $table->string('mime');
            $table->string('origin_name')->nullable();
            $table->string('extension', 4);
            $table->float('sfw_score', total:2, places:1, unsigned:true)->nullable()->default(.5);
            MigrationHelper::addTimeStamps($table, Video::class);
        });
        $schema->cascadeMorphsWithTriggers([
            Post::class,
            Comment::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::transaction(function(){
            Video::all()->each(function($model){
                $model->delete();
            });
        });
        Schema::dropIfExists(Video::tablename());
    }
}
