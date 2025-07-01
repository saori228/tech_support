<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Контроллер аутентификации
 * Отвечает за вход, регистрацию, сброс пароля и выход пользователей
 */
class AuthController extends Controller
{
    /**
     * Показать форму входа в систему
     * 
     */
    public function showLoginForm()
    {
        // Возвращаем представление (view) с формой входа
        return view('auth.login');
    }

    /**
     * Обработка входа пользователя в систему
     * Проверяет email и пароль, авторизует пользователя
     */
    public function login(Request $request)
    {
        // ВАЛИДАЦИЯ ВХОДНЫХ ДАННЫХ
        // Создаем валидатор для проверки данных формы
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',    // Email обязателен
            'password' => 'required',       // Пароль обязателен
        ]);

        // Если валидация не прошла
        if ($validator->fails()) {
            // Возвращаемся назад с ошибками и сохраняем введенные данные
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ПОПЫТКА ВХОДА
        // Получаем только email и password из запроса
        $credentials = $request->only('email', 'password');

        // Пытаемся авторизовать пользователя с этими данными
        if (Auth::attempt($credentials)) {
            // Если успешно - перенаправляем на главную страницу
            return redirect()->route('home');
        }

        // Если вход не удался - возвращаемся с ошибкой
        return redirect()->back()->withErrors(['email' => 'Неверные учетные данные'])->withInput();
    }

    /**
     * Показать форму регистрации
     * 
     */
    public function showRegisterForm()
    {
        // Возвращаем представление с формой регистрации
        return view('auth.register');
    }

    /**
     * Обработка регистрации нового пользователя
     * Создает нового пользователя и сразу авторизует его
     */
    public function register(Request $request)
    {
        // ВАЛИДАЦИЯ ДАННЫХ РЕГИСТРАЦИИ
        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:30',     // Имя: обязательно, строка, максимум 30 символов
            'last_name' => 'required|string|max:30',      // Фамилия: обязательно, строка, максимум 30 символов
            'email' => 'required|email|unique:users',     // Email: обязательно, корректный, уникальный в таблице users
            // Пароль: обязательно, строка, 8-30 символов, должен содержать буквы и цифры
            'password' => 'required|string|min:8|max:30|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
        ]);

        // Если валидация не прошла
        if ($validator->fails()) {
            // Возвращаемся назад с ошибками и сохраняем введенные данные
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ПОЛУЧЕНИЕ РОЛИ ПОЛЬЗОВАТЕЛЯ
        // Находим роль "пользователь" в базе данных
        $userRole = Role::where('name', 'пользователь')->first();

        // СОЗДАНИЕ НОВОГО ПОЛЬЗОВАТЕЛЯ
        User::create([
            'first_name' => $request->first_name,           // Имя из формы
            'last_name' => $request->last_name,             // Фамилия из формы
            'email' => $request->email,                     // Email из формы
            'password' => Hash::make($request->password),   // Хешируем пароль для безопасности
            'role_id' => $userRole->id,                     // Присваиваем роль обычного пользователя
        ]);

        // АВТОМАТИЧЕСКИЙ ВХОД ПОСЛЕ РЕГИСТРАЦИИ
        // Сразу авторизуем пользователя с его данными
        Auth::attempt($request->only('email', 'password'));

        // Перенаправляем на главную страницу
        return redirect()->route('home');
    }

    /**
     * Показать форму восстановления пароля
     * 
     */
    public function showForgotPasswordForm()
    {
        // Возвращаем представление с формой восстановления пароля
        return view('auth.forgot-password');
    }

    /**
     * Сброс пароля пользователя
     * Позволяет пользователю установить новый пароль по email
     */
    public function resetPassword(Request $request)
    {
        // ВАЛИДАЦИЯ ДАННЫХ СБРОСА ПАРОЛЯ
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',           // Email должен существовать в базе
            // Новый пароль: те же требования что и при регистрации
            'password' => 'required|string|min:8|max:30|regex:/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/',
            'password_confirmation' => 'required|same:password',      // Подтверждение пароля должно совпадать
        ]);

        // Если валидация не прошла
        if ($validator->fails()) {
            // Возвращаемся назад с ошибками и сохраняем введенные данные
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // ОБНОВЛЕНИЕ ПАРОЛЯ
        // Находим пользователя по email
        $user = User::where('email', $request->email)->first();
        
        // Устанавливаем новый хешированный пароль
        $user->password = Hash::make($request->password);
        
        // Сохраняем изменения в базе данных
        $user->save();

        // Перенаправляем на страницу входа с сообщением об успехе
        return redirect()->route('login')->with('success', 'Пароль успешно изменен');
    }

    /**
     * Выход пользователя из системы
     * Завершает сессию пользователя
     */
    public function logout()
    {
        // Завершаем авторизацию пользователя
        Auth::logout();
        
        // Перенаправляем на страницу входа
        return redirect()->route('login');
    }
}