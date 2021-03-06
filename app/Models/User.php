<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [ 'name', 'email', 'password', 'role', 'api_token' ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function taskResults()
    {
        return $this->hasMany(TaskResult::class);
    }

    public function isAdmin()
    {
        return $this->role == 'admin' ? true : false;
    }

    public function isTaskMaster()
    {
        return $this->role == 'pengawas' ? true : false;
    }

    public function isTester()
    {
        return $this->role == 'penguji' ? true : false;
    }

    public static function search($search)
    {
        return empty($search) ? static::query() : 
        static::where('name', 'like', '%'.$search.'%')
                ->orWhere('email', 'like', '%'.$search.'%')
                ->orWhere('created_at', 'like', '%'.$search.'%');
    }

    public static function validateForm($params)
    {
        $rules = [
            'name' => ['required', 'string', 'min:5'],
            'email' => ['email', 'unique:users,email'],
            'role' => ['required', 'in:pengawas,penguji'],
        ];

        $messages = [
            'required' => ':attribute tidak boleh kosong',
            'string' => ':attribute harus bertipe teks',
            'min' => ':attribute minimal :min',
            'unique' => ':attribute sudah pernah ditambahkan',
            'email' => ':attribute harus bertipe email',
            'in' => ':attribute harus diantara :in',
        ];

        $attributes = [
            'name' => 'Nama',  
            'email' => 'Email Address',
            'role' => 'Role',   
        ];

        return Validator::make($params, $rules, $messages, $attributes);
    }

    public static function createOrUpdate($params)
    {
        $validator = self::validateForm($params);
        if ($validator->fails()) {
            return [
                'status' => '422',
                'message' => $validator->getMessageBag()
            ];
        }

        if ($params['id']) {
            $user = self::whereId($params['id'])->first();
            $user->update([
                'name' => $params['name'] ?? $user->name,
                'role' => $params['role'] ?? $user->role,
                //'email' => $params['email'] ?? $user->email,
            ]);

            return [
                "url" => url("/").'/user',
                'status' => 'success',
                'message' => 'berhasil mengubah data !'
            ];
        }

        self::create([
            'name' => $params['name'],
            'email' => $params['email'],
            'role' => $params['role'],
            'api_token' => Str::random(60),
            'password' => Hash::make('12345678')
        ]);

        return [
            "url" => url("/").'/user',
            'status' => 'success',
            'message' => 'berhasil menambahkan data !'
        ];
    }
}
