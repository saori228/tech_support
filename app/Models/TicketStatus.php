<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель статусов обращений
 * Представляет возможные статусы обращений в системе технической поддержки
 * Определяет этапы жизненного цикла обращения: обработка, приостановка, завершение
 */
class TicketStatus extends Model
{
    use HasFactory;
    
    /**
     * Поля, которые можно массово заполнять
     * Определяет, какие поля можно безопасно заполнять через create() или fill()
     */
    protected $fillable = ['name'];  // Только название статуса можно заполнять массово
    
    /**
     * Связь с обращениями
     * Один статус может быть назначен многим обращениям (связь один-ко-многим)
     */
    public function tickets()
    {
        // hasMany означает "имеет много"
        // Один статус может быть у многих обращений
        return $this->hasMany(Ticket::class, 'status_id');
    }
}