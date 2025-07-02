@extends('layouts.app')

@section('content')

<div class="admin-container">
    <h2 class="title">Админ-панель</h2>
    
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif
    
    <div class="admin-users">
        <h3 class="admin-subtitle">Управление пользователями</h3>
        
        <!-- Поиск пользователей -->
        <div class="search-container">
            <input type="text" 
                   class="search-input" 
                   id="userSearch" 
                   placeholder="Поиск пользователей по email или ФИО..."
                   autocomplete="off">
            <div class="search-results" id="searchResults"></div>
        </div>
        
        <div class="admin-users-list" id="usersList">
            @foreach($users as $user)
                <div class="admin-user-item">
                    <div class="user-info">
                        <div class="user-name">{{ $user->first_name }} {{ $user->last_name }}</div>
                        <div class="user-email">{{ $user->email }}</div>
                        <div class="user-role">Роль: {{ $user->role->name }}</div>
                    </div>
                    <div class="user-actions">
                        <form action="{{ route('admin.users.role.update', $user) }}" method="POST" class="role-form">
                            @csrf
                            @method('PUT')
                            <div class="role-form-group">
                                <select name="role_id" class="role-select">
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="button role-button">Изменить роль</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="admin-table-desktop">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Имя</th>
                        <th>Фамилия</th>
                        <th>Email</th>
                        <th>Роль</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    @foreach($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->first_name }}</td>
                            <td>{{ $user->last_name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->role->name }}</td>
                            <td>
                                <form action="{{ route('admin.users.role.update', $user) }}" method="POST" class="table-role-form">
                                    @csrf
                                    @method('PUT')
                                    <div class="table-form-group">
                                        <select name="role_id" class="table-role-select">
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>
                                                    {{ $role->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <button type="submit" class="button table-role-button">Изменить</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="admin-links">
        <a href="{{ route('profile') }}" class="button">Личный кабинет</a>
        <a href="{{ route('home') }}" class="button">На главную</a>
    </div>
</div>

<style>
    .search-container {
        width: 100%;
        max-width: 500px;
        margin-bottom: 20px;
        position: relative;
    }
    
    .search-input {
        width: 100%;
        padding: 10px 15px;
        border: 2px solid #000;
        border-radius: 25px;
        font-size: 16px;
        font-weight: 900;
        outline: none;
    }
    
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 2px solid #000;
        border-top: none;
        border-radius: 0 0 15px 15px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 1000;
        display: none;
    }
    
    .search-result-item {
        padding: 10px 15px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .search-result-item:hover {
        background-color: #f5f5f5;
    }
    
    .search-result-item:last-child {
        border-bottom: none;
    }
    
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }
    
    .admin-table th, .admin-table td {
        border: 1px solid #ddd;
        padding: 12px 8px;
        text-align: left;
        font-weight: 900;
        vertical-align: middle;
    }
    
    .admin-table th {
        background-color: #f2f2f2;
    }
    
    .admin-table tr:nth-child(even) {
        background-color: #f9f9f9;
    }
    
    .admin-table tr:hover {
        background-color: #f1f1f1;
    }
    
    .alert {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid transparent;
        border-radius: 4px;
        font-weight: 900;
    }
    
    .alert-success {
        color: #3c763d;
        background-color: #dff0d8;
        border-color: #d6e9c6;
    }
    
    .alert-danger {
        color: #a94442;
        background-color: #f2dede;
        border-color: #ebccd1;
    }
    
    .admin-subtitle {
        margin: 20px 0 15px;
        font-weight: 900;
    }
    
    .admin-users-list {
        display: none;
    }
    
    .admin-user-item {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        background-color: #fff;
    }
    
    .user-info {
        margin-bottom: 15px;
    }
    
    .user-name {
        font-weight: 900;
        font-size: 16px;
        margin-bottom: 5px;
    }
    
    .user-email, .user-role {
        font-size: 14px;
        margin-bottom: 3px;
        font-weight: 900;
        color: #666;
    }
    
    .role-form {
        width: 100%;
    }
    
    .role-form-group {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .role-select {
        padding: 8px 12px;
        font-weight: 900;
        border: 2px solid #000;
        border-radius: 5px;
        background-color: #fff;
    }
    
    .role-button {
        padding: 8px 16px;
        font-size: 14px;
        min-width: auto;
        margin: 0;
    }
    
    .table-role-form {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .table-form-group {
        display: flex;
        align-items: center;
        gap: 5px;
        flex-wrap: wrap;
    }
    
    .table-role-select {
        padding: 4px 8px;
        font-weight: 900;
        border: 1px solid #000;
        border-radius: 3px;
        background-color: #fff;
        min-width: 120px;
    }
    
    .table-role-button {
        padding: 4px 8px;
        font-size: 12px;
        min-width: auto;
        margin: 0;
        white-space: nowrap;
    }
    
    .admin-links {
        margin-top: 30px;
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .admin-links .button {
        flex: 1;
        min-width: 150px;
    }
    
    @media (max-width: 480px) {
        .admin-container .title {
            font-size: 20px;
            margin-bottom: 15px;
        }
        
        .admin-subtitle {
            font-size: 18px;
        }
        
        .admin-table-desktop {
            display: none;
        }
        
        .admin-users-list {
            display: block;
        }
        
        .admin-links {
            flex-direction: column;
        }
        
        .admin-links .button {
            width: 100%;
            flex: none;
        }
        
        .search-container {
            margin-bottom: 15px;
        }
        
        .search-input {
            padding: 8px 12px;
            font-size: 14px;
        }
        
        .search-results {
            max-height: 180px;
        }
        
        .search-result-item {
            padding: 8px 12px;
            font-size: 14px;
        }
        
        .admin-user-item {
            padding: 12px;
        }
        
        .user-name {
            font-size: 14px;
        }
        
        .user-email, .user-role {
            font-size: 12px;
        }
        
        .role-button {
            font-size: 12px;
            padding: 6px 12px;
        }
    }
</style>

<script>
    //загрузка скрипта DOM (DOM - объектаная модель документа, представленная html-документом)
    document.addEventListener('DOMContentLoaded', function() {
        // элементы dom
        const searchInput = document.getElementById('userSearch');
        const searchResults = document.getElementById('searchResults');
        const usersList = document.getElementById('usersList');
        const usersTableBody = document.getElementById('usersTableBody');
        // сохранение оригинального содержимое списков
        let originalUsersListHTML = usersList.innerHTML;
        let originalTableHTML = usersTableBody.innerHTML;
        
        // Получаем данные о ролях из PHP
        const roles = @json($roles);
        const updateRoleBaseUrl = "{{ url('admin/users') }}";
        //проверка поиска
        if (searchInput) {
            let searchTimeout;
            
            // Показываем результаты при фокусе на поле поиска
            searchInput.addEventListener('focus', function() {
                if (this.value.trim() === '') {
                    performSearch('');
                }
            });
            // обработчик ввода текста в поле поиск
            searchInput.addEventListener('input', function() {
                //очищаем прошлый запрос, чтобы много запросов не было
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                //новое время поиска
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            });
            //функция для выполнения поиска пользователей
            function performSearch(query) {
                if (query === '') {
                    // Показываем первых 10 пользователей
                    fetch(`{{ route('admin.index') }}?search=`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        showSearchResults(data.slice(0, 10));
                    })
                    .catch(error => {
                        console.error('Ошибка поиска:', error);
                    });
                } else {
                    //поиск с переданным запросом
                    fetch(`{{ route('admin.index') }}?search=${encodeURIComponent(query)}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.json();
                    })
                    .then(data => {
                        //обновление элементов с результатами поиска
                        showSearchResults(data);
                        updateUsersList(data);
                        updateUsersTable(data);
                    })
                    .catch(error => {
                        console.error('Ошибка поиска:', error);
                    });
                }
            }
            // функция для отображения поиска в выпадающем списке
            function showSearchResults(users) {
                searchResults.innerHTML = '';
                
                if (users.length === 0) {
                    searchResults.innerHTML = '<div class="search-result-item">Пользователи не найдены</div>';
                } else {
                     //создание элеметов для каждого найденного пользователя
                    users.forEach(user => {
                        const item = document.createElement('div');
                        item.className = 'search-result-item';
                        
                        const userInfo = document.createElement('div');
                        userInfo.innerHTML = `<strong>${user.first_name} ${user.last_name}</strong><br><small>${user.email}</small>`;
                        
                        item.appendChild(userInfo);
                        //обработка клика по результату поиска
                        item.addEventListener('click', function() {
                            // заполняем поле поиска именем пользователя
                            searchInput.value = `${user.first_name} ${user.last_name}`;
                            searchResults.style.display = 'none';
                            //обновление только для выбранного пользователя
                            updateUsersList([user]);
                            updateUsersTable([user]);
                        });
                        
                        searchResults.appendChild(item);
                    });
                }
                // блок результат
                searchResults.style.display = 'block';
            }
            //функция для выбора ролей
            function createRoleOptions(selectedRoleId) {
                return roles.map(role => 
                    `<option value="${role.id}" ${selectedRoleId == role.id ? 'selected' : ''}>${role.name}</option>`
                ).join('');
            }
            
            function updateUsersList(users) {
                //если пользователей нет, то показывает сообщения
                if (users.length === 0) {
                    usersList.innerHTML = '<div class="admin-user-item">Пользователи не найдены</div>';
                    return;
                }
                
                usersList.innerHTML = '';//очистка текущего списка
                // для каждого пользователя создаём карточку
                users.forEach(user => {
                    //создание контейнера для карточки
                    const userItem = document.createElement('div');
                    userItem.className = 'admin-user-item'; //базовый класс для стилей
                    // заполняем карточку содержимым
                    // основнная инфа о пользователе
                    // форма для изменения роли
                    // выпадающий список ролей
                    userItem.innerHTML = `
                        <div class="user-info">
                            <div class="user-name">${user.first_name} ${user.last_name}</div>
                            <div class="user-email">${user.email}</div>
                            <div class="user-role">Роль: ${user.role.name}</div>
                        </div>
                        <div class="user-actions">
                            <form action="${updateRoleBaseUrl}/${user.id}/role" method="POST" class="role-form">
                                @csrf
                                @method('PUT')
                                <div class="role-form-group">
                                    <select name="role_id" class="role-select">
                                        ${createRoleOptions(user.role_id)}
                                    </select>
                                    <button type="submit" class="button role-button">Изменить роль</button>
                                </div>
                            </form>
                        </div>
                    `;
                    // добавляем карточку в список
                    usersList.appendChild(userItem);
                });
            }
            
            function updateUsersTable(users) {
                if (users.length === 0) {
                    usersTableBody.innerHTML = '<tr><td colspan="6">Пользователи не найдены</td></tr>';
                    return;
                }
                //очищаем таблицу
                usersTableBody.innerHTML = '';
                // создаем строки для каждого пользователя
                users.forEach(user => {
                    const row = document.createElement('tr'); //создание строки в таблице
                    //заполняем ячейки данными
                    //основные данные пользователей
                    row.innerHTML = `
                        <td>${user.id}</td>
                        <td>${user.first_name}</td>
                        <td>${user.last_name}</td>
                        <td>${user.email}</td>
                        <td>${user.role.name}</td>
                        <td>
                            <form action="${updateRoleBaseUrl}/${user.id}/role" method="POST" class="table-role-form">
                                @csrf
                                @method('PUT')
                                <div class="table-form-group">
                                    <select name="role_id" class="table-role-select">
                                        ${createRoleOptions(user.role_id)}
                                    </select>
                                    <button type="submit" class="button table-role-button">Изменить</button>
                                </div>
                            </form>
                        </td>
                    `;
                    //добавляем строку в таблицу
                    usersTableBody.appendChild(row);
                });
            }
            
            // Скрываем результаты при клике вне поиска
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
            
            // Сброс поиска при очистке поля
            searchInput.addEventListener('blur', function() {
                setTimeout(() => {
                    if (this.value.trim() === '') {
                        usersList.innerHTML = originalUsersListHTML;
                        usersTableBody.innerHTML = originalTableHTML;
                    }
                }, 200);
            });
        }
    });
</script>

@endsection
