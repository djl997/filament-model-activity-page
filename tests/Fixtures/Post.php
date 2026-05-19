<?php

namespace Djl997\FilamentModelActivityPage\Tests\Fixtures;

use Djl997\FilamentModelActivityPage\Traits\HasActivities;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasActivities;

    protected $table = 'posts';

    protected $fillable = ['title'];

    public $timestamps = false;
}
