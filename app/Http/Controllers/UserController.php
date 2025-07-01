<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Контроллер пользователя
 * Управляет профилем пользователя:
 * - Отображение страницы профиля
 * - Обновление личных данных пользователя
 */
class UserController extends Controller
{
    /**
     * Отображение страницы профиля пользователя
     * Показывает текущие данные авторизованного пользователя
     */
    public function profile()
    {
        // ПОЛУЧЕНИЕ ДАННЫХ ПОЛЬЗОВАТЕЛЯ
        // Получаем текущего авторизованного пользователя
        $user = Auth::user();
        
        // ВОЗВРАТ ПРЕДСТАВЛЕНИЯ
        // Возвращаем страницу профиля с данными пользователя
        // compact('user') создает массив ['user' => $user] для передачи в представление
        return view('users.profile', compact('user'));
    }
    
    /**
     * Обновление данных профиля пользователя
     * Позволяет пользователю изменить свои личные данные
     */
    public function update(Request $request)
    {
        // ПОЛУЧЕНИЕ ТЕКУЩЕГО ПОЛЬЗОВАТЕЛЯ
        // Получаем пользователя, который выполняет обновление
        $user = Auth::user();
        
        // ВАЛИДАЦИЯ ВХОДНЫХ ДАННЫХ
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:30',                           // Имя: обязательно, строка, максимум 30 символов
            'last_name' => 'required|string|max:30',                            // Фамилия: обязательно, строка, максимум 30 символов
            'email' => 'required|email|unique:users,email,' . $user->id,        // Email: обязательно, корректный, уникальный (исключая текущего пользователя)
            // Пароль: необязательно, но если указан - должен соответствовать требованиям
            'password' => 'nullable|string|min:8|max:30|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
        ]);
        
        // ПРОВЕРКА РЕЗУЛЬТАТОВ ВАЛИДАЦИИ
        if ($validator->fails()) {
            // Если валидация не прошла - возвращаемся назад с ошибками и сохраняем введенные данные
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // ОБНОВЛЕНИЕ ОСНОВНЫХ ДАННЫХ
        // Обновляем имя пользователя из формы
        $user->first_name = $request->first_name;
        
        // Обновляем фамилию пользователя из формы
        $user->last_name = $request->last_name;
        
        // Обновляем email пользователя из формы
        $user->email = $request->email;
        
        // ОБНОВЛЕНИЕ ПАРОЛЯ (если указан)
        // Проверяем, заполнено ли поле пароля
        if ($request->filled('password')) {
            // Если пароль указан - хешируем его и сохраняем
            // Hash::make() создает безопасный хеш пароля
            $user->password = Hash::make($request->password);
        }
        // Если пароль не указан - оставляем старый пароль без изменений
        
        // СОХРАНЕНИЕ ИЗМЕНЕНИЙ
        // Сохраняем все изменения в базе данных
        $user->save();
        
        // ВОЗВРАТ С СООБЩЕНИЕМ ОБ УСПЕХЕ
        // Возвращаемся на ту же страницу с сообщением об успешном обновлении
        return redirect()->back()->with('success', 'Профиль успешно обновлен');
    }
}