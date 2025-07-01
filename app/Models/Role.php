<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель ролей пользователей
 * Представляет роли в системе: пользователь, сотрудник, администратор
 * Определяет права доступа и функционал для каждого типа пользователей
 */
class Role extends Model
{
    use HasFactory;
    
    /**
     * Поля, которые можно массово заполнять
     * Определяет, какие поля можно безопасно заполнять через create() или fill()
     */
    protected $fillable = ['name'];  // Только название роли можно заполнять массово
    
    /**
     * Связь с пользователями
     * Одна роль может принадлежать многим пользователям (связь один-ко-многим)
     */
    public function users()
    {
        // hasMany означает "имеет много"
        // Одна роль может быть назначена многим пользователям
        // Laravel автоматически ищет поле role_id в таблице users
        return $this->hasMany(User::class);
    }
}