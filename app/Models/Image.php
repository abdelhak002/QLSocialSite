<?php

namespace App\Models;

use App\Models\Traits\Attachement;
use App\Models\Traits\HasStorageUrl;
use App\Models\Traits\ModelTraits;
use App\Rules\PolymorphicRelationExists;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Validator;

/**
 * App\Models\Image
 *
 * @property int $id
 * @property string $imageable_type
 * @property int $imageable_id
 * @property string|null $purpose
 * @property string $sha256
 * @property int $type
 * @property int $width
 * @property int $height
 * @property int $size
 * @property string|null $origin_name
 * @property string $mime
 * @property string $extension
 * @property float|null $sfw_score
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $reason_deleted
 * @property-read mixed $created
 * @property-read string $file_name
 * @property-read string $real_path
 * @property-read string $url
 * @property-read Model|\Eloquent $imageable
 * @property-write mixed $temporary_file_location
 * @method static \Database\Factories\ImageFactory factory(...$parameters)
 * @method static \Illuminate\Database\Eloquent\Builder|Image newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Image newQuery()
 * @method static \Illuminate\Database\Query\Builder|Image onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Image query()
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereImageableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereImageableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereMime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereOriginName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image wherePurpose($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereReasonDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereSfwScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereSha256($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Image whereWidth($value)
 * @method static \Illuminate\Database\Query\Builder|Image withTrashed()
 * @method static \Illuminate\Database\Query\Builder|Image withoutTrashed()
 * @mixin \Eloquent
 */
class Image extends Model implements Attachement
{
    use HasFactory, 
    ModelTraits, 
    HasStorageUrl,
    SoftDeletes;

    public const DEFAULT_COMMUNITY_ICON_IMAGE_ID = 1;
    public const DEFAULT_COMMUNITY_COVER_IMAGE_ID = 2;
    public const DEFAULT_PROFILE_COVER_IMAGE_ID = 3;
    public const DEFAULT_PROFILE_PROFILE_IMAGE_ID = 4;

    public $storage = 'images';

    protected $guarded = [];
    
    public function imageable()
    {
        return $this->morphTo();
    }
    public function attacheable()
    {
        return $this->imageable();
    }
    public static function extractAttributesFromFile($path)
    {
        list(0 => $width, 1 => $height, 2 => $type, 'mime' => $mime) = getimagesize($path);
        return [
            'extension' => explode('/', $mime)[1],
            'mime' => $mime,
            'size' => filesize($path),
            'type' => $type,
            'width' => $width,
            'height' => $height,
        ];
    }
    public static function extractModelFromFile($path, array $adds = [])
    {
        $temp = tempnam(sys_get_temp_dir(), 'png');
        copy($path, $temp);
        return Image::make($adds + self::extractAttributesFromFile($path))->setTemporaryFileLocationAttribute($temp);
    }
}
