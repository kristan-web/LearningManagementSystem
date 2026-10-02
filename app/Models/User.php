<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'email',
        'password',
        'must_change_password',
        'role',
        'status',
        'is_deleted',
        'contact_number',
        'address',
        'birthdate',
        'gender',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'must_change_password' => 'boolean',
            'is_deleted' => 'boolean',
            'birthdate' => 'date',
        ];
    }

    public function getAuthIdentifierName(): string
    {
        return 'user_id';
    }

    public function teacher()
    {
        return $this->hasOne(Teacher::class, 'user_id', 'user_id');
    }


    public function student()
    {
        return $this->hasOne(Student::class, 'user_id', 'user_id');
    }

}
