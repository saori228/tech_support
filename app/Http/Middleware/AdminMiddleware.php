<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

/**
 * Middleware для проверки прав администратора
 * Промежуточное ПО, которое проверяет, является ли пользователь администратором
 * перед доступом к защищенным административным страницам
 */
class AdminMiddleware
{
    /**
     * Обработка входящего запроса
     * Проверяет права доступа пользователя перед выполнением запроса
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ЛОГИРОВАНИЕ ДЛЯ ОТЛАДКИ
        // Записываем в лог информацию о проверке прав доступа
        // Это помогает отслеживать, кто пытается получить доступ к админ-панели
        Log::info('AdminMiddleware: Checking if user is admin', [
            'user_id' => auth()->id(),                              // ID пользователя (или null если не авторизован)
            'user_role' => auth()->user()->role->name ?? 'no role', // Название роли пользователя или 'no role'
            'is_admin_method' => auth()->user()->isAdmin(),         // Результат проверки метода isAdmin()
        ]);
        
        // ПРОВЕРКА ПРАВ ДОСТУПА
        // Проверяем два условия:
        // 1. Пользователь должен быть авторизован (!auth()->user() проверяет отсутствие авторизации)
        // 2. Пользователь должен быть администратором (!auth()->user()->isAdmin() проверяет права)
        if (!auth()->user() || !auth()->user()->isAdmin()) {
            // БЛОКИРОВКА ДОСТУПА
            // Если любое из условий не выполнено - перенаправляем на главную страницу
            // with('error', ...) добавляет сообщение об ошибке в сессию для отображения пользователю
            return redirect()->route('home')->with('error', 'У вас нет прав для доступа к этой странице');
        }
        
        // РАЗРЕШЕНИЕ ДОСТУПА
        // Если все проверки пройдены - передаем управление следующему middleware или контроллеру
        // $next($request) продолжает выполнение цепочки обработки запроса
        return $next($request);
    }
}