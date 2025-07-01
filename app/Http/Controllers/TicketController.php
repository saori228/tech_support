<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Ticket;
use App\Models\TicketStatus;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Контроллер обращений (тикетов)
 * Управляет системой технической поддержки:
 * - Пользователи создают обращения
 * - Сотрудники обрабатывают обращения и управляют ими
 * - Админы видят все обращения
 */
class TicketController extends Controller
{
    /**
     * Главная страница обращений
     * Показывает разный контент в зависимости от роли пользователя
     */
    public function index(Request $request)
    {
        // Получаем текущего авторизованного пользователя
        $user = Auth::user();
        
        // ЛОГИКА ДЛЯ СОТРУДНИКА ПОДДЕРЖКИ
        if ($user->isSupport()) {
            
            // БЛОК ПОИСКА ПОЛЬЗОВАТЕЛЕЙ
            // Если есть параметр поиска - обрабатываем AJAX-запрос поиска
            if ($request->has('search')) {
                // Получаем роль обычного пользователя
                $userRole = Role::where('name', 'пользователь')->first();
                $searchTerm = $request->search;  // Поисковый запрос
                
                if (empty($searchTerm)) {
                    // Если поисковый запрос пустой - показываем всех пользователей
                    $searchResults = User::where('role_id', $userRole->id)
                        ->orderBy('first_name')  // Сортируем по имени
                        ->limit(10)              // Ограничиваем 10 результатами
                        ->get();
                } else {
                    // Поиск пользователей по email и ФИО
                    $searchResults = User::where('role_id', $userRole->id)
                        ->where(function($query) use ($searchTerm) {
                            $query->where('email', 'like', '%' . $searchTerm . '%')                    // Поиск по email
                                  ->orWhere('first_name', 'like', '%' . $searchTerm . '%')             // Поиск по имени
                                  ->orWhere('last_name', 'like', '%' . $searchTerm . '%')              // Поиск по фамилии
                                  ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $searchTerm . '%'); // Поиск по полному имени
                        })
                        ->get();
                }
                
                // Возвращаем результаты поиска в формате JSON для AJAX
                return response()->json($searchResults);
            }
            
            // ОСНОВНАЯ ЛОГИКА ДЛЯ СОТРУДНИКА
            // Получаем всех пользователей (не сотрудников и не админов)
            $userRole = Role::where('name', 'пользователь')->first();
            $users = User::where('role_id', $userRole->id)->get();
            
            // Если пользователей нет - возвращаем пустую страницу
            if ($users->isEmpty()) {
                return view('tickets.index', ['users' => collect(), 'tickets' => collect()]);
            }
            
            // ВЫБОР ТЕКУЩЕГО ПОЛЬЗОВАТЕЛЯ
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
            // Используем модуль (%) для циклического перехода
            $prevUserIndex = ($currentUserIndex - 1 + $users->count()) % $users->count();
            $nextUserIndex = ($currentUserIndex + 1) % $users->count();
            
            $prevUser = $users[$prevUserIndex];  // Предыдущий пользователь
            $nextUser = $users[$nextUserIndex];  // Следующий пользователь
            
            // ПОЛУЧЕНИЕ ОБРАЩЕНИЙ ВЫБРАННОГО ПОЛЬЗОВАТЕЛЯ
            // Получаем все обращения конкретного пользователя с загрузкой статуса
            $tickets = Ticket::where('user_id', $selectedUser->id)->with('status')->get();
            
            // Возвращаем представление с данными для сотрудника
            return view('tickets.index', compact('users', 'selectedUser', 'tickets', 'prevUser', 'nextUser'));
            
        // ЛОГИКА ДЛЯ АДМИНИСТРАТОРА
        } elseif ($user->isAdmin()) {
            // Для админа показываем ВСЕ обращения всех пользователей
            // Загружаем связанные данные: пользователя и статус каждого обращения
            $tickets = Ticket::with(['user', 'status'])->get();
            return view('tickets.index', compact('tickets'));
            
        // ЛОГИКА ДЛЯ ОБЫЧНОГО ПОЛЬЗОВАТЕЛЯ
        } else {
            // Для обычного пользователя показываем только ЕГО обращения
            $tickets = Ticket::where('user_id', $user->id)->with('status')->get();
            return view('tickets.index', compact('tickets'));
        }
    }
    
    /**
     * Показать форму создания нового обращения
     * Только для пользователей (не для админов)
     */
    public function create()
    {
        // ПРОВЕРКА ПРАВ ДОСТУПА
        // Администраторы не могут создавать обращения (бизнес-логика)
        if (Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Администратор не может создавать обращения');
        }
        
        // Возвращаем форму создания обращения
        return view('tickets.create');
    }
    
    /**
     * Сохранение нового обращения
     * Создает новое обращение в базе данных
     */
    public function store(Request $request)
    {
        // ПРОВЕРКА ПРАВ ДОСТУПА
        // Повторная проверка, что админ не создает обращение
        if (Auth::user()->isAdmin()) {
            return redirect()->route('home')->with('error', 'Администратор не может создавать обращения');
        }
        
        // ВАЛИДАЦИЯ ВХОДНЫХ ДАННЫХ
        $validator = Validator::make($request->all(), [
            'error_datetime' => 'required|date|before_or_equal:now',  // Дата ошибки не может быть в будущем
            'description' => 'required|string',                      // Описание обязательно
            'error_text' => 'required|string',                       // Текст ошибки обязателен
        ], [
            // Кастомное сообщение об ошибке
            'error_datetime.before_or_equal' => 'Дата и время возникновения ошибки не могут быть в будущем',
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // ПОЛУЧЕНИЕ СТАТУСА ПО УМОЛЧАНИЮ
        // Новые обращения получают статус "В обработке"
        $status = TicketStatus::where('name', 'В обработке')->first();
        
        // ГЕНЕРАЦИЯ УНИКАЛЬНОГО НОМЕРА ОБРАЩЕНИЯ
        // Генерируем случайный номер от 1 до 999 и проверяем уникальность
        do {
            $ticketNumber = rand(1, 999);  // Случайное число
        } while (Ticket::where('ticket_number', $ticketNumber)->exists());  // Повторяем, пока не найдем уникальный
        
        // СОЗДАНИЕ НОВОГО ОБРАЩЕНИЯ
        Ticket::create([
            'ticket_number' => $ticketNumber,                    // Уникальный номер
            'user_id' => Auth::id(),                            // ID текущего пользователя
            'description' => $request->description,              // Описание проблемы
            'error_text' => $request->error_text,               // Текст ошибки
            'error_datetime' => $request->error_datetime,        // Дата/время возникновения ошибки
            'processing_deadline' => now()->addDays(3),         // Срок обработки: +3 дня от текущего момента
            'status_id' => $status->id,                         // ID статуса "В обработке"
        ]);
        
        // Перенаправляем на список обращений с сообщением об успехе
        return redirect()->route('tickets.index')->with('success', 'Обращение успешно создано');
    }
    
    /**
     * Обновление срока обработки обращения
     * Только сотрудники поддержки могут изменять сроки
     */
    public function updateDeadline(Request $request, Ticket $ticket)
    {
        // ПРОВЕРКА ПРАВ ДОСТУПА
        // Только сотрудники поддержки могут изменять сроки обработки
        if (!Auth::user()->isSupport()) {
            return redirect()->back()->with('error', 'У вас нет прав для выполнения этого действия');
        }
        
        // ВАЛИДАЦИЯ ВХОДНЫХ ДАННЫХ
        $validator = Validator::make($request->all(), [
            'processing_deadline' => 'required|date',  // Дата обязательна и должна быть корректной
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // ПРОВЕРКА, ЧТО ДАТА НЕ В ПРОШЛОМ
        // Создаем объекты Carbon для сравнения дат
        $selectedDate = Carbon::createFromFormat('Y-m-d', $request->processing_deadline)->startOfDay();  // Выбранная дата (начало дня)
        $today = Carbon::now()->startOfDay();  // Сегодняшняя дата (начало дня)
        
        // Если выбранная дата меньше сегодняшней
        if ($selectedDate->lt($today)) {
            return redirect()->back()->with('error', 'ОШИБКА: Срок обработки не может быть установлен на прошедшую дату! Выберите сегодняшнюю дату или позже.');
        }
        
        // ОБНОВЛЕНИЕ СРОКА ОБРАБОТКИ
        $ticket->processing_deadline = $request->processing_deadline;  // Устанавливаем новый срок
        $ticket->save();  // Сохраняем изменения в базе данных
        
        // Возвращаемся с сообщением об успехе
        return redirect()->back()->with('success', 'Срок обработки обращения успешно обновлен');
    }
    
    /**
     * Обновление статуса обращения
     * Только сотрудники поддержки могут изменять статусы
     */
    public function updateStatus(Request $request, Ticket $ticket)
    {
        // ПРОВЕРКА ПРАВ ДОСТУПА
        // Только сотрудники поддержки могут изменять статусы обращений
        if (!Auth::user()->isSupport()) {
            return redirect()->back()->with('error', 'У вас нет прав для выполнения этого действия');
        }
        
        // ВАЛИДАЦИЯ ВХОДНЫХ ДАННЫХ
        $validator = Validator::make($request->all(), [
            'status_id' => 'required|exists:ticket_statuses,id',  // ID статуса должен существовать в таблице
        ]);
        
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        // ОБНОВЛЕНИЕ СТАТУСА
        $ticket->status_id = $request->status_id;  // Устанавливаем новый статус
        $ticket->save();  // Сохраняем изменения
        
        // Возвращаемся с сообщением об успехе
        return redirect()->back()->with('success', 'Статус обращения обновлен');
    }
}
