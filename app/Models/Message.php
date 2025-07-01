<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Модель сообщений
 * Представляет сообщения в системе чата между пользователями и сотрудниками поддержки
 * Поддерживает двустороннюю связь: пользователь ↔ сотрудник, админ ↔ сотрудник
 */
class Message extends Model
{
    use HasFactory;
    
    /**
     * Поля, которые можно массово заполнять
     * Определяет, какие поля можно безопасно заполнять через create() или fill()
     */
    protected $fillable = [
        'user_id',      // ID пользователя (отправитель или получатель)
        'support_id',   // ID сотрудника поддержки (отправитель или получатель)
        'content',      // Текст сообщения
        'attachment',   // Путь к прикрепленному файлу (если есть)
        'is_from_user', // Флаг: true = сообщение ОТ пользователя, false = ОТ сотрудника
        'is_read',      // Флаг: прочитано ли сообщение
    ];
    
    /**
     * Приведение типов для атрибутов
     * Автоматически преобразует значения из базы данных в нужные типы PHP
     */
    protected $casts = [
        'is_from_user' => 'boolean',  // Преобразует 0/1 из БД в true/false
        'is_read' => 'boolean',       // Преобразует 0/1 из БД в true/false
    ];
    
    /**
     * Связь с пользователем
     * Возвращает пользователя, который участвует в переписке
     */
    public function user()
    {
        // belongsTo означает "принадлежит к"
        // Это сообщение принадлежит пользователю с ID = user_id
        return $this->belongsTo(User::class, 'user_id');
    }
    
    /**
     * Связь с сотрудником поддержки
     * Возвращает сотрудника, который участвует в переписке
     */
    public function support()
    {
        // Это сообщение принадлежит сотруднику с ID = support_id
        // Используем ту же модель User, но это сотрудник поддержки
        return $this->belongsTo(User::class, 'support_id');
    }
    
    /**
     * Получение всех сообщений между пользователем и сотрудником
     * Статический метод для получения полной переписки между двумя участниками
     */
    public static function getBetweenUserAndSupport($userId, $supportId)
    {
        return self::where(function($query) use ($userId, $supportId) {
            // ПЕРВЫЙ СЛУЧАЙ: пользователь пишет сотруднику
            $query->where(function($q) use ($userId, $supportId) {
                $q->where('user_id', $userId)      // Пользователь как отправитель
                  ->where('support_id', $supportId); // Сотрудник как получатель
            })
            // ИЛИ ВТОРОЙ СЛУЧАЙ: сотрудник пишет пользователю (или админ пишет сотруднику)
            ->orWhere(function($q) use ($userId, $supportId) {
                $q->where('user_id', $supportId)   // Сотрудник как "пользователь" (для админ-чата)
                  ->where('support_id', $userId);  // Пользователь как "сотрудник"
            });
        })
        ->orderBy('created_at')  // Сортируем по времени создания (от старых к новым)
        ->get();                 // Получаем все результаты
    }
    
    /**
     * Получение всех сообщений конкретного пользователя
     * Возвращает все сообщения, где пользователь участвует (как отправитель или получатель)
     */
    public static function getAllForUser($userId)
    {
        return self::where(function($query) use ($userId) {
            $query->where('user_id', $userId)    // Пользователь как отправитель
                  ->orWhere('support_id', $userId); // ИЛИ пользователь как получатель
        })
        ->orderBy('created_at')  // Сортируем по времени
        ->get();
    }
    
    /**
     * Отметка сообщений как прочитанных
     * Помечает все непрочитанные сообщения между двумя пользователями как прочитанные
     */
    public static function markAsReadBetweenUsers($fromUserId, $toUserId)
    {
        return self::where('user_id', $fromUserId)    // Сообщения ОТ первого пользователя
            ->where('support_id', $toUserId)          // К второму пользователю
            ->where('is_read', false)                 // Которые еще не прочитаны
            ->update(['is_read' => true]);            // Помечаем как прочитанные
    }
    
    /**
     * Проверка наличия непрочитанных сообщений
     * Проверяет, есть ли непрочитанные сообщения от одного пользователя к другому
     */
    public static function hasUnreadMessages($fromUserId, $toUserId, $isFromUser = true)
    {
        return self::where('user_id', $fromUserId)        // От кого
            ->where('support_id', $toUserId)              // Кому
            ->where('is_from_user', $isFromUser)          // Направление сообщения
            ->where('is_read', false)                     // Непрочитанные
            ->exists();                                   // Проверяем существование (true/false)
    }
    
    /**
     * Подсчет непрочитанных сообщений
     * Возвращает количество непрочитанных сообщений между пользователями
     */
    public static function getUnreadCount($fromUserId, $toUserId, $isFromUser = true)
    {
        return self::where('user_id', $fromUserId)        // От кого
            ->where('support_id', $toUserId)              // Кому
            ->where('is_from_user', $isFromUser)          // Направление
            ->where('is_read', false)                     // Непрочитанные
            ->count();                                    // Считаем количество
    }
    
    /**
     * Получение пользователей с непрочитанными сообщениями для сотрудника
     * Возвращает список ID пользователей, которые писали сотруднику и сообщения не прочитаны
     */
    public static function getUsersWithUnreadMessages($supportId)
    {
        return self::where('support_id', $supportId)              // К конкретному сотруднику
            ->where('is_from_user', true)                         // Сообщения ОТ пользователей
            ->where('is_read', false)                             // Непрочитанные
            ->where('created_at', '>', now()->subHours(24))       // За последние 24 часа
            ->distinct()                                          // Уникальные пользователи
            ->pluck('user_id');                                   // Получаем только ID пользователей
    }
}