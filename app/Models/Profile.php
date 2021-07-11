<?php

namespace App\Models;

use App\DataBase\Eloquent\HasMany as CustomHasMany;
use App\DataBase\Eloquent\MorphOne;
use App\Models\Traits\CanComment;
use App\Models\Traits\CanFollow;
use App\Models\Traits\CanJoinCommunities;
use App\Models\Traits\CanLike;
use App\Models\Traits\CanView;
use App\Models\Traits\CreatesCommunities;
use App\Models\Traits\CreatesPosts;
use App\Models\Traits\HasFollowers;
use App\Models\Traits\HasImages;
use App\Models\Traits\HasPosts;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\HasDatabaseNotifications;
use App\Models\Traits\ModelTraits;
use App\Models\Traits\Urlable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Watson\Validating\ValidatingTrait;

/**
 * App\Models\Profile
 *
 * @property string lastName
 * @property bool is_liked
 * @property User $account
 * @property ProfileSettings $settings
 * @property int $id
 * @property int $user_id
 * @property string $username
 * @property bool|null $active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $reason_deleted
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $comments
 * @property-read int|null $comments_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Community[] $communities
 * @property-read int|null $communities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Post[] $communitiesPosts
 * @property-read int|null $communities_posts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CommunityMember[] $communitiesSubscriptions
 * @property-read int|null $communities_subscriptions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Post[] $createdPosts
 * @property-read int|null $created_posts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Profile[] $followers
 * @property-read int|null $followers_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Follow[] $followersModels
 * @property-read int|null $followers_models_count
 * @property-read \Illuminate\Database\Eloquent\Collection|Profile[] $followings
 * @property-read int|null $followings_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Follow[] $followingsModels
 * @property-read int|null $followings_models_count
 * @property-read mixed $created
 * @property-read string $url
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Image[] $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Community[] $joinedCommunities
 * @property-read int|null $joined_communities_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Like[] $likes
 * @property-read int|null $likes_count
 * @property-read Model|\Eloquent $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Post[] $posts
 * @property-read int|null $posts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Post[] $profilePosts
 * @property-read int|null $profile_posts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Comment[] $replies
 * @property-read int|null $replies_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PostView[] $viewedPosts
 * @property-read int|null $viewed_posts_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\PostView[] $views
 * @property-read int|null $views_count
 * @method static \Database\Factories\ProfileFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Profile newQuery()
 * @method static \Illuminate\Database\Query\Builder|Profile onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Profile query()
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereReasonDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Profile whereUsername($value)
 * @method static \Illuminate\Database\Query\Builder|Profile withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Profile withoutTrashed()
 * @mixin \Eloquent
 * @property-read \App\Models\ProfileData|null $data
 * @property-read bool $is_liked
 * @property-read int|null $notifications_count
 */
class Profile extends Model
{
    use HasFactory,
    Notifiable,
    HasDatabaseNotifications,
    SoftDeletes,
    ModelTraits,

    CreatesPosts,
    CreatesCommunities,

    HasPosts,
    HasImages,
    HasFollowers,

    CanView,
    CanLike,
    CanComment,
    CanFollow,
    CanJoinCommunities,

    Urlable

    ;
    
    protected $guarded = [];

    protected $casts = [
        'active' => 'boolean',
    ];
    protected $with = [];

    public function __construct($attributes = [])
    {
        parent::__construct($attributes);
        $this->setAttribute('active', $attributes['active'] ?? 1);
    }

    public function getIsLikedAttribute():bool
    {
        return $this->likes()->where('liker_id', self::current_id())->exists();
    }
    public static function boot()
    {
        parent::boot();
        // set other profiles active state to false when a new profile is created.
        static::created(function (Profile $profile) {
            $user = $profile->account;
            if ($user->profiles()->count() > 1) {
                $user->profiles()->where('id', '!=', $profile->getKey())->whereActive(true)->update(["active" => false]);
            }
            $profile->settings()->create();
            $profile->data()->create();
        });
        // assert only one active profile per account
        static::updating(function (Profile $profile) {
            $user = $profile->account;
            if ((bool)$profile->active !== (bool)$profile->getOriginal('active') && (bool)$profile->active === true) {
                if ($user->profiles()->count() <= 1) {
                    throw new \Exception("attempting to deactivate profile but the user only has one profile");
                }
                $oldActive = $user->profiles()->where('id', '!=', $profile->getKey())->whereActive(true)->first();
                if (! is_null($oldActive)) {
                    $oldActive->update(["active" => false]);
                }
            }
        });
    }
    
    public function getFollowingsCountAttribute()
    {
        if (isset($this->attributes['followings_count'])) {
            return $this->attributes['followings_count'];
        }
        if ($this->relationLoaded('followings')) {
            return $this->followings->count();
        }
        return $this->followings()->count();
    }
    public function getFollowersCountAttribute()
    {
        if (isset($this->attributes['followers_count'])) {
            return $this->attributes['followers_count'];
        }
        if ($this->relationLoaded('followers')) {
            return $this->followers->count();
        }
        return $this->followers()->count();
    }




