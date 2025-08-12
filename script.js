// Termux Commands Data
const termuxCommands = [
    {
        title: "Обновление системы",
        description: "Обновление списка пакетов и системы",
        icon: "fas fa-sync-alt",
        iconBg: "#10b981",
        syntax: "pkg update && pkg upgrade",
        examples: [
            "pkg update",
            "pkg upgrade",
            "apt update && apt upgrade"
        ]
    },
    {
        title: "Установка пакетов",
        description: "Установка новых программ и утилит",
        icon: "fas fa-download",
        iconBg: "#6366f1",
        syntax: "pkg install [имя_пакета]",
        examples: [
            "pkg install git",
            "pkg install python",
            "pkg install nodejs"
        ]
    },
    {
        title: "Удаление пакетов",
        description: "Удаление установленных программ",
        icon: "fas fa-trash",
        iconBg: "#ef4444",
        syntax: "pkg remove [имя_пакета]",
        examples: [
            "pkg remove git",
            "pkg remove python",
            "pkg remove nodejs"
        ]
    },
    {
        title: "Поиск пакетов",
        description: "Поиск доступных пакетов в репозитории",
        icon: "fas fa-search",
        iconBg: "#f59e0b",
        syntax: "pkg search [запрос]",
        examples: [
            "pkg search editor",
            "pkg search python",
            "pkg search web"
        ]
    },
    {
        title: "Информация о пакете",
        description: "Получение подробной информации о пакете",
        icon: "fas fa-info-circle",
        iconBg: "#8b5cf6",
        syntax: "pkg show [имя_пакета]",
        examples: [
            "pkg show git",
            "pkg show python",
            "pkg show vim"
        ]
    },
    {
        title: "Список установленных",
        description: "Просмотр всех установленных пакетов",
        icon: "fas fa-list",
        iconBg: "#06b6d4",
        syntax: "pkg list-installed",
        examples: [
            "pkg list-installed",
            "pkg list-installed | grep python",
            "dpkg -l"
        ]
    },
    {
        title: "Очистка кэша",
        description: "Очистка кэша пакетного менеджера",
        icon: "fas fa-broom",
        iconBg: "#84cc16",
        syntax: "pkg clean",
        examples: [
            "pkg clean",
            "apt clean",
            "apt autoclean"
        ]
    },
    {
        title: "Просмотр файлов",
        description: "Навигация и просмотр файловой системы",
        icon: "fas fa-folder",
        iconBg: "#f97316",
        syntax: "ls [опции] [путь]",
        examples: [
            "ls -la",
            "ls -lh",
            "ls /storage"
        ]
    },
    {
        title: "Смена директории",
        description: "Переход между папками",
        icon: "fas fa-arrow-right",
        iconBg: "#ec4899",
        syntax: "cd [путь]",
        examples: [
            "cd /storage",
            "cd ..",
            "cd ~"
        ]
    },
    {
        title: "Создание папок",
        description: "Создание новых директорий",
        icon: "fas fa-folder-plus",
        iconBg: "#14b8a6",
        syntax: "mkdir [имя_папки]",
        examples: [
            "mkdir projects",
            "mkdir -p projects/web",
            "mkdir \"My Folder\""
        ]
    },
    {
        title: "Копирование файлов",
        description: "Копирование файлов и папок",
        icon: "fas fa-copy",
        iconBg: "#a855f7",
        syntax: "cp [источник] [назначение]",
        examples: [
            "cp file.txt backup/",
            "cp -r folder/ backup/",
            "cp -v file.txt newfile.txt"
        ]
    },
    {
        title: "Перемещение файлов",
        description: "Перемещение и переименование файлов",
        icon: "fas fa-cut",
        iconBg: "#f43f5e",
        syntax: "mv [источник] [назначение]",
        examples: [
            "mv oldname.txt newname.txt",
            "mv file.txt folder/",
            "mv -i file.txt backup/"
        ]
    },
    {
        title: "Удаление файлов",
        description: "Удаление файлов и папок",
        icon: "fas fa-trash-alt",
        iconBg: "#dc2626",
        syntax: "rm [опции] [файл]",
        examples: [
            "rm file.txt",
            "rm -r folder/",
            "rm -f file.txt"
        ]
    },
    {
        title: "Просмотр содержимого",
        description: "Просмотр содержимого файлов",
        icon: "fas fa-file-alt",
        iconBg: "#0891b2",
        syntax: "cat [файл]",
        examples: [
            "cat file.txt",
            "cat -n file.txt",
            "cat file1.txt file2.txt"
        ]
    },
    {
        title: "Редактирование файлов",
        description: "Редактирование текстовых файлов",
        icon: "fas fa-edit",
        iconBg: "#059669",
        syntax: "nano [файл] или vim [файл]",
        examples: [
            "nano file.txt",
            "vim file.txt",
            "nano -w file.txt"
        ]
    },
    {
        title: "Права доступа",
        description: "Изменение прав доступа к файлам",
        icon: "fas fa-key",
        iconBg: "#7c3aed",
        syntax: "chmod [права] [файл]",
        examples: [
            "chmod +x script.sh",
            "chmod 755 file.txt",
            "chmod -R 644 folder/"
        ]
    },
    {
        title: "Владелец файла",
        description: "Изменение владельца файлов",
        icon: "fas fa-user",
        iconBg: "#ea580c",
        syntax: "chown [пользователь] [файл]",
        examples: [
            "chown user file.txt",
            "chown user:group file.txt",
            "chown -R user folder/"
        ]
    },
    {
        title: "Поиск файлов",
        description: "Поиск файлов по системе",
        icon: "fas fa-search",
        iconBg: "#16a34a",
        syntax: "find [путь] [критерии]",
        examples: [
            "find . -name \"*.txt\"",
            "find /storage -type f -mtime -7",
            "find . -size +100M"
        ]
    },
    {
        title: "Поиск текста",
        description: "Поиск текста в файлах",
        icon: "fas fa-search-plus",
        iconBg: "#9333ea",
        syntax: "grep [опции] [шаблон] [файл]",
        examples: [
            "grep \"text\" file.txt",
            "grep -r \"text\" folder/",
            "grep -i \"TEXT\" file.txt"
        ]
    },
    {
        title: "Сетевые соединения",
        description: "Просмотр сетевых соединений",
        icon: "fas fa-network-wired",
        iconBg: "#0ea5e9",
        syntax: "netstat [опции]",
        examples: [
            "netstat -tuln",
            "netstat -an",
            "netstat -i"
        ]
    },
    {
        title: "Процессы системы",
        description: "Управление процессами",
        icon: "fas fa-tasks",
        iconBg: "#fbbf24",
        syntax: "ps [опции]",
        examples: [
            "ps aux",
            "ps -ef",
            "ps -p [PID]"
        ]
    }
];

