<?php

namespace Conquest\Upload\Models;

use Carbon\Carbon;
use Conquest\Upload\Casts\FileUrl;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Upload extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'uploads';

    protected $fillable = ['*'];

    protected $casts = [
        'path' => FileUrl::class,
    ];

}