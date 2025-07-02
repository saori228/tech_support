@extends('layouts.app')

@section('content')
<div class="chat-container">
    <div class="chat-title">ЧАТ</div>
    
    @if(auth()->user()->isSupport())
        <!-- Поиск клиентов для сотрудника поддержки -->
        <div class="search-container">
            <input type="text" 
                   class="search-input" 
                   id="userSearch" 
                   placeholder="Поиск клиентов по email или ФИО..."
                   autocomplete="off">
            <div class="search-results" id="searchResults"></div>
        </div>
        
        <div class="chat-navigation">
            <a href="{{ route('chat.index', ['user_id' => isset($prevUser) ? $prevUser->id : '']) }}" class="nav-link">
                &lt; Предыдущий
            </a>
            <div class="current-user">{{ isset($selectedUser) ? $selectedUser->first_name . ' ' . $selectedUser->last_name : 'Нет пользователей' }}</div>
            <a href="{{ route('chat.index', ['user_id' => isset($nextUser) ? $nextUser->id : '']) }}" class="nav-link">
                Следующий &gt;
            </a>
        </div>
    @elseif(auth()->user()->isAdmin())
        <!-- Поиск сотрудников для админа -->
        <div class="search-container">
            <input type="text" 
                   class="search-input" 
                   id="supportSearch" 
                   placeholder="Поиск сотрудников по email или ФИО..."
                   autocomplete="off">
            <div class="search-results" id="searchResults"></div>
        </div>
        
        <div class="chat-navigation">
            <a href="{{ route('chat.index', ['support_id' => isset($prevSupport) ? $prevSupport->id : '']) }}" class="nav-link">
                &lt; Предыдущий
            </a>
            <div class="current-user">{{ isset($selectedSupport) ? $selectedSupport->first_name . ' ' . $selectedSupport->last_name : 'Нет сотрудников' }}</div>
            <a href="{{ route('chat.index', ['support_id' => isset($nextSupport) ? $nextSupport->id : '']) }}" class="nav-link">
                Следующий &gt;
            </a>
        </div>
    @endif
    
    <div class="chat-messages">
        @if(isset($messages) && $messages->count() > 0)
            @foreach($messages as $message)
                <div class="message {{ $message->is_from_user ? 'message-user' : 'message-support' }}">
                    @if($message->is_from_user)
                        <div>{{ auth()->id() == $message->user_id ? 'Я' : ($message->user ? $message->user->first_name : 'Пользователь') }}</div>
                    @else
                        <div>{{ auth()->id() == $message->support_id ? 'Я' : 'Сотрудник' }}</div>
                    @endif
                    <div>{{ $message->content }}</div>
                    @if($message->attachment)
                        <div>
                            <a href="{{ asset('storage/' . $message->attachment) }}" target="_blank">Вложение</a>
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="no-messages">Нет сообщений</div>
        @endif
    </div>
    
    <form action="{{ route('chat.store') }}" method="POST" enctype="multipart/form-data" class="chat-input">
        @csrf
        @if(auth()->user()->isSupport() && isset($selectedUser))
            <input type="hidden" name="recipient_id" value="{{ $selectedUser->id }}">
        @elseif(auth()->user()->isAdmin() && isset($selectedSupport))
            <input type="hidden" name="recipient_id" value="{{ $selectedSupport->id }}">
        @endif
        <button type="button" onclick="document.getElementById('attachment').click();" class="attachment-btn">
            <img src="{{ asset('clip.png') }}" alt="Прикрепить" class="attachment-icon">
        </button>
        <input type="file" id="attachment" name="attachment" style="display: none;">
        <input type="text" name="content" placeholder="Сообщение" required class="message-input">
        <button type="submit" class="send-btn">
            <img src="{{ asset('send.png') }}" alt="Отправить" class="send-icon">
        </button>
    </form>
</div>

