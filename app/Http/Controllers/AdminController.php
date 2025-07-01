<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Message;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Контроллер для управления административными функциями
 * Позволяет администратору управлять пользователями и их ролями
 */
class AdminController extends Controller
{
    /**
     * Главная страница админ-панели
     * Показывает список всех пользователей и позволяет искать их
     */
    public function index(Request $request)
    {
        // Получаем текущего авторизованного пользователя
        $user = Auth::user();
        
        // Записываем в лог информацию для отладки (помогает найти проблемы)
        Log::info('AdminController: Checking if user is admin', [
            'user_id' => $user->id,                    // ID пользователя
            'user_role' => $user->role->name ?? 'no role',  // Название роли или 'no role' если роли нет
            'is_admin_method' => $user->isAdmin(),     // Результат проверки метода isAdmin()
        ]);
        
        // Проверяем, является ли пользователь администратором
        // Проверяем роль напрямую через базу данных, чтобы избежать ошибок в методе isAdmin()
        if ($user->role && ($user->role->name === 'администратор' || $user->role->name === 'admin')) {
            
            // БЛОК ПОИСКА ПОЛЬЗОВАТЕЛЕЙ
            // Если в запросе есть параметр 'search' и он не пустой
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;  // Получаем поисковый запрос
                
                // Ищем пользователей по разным полям
                $users = User::where(function($query) use ($searchTerm) {
                    $query->where('email', 'like', '%' . $searchTerm . '%')           // Поиск по email
                          ->orWhere('first_name', 'like', '%' . $searchTerm . '%')     // Поиск по имени
                          ->orWhere('last_name', 'like', '%' . $searchTerm . '%')      // Поиск по фамилии
                          ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', '%' . $searchTerm . '%'); // Поиск по полному имени
                })
                ->with('role')                    // Загружаем связанную роль пользователя
                ->where('id', '!=', Auth::id())   // Исключаем текущего администратора из результатов
                ->get();                          // Выполняем запрос и получаем результаты
                
                // Если это AJAX-запрос (асинхронный запрос из JavaScript)
                if ($request->ajax()) {
                    return response()->json($users);  // Возвращаем результаты в формате JSON
                }
            } else {
                // Если поиска нет, показываем всех пользователей
                $users = User::with('role')              // Загружаем роли
                            ->where('id', '!=', Auth::id()) // Исключаем текущего админа
                            ->get();                         // Получаем всех пользователей
            }
            
            // Получаем все доступные роли для выпадающего списка
            $roles = Role::all();
            
            // Возвращаем представление (view) с данными
            return view('admin.index', compact('users', 'roles'));
        }
        
        // Если пользователь НЕ администратор - перенаправляем на главную с ошибкой
        return redirect()->route('home')->with('error', 'У вас нет прав для доступа к этой странице');
    }
    
    /**
     * Обновление роли пользователя
     * Позволяет администратору изменить роль любого пользователя
     */
    public function updateRole(Request $request, User $user)
    {
        // Получаем текущего пользователя (того, кто выполняет действие)
        $currentUser = Auth::user();
        
        // ПРОВЕРКА ПРАВ ДОСТУПА
        // Проверяем, что текущий пользователь - администратор
        if (!$currentUser->role || !($currentUser->role->name === 'администратор' || $currentUser->role->name === 'admin')) {
            return redirect()->route('home')->with('error', 'У вас нет прав для выполнения этого действия');
        }
        
        // ВАЛИДАЦИЯ ВХОДНЫХ ДАННЫХ
        // Проверяем, что role_id передан и существует в таблице roles
        $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);
        
        // Сохраняем старую и новую роль для логики обработки
        $oldRoleId = $user->role_id;  // Текущая роль пользователя
        $newRoleId = $request->role_id;  // Новая роль, которую хотим установить
        
        // НАЧИНАЕМ ТРАНЗАКЦИЮ (последовательность одной или нескольких операций, которые выполняются как единое целое)
        // Транзакция гарантирует, что либо все изменения применятся, либо ничего не изменится
        DB::beginTransaction();
        
        try {
            // ОБНОВЛЯЕМ РОЛЬ ПОЛЬЗОВАТЕЛЯ
            $user->role_id = $newRoleId;  // Устанавливаем новую роль
            $user->save();                // Сохраняем изменения в базе данных
            
            // Получаем объекты ролей для дальнейшей логики
            $userRole = Role::where('name', 'пользователь')->first();  // Роль обычного пользователя
            $supportRole = Role::where('name', 'сотрудник')->first();  // Роль сотрудника поддержки
            
            // ЛОГИКА ОБРАБОТКИ СМЕНЫ РОЛЕЙ
            
            // Если пользователь стал сотрудником из обычного пользователя
            if ($oldRoleId == $userRole->id && $newRoleId == $supportRole->id) {
                // Ничего не делаем с сообщениями, они должны остаться видимыми
                // для нового сотрудника в его чатах с другими пользователями
                // Это позволяет новому сотруднику видеть историю переписки
            }
            
            // Если сотрудник стал обычным пользователем
            if ($oldRoleId == $supportRole->id && $newRoleId == $userRole->id) {
                // Ничего не делаем с сообщениями, они должны остаться в истории
                // но новые сообщения будут идти к новому сотруднику
                // Старые сообщения сохраняются для истории
            }
            
            // ПОДТВЕРЖДАЕМ ТРАНЗАКЦИЮ
            // Все изменения успешно применяются к базе данных
            DB::commit();
            
            // Возвращаемся на предыдущую страницу с сообщением об успехе
            return redirect()->back()->with('success', 'Роль пользователя обновлена');
            
        } catch (\Exception $e) {
            // ОТКАТЫВАЕМ ТРАНЗАКЦИЮ при ошибке
            // Все изменения отменяются, база данных остается в исходном состоянии
            DB::rollBack();
            
            // Записываем ошибку в лог для отладки
            Log::error('Error updating user role', [
                'user_id' => $user->id,           // ID пользователя, чью роль меняли
                'old_role_id' => $oldRoleId,      // Старая роль
                'new_role_id' => $newRoleId,      // Новая роль
                'error' => $e->getMessage(),      // Текст ошибки
            ]);
            
            // Возвращаемся с сообщением об ошибке
            return redirect()->back()->with('error', 'Произошла ошибка при обновлении роли: ' . $e->getMessage());
        }
    }
}