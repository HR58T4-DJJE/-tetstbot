(function(){
  const root = document.documentElement;
  const themeToggle = () => {
    const light = root.classList.toggle('light');
    localStorage.setItem('theme', light ? 'light' : 'dark');
    showToast(light ? 'Светлая тема' : 'Тёмная тема');
  };
  const themePref = localStorage.getItem('theme');
  if(themePref === 'light') root.classList.add('light');

  const commands = [
    {
      id: 'pkg-update',
      title: 'Обновление пакетов',
      description: 'Обновляет индекс пакетов и устанавливает последние версии.',
      command: 'pkg update && pkg upgrade',
      categories: ['База', 'Пакеты']
    },
    {
      id: 'pkg-install',
      title: 'Установка пакета',
      description: 'Установить один или несколько пакетов.',
      command: 'pkg install <имя>',
      categories: ['База', 'Пакеты']
    },
    {
      id: 'storage-setup',
      title: 'Доступ к памяти',
      description: 'Запрашивает доступ Termux к общей памяти устройства.',
      command: 'termux-setup-storage',
      categories: ['Android', 'База']
    },
    {
      id: 'git-clone',
      title: 'Клонирование репозитория',
      description: 'Скачивает код из Git-репозитория.',
      command: 'git clone https://github.com/пользователь/репозиторий.git',
      categories: ['Git', 'Разработка']
    },
    {
      id: 'ssh-connect',
      title: 'Подключение к серверу по SSH',
      description: 'Открывает SSH-сеанс к удалённой машине.',
      command: 'ssh user@host',
      categories: ['Сеть', 'SSH']
    },
    {
      id: 'openssh-start',
      title: 'Запуск локального SSH-сервера',
      description: 'Запускает sshd в Termux. Пароль задаётся: passwd.',
      command: 'pkg install openssh && sshd',
      categories: ['Сеть', 'SSH']
    },
    {
      id: 'python-install',
      title: 'Python и pip',
      description: 'Установка Python и менеджера пакетов pip.',
      command: 'pkg install python && pip install --upgrade pip',
      categories: ['Разработка', 'Python']
    },
    {
      id: 'node-install',
      title: 'Node.js и npm',
      description: 'Установка Node.js вместе с npm.',
      command: 'pkg install nodejs',
      categories: ['Разработка', 'Node.js']
    },
    {
      id: 'nano-edit',
      title: 'Редактирование файла (nano)',
      description: 'Простой консольный редактор для быстрого редактирования.',
      command: 'nano файл.txt',
      categories: ['Инструменты', 'Редакторы']
    },
    {
      id: 'vim-edit',
      title: 'Редактирование файла (vim)',
      description: 'Мощный модальный редактор. Выход: :wq или :q!.',
      command: 'vim файл.txt',
      categories: ['Инструменты', 'Редакторы']
    },
    {
      id: 'tmux-start',
      title: 'Мультиплексор tmux',
      description: 'Запуск tmux. Новое окно: Ctrl+b, c; выход: exit.',
      command: 'pkg install tmux && tmux',
      categories: ['Инструменты']
    },
    {
      id: 'curl-download',
      title: 'Загрузка файла (curl)',
      description: 'Скачать файл по URL в текущую директорию.',
      command: 'curl -LO https://example.com/file.tar.gz',
      categories: ['Сеть']
    },
    {
      id: 'wget-download',
      title: 'Загрузка файла (wget)',
      description: 'Альтернатива curl для загрузки файлов.',
      command: 'wget https://example.com/file.zip',
      categories: ['Сеть']
    },
    {
      id: 'proot-install',
      title: 'Установка proot-distro',
      description: 'Установка утилиты для контейнеров без root.',
      command: 'pkg install proot-distro',
      categories: ['Linux', 'proot']
    },
    {
      id: 'proot-ubuntu',
      title: 'Ubuntu в Termux',
      description: 'Установка и вход в окружение Ubuntu.',
      command: 'proot-distro install ubuntu && proot-distro login ubuntu',
      categories: ['Linux', 'proot']
    },
    {
      id: 'env-path',
      title: 'Проверка PATH',
      description: 'Показывает переменную окружения PATH.',
      command: 'echo $PATH',
      categories: ['База']
    },
    {
      id: 'find-grep',
      title: 'Поиск по файлам',
      description: 'Ищет строку во всех файлах рекурсивно.',
      command: 'grep -R "строка" .',
      categories: ['Инструменты']
    },
    {
      id: 'zip-unzip',
      title: 'Распаковка архива',
      description: 'Распаковать zip-архив в текущую папку.',
      command: 'unzip archive.zip',
      categories: ['Инструменты', 'Архивы']
    },
    {
      id: 'tar-extract',
      title: 'Распаковка tar.gz',
      description: 'Распаковать tar.gz архив.',
      command: 'tar -xzf file.tar.gz',
      categories: ['Инструменты', 'Архивы']
    },
    {
      id: 'pipx',
      title: 'Изолированная установка Python‑CLI (pipx)',
      description: 'Установка pipx для установки CLI‑утилит изолированно.',
      command: 'pip install --user pipx && pipx ensurepath',
      categories: ['Python', 'Разработка']
    }
  ];

  const state = {
    layout: 'grid',
    query: '',
    category: 'Все'
  };

  const el = (selector) => document.querySelector(selector);
  const cardsEl = el('#cards');
  const searchEl = el('#search');
  const clearSearchEl = el('#clearSearch');
  const layoutGridEl = el('#layoutGrid');
  const layoutListEl = el('#layoutList');
  const resultCountEl = el('#resultCount');
  const categoryFiltersEl = el('#categoryFilters');
  const themeToggleEl = el('#themeToggle');
  const toastEl = el('#toast');

  function uniq(arr){ return Array.from(new Set(arr)); }
  const categories = ['Все', ...uniq(commands.flatMap(c => c.categories))];

  function buildCategoryChips(){
    const frag = document.createDocumentFragment();
    categories.forEach(cat => {
      const btn = document.createElement('button');
      btn.className = 'chip';
      btn.type = 'button';
      btn.setAttribute('role','tab');
      btn.setAttribute('aria-pressed', cat === state.category ? 'true' : 'false');
      btn.textContent = cat;
      btn.addEventListener('click', () => {
        state.category = cat;
        [...categoryFiltersEl.children].forEach(n => n.setAttribute('aria-pressed','false'));
        btn.setAttribute('aria-pressed','true');
        render();
      });
      frag.appendChild(btn);
    });
    categoryFiltersEl.innerHTML = '';
    categoryFiltersEl.appendChild(frag);
  }

  function filterCommands(){
    const q = state.query.trim().toLowerCase();
    return commands.filter(c => {
      const inCategory = state.category === 'Все' || c.categories.includes(state.category);
      if(!inCategory) return false;
      if(!q) return true;
      const hay = [c.title, c.description, c.command, ...c.categories].join(' ').toLowerCase();
      return hay.includes(q);
    });
  }

  function createCard(cmd){
    const card = document.createElement('article');
    card.className = 'card';

    const title = document.createElement('h4');
    title.textContent = cmd.title;
    card.appendChild(title);

    const tags = document.createElement('div');
    tags.className = 'tags';
    cmd.categories.forEach(t => {
      const tag = document.createElement('span');
      tag.className = 'tag';
      tag.textContent = t;
      tags.appendChild(tag);
    });
    card.appendChild(tags);

    const desc = document.createElement('p');
    desc.textContent = cmd.description;
    card.appendChild(desc);

    const pre = document.createElement('pre');
    const code = document.createElement('code');
    code.textContent = cmd.command;
    pre.appendChild(code);
    card.appendChild(pre);

    const actions = document.createElement('div');
    actions.className = 'actions';

    const copyBtn = document.createElement('button');
    copyBtn.className = 'btn copy-btn';
    copyBtn.type = 'button';
    copyBtn.innerHTML = '<span class="icon-list" aria-hidden="true"></span><span>Копировать</span>';
    copyBtn.addEventListener('click', async () => {
      try{
        await navigator.clipboard.writeText(cmd.command);
        showToast('Скопировано');
      }catch(err){
        fallbackCopy(cmd.command);
      }
    });
    actions.appendChild(copyBtn);

    card.appendChild(actions);
    return card;
  }

  function fallbackCopy(text){
    const ta = document.createElement('textarea');
    ta.value = text; document.body.appendChild(ta); ta.select();
    try{ document.execCommand('copy'); showToast('Скопировано'); }
    catch{ showToast('Не удалось скопировать'); }
    finally{ document.body.removeChild(ta); }
  }

  let toastTimer = null;
  function showToast(text){
    toastEl.textContent = text;
    toastEl.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => { toastEl.hidden = true; }, 1500);
  }

  function render(){
    const list = filterCommands();
    resultCountEl.textContent = `Найдено: ${list.length}`;
    cardsEl.setAttribute('aria-busy','true');
    cardsEl.classList.toggle('list', state.layout === 'list');
    cardsEl.innerHTML = '';
    const frag = document.createDocumentFragment();
    list.forEach(c => frag.appendChild(createCard(c)));
    cardsEl.appendChild(frag);
    cardsEl.setAttribute('aria-busy','false');
  }

  searchEl.addEventListener('input', (e) => {
    state.query = e.target.value;
    render();
  });

  clearSearchEl.addEventListener('click', () => {
    searchEl.value = '';
    state.query = '';
    searchEl.focus();
    render();
  });

  layoutGridEl.addEventListener('click', () => {
    state.layout = 'grid';
    layoutGridEl.classList.add('is-active');
    layoutListEl.classList.remove('is-active');
    render();
  });
  layoutListEl.addEventListener('click', () => {
    state.layout = 'list';
    layoutListEl.classList.add('is-active');
    layoutGridEl.classList.remove('is-active');
    render();
  });

  document.addEventListener('keydown', (e) => {
    if((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k'){
      e.preventDefault();
      searchEl.focus();
    }
  });

  themeToggleEl.addEventListener('click', themeToggle);

  buildCategoryChips();
  render();
})();