<style>
    .chat-navigation {
        padding: 10px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .nav-link {
        text-decoration: none;
        color: #000;
        font-weight: 900;
    }
    
    .current-user {
        font-weight: 900;
    }
    
    .no-messages {
        text-align: center;
        padding: 20px;
        font-weight: 900;
    }
    
    .attachment-btn, .send-btn {
        background: none;
        border: none;
        cursor: pointer;
    }
    
    .attachment-icon, .send-icon {
        width: 20px;
        height: 20px;
    }
    
    .message-input {
        flex: 1;
        padding: 10px;
        border: none;
        outline: none;
        font-weight: 900;
    }
    
    .unread-count {
        background-color: #ff4444;
        color: white;
        border-radius: 50%;
        padding: 2px 6px;
        font-size: 12px;
        font-weight: 900;
        margin-left: 5px;
        min-width: 18px;
        text-align: center;
        display: inline-block;
    }
    
    @media (max-width: 480px) {
        .chat-title {
            font-size: 18px;
        }
        
        .chat-messages {
            height: 350px;
        }
        
        .message {
            padding: 8px;
            font-size: 14px;
        }
        
        .message-user {
            border-radius: 15px;
        }
        
        .message-support {
            border-radius: 15px;
        }
        
        .chat-input {
            padding: 8px;
        }
        
        .message-input {
            font-size: 14px;
        }
        
        .attachment-icon, .send-icon {
            width: 18px;
            height: 18px;
        }
    }
</style>

<script>
    // загрузка дом (DOM - объектаная модель документа, представленная html-документом)
    document.addEventListener('DOMContentLoaded', function() {
        // поле поиска
        const searchInput = document.getElementById('userSearch') || document.getElementById('supportSearch');
        // контейнер для результат поиска
        const searchResults = document.getElementById('searchResults');
        //проверка существования поиска
        if (searchInput) {
            let searchTimeout; // переменная для хранения таймера
            
            // Показываем результаты при фокусе на поле поиска
            searchInput.addEventListener('focus', function() {
                //поиск без запроса
                if (this.value.trim() === '') {
                    performSearch('');
                }
            });
                //обработчик ввода текста
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                const query = this.value.trim();
                
                searchTimeout = setTimeout(() => {
                    performSearch(query);
                }, 300);
            });
            // функция выполнения поиска пользователей
            function performSearch(query) {
                //ajax запрос на поиск
                fetch(`{{ route('chat.index') }}?search=${encodeURIComponent(query)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest', // указываем, что это ajax
                        'Accept': 'application/json' //ожидание json в ответе
                    }
                })
                .then(response => response.json()) //преобразуем ответ в json
                .then(data => {
                    // очищаем предыдущие результаты
                    searchResults.innerHTML = '';
                    // если пользователи не найдены
                    if (data.length === 0) {
                        searchResults.innerHTML = '<div class="search-result-item">Пользователи не найдены</div>';
                    } else {
                        // для каждого найденного пользователя создаем элемент
                        data.forEach(user => {
                            const item = document.createElement('div');
                            item.className = 'search-result-item';
                            // блок с информацией о пользователе
                            const userInfo = document.createElement('div');
                            userInfo.innerHTML = `<strong>${user.first_name} ${user.last_name}</strong><br><small>${user.email}</small>`;
                            
                            item.appendChild(userInfo);
                            
                            // Добавляем индикатор новых сообщений
                            @if(auth()->user()->isSupport())
                            if (user.has_new_messages) {
                                const indicatorContainer = document.createElement('div');
                                indicatorContainer.style.display = 'flex';
                                indicatorContainer.style.alignItems = 'center';
                                indicatorContainer.style.gap = '5px';
                                //индикатор
                                const indicator = document.createElement('div');
                                indicator.className = 'new-message-indicator';
                                indicator.title = 'Есть новые сообщения';
                                //счётчик для непрочитанных сообщений
                                if (user.unread_count > 0) {
                                    const countBadge = document.createElement('span');
                                    countBadge.className = 'unread-count';
                                    countBadge.textContent = user.unread_count;
                                    indicatorContainer.appendChild(countBadge);
                                }
                                
                                indicatorContainer.appendChild(indicator);
                                item.appendChild(indicatorContainer);
                            }
                            @endif
                            //обработка клика
                            item.addEventListener('click', function() {
                                @if(auth()->user()->isSupport())
                                //для перехода в чат с пользователями
                                window.location.href = `{{ route('chat.index') }}?user_id=${user.id}`;
                                @elseif(auth()->user()->isAdmin())
                                window.location.href = `{{ route('chat.index') }}?support_id=${user.id}`;
                                @endif
                            });
                            // добавляем элемент в контейнер результатов
                            searchResults.appendChild(item);
                        });
                    }
                    //блок с результатами
                    searchResults.style.display = 'block';
                })
                .catch(error => {
                    console.error('Ошибка поиска:', error);
                });
            }
            
            // Скрываем результаты при клике вне поиска
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.style.display = 'none';
                }
            });
        }
        
        // Прокручиваем чат вниз при загрузке страницы
        const chatMessages = document.querySelector('.chat-messages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    });
</script>
@endsection
