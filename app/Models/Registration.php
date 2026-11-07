<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Registration extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'registration_number',
        'user_id',
        'study_program_id',
        'full_name',
        'phone_number',
        'gender',
        'birth_place',
        'birth_date',
        'address',
        'previous_school',
        'status',
        'notes',
        'submitted_at',
        'verified_at',
        'passed_at',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'submitted_at' => 'datetime',
            'verified_at' => 'datetime',
            'passed_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function paymentConfirmation(): HasOne
    {
        return $this->hasOne(PaymentConfirmation::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('ktp')->singleFile();
        $this->addMediaCollection('ijazah')->singleFile();
        $this->addMediaCollection('transkrip_nilai')->singleFile();
        $this->addMediaCollection('pas_foto')->singleFile();
        $this->addMediaCollection('bukti_pembayaran')->singleFile();
    }
}

