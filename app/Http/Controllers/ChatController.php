<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

/**
 * Контроллер чата
 * Обрабатывает сообщения между пользователями, сотрудниками поддержки и администраторами
 * 
 */
class ChatController extends Controller
{
    /**
     * Главная страница чата
     * Показывает разный интерфейс в зависимости от роли пользователя:
     * - Сотрудник: чат с пользователями
     * 
     * - Пользователь: чат с сотрудником поддержки
     */
    public function index(Request $request)
    {
        // Получаем текущего авторизованного пользователя
        $user = Auth::user();
        
        // ЛОГИКА ДЛЯ СОТРУДНИКА ПОДДЕРЖКИ
        if ($user->isSupport()) {
            
            // БЛОК ПОИСКА ПОЛЬЗОВАТЕЛЕЙ
            // Если есть параметр поиска - обрабатываем поиск
            if ($request->has('search')) {
                // Получаем роль обычного пользователя
                $userRole = Role::where('name', 'пользователь')->first();
                
                if (!$userRole) {
                    return response()->json([]);
                }
                
                $searchTerm = $request->search;  // Поисковый запрос
                
                // Если поисковый запрос пустой
                if (empty($searchTerm)) {
                    // Показываем всех пользователей
                    $searchResults = User::where('role_id', $userRole->id)
                        ->orderBy('first_name')
                        ->limit(10)
                        ->get();
                } else {
                    // Поиск по email и ФИО
                    $searchResults = User::where('role_id', $userRole->id)
                        ->where(function($query) use ($searchTerm) {
                            $query->where('email', 'like', '%' . $searchTerm . '%')
                                  ->orWhere('first_name', 'like', '%' . $searchTerm . '%')
                                  ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchTerm . '%']);
                        })
                        ->get();
                }
                
                // ДОБАВЛЯЕМ ИНФОРМАЦИЮ О НОВЫХ СООБЩЕНИЯХ
                foreach ($searchResults as $searchUser) {
                    // Проверяем, когда сотрудник последний раз открывал чат с этим пользователем
                    // Если не открывал - берем время сутки назад
                    $lastViewTime = session('last_view_' . $searchUser->id, now()->subDays(1));
                    
                    // Проверяем, есть ли новые сообщения от пользователя после последнего просмотра
                    $searchUser->has_new_messages = Message::where('user_id', $searchUser->id)
                        ->where('support_id', $user->id)      // К текущему сотруднику
                        ->where('is_from_user', true)         // От пользователя
                        ->where('created_at', '>', $lastViewTime)  // После последнего просмотра
                        ->exists();  // Проверяем существование таких сообщений
                    
                    // Считаем количество непрочитанных сообщений
                    $searchUser->unread_count = Message::where('user_id', $searchUser->id)
                        ->where('support_id', $user->id)
                        ->where('is_from_user', true)
                        ->where('created_at', '>', $lastViewTime)
                        ->count();  // Считаем количество
                }
                
                // Возвращаем результаты поиска в формате JSON для AJAX (Передаём текстовые данные, чтобы не обновлять страницу для их получения)
                return response()->json($searchResults);
            }
            
            // ОСНОВНАЯ ЛОГИКА ДЛЯ СОТРУДНИКА (когда нет поиска)
            // Получаем всех пользователей (не сотрудников и не админов)
            $userRole = Role::where('name', 'пользователь')->first();
            $users = User::where('role_id', $userRole->id)->get();
            
            // Если пользователей нет - возвращаем пустую страницу
            if ($users->isEmpty()) {
                return view('chat.index', ['users' => collect(), 'messages' => collect()]);
            }
            
            // ВЫБОР ТЕКУЩЕГО ПОЛЬЗОВАТЕЛЯ ДЛЯ ЧАТА
            $currentUserIndex = 0;  // По умолчанию первый пользователь
            if ($request->has('user_id')) {
                // Если передан ID пользователя - ищем его в списке
                $selectedUser = $users->firstWhere('id', $request->user_id);
                if ($selectedUser) {
                    // Находим индекс выбранного пользователя в коллекции
                    $currentUserIndex = $users->search(function($item) use ($selectedUser) {
                        return $item->id === $selectedUser->id;
                    });
                } else {
                    // Если пользователь не найден - берем первого
                    $selectedUser = $users->first();
                }
            } else {
                // Если ID не передан - берем первого пользователя
                $selectedUser = $users->first();
            }
            
