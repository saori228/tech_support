<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Техническая поддержка</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@900&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', Arial, sans-serif;
            font-weight: 900;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            border-bottom: 1px solid #000;
        }
        
        .logo {
            width: 50px;
            height: 50px;
            cursor: pointer;
        }
        
        .title {
            font-size: 24px;
            font-weight: 900;
            color: #000;
        }
        
        .profile-link {
            color: #000;
            text-decoration: none;
            font-weight: 900;
        }
        
        .content {
            flex: 1;
            padding: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .footer {
            padding: 15px 20px;
            border-top: 1px solid #000;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .button {
            display: inline-block;
            background-color: #000;
            color: #fff;
            padding: 10px 20px;
            border-radius: 20px;
            text-decoration: none;
            margin: 10px 0;
            text-align: center;
            min-width: 200px;
            cursor: pointer;
            border: none;
            font-size: 16px;
            font-weight: 900;
        }
        
        .button:hover {
            opacity: 0.8;
        }
        
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
        
        .new-message-indicator {
            width: 12px;
            height: 12px;
            background-color: #ff4444;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .form-container {
            width: 100%;
            max-width: 500px;
            border: 1px solid #ccc;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .form-title {
            padding: 15px;
            text-align: center;
            font-weight: 900;
            border-bottom: 1px solid #ccc;
        }
        
        .form-content {
            padding: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-input {
            width: 100%;
            padding: 10px;
            border-radius: 20px;
            border: none;
            background-color: #000;
            color: #fff;
            font-weight: 900;
        }
        
        .form-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
            font-weight: 900;
        }
        
        .form-link {
            display: block;
            text-align: center;
            margin: 10px 0;
            color: #000;
            text-decoration: none;
            font-weight: 900;
        }
        
        .chat-container {
            width: 100%;
            max-width: 500px;
            border: 1px solid #ccc;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .chat-title {
            padding: 15px;
            text-align: center;
            font-weight: 900;
            border-bottom: 1px solid #ccc;
        }
        
        .chat-messages {
            padding: 15px;
            height: 400px;
            overflow-y: auto;
        }
        
        .message {
            margin-bottom: 15px;
            max-width: 80%;
            padding: 10px;
            border-radius: 10px;
            font-weight: 900;
        }
        
        .message-user {
            background-color: #3498db;
            color: #fff;
            align-self: flex-start;
            margin-right: auto;
        }
        
        .message-support {
            background-color: #f1c40f;
            color: #000;
            align-self: flex-end;
            margin-left: auto;
        }
        
        .chat-input {
            display: flex;
            padding: 10px;
            border-top: 1px solid #ccc;
        }
        
        .chat-input input {
            flex: 1;
            padding: 10px;
            border: none;
            outline: none;
            font-weight: 900;
        }
        
        .chat-input button {
            border-radius: 10px;
            background-color: #000;
            color: #fff;
            border: none;
            padding: 10px 15px;
            cursor: pointer;
            font-weight: 900;
        }
        
        .ticket-list {
            width: 100%;
            max-width: 800px;
            border: 1px solid #ccc;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .ticket-item {
            display: flex;
            border-bottom: 1px solid #ccc;
        }
        
        .ticket-left {
            width: 50%;
            padding: 15px;
        }
        
        .ticket-right {
            width: 50%;
            padding: 15px;
        }
        
        .ticket-number {
            font-weight: 900;
            margin-bottom: 10px;
        }
        
        .ticket-deadline {
            font-size: 14px;
            font-weight: 900;
        }
        
        .ticket-description {
            overflow-y: auto;
            max-height: 100px;
            font-weight: 900;
        }
        
        .admin-container {
            width: 100%;
            max-width: 800px;
            border: 1px solid #ccc;
            border-radius: 10px;
            overflow: hidden;
        }
        
        .admin-title {
            padding: 15px;
            text-align: center;
            font-weight: 900;
            border-bottom: 1px solid #ccc;
        }
        
        .admin-content {
            padding: 15px;
            max-height: 500px;
            overflow-y: auto;
        }
        
        .user-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        
        .user-name {
            font-weight: 900;
        }
        
        .user-role {
            display: flex;
            align-items: center;
        }
        
        .role-toggle {
            margin-left: 10px;
            cursor: pointer;
        }
        
        .profile-container {
            width: 100%;
            max-width: 800px;
            display: flex;
        }
        
        .profile-left {
            width: 60%;
            padding-right: 20px;
        }
        
        .profile-right {
            width: 40%;
            display: flex;
            flex-direction: column;
        }
        
        .profile-title {
            font-weight: 900;
            margin-bottom: 20px;
        }
        
        .profile-field {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        
        .error {
            color: red;
            margin-bottom: 10px;
            font-weight: 900;
        }
        
        .success {
            color: green;
            margin-bottom: 10px;
            font-weight: 900;
        }
        
        /* Чередование цветов в истории обращений */
        .ticket-item:nth-child(odd) .ticket-left {
            background-color: #fff;
            color: #000;
        }
        
        .ticket-item:nth-child(odd) .ticket-right {
            background-color: #000;
            color: #fff;
        }
        
        .ticket-item:nth-child(even) .ticket-left {
            background-color: #000;
            color: #fff;
        }
        
        .ticket-item:nth-child(even) .ticket-right {
            background-color: #fff;
            color: #000;
        }
        
        /* Мобильная адаптация */
        @media (max-width: 480px) {
            .header {
                padding: 10px;
                border-bottom: 2px solid #000;
            }
            
            .title {
                font-size: 18px;
            }
            
            .profile-link {
                display: none;
            }
            
            .profile-icon {
                display: block;
                width: 30px;
                height: 30px;
            }
            
            .content {
                padding: 15px 10px;
                border-bottom: 2px solid #000;
                border-top: 2px solid #000;
            }
            
            .footer {
                padding: 10px;
                border-top: 2px solid #000;
                justify-content: space-between;
            }
            
            .home-link {
                display: flex;
                align-items: center;
                text-decoration: none;
                color: #000;
                font-weight: 900;
            }
            
            .home-hint {
                display: flex;
                align-items: center;
                margin-left: 10px;
                margin-bottom: 15px;
                opacity: 0.5;
            }
            
            .home-hint-icon {
                width: 20px;
                height: 20px;
                margin-right: 5px;
            }
            
            .button {
                min-width: 150px;
                padding: 8px 15px;
                font-size: 14px;
            }
            
            /* Адаптация формы */
            .form-container {
                max-width: 100%;
            }
            
            /* Адаптация чата */
            .chat-container {
                max-width: 100%;
            }
            
            .chat-title {
                font-size: 18px;
            }
            
            .chat-messages {
                height: 350px;
            }
            
            .message {
                max-width: 85%;
                font-size: 14px;
            }
            
            /* Адаптация истории обращений */
            .ticket-list {
                max-width: 100%;
            }
            
            .ticket-item {
                flex-direction: column;
            }
            
            .ticket-left, .ticket-right {
                width: 100%;
            }
            
            /* Адаптация админ-панели */
            .admin-container {
                max-width: 100%;
            }
            
            .admin-table {
                font-size: 12px;
            }
            
            .admin-table th, .admin-table td {
                padding: 5px;
            }
            
            /* Адаптация профиля */
            .profile-container {
                flex-direction: column;
                max-width: 100%;
            }
            
            .profile-left, .profile-right {
                width: 100%;
                padding-right: 0;
            }
            
            .profile-right {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <a href="{{ route('home') }}">
            <img src="{{ asset('logo.png') }}" alt="Логотип" class="logo">
        </a>
        <div class="title">Техническая поддержка</div>
        @auth
            <a href="{{ route('profile') }}" class="profile-link">Личный кабинет</a>
            <a href="{{ route('profile') }}" class="profile-icon" style="display: none;">
                <img src="{{ asset('profile-icon.png') }}" alt="Личный кабинет" width="30" height="30">
            </a>
        @else
            <a href="{{ route('login') }}" class="profile-link">Личный кабинет</a>
            <a href="{{ route('login') }}" class="profile-icon" style="display: none;">
                <img src="{{ asset('profile-icon.png') }}" alt="Личный кабинет" width="30" height="30">
            </a>
        @endauth
    </div>
    
    <div class="content">
        @if(session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif
        
        @if($errors->any())
            <div class="error">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        
        @yield('content')
        
        <!-- Подсказка для возврата на главную (видна только на мобильных) -->
        <div class="home-hint" style="display: none;">
            
        </div>
    </div>
    
    <div class="footer">
        <a href="{{ route('home') }}">
            <img src="{{ asset('logo.png') }}" alt="Логотип" class="logo">
        </a>
        <a href="{{ route('home') }}" class="home-link" style="display: none;">
            <img src="{{ asset('home-icon.png') }}" alt="На главную" width="24" height="24">
            <span style="margin-left: 5px;">На главную</span>
        </a>
    </div>
    
    <script>
        // Проверка мобильного устройства и применение соответствующих стилей (DOM - объектаная модель документа, представленная html-документом)
        document.addEventListener('DOMContentLoaded', function() {
            const isMobile = window.innerWidth <= 480;
            //применяет мобильные стили, если включена мобилка
            if (isMobile) {
                //скрывает текстовую ссылку профиля
                document.querySelector('.profile-link').style.display = 'none';
                // показываем иконфу
                document.querySelector('.profile-icon').style.display = 'block';
                // подсказка + ссылка на лк
                document.querySelector('.home-hint').style.display = 'flex';
                document.querySelector('.home-link').style.display = 'flex';
            }
            
            // Обработка изменения размера окна
            window.addEventListener('resize', function() {
                const isMobileNow = window.innerWidth <= 480;
                //динамическое изменение окон
                document.querySelector('.profile-link').style.display = isMobileNow ? 'none' : 'block';
                document.querySelector('.profile-icon').style.display = isMobileNow ? 'block' : 'none';
                document.querySelector('.home-hint').style.display = isMobileNow ? 'flex' : 'none';
                document.querySelector('.home-link').style.display = isMobileNow ? 'flex' : 'none';
            });
        });
    </script>
</body>
</html>
