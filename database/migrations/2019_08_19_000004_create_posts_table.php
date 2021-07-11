<?php

use App\Models\Community;
use App\Models\Morphs\Profileable;
use App\Models\Post;
use App\Models\Profile;
use Database\Seeders\MigrationHelper;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
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
        Schema::create(Post::tablename(), function (Blueprint $table) use(&$schema){
            $schema=$table;
            $table->id();
            $table->foreignId('author_id')->nullable()->constrained(Profile::tablename())->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('body')->nullable();
            $table->string('uuid62')->unique()->index('posts_by_slug');
            $table->morphs('pageable');
            $table->string('slug')->unique()->nullable()->index('posts_by_slug');
            MigrationHelper::addTimeStamps($table, Post::class);
        });
        $schema->cascadeForeignKeysWithTriggers('author_id');
        $schema->cascadeMorphsWithTriggers([
            'relation' => 'pageable',
            'models' => [
                Community::class,
                Profile::class,
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(Post::tablename());
    }
}
