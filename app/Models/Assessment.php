<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    protected $fillable = [
        'collection_id',
        'user_id',
        'status',
        'mark_task'
    ];

    /**
     * Relasi ke user (guru yang menilai)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke collection
     */
    public function collection()
    {
        return $this->belongsTo(Collection::class);
    }

    /**
     * Mendapatkan tugas melalui collection
     */
    public function task()
    {
        return $this->hasOneThrough(Task::class, Collection::class, 'id', 'id', 'collection_id', 'task_id');
    }

    /**
     * Mendapatkan kelas dari siswa yang dinilai
     */
    public function classes()
    {
        return Classes::whereHas('students', function ($query) {
            $query->where('user_id', $this->user_id);
        });
    }
}
