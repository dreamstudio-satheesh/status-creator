<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = [
        'log_name',
        'description',
        'subject_type',
        'subject_id',
        'causer_type',
        'causer_id',
        'properties',
        'event',
        'batch_uuid',
    ];

    protected $casts = [
        'properties' => 'array',
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(string $description): ActivityLogBuilder
    {
        return new ActivityLogBuilder($description);
    }

    public function scopeInLog($query, ...$logNames)
    {
        if (is_array($logNames[0])) {
            $logNames = $logNames[0];
        }

        return $query->whereIn('log_name', $logNames);
    }

    public function scopeCausedBy($query, Model $causer)
    {
        return $query
            ->where('causer_type', $causer->getMorphClass())
            ->where('causer_id', $causer->getKey());
    }

    public function scopeForSubject($query, Model $subject)
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }
}

class ActivityLogBuilder
{
    protected string $description;
    protected ?Model $subject = null;
    protected ?Model $causer = null;
    protected array $properties = [];
    protected ?string $logName = null;
    protected ?string $event = null;

    public function __construct(string $description)
    {
        $this->description = $description;
    }

    public function on(Model $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    public function by(Model $causer): self
    {
        $this->causer = $causer;
        return $this;
    }

    public function withProperties(array $properties): self
    {
        $this->properties = array_merge($this->properties, $properties);
        return $this;
    }

    public function withProperty(string $key, $value): self
    {
        $this->properties[$key] = $value;
        return $this;
    }

    public function useLog(string $logName): self
    {
        $this->logName = $logName;
        return $this;
    }

    public function event(string $event): self
    {
        $this->event = $event;
        return $this;
    }

    public function log(): ActivityLog
    {
        $data = [
            'description' => $this->description,
            'log_name' => $this->logName,
            'event' => $this->event,
            'properties' => $this->properties,
            'created_at' => now(),
        ];

        if ($this->subject) {
            $data['subject_type'] = $this->subject->getMorphClass();
            $data['subject_id'] = $this->subject->getKey();
        }

        if ($this->causer) {
            $data['causer_type'] = $this->causer->getMorphClass();
            $data['causer_id'] = $this->causer->getKey();
        }

        return ActivityLog::create($data);
    }
}