// DOM Elements
const commandsGrid = document.getElementById('commandsGrid');
const commandSearch = document.getElementById('commandSearch');
const navToggle = document.querySelector('.nav-toggle');
const navMenu = document.querySelector('.nav-menu');

// Initialize the website
document.addEventListener('DOMContentLoaded', function() {
    initializeCommands();
    initializeSearch();
    initializeMobileNav();
    initializeSmoothScrolling();
    initializeAnimations();
});

// Initialize Commands Grid
function initializeCommands() {
    if (commandsGrid) {
        commandsGrid.innerHTML = '';
        termuxCommands.forEach(command => {
            const commandCard = createCommandCard(command);
            commandsGrid.appendChild(commandCard);
        });
    }
}

// Create Command Card
function createCommandCard(command) {
    const card = document.createElement('div');
    card.className = 'command-card fade-in-up';
    
    card.innerHTML = `
        <div class="command-header">
            <div class="command-icon" style="background-color: ${command.iconBg}">
                <i class="${command.icon}"></i>
            </div>
            <div class="command-title">${command.title}</div>
        </div>
        <div class="command-description">${command.description}</div>
        <div class="command-syntax">${command.syntax}</div>
        <div class="command-examples">
            ${command.examples.map(example => `
                <div class="command-example">${example}</div>
            `).join('')}
        </div>
    `;
    
    return card;
}

// Initialize Search Functionality
function initializeSearch() {
    if (commandSearch) {
        commandSearch.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            filterCommands(searchTerm);
        });
    }
}

// Filter Commands
function filterCommands(searchTerm) {
    const filteredCommands = termuxCommands.filter(command => 
        command.title.toLowerCase().includes(searchTerm) ||
        command.description.toLowerCase().includes(searchTerm) ||
        command.syntax.toLowerCase().includes(searchTerm) ||
        command.examples.some(example => example.toLowerCase().includes(searchTerm))
    );
    
    displayFilteredCommands(filteredCommands);
}

// Display Filtered Commands
function displayFilteredCommands(commands) {
    if (commandsGrid) {
        commandsGrid.innerHTML = '';
        commands.forEach(command => {
            const commandCard = createCommandCard(command);
            commandsGrid.appendChild(commandCard);
        });
        
        if (commands.length === 0) {
            commandsGrid.innerHTML = `
                <div class="no-results" style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                    <i class="fas fa-search" style="font-size: 3rem; color: var(--text-light); margin-bottom: 1rem;"></i>
                    <h3>Команды не найдены</h3>
                    <p>Попробуйте изменить поисковый запрос</p>
                </div>
            `;
        }
    }
}

