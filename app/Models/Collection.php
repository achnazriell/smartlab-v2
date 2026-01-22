<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Collection extends Model
{
    protected $table = 'collections';
    protected $fillable = ['user_id', 'status', 'task_id', 'file_collection'];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }

    // App\Models\Collection.php
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assessment()
    {
        return $this->hasOne(Assessment::class);
    }

    protected static function booted()
    {
        static::updating(function ($collection) {
            if ($collection->isDirty('date_collection')) {
                $now = now();

                if ($collection->date_collection > $now && $collection->status == 'Tidak mengumpulkan') {
                    $collection->status = 'Belum mengumpulkan';
                }
            }
        });
    }
}
