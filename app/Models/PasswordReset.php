<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PasswordReset extends Model
{
    use HasFactory;

    /**
     * Atributos que podem ser preenchidos em massa.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'code',
        'expires_at',
    ];

    /**
     * O nome da tabela associada ao model.
     *
     * @var string
     */
    protected $table = 'password_resets';

    /**
     * Define o relacionamento com o modelo `User`.
     * Cada código de redefinição de senha pertence a um usuário.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verifica se o código de redefinição de senha expirou.
     *
     * @return bool
     */
    public function isExpired(): bool
    {
        return Carbon::now()->greaterThan($this->expires_at);
    }

    /**
     * Gera um código de redefinição de senha único.
     *
     * @return string
     */
    public static function generateUniqueCode(): string
    {
        do {
            $code = bin2hex(random_bytes(16)); // Gera um código hexadecimal aleatório de 32 caracteres
        } while (self::where('code', $code)->exists());

        return $code;
    }

    /**
     * Define o atributo `expires_at` como uma instância de `Carbon`.
     *
     * @param  string  $value
     * @return void
     */
    public function setExpiresAtAttribute($value): void
    {
        $this->attributes['expires_at'] = Carbon::parse($value);
    }
}
