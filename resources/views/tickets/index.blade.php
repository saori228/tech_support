@extends('layouts.app')

@section('content')
    <div class="ticket-list">
        <h2 class="title_history">История обращений</h2>
        
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-error">{{ session('error') }}</div>
        @endif
        
        @if(auth()->user()->isSupport())
            <!-- Поиск клиентов для сотрудника поддержки -->
            <div class="search-container">
                <input type="text" 
                       class="search-input" 
                       id="userSearch" 
                       placeholder="Поиск клиентов по email и ФИО..."
                       autocomplete="off">
                <div class="search-results" id="searchResults"></div>
            </div>
        @endif
        
        @if(auth()->user()->isSupport() && isset($selectedUser))
            <div class="user-navigation">
                <a href="{{ route('tickets.index', ['user_id' => isset($prevUser) ? $prevUser->id : '']) }}" class="nav-link">
                    &lt; Предыдущий
                </a>
                <div class="current-user">{{ isset($selectedUser) ? $selectedUser->first_name . ' ' . $selectedUser->last_name : 'Нет пользователей' }}</div>
                <a href="{{ route('tickets.index', ['user_id' => isset($nextUser) ? $nextUser->id : '']) }}" class="nav-link">
                    Следующий &gt;
                </a>
            </div>
        @endif
        
        <div class="tickets-container">
            @forelse(isset($tickets) ? $tickets : [] as $ticket)
                <div class="ticket-item">
                    <div class="ticket-left">
                        <div class="ticket-number">Номер обращения: {{ $ticket->ticket_number }}</div>
                        <div class="ticket-deadline">
                            @if($ticket->status->name === 'Завершено')
                                Время обработки обращения: завершено
                            @elseif($ticket->status->name === 'Приостановлено')
                                Время обработки обращения: приостановлено
                            @else
                                Время обработки обращения до: {{ $ticket->processing_deadline->format('d.m.Y') }} включительно
                            @endif
                        </div>
                        
                        @if(auth()->user()->isSupport())
                            <form action="{{ route('tickets.update.deadline', $ticket) }}" method="POST" class="deadline-form">
                                @csrf
                                @method('PUT')
                                <input type="date" 
                                       name="processing_deadline" 
                                       value="{{ $ticket->processing_deadline->format('Y-m-d') }}" 
                                       class="date-input"
                                       min="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                                <button type="submit" class="button">Обновить срок</button>
                            </form>
                            
                            <form action="{{ route('tickets.update.status', $ticket) }}" method="POST" class="status-form">
                                @csrf
                                @method('PUT')
                                <select name="status_id" class="status-select">
                                    @foreach(\App\Models\TicketStatus::all() as $status)
                                        <option value="{{ $status->id }}" {{ $ticket->status_id == $status->id ? 'selected' : '' }}>
                                            {{ $status->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="submit" class="button">Обновить статус</button>
                            </form>
                        @endif
                    </div>
                    <div class="ticket-right">
                        <div class="ticket-description">{{ $ticket->description }}</div>
                        <div class="ticket-error-text">
                            <strong>Текст ошибки:</strong> {{ $ticket->error_text }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="no-tickets">
                    @if(auth()->user()->isSupport() && isset($selectedUser))
                        У пользователя {{ $selectedUser->first_name }} {{ $selectedUser->last_name }} пока нет обращений
                    @else
                        У вас пока нет обращений
                    @endif
                </div>
            @endforelse
        </div>
    </div>
    
    <style>
        .title_history{
            font-size: 24px;
            font-weight: 900;
            color: #000;
            margin: 10px 0px 10px 270px;
        }

        .alert {
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .search-container {
            position: relative;
            margin-bottom: 20px;
        }

        .search-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 900;
            font-style: italic;
        }

        .search-results {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 1000;
            display: none;
        }

        .search-result-item {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
            font-weight: 900;
            font-style: italic;
        }

        .search-result-item:hover {
            background: #f5f5f5;
        }

        .search-result-item:last-child {
            border-bottom: none;
        }

        .user-navigation {
            padding: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .nav-link {
            text-decoration: none;
            color: #000;
            font-weight: 900;
            font-style: italic;
        }
        
        .current-user {
            font-weight: 900;
            font-style: italic;
        }
        
        .no-tickets {
            padding: 20px;
            text-align: center;
            font-weight: 900;
            font-style: italic;
        }
        
        .deadline-form, .status-form {
            margin-top: 10px;
        }
        
        .date-input, .status-select {
            padding: 5px;
            margin-right: 5px;
            font-weight: 900;
            font-style: italic;
            border: 1px solid #ddd;
            border-radius: 3px;
        }

        .date-input.invalid {
            border-color: #dc3545 !important;
            background-color: #f8d7da !important;
        }
        
        .ticket-error-text {
            margin-top: 10px;
            color: #d9534f;
            font-weight: 900;
            font-style: italic;
        }
        
        @media (max-width: 480px) {
            .ticket-list .title {
                font-size: 20px;
                margin-bottom: 15px;
            }
            
            .ticket-item {
                flex-direction: column;
            }
            
            .ticket-left, .ticket-right {
                width: 100%;
                padding: 10px;
            }
            
            .ticket-number {
                font-size: 16px;
            }
            
            .ticket-deadline {
                font-size: 14px;
            }
            
            .ticket-description {
                max-height: none;
                margin-bottom: 10px;
            }
            
            .deadline-form, .status-form {
                display: flex;
                flex-direction: column;
            }
            
            .date-input, .status-select {
                margin-bottom: 5px;
                width: 100%;
            }
            
            .button {
                width: 100%;
                margin-top: 5px;
            }
            .title_history{
                font-size: 24px;
                font-weight: 900;
                color: #000;
                margin: 10px 0px 10px 95px;
            }
        }
    </style>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Получаем сегодняшнюю дату
            const today = new Date();
            const todayString = today.getFullYear() + '-' + 
                String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                String(today.getDate()).padStart(2, '0');

            // Поиск пользователей (ваш существующий код)
            const searchInput = document.getElementById('userSearch');
            const searchResults = document.getElementById('searchResults');
            
            if (searchInput) {
                let searchTimeout;
                
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();
                    
                    if (query.length < 2) {
                        searchResults.style.display = 'none';
                        return;
                    }
                    
                    searchTimeout = setTimeout(() => {
                        fetch(`{{ route('tickets.index') }}?search=${encodeURIComponent(query)}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            searchResults.innerHTML = '';
                            
                            if (data.length === 0) {
                                searchResults.innerHTML = '<div class="search-result-item">Пользователи не найдены</div>';
                            } else {
                                data.forEach(user => {
                                    const item = document.createElement('div');
                                    item.className = 'search-result-item';
                                    
                                    const userInfo = document.createElement('div');
                                    userInfo.innerHTML = `<strong>${user.first_name} ${user.last_name}</strong><br><small>${user.email}</small>`;
                                    
                                    item.appendChild(userInfo);
                                    
                                    item.addEventListener('click', function() {
                                        window.location.href = `{{ route('tickets.index') }}?user_id=${user.id}`;
                                    });
                                    
                                    searchResults.appendChild(item);
                                });
                            }
                            
                            searchResults.style.display = 'block';
                        })
                        .catch(error => {
                            console.error('Ошибка поиска:', error);
                        });
                    }, 300);
                });
                
                // Скрываем результаты при клике вне поиска
                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                        searchResults.style.display = 'none';
                    }
                });
            }

            // ДОБАВЛЯЕМ ВАЛИДАЦИЮ ДАТ
            document.querySelectorAll('.date-input').forEach(input => {
                // Валидация при изменении даты
                input.addEventListener('change', function() {
                    const selectedDate = this.value;
                    
                    if (selectedDate < todayString) {
                        this.classList.add('invalid');
                        showAlert('❌ Срок обработки не может быть установлен на прошедшую дату!', 'error');
                        
                        // Возвращаем к исходному значению через 2 секунды
                        setTimeout(() => {
                            this.value = this.getAttribute('value'); // Возвращаем к исходному значению
                            this.classList.remove('invalid');
                        }, 2000);
                    } else {
                        this.classList.remove('invalid');
                    }
                });
            });

            // ДОБАВЛЯЕМ ВАЛИДАЦИЮ ФОРМ ПЕРЕД ОТПРАВКОЙ
            document.querySelectorAll('.deadline-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    const dateInput = this.querySelector('.date-input');
                    const selectedDate = dateInput.value;
                    
                    if (selectedDate < todayString) {
                        e.preventDefault(); // Останавливаем отправку формы
                        dateInput.classList.add('invalid');
                        showAlert('❌ ЗАПРЕЩЕНО! Нельзя установить срок обработки на прошедшую дату!', 'error');
                        
                        // Возвращаем к исходному значению
                        setTimeout(() => {
                            dateInput.value = dateInput.getAttribute('value');
                            dateInput.classList.remove('invalid');
                        }, 2000);
                        
                        return false;
                    }
                });
            });

            function showAlert(message, type) {
                // Удаляем существующие алерты
                document.querySelectorAll('.alert').forEach(alert => alert.remove());
                
                const alertDiv = document.createElement('div');
                alertDiv.className = `alert alert-${type}`;
                alertDiv.textContent = message;
                alertDiv.style.fontSize = '16px';
                alertDiv.style.fontWeight = '900';
                
                const container = document.querySelector('.ticket-list');
                const title = container.querySelector('.title_history');
                title.insertAdjacentElement('afterend', alertDiv);
                
                // Автоматически скрываем алерт через 5 секунд
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
            }
        });
    </script>
@endsection