// Initialize Mobile Navigation
function initializeMobileNav() {
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
            navToggle.classList.toggle('active');
        });
        
        // Close mobile menu when clicking on a link
        const navLinks = navMenu.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', function() {
                navMenu.classList.remove('active');
                navToggle.classList.remove('active');
            });
        });
    }
}

// Initialize Smooth Scrolling
function initializeSmoothScrolling() {
    const links = document.querySelectorAll('a[href^="#"]');
    
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const headerHeight = document.querySelector('.header').offsetHeight;
                const targetPosition = targetSection.offsetTop - headerHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Initialize Animations
function initializeAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate');
            }
        });
    }, observerOptions);
    
    // Observe all animated elements
    const animatedElements = document.querySelectorAll('.command-card, .package-card, .tutorial-card, .stat');
    animatedElements.forEach(el => observer.observe(el));
}

// Header scroll effect
window.addEventListener('scroll', function() {
    const header = document.querySelector('.header');
    if (window.scrollY > 100) {
        header.style.background = 'rgba(255, 255, 255, 0.98)';
        header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
    } else {
        header.style.background = 'rgba(255, 255, 255, 0.95)';
        header.style.boxShadow = 'none';
    }
});

// Terminal typing effect
function typeWriter(element, text, speed = 100) {
    let i = 0;
    element.innerHTML = '';
    
    function type() {
        if (i < text.length) {
            element.innerHTML += text.charAt(i);
            i++;
            setTimeout(type, speed);
        }
    }
    
    type();
}

// Initialize terminal typing effect when page loads
window.addEventListener('load', function() {
    const terminalCommands = document.querySelectorAll('.terminal-line .command');
    if (terminalCommands.length > 0) {
        setTimeout(() => {
            terminalCommands.forEach((cmd, index) => {
                setTimeout(() => {
                    const text = cmd.textContent;
                    typeWriter(cmd, text, 50);
                }, index * 1000);
            });
        }, 1000);
    }
});

// Copy command to clipboard functionality
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('command-syntax') || e.target.classList.contains('command-example')) {
        const text = e.target.textContent;
        navigator.clipboard.writeText(text).then(function() {
            // Show success message
            const originalText = e.target.textContent;
            e.target.textContent = 'Скопировано!';
            e.target.style.background = '#10b981';
            e.target.style.color = 'white';
            
            setTimeout(() => {
                e.target.textContent = originalText;
                e.target.style.background = '';
                e.target.style.color = '';
            }, 1500);
        }).catch(function(err) {
            console.error('Ошибка копирования: ', err);
        });
    }
});

// Add loading animation for images
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        img.addEventListener('load', function() {
            this.classList.add('loaded');
        });
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K for search focus
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        if (commandSearch) {
            commandSearch.focus();
        }
    }
    
    // Escape to clear search
    if (e.key === 'Escape') {
        if (commandSearch) {
            commandSearch.value = '';
            filterCommands('');
        }
    }
});

// Performance optimization: Debounce search
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Apply debouncing to search
if (commandSearch) {
    const debouncedSearch = debounce(function(searchTerm) {
        filterCommands(searchTerm);
    }, 300);
    
    commandSearch.addEventListener('input', function(e) {
        debouncedSearch(e.target.value.toLowerCase());
    });
}

// Add CSS for mobile menu
const style = document.createElement('style');
style.textContent = `
    @media (max-width: 768px) {
        .nav-menu {
            position: fixed;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            flex-direction: column;
            padding: 2rem;
            box-shadow: var(--shadow-lg);
            transform: translateY(-100%);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        
        .nav-menu.active {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
        
        .nav-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }
        
        .nav-toggle.active span:nth-child(2) {
            opacity: 0;
        }
        
        .nav-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }
    }
    
    .command-card.animate {
        animation: fadeInUp 0.6s ease-out;
    }
    
    .package-card.animate {
        animation: slideInLeft 0.6s ease-out;
    }
    
    .tutorial-card.animate {
        animation: slideInRight 0.6s ease-out;
    }
    
    .stat.animate {
        animation: fadeInUp 0.6s ease-out;
    }
    
    .no-results {
        animation: fadeInUp 0.6s ease-out;
    }
    
    .command-syntax, .command-example {
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .command-syntax:hover, .command-example:hover {
        transform: scale(1.02);
        box-shadow: var(--shadow-md);
    }
`;
document.head.appendChild(style);