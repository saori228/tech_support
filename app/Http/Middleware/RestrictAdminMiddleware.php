<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware для ограничения доступа администраторов
 * Промежуточное ПО, которое БЛОКИРУЕТ администраторам доступ к определенным страницам
 * Обратная логика AdminMiddleware - не пускает админов туда, где они не должны быть
 */
class RestrictAdminMiddleware
{
    /**
     * Обработка входящего запроса
     * Проверяет, является ли пользователь администратором, и блокирует доступ к запрещенным страницам
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ПРОВЕРКА ОГРАНИЧЕНИЙ ДЛЯ АДМИНИСТРАТОРА
        // Проверяем два условия одновременно:
        // 1. Пользователь является администратором (auth()->user()->isAdmin())
        // 2. Текущий маршрут НЕ входит в список разрешенных для админа
        if (auth()->user()->isAdmin() && !in_array($request->route()->getName(), ['tickets.index'])) {
            
            // БЛОКИРОВКА ДОСТУПА АДМИНИСТРАТОРА
            // Если админ пытается зайти на запрещенную для него страницу - 
            // перенаправляем его на главную страницу без сообщения об ошибке
            return redirect()->route('home');
        }
        
        // РАЗРЕШЕНИЕ ДОСТУПА
        // Если пользователь НЕ админ ИЛИ админ заходит на разрешенную страницу -
        // продолжаем выполнение запроса
        return $next($request);
    }
}