    #region RELATIONS
    public function data():HasOne
    {
        return $this->hasOne(ProfileData::class);
    }
    public function communitiesPosts():CustomHasMany
    {
        return $this->createdPosts()->withFixedConstraint('pageable_type', 'App\Models\Community');
    }
    public function profilePosts():MorphMany
    {
        return $this->posts();
    }
    public function viewedPosts():HasMany
    {
        return $this->views();
    }
    public function replies():CustomHasMany
    {
        return $this->comments()->withFixedConstraint('commentable_type', 'App\Models\Comment');
    }
    public function account():BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function likedPosts():CustomHasMany
    {
        return $this->likes()->withFixedConstraint('likeable_type', 'App\Models\Post');
    }
    public function likedComments():CustomHasMany
    {
        return $this->likes()->withFixedConstraint('likeable_type', 'App\Models\Comment');
    }

    public function avatarImage():MorphOne
    {
        
        return (new MorphOne(
            Image::query(),
            $this,
            'images.imageable_type',
            'images.imageable_id',
            'id'
        ))
        ->withDefault(function(){
            return new Image([
                'id' => Image::DEFAULT_PROFILE_PROFILE_IMAGE_ID,
                'type' => IMAGETYPE_JPEG,
                'extension' => 'jpeg',
                'mime' => 'image/jpeg'
            ]);
        })
        ->withFixedConstraint('purpose', __FUNCTION__);
    }
    public function coverImage():MorphOne
    {
        return (new MorphOne(
            Image::query(),
            $this,
            'images.imageable_type',
            'images.imageable_id',
            'id'
        ))->withDefault(function(){
            return new Image([
                'id' => Image::DEFAULT_PROFILE_COVER_IMAGE_ID,
                'type' => IMAGETYPE_JPEG,
                'extension' => 'jpeg',
                'mime' => 'image/jpeg'
            ]);
        })
        ->withFixedConstraint('purpose', __FUNCTION__);
    }
    public function communities():BelongsToMany
    {
        return $this->belongsToMany(Community::class, 'communities_members', 'profile_id');
    }
    
    public function settings():HasOne
    {
        return $this->hasOne(ProfileSettings::class);
    }

    #endregion

    /**
     * returns the currently logged-in user's active profile
     * @param string|array $withEagerLoads
     * @return HasOne
     */
     public static function currentRelation($withEagerLoads = null):HasOne
     {
        $query = Profile::query()->whereActive(true)->limit(1);
        if($withEagerLoads !== null)
            $query->with(func_get_args());
        return new HasOne($query, new User(['id' => Auth::id()]), 'user_id', 'id');
     }

    /**
     * returns the currently logged-in user's active profile
     *
     * @return Profile|null
     */
    public static function current():Profile|null
    {
        try {
            return Auth::user()->activeProfile ?? null;
        } catch (\Throwable $e) {
            report($e);
        }
        return null;
    }
    /**
     * returns the currently logged-in user's active profile, new profile if null
     *
     * @return Profile
     */
    public static function currentOrNew():Profile
    {
        return self::current() ?? new Profile;
    }
    /**
     * returns the id of the currently logged-in user's active profile
     *
     * @return int|null
     */
    public static function current_id():int|null
    {
        try {
            return Auth::user()->activeProfile->id ?? null;
        } catch (\Throwable $e) {
            report($e);
        }
        return null;
    }

    public function reloadCurrent():void
    {
        Auth::user()->load('activeProfile');
    }
    public function getUrlAttribute(): string
    {
        return empty($this->getAttribute('username')) ? '' : route('profile.show', $this->getAttribute('username'));
    }


    public function allows($permission_id, $profile): bool
    {
        if (empty($permission_id) || empty($profile)) {
            return false;
        }
        if ($profile instanceof Profile) {
            if (! $profile->exists) {
                return false;
            }
            $profile_id = $profile->getKey();
        } else {
            if (! Profile::where('id', $profile)->exists()) {
                return false;
            }
            $profile_id = $profile;
        }
        $he_follow_me = $this->followersModels()->where('follower_id', $profile_id)->exists();
        $i_follow_him = $this->followingsModels()->where('profile_id', $profile_id)->exists();
        $we_are_friends = $he_follow_me && $i_follow_him;

        $settings = $this->settings;
        if ($permission_id === config('permissions.profiles.can-comment')) {
            if ($settings->allow_non_followers_to_comment_on_my_profile_posts) {
                return true;
            }
            if ($he_follow_me && $settings->allow_followers_to_comment_on_my_profile_posts) {
                return true;
            }
            if ($i_follow_him && $settings->allow_followings_to_comment_on_my_profile_posts) {
                return true;
            }
            if ($we_are_friends && $settings->allow_friends_to_comment_on_my_profile_posts) {
                return true;
            }
        } else if ($permission_id === config('permissions.profiles.can-view-posts')) {
            if ($settings->allow_non_followers_to_view_my_profile_posts) {
                return true;
            }
            if ($he_follow_me && $settings->allow_followers_to_view_my_profile_posts) {
                return true;
            }
            if ($i_follow_him && $settings->allow_followings_to_view_my_profile_posts) {
                return true;
            }
            if ($we_are_friends && $settings->allow_friends_to_view_my_profile_posts) {
                return true;
            }
        } else if ($permission_id === config('permissions.profiles.can-follow')) {
            if ($settings->allow_others_to_follow_me) {
                return true;
            }
            if ($i_follow_him && $settings->allow_followings_to_follow_me_back) {
                return true;
            }
        }
        return false;
    }
    /**
     *
     * Does this community allow this action for the current profile?
     *
     * @param int $permission_id if of the permission
     * @return true if this community allows the action for the current profile
     * @return false if this community does not allow the current profile to do the action
     */
    public function allowsCurrent(int $permission_id): bool
    {
        return $this->allows($permission_id, Profile::current_id());
    }
}
