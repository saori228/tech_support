<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель обращений (тикетов)
 * Представляет обращения пользователей в службу технической поддержки
 * Содержит информацию о проблеме, сроках обработки и текущем статусе
 */
class Ticket extends Model
{
    use HasFactory;
    
    /**
     * Поля, которые можно массово заполнять
     * Определяет, какие поля можно безопасно заполнять через create() или fill()
     */
    protected $fillable = [
        'ticket_number',        // Уникальный номер обращения (1-999)
        'user_id',             // ID пользователя, создавшего обращение
        'description',         // Описание проблемы от пользователя
        'error_text',          // Текст ошибки, которую получил пользователь
        'error_datetime',      // Дата и время возникновения ошибки
        'processing_deadline', // Срок обработки обращения (дедлайн)
        'status_id',          // ID текущего статуса обращения
    ];
    
    /**
     * Приведение типов для атрибутов
     * Автоматически преобразует значения из базы данных в нужные типы PHP
     */
    protected $casts = [
        'error_datetime' => 'datetime',      // Преобразует строку из БД в объект Carbon (дата/время)
        'processing_deadline' => 'datetime', // Преобразует строку из БД в объект Carbon (дата/время)
    ];
    
    /**
     * Связь с пользователем
     * Возвращает пользователя, который создал это обращение
     */
    public function user()
    {
        // belongsTo означает "принадлежит к"
        // Это обращение принадлежит пользователю с ID = user_id
        // Laravel автоматически ищет поле user_id в таблице tickets
        return $this->belongsTo(User::class);
    }
    
    /**
     * Связь со статусом обращения
     * Возвращает текущий статус этого обращения
     */
    public function status()
    {
        // Это обращение принадлежит статусу с ID = status_id
        // Указываем явно поле 'status_id', так как Laravel ожидал бы 'ticket_status_id'
        return $this->belongsTo(TicketStatus::class, 'status_id');
    }
}