            // НАВИГАЦИЯ МЕЖДУ ПОЛЬЗОВАТЕЛЯМИ (циклическая)
            // Вычисляем индексы предыдущего и следующего пользователя
            $prevUserIndex = ($currentUserIndex - 1 + $users->count()) % $users->count();
            $nextUserIndex = ($currentUserIndex + 1) % $users->count();
            
            $prevUser = $users[$prevUserIndex];  // Предыдущий пользователь
            $nextUser = $users[$nextUserIndex];  // Следующий пользователь
            
            // ПОЛУЧЕНИЕ СООБЩЕНИЙ
            // Получаем все сообщения между выбранным пользователем и текущим сотрудником
            $messages = Message::getBetweenUserAndSupport($selectedUser->id, $user->id);
            
            // ОТМЕТКА О ПРОСМОТРЕ
            // Сохраняем время просмотра чата с этим пользователем в сессии
            // Это нужно для определения новых сообщений
            session(['last_view_' . $selectedUser->id => now()]);
            
            // Возвращаем представление с данными
            return view('chat.index', compact('users', 'selectedUser', 'messages', 'prevUser', 'nextUser'));
            
        // ЛОГИКА ДЛЯ АДМИНИСТРАТОРА
        } elseif ($user->isAdmin()) {
            
            // ПОИСК СОТРУДНИКОВ ДЛЯ АДМИНА
            if ($request->has('search')) {
                $supportRole = Role::where('name', 'сотрудник')->first();
                
                if (!$supportRole) {
                    return response()->json([]);
                }
                
                $searchTerm = $request->search;
                
                if (empty($searchTerm)) {
                    // Показываем всех сотрудников
                    $searchResults = User::where('role_id', $supportRole->id)
                        ->orderBy('first_name')
                        ->limit(10)
                        ->get();
                } else {
                    // Поиск сотрудников по email и ФИО
                    $searchResults = User::where('role_id', $supportRole->id)
                        ->where(function($query) use ($searchTerm) {
                            $query->where('email', 'like', '%' . $searchTerm . '%')
                                  ->orWhere('first_name', 'like', '%' . $searchTerm . '%')
                                  ->orWhere('last_name', 'like', '%' . $searchTerm . '%')
                                  ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ['%' . $searchTerm . '%']);
                        })
                        ->get();
                }
                
                return response()->json($searchResults);
            }
            
            // ОСНОВНАЯ ЛОГИКА ДЛЯ АДМИНА
            // Для админа показываем чат с сотрудниками
            $supportRole = Role::where('name', 'сотрудник')->first();
            $supportUsers = User::where('role_id', $supportRole->id)->get();
            
            // Если сотрудников нет
            if ($supportUsers->isEmpty()) {
                return view('chat.index', ['messages' => collect()]);
            }
            
            // ВЫБОР СОТРУДНИКА ДЛЯ ЧАТА (аналогично логике с пользователями)
            $currentSupportIndex = 0;
            if ($request->has('support_id')) {
                $selectedSupport = $supportUsers->firstWhere('id', $request->support_id);
                if ($selectedSupport) {
                    $currentSupportIndex = $supportUsers->search(function($item) use ($selectedSupport) {
                        return $item->id === $selectedSupport->id;
                    });
                } else {
                    $selectedSupport = $supportUsers->first();
                }
            } else {
                $selectedSupport = $supportUsers->first();
            }
            
            // НАВИГАЦИЯ МЕЖДУ СОТРУДНИКАМИ
            $prevSupportIndex = ($currentSupportIndex - 1 + $supportUsers->count()) % $supportUsers->count();
            $nextSupportIndex = ($currentSupportIndex + 1) % $supportUsers->count();
            
            $prevSupport = $supportUsers[$prevSupportIndex];
            $nextSupport = $supportUsers[$nextSupportIndex];
            
            // Получаем сообщения между админом и выбранным сотрудником
            $messages = Message::getBetweenUserAndSupport($user->id, $selectedSupport->id);
            
            return view('chat.index', compact('messages', 'supportUsers', 'selectedSupport', 'prevSupport', 'nextSupport'));
            
        // ЛОГИКА ДЛЯ ОБЫЧНОГО ПОЛЬЗОВАТЕЛЯ
        } else {
            // Для обычного пользователя - простой чат с первым доступным сотрудником
            $supportRole = Role::where('name', 'сотрудник')->first();
            $supportUser = User::where('role_id', $supportRole->id)->first();  // Берем первого сотрудника
            
            if ($supportUser) {
                // Получаем сообщения между пользователем и сотрудником
                $messages = Message::getBetweenUserAndSupport($user->id, $supportUser->id);
            } else {
                // Если сотрудников нет - пустая коллекция сообщений
                $messages = collect();
            }
            
            return view('chat.index', compact('messages', 'supportUser'));
        }
    }
    
    /**
     * Отправка нового сообщения
     * Обрабатывает отправку сообщений с учетом роли отправителя
     */
    public function store(Request $request)
    {
        // ВАЛИДАЦИЯ ДАННЫХ СООБЩЕНИЯ
        $validator = Validator::make($request->all(), [
            'content' => 'required|string',              // Текст сообщения обязателен
            'attachment' => 'nullable|file|max:2048',    // Вложение необязательно, максимум 2MB
            'recipient_id' => 'nullable|exists:users,id', // ID получателя должен существовать в таблице users
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        $user = Auth::user();
        $attachmentPath = null;  // Путь к загруженному файлу
        
        // ОБРАБОТКА ВЛОЖЕНИЯ
        if ($request->hasFile('attachment')) {
            // Сохраняем файл в папку attachments в публичном хранилище
            $attachmentPath = $request->file('attachment')->store('attachments', 'public');
        }
        
        // ЛОГИКА ОТПРАВКИ В ЗАВИСИМОСТИ ОТ РОЛИ
        
        // СОТРУДНИК ОТПРАВЛЯЕТ СООБЩЕНИЕ ПОЛЬЗОВАТЕЛЮ
        if ($user->isSupport()) {
            Message::create([
                'user_id' => $request->recipient_id,      // ID пользователя-получателя
                'support_id' => $user->id,                // ID сотрудника-отправителя
                'content' => $request->content,           // Текст сообщения
                'attachment' => $attachmentPath,          // Путь к вложению
                'is_from_user' => false,                  // Сообщение НЕ от пользователя (от сотрудника)
            ]);
            
            // Перенаправляем обратно к чату с этим пользователем
            return redirect()->route('chat.index', ['user_id' => $request->recipient_id]);
            
        // АДМИН ОТПРАВЛЯЕТ СООБЩЕНИЕ СОТРУДНИКУ
        } elseif ($user->isAdmin()) {
            Message::create([
                'user_id' => $user->id,                   // ID админа как "пользователя"
                'support_id' => $request->recipient_id,   // ID сотрудника-получателя
                'content' => $request->content,
                'attachment' => $attachmentPath,
                'is_from_user' => true,                   // Админ выступает как "пользователь" в этой связке
            ]);
            
            // Перенаправляем обратно к чату с этим сотрудником
            return redirect()->route('chat.index', ['support_id' => $request->recipient_id]);
            
        // ОБЫЧНЫЙ ПОЛЬЗОВАТЕЛЬ ОТПРАВЛЯЕТ СООБЩЕНИЕ СОТРУДНИКУ
        } else {
            // Находим первого доступного сотрудника
            $supportRole = Role::where('name', 'сотрудник')->first();
            $supportUser = User::where('role_id', $supportRole->id)->first();
            
            Message::create([
                'user_id' => $user->id,                           // ID пользователя-отправителя
                'support_id' => $supportUser ? $supportUser->id : null,  // ID сотрудника или null если нет сотрудников
                'content' => $request->content,
                'attachment' => $attachmentPath,
                'is_from_user' => true,                           // Сообщение ОТ пользователя
            ]);
            
            // Перенаправляем на главную страницу чата
            return redirect()->route('chat.index');
        }
    }
}
