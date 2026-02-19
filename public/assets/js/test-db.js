const mysql = require('mysql2');

const connection = mysql.createConnection({
    host: 'localhost',
    port: 3306,
    user: 'root',
    password: '',
    database: 'sentinel_db'
});

connection.connect((err) => {
    if (err) {
        console.error('❌ MySQL Connection Failed!');
        console.error('Error:', err.message);
        console.error('\nPossible solutions:');
        console.error('1. Start MySQL in XAMPP Control Panel');
        console.error('2. Check if MySQL is using port 3306');
        console.error('3. Check if password is correct (default is empty)');
    } else {
        console.log('✅ MySQL Connected Successfully!');
        console.log('   Database: sentinel_db');
        connection.end();
    }
});