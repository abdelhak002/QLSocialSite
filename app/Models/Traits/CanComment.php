<?php


namespace App\Models\Traits;

use App\DataBase\Eloquent\HasMany;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;

trait CanComment
{
    public static function bootCanComment()
    {
        static::deleting(function (Model $commentor) {
            $commentor->cascadeDeleteRelation(Comment::make(), 'comments');
        });
        if (self::canBeSoftDeleted()) {
            static::restored(function (Model $commentor) {
                $commentor->restoreCascadedRelation('comments');
            });
        }
    }

    public function comments():HasMany
    {
        return new HasMany(Comment::query(), $this, 'commentor_id', 'id');
    }
}