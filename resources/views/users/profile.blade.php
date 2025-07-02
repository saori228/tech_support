@extends('layouts.app')

@section('content')
    <h2 class="title">Личный кабинет</h2>
    
    <div class="profile-container">
        <div class="profile-left">
            <div class="profile-title">Личные данные:</div>
            
            <form action="{{ route('profile.update') }}" method="POST" class="profile-form">
                @csrf
                @method('PUT')
                
                <div class="profile-field">
                    <div class="field-label">{{ $user->first_name }}:</div>
                    <div class="field-input">
                        <input type="text" name="first_name" value="{{ $user->first_name }}" style="display: none;" id="first_name_input" class="edit-input">
                        <button type="button" onclick="toggleEdit('first_name')" id="first_name_btn" class="edit-btn">Изменить</button>
                    </div>
                </div>
                
                <div class="profile-field">
                    <div class="field-label">{{ $user->last_name }}:</div>
                    <div class="field-input">
                        <input type="text" name="last_name" value="{{ $user->last_name }}" style="display: none;" id="last_name_input" class="edit-input">
                        <button type="button" onclick="toggleEdit('last_name')" id="last_name_btn" class="edit-btn">Изменить</button>
                    </div>
                </div>
                
                <div class="profile-field">
                    <div class="field-label">********:</div>
                    <div class="field-input">
                        <input type="password" name="password" style="display: none;" id="password_input" class="edit-input">
                        <button type="button" onclick="toggleEdit('password')" id="password_btn" class="edit-btn">Изменить</button>
                    </div>
                </div>
                
                <div class="profile-field">
                    <div class="field-label">{{ $user->email }}:</div>
                    <div class="field-input">
                        <input type="email" name="email" value="{{ $user->email }}" style="display: none;" id="email_input" class="edit-input">
                        <button type="button" onclick="toggleEdit('email')" id="email_btn" class="edit-btn">Изменить</button>
                    </div>
                </div>
                
                <button type="submit" class="button" id="submit_btn" style="display: none;">Подтвердить изменения</button>
            </form>
        </div>
        
        <div class="profile-right">
            @if($user->isAdmin())
                <a href="{{ route('admin.index') }}" class="button">Админ-панель</a>
                
                <a href="{{ route('home') }}" class="button">На главную</a>
            @elseif($user->isSupport())
                <a href="{{ route('tickets.index') }}" class="button">История обращений</a>
                <a href="{{ route('chat.index') }}" class="button">Чат с пользователями</a>
                <a href="{{ route('home') }}" class="button">На главную</a>
            @else
                <a href="{{ route('tickets.index') }}" class="button">История обращений</a>
                <a href="{{ route('tickets.create') }}" class="button">Создать обращение</a>
                <a href="{{ route('chat.index') }}" class="button">Чат</a>
                <a href="{{ route('home') }}" class="button">На главную</a>
            @endif
            
            <form action="{{ route('logout') }}" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="button">Выйти</button>
            </form>
        </div>
    </div>
    
    <style>
        .profile-form {
            width: 100%;
        }
        
        .field-label {
            font-weight: bold;
        }
        
        .field-input {
            display: flex;
            align-items: center;
        }
        
        .edit-input {
            padding: 5px;
            margin-right: 5px;
        }
        
        .edit-btn {
            padding: 5px 10px;
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .logout-form {
            margin-top: 10px;
        }
        
        @media (max-width: 480px) {
            .profile-container {
                flex-direction: column;
            }
            
            .profile-left, .profile-right {
                width: 100%;
            }
            
            .profile-right {
                margin-top: 20px;
            }
            
            .profile-field {
                flex-direction: column;
                align-items: flex-start;
                margin-bottom: 20px;
            }
            
            .field-label {
                margin-bottom: 5px;
            }
            
            .field-input {
                width: 100%;
            }
            
            .edit-input {
                width: 100%;
                margin-bottom: 5px;
            }
            
            .edit-btn {
                width: 100%;
            }
            
            .button {
                width: 100%;
            }
        }
    </style>
    
    <script>
        // получения элементов дом, функция переключения режима редактирования для профиля (DOM - объектаная модель документа, представленная html-документом)
        function toggleEdit(field) {
            const input = document.getElementById(field + '_input'); //поле ввода
            const btn = document.getElementById(field + '_btn'); //кнопка изменить/отмена
            const submitBtn = document.getElementById('submit_btn'); //кнопка отправки
            
            if (input.style.display === 'none') {
                //редактирование
                input.style.display = 'inline-block'; //поле ввода
                btn.textContent = 'Отмена'; //меняем текст кнопки
                submitBtn.style.display = 'block'; //кнопка отправки
            } else {
                //если не захотим что-то менять
                input.style.display = 'none'; // скрываем поля ввода
                btn.textContent = 'Изменить'; //возвращает исходный текст кнопки
                
                // Проверяем, есть ли еще видимые поля ввода
                const inputs = document.querySelectorAll('input[id$="_input"]');
                let allHidden = true;
                //проверка каждого поле ввода на странице
                for (const inp of inputs) {
                    if (inp.style.display !== 'none') {
                        allHidden = false;
                        break; // прерываем цикл, если нашли видимое поле
                    } 
                }
                // если поля скрыты, то скрываем кнопку отправить
                if (allHidden) {
                    submitBtn.style.display = 'none';
                }
            }
        }
    </script>
@endsection
