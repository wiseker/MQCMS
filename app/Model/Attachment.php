<?php

declare (strict_types=1);
namespace App\Model;

/**
 * @property int $id 
 * @property int $user_id 
 * @property string $attach_name 
 * @property string $attach_origin_name 
 * @property string $attach_url 
 * @property int $attach_type 
 * @property string $attach_minetype 
 * @property string $attach_extension 
 * @property string $attach_size 
 * @property int $status 
 * @property \Carbon\Carbon $created_at 
 * @property \Carbon\Carbon $updated_at 
 */
class Attachment extends Model
{
    /**
     * @var string
     */
    protected $dateFormat = 'U';

    /**
     * @var bool
     */
    public $timestamps = true;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'attachment';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['id', 'user_id', 'attach_name', 'attach_origin_name', 'attach_url', 'attach_type', 'attach_minetype', 'attach_extension', 'attach_size', 'status', 'created_at', 'updated_at'];
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['id' => 'integer', 'user_id' => 'integer', 'attach_type' => 'integer', 'status' => 'integer', 'created_at' => 'datetime', 'updated_at' => 'datetime'];
}