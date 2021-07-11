<?php

use App\Http\Controllers\ApiTokenController;
use App\Http\Controllers\Auth\VerificationController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\FeedController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PermissionsController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

//-- Authentication Routes --//
Auth::routes();

//-- Custom Verification Routes --//
Route::post('/verify/attempt', [VerificationController::class, 'verify'])->name('verification.verify');
Route::post('/verify/resend', [VerificationController::class, 'resend'])->name('verification.resend');
Route::get('/verify/{method}/notice', [VerificationController::class, 'show'])->name('verification.notice');




#region //! browser urls
Route::get('/c/{community:name}/p/{post:uuid62}/r/{comment:uuid62}', [CommentController::class, 'show'])->name('community.posts.comments.show');
Route::get('/u/{profile:username}/p/{post:uuid62}/r/{comment:uuid62}', [CommentController::class, 'show'])->name('profile.posts.comments.show');

Route::get('/c/{community:name}', [CommunityController::class, 'show'])->name('community.show');
Route::get('/c/{community:name}/p/{post:uuid62}/{post_by_slug}', [PostController::class, 'show'])->name('community-post.show');
Route::get('/u/{profile:username}/p/{post:uuid62}/{post_by_slug}', [PostController::class, 'show'])->name('profile-post.show');
Route::get('/communities', [CommunityController::class, 'list'])->name('communities.list');
Route::get('/u/init', [ProfileController::class, 'init'])->name('profile.init');
Route::get('/u/{profile:username}', [ProfileController::class, 'show'])->name('profile.show');
#endregion






#region //! browser redirections
Route::get('/r/{comment:uuid62}', [CommentController::class, 'redirectToPage']);
Route::get('/r/{comment}', [CommentController::class, 'redirectToPage']);

Route::get('/p/{post:uuid62}/{garbage?}', [PostController::class, 'redirectToPage'])->name('post.show');
Route::get('/c/{community:name}/p/{post:uuid62}/{garbage?}', [PostController::class, 'redirectToPage']);
Route::get('/u/{profile:username}/p/{post:uuid62}/{garbage?}', [PostController::class, 'redirectToPage']);
#endregion





#region //! form requests
Route::get('/community/create', [CommunityController::class, 'create'])->name('community.create');
Route::get('/c/{community:name}/edit', [CommunityController::class, 'edit'])->name('community.edit');
Route::get('/u/{profile}/edit', [ProfileController::class, 'edit'])->name('profile.edit');
#endregion





#region //! backend submits
Route::post('/c/{comment}/update', [CommentController::class, 'update'])->name('comments.update');

Route::post('/p/{post}/like', [LikeController::class, 'likePost'])->name('post.like');
Route::post('/p/{post}/unlike', [LikeController::class, 'unlikePost'])->name('post.unlike');

Route::post('/p/{post}/delete', [PostController::class, 'delete'])->name('post.delete');

Route::post('/r/{comment}/like', [LikeController::class, 'likeComment'])->name('comment.like');
Route::post('/r/{comment}/unlike', [LikeController::class, 'unlikeComment'])->name('comment.unlike');

Route::post('/p/{post}/comment', [CommentController::class, 'storeComment'])->name('post.storeComment');
Route::post('/r/{comment}/reply', [CommentController::class, 'storeReply'])->name('comment.storeComment');
Route::post('/r/{comment}/update', [CommentController::class, 'update'])->name('comment.update');

Route::post('/c/{community}/posts', [PostController::class, 'storeCommunityPost'])->name('community.posts.store');
Route::post('/u/posts', [PostController::class, 'storeProfilePost'])->name('profile.posts.store');
Route::post('/community', [CommunityController::class, 'store'])->name('community.store');
Route::post('/profile', [ProfileController::class, 'store'])->name('profile.store');

Route::post('/c/{community}/join', [CommunityController::class, 'join'])->name('community.join');
Route::post('/c/{community}/leave', [CommunityController::class, 'leave'])->name('community.leave');

Route::post('/u/{profile}/follow', [FollowController::class, 'follow'])->name('profile.follow');
Route::post('/u/{profile}/unfollow', [FollowController::class, 'unfollow'])->name('profile.unfollow');
Route::put('/u//{profile}/update', [CommunityController::class, 'update'])->name('profile.update');

Route::put('/community/{community}', [CommunityController::class, 'update'])->name('community.update');
Route::delete('/community/{community}', [CommunityController::class, 'destroy'])->name('community.destory');


Route::post('/c/{community}/posts', [PostController::class, 'storeCommunityPost'])->name('community.posts.store');
Route::post('/u/posts', [PostController::class, 'storeProfilePost'])->name('profile.posts.store');
#endregion

#region //! backend requests
Route::post('/token/update', [ApiTokenController::class, 'update'])->name('token.update');
Route::get('/permissions/{community}', [PermissionsController::class, 'permissionsForCommunity'])->name('permissions.forCommunity');
Route::get('/permissions', [PermissionsController::class, 'permissionsList'])->name('permissions.all');
Route::post('/u/switch/{profile}', [ProfileController::class, 'switch'])->name('profile.switch');
Route::post('/api/setLocale', [\App\Http\Controllers\AppLanguageController::class, 'update'])->name('locale.update');

Route::post('/p/{post}/comments', [PostController::class, 'loadComments'])->name('posts.loadComments');
Route::post('/r/{comment}/replies', [CommentController::class, 'loadReplies'])->name('comments.loadReplies');

Route::post('/n/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
Route::post('/n/read', [NotificationController::class, 'read'])->name('notifications.read');

#endregion


#region //! backend wapi requests
Route::post('/wapi/profile/current', [ProfileController::class, 'current']);
Route::post('/wapi/feed', [FeedController::class, 'wapiIndex']);
#endregion



//-- Legal stuff
Route::get('/terms', function () {
    return "The terms blade-view";
})->name('legal.terms');
Route::get('/privacy', function () {
    return "The privacy blade-view";
})->name('legal.privacy');


// landing
Route::get('/', [HomeController::class, 'landing']);
