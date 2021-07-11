<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class CascadedTrashScope implements Scope
{
    /**
     * All of the extensions to be added to the builder.
     *
     * @var string[]
     */
    protected $extensions = [
        'TreeDelete',
        'TreeRestore',
        'TreeRestoreCascaded',
        'TreeForceDelete',

        'CascadeDeleteRelation',
        'RestoreCascadedRelation',

        'IncludeTrashed',
        'OnlyIncludeTrashed',
    ];
    // cascadeDeleteRelation
    // restoreCascadedRelation
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        // $builder->whereNull($model->getQualifiedDeletedAtColumn());
    }

    /**
     * Extend the query builder with the needed functions.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @return void
     */
    public function extend(Builder $builder)
    {
        foreach ($this->extensions as $extension) {
            $this->{"add{$extension}"}($builder);
        }
    }
    

    protected function addTreeDelete(Builder $builder)
    {
        $builder->macro('treeDelete', function (Builder $builder) {
            return $builder
                ->each(function ($model) {
                    $model->delete();
                }, 500);
        });
    }

    protected function addTreeForceDelete(Builder $builder)
    {
        $builder->macro('treeForceDelete', function (Builder $builder) {
            if ($builder->getModel()->canBeSoftDeleted()) {
                return $builder->each(function ($model) {
                    $model->doForceDelete();
                }, 500);
            } else {
                return $builder->each(function ($model) {
                    $model->delete();
                }, 500);
            }
        });
    }

    protected function addCascadeDeleteRelation(Builder $builder)
    {
        $builder->macro('cascadeDeleteRelation', function (Builder $builder, $relatedInstance, string $relation) {
            $parent = $builder->getModel();
            if ($parent->forceDeleting()) {
                return $parent->{$relation}()->includeTrashed()->treeForceDelete();
            } elseif ($relatedInstance->canBeSoftDeleted()) {
                return $parent->{$relation}()
                ->each(function ($model) {
                    $model->update(['reason_deleted' => REASON_CASCADE]);
                    $model->delete();
                }, 500);
            }
            return $builder;
        });
    }
    protected function addRestoreCascadedRelation(Builder $builder)
    {
        $builder->macro('restoreCascadedRelation', function (Builder $builder, string $relation) {
            return $builder->getModel()->{$relation}()->treeRestoreCascaded();
        });
    }
    protected function addTreeRestoreCascaded(Builder $builder)
    {
        $builder->macro('treeRestoreCascaded', function (Builder $builder) {
            if ($builder->getModel()->canBeSoftDeleted()) {
                return $builder->where('reason_deleted', REASON_CASCADE)->treeRestore();
            }
            return $builder;
        });
    }
    protected function addTreeRestore(Builder $builder)
    {
        $builder->macro('treeRestore', function (Builder $builder) {
            if ($builder->getModel()->canBeSoftDeleted()) {
                return $builder
                ->onlyIncludeTrashed()
                ->each(function ($model) {
                    $model->restore();
                    $model->update(["reason_deleted" => null]);
                }, 500);
            }
            return $builder;
        });
    }

    protected function addIncludeTrashed(Builder $builder)
    {
        $builder->macro('includeTrashed', function (Builder $builder) {
            if ($builder->getModel()->canBeSoftDeleted()) {
                return $builder->withTrashed();
            }
            return $builder;
        });
    }
    
    protected function addOnlyIncludeTrashed(Builder $builder)
    {
        $builder->macro('onlyIncludeTrashed', function (Builder $builder) {
            if ($builder->getModel()->canBeSoftDeleted()) {
                return $builder->onlyTrashed();
            }
            return $builder;
        });
    }
}
