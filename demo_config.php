<?php
// Демонстрационная конфигурация для сайта
// Этот файл используется вместо config.php для демонстрации

// Имитация подключения к базе данных
$demo_mode = true;

// Демонстрационные данные
$demo_tables = [
    'users' => [
        'count' => 1250,
        'fields' => [
            ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => '', 'Extra' => 'auto_increment'],
            ['Field' => 'username', 'Type' => 'varchar(50)', 'Null' => 'NO', 'Key' => 'UNI', 'Default' => '', 'Extra' => ''],
            ['Field' => 'email', 'Type' => 'varchar(100)', 'Null' => 'NO', 'Key' => 'UNI', 'Default' => '', 'Extra' => ''],
            ['Field' => 'created_at', 'Type' => 'timestamp', 'Null' => 'NO', 'Key' => '', 'Default' => 'CURRENT_TIMESTAMP', 'Extra' => '']
        ]
    ],
    'products' => [
        'count' => 342,
        'fields' => [
            ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => '', 'Extra' => 'auto_increment'],
            ['Field' => 'name', 'Type' => 'varchar(200)', 'Null' => 'NO', 'Key' => '', 'Default' => '', 'Extra' => ''],
            ['Field' => 'price', 'Type' => 'decimal(10,2)', 'Null' => 'NO', 'Key' => '', 'Default' => '0.00', 'Extra' => ''],
            ['Field' => 'category_id', 'Type' => 'int(11)', 'Null' => 'YES', 'Key' => 'MUL', 'Default' => 'NULL', 'Extra' => '']
        ]
    ],
    'orders' => [
        'count' => 89,
        'fields' => [
            ['Field' => 'id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'PRI', 'Default' => '', 'Extra' => 'auto_increment'],
            ['Field' => 'user_id', 'Type' => 'int(11)', 'Null' => 'NO', 'Key' => 'MUL', 'Default' => '', 'Extra' => ''],
            ['Field' => 'total_amount', 'Type' => 'decimal(10,2)', 'Null' => 'NO', 'Key' => '', 'Default' => '0.00', 'Extra' => ''],
            ['Field' => 'status', 'Type' => 'enum(\'pending\',\'completed\',\'cancelled\')', 'Null' => 'NO', 'Key' => '', 'Default' => 'pending', 'Extra' => '']
        ]
    ]
];

// Функция для имитации PDO
class DemoPDO {
    private $demo_tables;
    
    public function __construct($demo_tables) {
        $this->demo_tables = $demo_tables;
    }
    
    public function query($sql) {
        if (strpos($sql, 'SHOW TABLES') !== false) {
            return new DemoStatement(array_keys($this->demo_tables));
        } elseif (strpos($sql, 'SELECT VERSION()') !== false) {
            return new DemoStatement([['version' => '8.0.35-0ubuntu0.22.04.1']]);
        } elseif (strpos($sql, 'SELECT DATABASE()') !== false) {
            return new DemoStatement([['current_db' => 'alikf142_image']]);
        } elseif (strpos($sql, 'DESCRIBE') !== false) {
            // Извлекаем имя таблицы из SQL
            preg_match('/DESCRIBE\s+`?(\w+)`?/i', $sql, $matches);
            $table = $matches[1] ?? '';
            if (isset($this->demo_tables[$table])) {
                return new DemoStatement($this->demo_tables[$table]['fields']);
            }
        } elseif (strpos($sql, 'COUNT(*)') !== false) {
            // Извлекаем имя таблицы из SQL
            preg_match('/FROM\s+`?(\w+)`?/i', $sql, $matches);
            $table = $matches[1] ?? '';
            if (isset($this->demo_tables[$table])) {
                return new DemoStatement([['count' => $this->demo_tables[$table]['count']]]);
            }
        }
        return new DemoStatement([]);
    }
    
    public function exec($sql) {
        // Имитация выполнения SQL
        return 1;
    }
    
    public function prepare($sql) {
        return new DemoPreparedStatement();
    }
}

class DemoStatement {
    private $data;
    private $position = 0;
    
    public function __construct($data) {
        $this->data = $data;
    }
    
    public function fetch($mode = PDO::FETCH_ASSOC) {
        if ($this->position < count($this->data)) {
            return $this->data[$this->position++];
        }
        return false;
    }
    
    public function fetchAll($mode = PDO::FETCH_COLUMN) {
        if ($mode === PDO::FETCH_COLUMN) {
            return array_column($this->data, array_keys($this->data[0])[0]);
        }
        return $this->data;
    }
}

class DemoPreparedStatement {
    public function execute($params = []) {
        return true;
    }
}

// Создание демонстрационного объекта PDO
$pdo = new DemoPDO($demo_tables);

// Константы для совместимости
define('DB_HOST', 'localhost');
define('DB_NAME', 'alikf142_image');
define('DB_USER', 'alikf142_image');
define('DB_PASS', 'chVgj4U5');
?>